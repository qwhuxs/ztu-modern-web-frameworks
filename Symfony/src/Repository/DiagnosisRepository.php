<?php

namespace App\Repository;

use App\Entity\Diagnosis;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторій таблиці з фільтрацією за всіма полями та пагінацією.
 *
 * @extends FilterableRepository<Diagnosis>
 */
class DiagnosisRepository extends FilterableRepository
{
    /**
     * Поля, за якими можна фільтрувати: [параметр запиту => тип пошуку].
     *
     * @var array<string, string>
     */
    public const FIELDS = [
        'id' => 'EQUAL',
        'appointment_id' => 'EQUAL',
        'name' => 'LIKE',
        'description' => 'LIKE',
    ];

    /**
     * Відповідність параметра запиту полю сутності.
     *
     * @var array<string, string>
     */
    protected const COLUMNS = [
        'appointment_id' => 'appointment',
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
        parent::__construct($registry, Diagnosis::class);
    }
}