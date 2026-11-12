<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Фільтрація за полями таблиці та пагінація для списків.
 * Контролер задає константу FIELDS: [поле => EQUAL, LIKE або BETWEEN].
 */
trait FiltersRecords
{
    /**
     * Додає до запиту умови для кожного заповненого фільтра.
     * EQUAL - точний збіг, LIKE - пошук частини тексту,
     * BETWEEN - діапазон (field[gte], field[lte]) або точна дата.
     *
     * @param Builder $query Запит до таблиці
     * @param array $criteria Параметри запиту
     * @param array<string, string> $fields Поля фільтрації та їхні типи
     * @return Builder
     */
    protected function filterData(Builder $query, array $criteria, array $fields): Builder
    {
        foreach ($fields as $field => $type) {
            if (!isset($criteria[$field]) || $criteria[$field] === '' || $criteria[$field] === []) {
                continue;
            }

            $value = $criteria[$field];

            if ($type === 'LIKE' && is_string($value)) {
                $query->where($field, 'like', '%'.addcslashes($value, '%_\\').'%');
            } elseif ($type === 'EQUAL' && is_scalar($value)) {
                $query->where($field, $value);
            } elseif ($type === 'BETWEEN') {
                if (is_array($value)) {
                    if (!empty($value['gte'])) {
                        $query->where($field, '>=', $value['gte']);
                    }
                    if (!empty($value['lte'])) {
                        $query->where($field, '<=', $value['lte']);
                    }
                } else {
                    $query->whereDate($field, $value);
                }
            }
        }

        return $query;
    }

    /**
     * Розбиває результат на сторінки.
     * Записи віддаються масивом моделей, тому кожна з них перетворюється на JSON своїм методом jsonSerialize().
     *
     * @param Builder $query Запит до таблиці
     * @param array $criteria Параметри запиту з page та itemsPerPage
     * @return array{data: array, meta: array{page: int, itemsPerPage: int, total: int, pages: int}}
     */
    protected function paginate(Builder $query, array $criteria): array
    {
        $page = max(1, (int) ($criteria['page'] ?? 1));
        $itemsPerPage = min(100, max(1, (int) ($criteria['itemsPerPage'] ?? 10)));

        $paginator = $query->paginate($itemsPerPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->items(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'itemsPerPage' => $paginator->perPage(),
                'total' => $paginator->total(),
                'pages' => $paginator->lastPage(),
            ],
        ];
    }
}