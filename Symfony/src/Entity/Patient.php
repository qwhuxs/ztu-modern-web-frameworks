<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'patients')]
class Patient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $birthDate = null;

    #[ORM\Column(length: 20)]
    private ?string $phone = null;

    /** Повертає ідентифікатор. */
    public function getId(): ?int { return $this->id; }

    /** Повертає ім'я. */
    public function getFirstName(): ?string { return $this->firstName; }
    /** Встановлює ім'я. */
    public function setFirstName(string $firstName): void { $this->firstName = $firstName; }

    /** Повертає прізвище. */
    public function getLastName(): ?string { return $this->lastName; }
    /** Встановлює прізвище. */
    public function setLastName(string $lastName): void { $this->lastName = $lastName; }

    /** Повертає дату народження. */
    public function getBirthDate(): ?\DateTimeImmutable { return $this->birthDate; }
    /** Встановлює дату народження. */
    public function setBirthDate(\DateTimeImmutable $birthDate): void { $this->birthDate = $birthDate; }

    /** Повертає телефон. */
    public function getPhone(): ?string { return $this->phone; }
    /** Встановлює телефон. */
    public function setPhone(string $phone): void { $this->phone = $phone; }
}