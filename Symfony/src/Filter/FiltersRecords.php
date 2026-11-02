<?php

namespace App\Filter;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

trait FiltersRecords
{
    /**
     * Додає до запиту умови для заповнених полів фільтра.
     *
     * @param QueryBuilder $qb Запит до таблиці
     * @param Request $request Поточний запит із параметрами фільтра
     * @param array $filters Параметри адреси, поля сутності та типи пошуку
     * @return void
     */
    private function applyFilters(QueryBuilder $qb, Request $request, array $filters): void
    {
        $number = 0;

        foreach ($filters as $param => [$field, $type]) {
            $value = trim((string) $request->query->get($param));

            if ($value === '') {
                continue;
            }

            $number++;
            $name = 'filter'.$number;

            if ($type === 'text') {
                $qb->andWhere("$field LIKE :$name")
                    ->setParameter($name, '%'.addcslashes($value, '%_\\').'%');
            } elseif ($type === 'exact' && ctype_digit($value)) {
                $qb->andWhere("$field = :$name")
                    ->setParameter($name, (int) $value);
            } elseif ($type === 'date') {
                $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

                if ($date) {
                    $qb->andWhere("$field >= :{$name}from AND $field < :{$name}to")
                        ->setParameter($name.'from', $date, Types::DATETIME_IMMUTABLE)
                        ->setParameter($name.'to', $date->modify('+1 day'), Types::DATETIME_IMMUTABLE);
                }
            }
        }
    }

    /**
     * Розбиває результат запиту на сторінки.
     *
     * @param QueryBuilder $qb Запит до таблиці
     * @param Request $request Поточний запит із параметрами page та itemsPerPage
     * @return array Записи поточної сторінки та дані пагінації
     */
    private function paginate(QueryBuilder $qb, Request $request): array
    {
        $itemsPerPage = (int) $request->query->get('itemsPerPage', 10);
        if (!in_array($itemsPerPage, [5, 10, 20, 50], true)) {
            $itemsPerPage = 10;
        }

        $query = $qb->getQuery();
        $paginator = new Paginator($query, false);
        $total = count($paginator);
        $pages = max(1, (int) ceil($total / $itemsPerPage));
        $page = min($pages, max(1, (int) $request->query->get('page', 1)));

        $query->setFirstResult(($page - 1) * $itemsPerPage)->setMaxResults($itemsPerPage);

        return [
            'items' => $paginator,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'itemsPerPage' => $itemsPerPage,
        ];
    }
}