<?php

namespace App\Repository;

use App\Entity\Treatment;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторій таблиці з фільтрацією за всіма полями та пагінацією.
 *
 * @extends FilterableRepository<Treatment>
 */
class TreatmentRepository extends FilterableRepository
{
    /**
     * Поля, за якими можна фільтрувати: [параметр запиту => тип пошуку].
     *
     * @var array<string, string>
     */
    public const FIELDS = [
        'id' => 'EQUAL',
        'diagnosis_id' => 'EQUAL',
        'prescription' => 'LIKE',
        'start_date' => 'BETWEEN',
        'end_date' => 'BETWEEN',
    ];

    /**
     * Відповідність параметра запиту полю сутності.
     *
     * @var array<string, string>
     */
    protected const COLUMNS = [
        'diagnosis_id' => 'diagnosis',
        'start_date' => 'startDate',
        'end_date' => 'endDate',
    ];

    /**
     * Сортування за замовчуванням.
     *
     * @var array<string, string>
     */
    protected const DEFAULT_ORDER = ['id' => 'DESC'];

    /**
     * Отримує реєстр Doctrine і вказує, з якою сутністю працює репозиторій.
     *
     * @param ManagerRegistry $registry Реєстр Doctrine
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Treatment::class);
    }
}