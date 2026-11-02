<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FiltersRecords
{
    /**
     * Додає до запиту умови для заповнених полів фільтра.
     *
     * @param Builder $query Запит до таблиці
     * @param Request $request Поточний запит із параметрами фільтра
     * @param array $filters Поля таблиці та типи пошуку: text, exact або date
     * @return void
     */
    private function applyFilters(Builder $query, Request $request, array $filters): void
    {
        foreach ($filters as $field => $type) {
            $value = $request->query($field);

            if (!is_string($value) || trim($value) === '') {
                continue;
            }

            $value = trim($value);

            if ($type === 'text') {
                $query->where($field, 'like', '%'.addcslashes($value, '%_\\').'%');
            } elseif ($type === 'exact' && ctype_digit($value)) {
                $query->where($field, (int) $value);
            } elseif ($type === 'date') {
                $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

                if ($date) {
                    $query->whereDate($field, $date->format('Y-m-d'));
                }
            }
        }
    }

    /**
     * Визначає кількість записів на сторінці.
     *
     * @param Request $request Поточний запит із параметром itemsPerPage
     * @return int Дозволена кількість записів
     */
    private function itemsPerPage(Request $request): int
    {
        $value = (int) $request->query('itemsPerPage', 10);

        return in_array($value, [5, 10, 20, 50], true) ? $value : 10;
    }
}