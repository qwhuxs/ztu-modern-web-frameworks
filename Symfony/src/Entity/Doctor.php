<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'doctors')]
class Doctor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 100)]
    private ?string $specialty = null;

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

    /** Повертає спеціальність. */
    public function getSpecialty(): ?string { return $this->specialty; }
    /** Встановлює спеціальність. */
    public function setSpecialty(string $specialty): void { $this->specialty = $specialty; }

    /** Повертає телефон. */
    public function getPhone(): ?string { return $this->phone; }
    /** Встановлює телефон. */
    public function setPhone(string $phone): void { $this->phone = $phone; }
}