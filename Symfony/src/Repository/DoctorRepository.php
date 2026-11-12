<?php

namespace App\Repository;

use App\Entity\Doctor;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторій таблиці з фільтрацією за всіма полями та пагінацією.
 *
 * @extends FilterableRepository<Doctor>
 */
class DoctorRepository extends FilterableRepository
{
    /**
     * Поля, за якими можна фільтрувати: [параметр запиту => тип пошуку].
     *
     * @var array<string, string>
     */
    public const FIELDS = [
        'id' => 'EQUAL',
        'first_name' => 'LIKE',
        'last_name' => 'LIKE',
        'specialty' => 'LIKE',
        'phone' => 'LIKE',
    ];

    /**
     * Відповідність параметра запиту полю сутності.
     *
     * @var array<string, string>
     */
    protected const COLUMNS = [
        'first_name' => 'firstName',
        'last_name' => 'lastName',
    ];

    /**
     * Сортування за замовчуванням.
     *
     * @var array<string, string>
     */
    protected const DEFAULT_ORDER = ['lastName' => 'ASC', 'firstName' => 'ASC'];

    /**
     * Отримує реєстр Doctrine і вказує, з якою сутністю працює репозиторій.
     *
     * @param ManagerRegistry $registry Реєстр Doctrine
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Doctor::class);
    }
}