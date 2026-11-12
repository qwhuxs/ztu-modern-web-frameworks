<?php

namespace App\Repository;

use App\Entity\Appointment;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторій таблиці з фільтрацією за всіма полями та пагінацією.
 *
 * @extends FilterableRepository<Appointment>
 */
class AppointmentRepository extends FilterableRepository
{
    /**
     * Поля, за якими можна фільтрувати: [параметр запиту => тип пошуку].
     *
     * @var array<string, string>
     */
    public const FIELDS = [
        'id' => 'EQUAL',
        'patient_id' => 'EQUAL',
        'doctor_id' => 'EQUAL',
        'appointment_at' => 'BETWEEN',
        'complaints' => 'LIKE',
    ];

    /**
     * Відповідність параметра запиту полю сутності.
     *
     * @var array<string, string>
     */
    protected const COLUMNS = [
        'patient_id' => 'patient',
        'doctor_id' => 'doctor',
        'appointment_at' => 'appointmentAt',
    ];

    /**
     * Сортування за замовчуванням.
     *
     * @var array<string, string>
     */
    protected const DEFAULT_ORDER = ['appointmentAt' => 'DESC'];

    /**
     * Отримує реєстр Doctrine і вказує, з якою сутністю працює репозиторій.
     *
     * @param ManagerRegistry $registry Реєстр Doctrine
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }
}