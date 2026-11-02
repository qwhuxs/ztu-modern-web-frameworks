<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'diagnoses')]
class Diagnosis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Appointment::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Appointment $appointment = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /** Повертає ідентифікатор. */
    public function getId(): ?int { return $this->id; }

    /** Повертає прийом. */
    public function getAppointment(): ?Appointment { return $this->appointment; }
    /** Встановлює прийом. */
    public function setAppointment(Appointment $appointment): void { $this->appointment = $appointment; }

    /** Повертає назву діагнозу. */
    public function getName(): ?string { return $this->name; }
    /** Встановлює назву діагнозу. */
    public function setName(string $name): void { $this->name = $name; }

    /** Повертає опис. */
    public function getDescription(): ?string { return $this->description; }
    /** Встановлює опис. */
    public function setDescription(?string $description): void { $this->description = $description; }
}