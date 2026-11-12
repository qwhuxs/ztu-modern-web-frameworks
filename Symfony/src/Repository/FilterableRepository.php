<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Базовий репозиторій з фільтрацією та пагінацією.
 * Дочірні репозиторії задають константи FIELDS, COLUMNS і DEFAULT_ORDER.
 *
 * @template T of object
 * @extends ServiceEntityRepository<T>
 */
abstract class FilterableRepository extends ServiceEntityRepository
{
    /**
     * Поля, за якими можна фільтрувати: [параметр запиту => тип пошуку].
     * EQUAL - точний збіг, LIKE - пошук частини тексту, BETWEEN - діапазон (field[gte], field[lte]) або точне значення.
     *
     * @var array<string, string>
     */
    public const FIELDS = [];

    /**
     * Відповідність параметра запиту полю сутності, якщо назви відрізняються (first_name => firstName).
     *
     * @var array<string, string>
     */
    protected const COLUMNS = [];

    /**
     * Сортування за замовчуванням: [поле сутності => ASC або DESC].
     *
     * @var array<string, string>
     */
    protected const DEFAULT_ORDER = ['id' => 'ASC'];

    /**
     * Найменша й найбільша дозволена кількість записів на сторінці.
     */
    public const MIN_ITEMS_PER_PAGE = 1;
    public const MAX_ITEMS_PER_PAGE = 100;

    /**
     * Повертає відфільтровані записи поточної сторінки та дані пагінації.
     *
     * @param array $criteria Параметри запиту: фільтри, page та itemsPerPage
     * @return array{data: array, meta: array{page: int, itemsPerPage: int, total: int, pages: int}}
     */
    public function findByCriteria(array $criteria = []): array
    {
        $queryBuilder = $this->createQueryBuilder('e');
        $this->filterData($queryBuilder, $criteria);

        foreach (static::DEFAULT_ORDER as $field => $direction) {
            $queryBuilder->addOrderBy("e.$field", $direction);
        }

        $page = max(1, (int) ($criteria['page'] ?? 1));
        $itemsPerPage = (int) ($criteria['itemsPerPage'] ?? 10);
        $itemsPerPage = min(self::MAX_ITEMS_PER_PAGE, max(self::MIN_ITEMS_PER_PAGE, $itemsPerPage));

        $queryBuilder
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);

        $paginator = new Paginator($queryBuilder->getQuery(), false);
        $total = count($paginator);

        return [
            'data' => iterator_to_array($paginator, false),
            'meta' => [
                'page' => $page,
                'itemsPerPage' => $itemsPerPage,
                'total' => $total,
                'pages' => (int) ceil($total / $itemsPerPage),
            ],
        ];
    }

    /**
     * Додає до запиту умови для кожного заповненого фільтра з FIELDS.
     *
     * @param QueryBuilder $queryBuilder Запит до таблиці
     * @param array $criteria Параметри запиту
     * @return QueryBuilder
     */
    public function filterData(QueryBuilder $queryBuilder, array $criteria = []): QueryBuilder
    {
        $alias = $queryBuilder->getRootAliases()[0];

        foreach (static::FIELDS as $key => $type) {
            if (!isset($criteria[$key]) || $criteria[$key] === '' || $criteria[$key] === []) {
                continue;
            }

            $value = $criteria[$key];
            $field = $alias.'.'.(static::COLUMNS[$key] ?? $key);

            if ($type === 'LIKE' && is_string($value)) {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->like($field, ":$key"))
                    ->setParameter($key, '%'.addcslashes($value, '%_\\').'%');
            } elseif ($type === 'EQUAL' && is_scalar($value)) {
                $queryBuilder
                    ->andWhere("$field = :$key")
                    ->setParameter($key, $value);
            } elseif ($type === 'BETWEEN') {
                if (is_array($value)) {
                    if (!empty($value['gte'])) {
                        $queryBuilder
                            ->andWhere("$field >= :{$key}Gte")
                            ->setParameter("{$key}Gte", $value['gte']);
                    }
                    if (!empty($value['lte'])) {
                        $queryBuilder
                            ->andWhere("$field <= :{$key}Lte")
                            ->setParameter("{$key}Lte", $value['lte']);
                    }
                } else {
                    $queryBuilder
                        ->andWhere("$field = :$key")
                        ->setParameter($key, $value);
                }
            }
        }

        return $queryBuilder;
    }
}