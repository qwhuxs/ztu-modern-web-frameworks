<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'appointments')]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Patient::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    #[ORM\ManyToOne(targetEntity: Doctor::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Doctor $doctor = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $appointmentAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $complaints = null;

    /** Повертає ідентифікатор. */
    public function getId(): ?int { return $this->id; }

    /** Повертає пацієнта. */
    public function getPatient(): ?Patient { return $this->patient; }
    /** Встановлює пацієнта. */
    public function setPatient(Patient $patient): void { $this->patient = $patient; }

    /** Повертає лікаря. */
    public function getDoctor(): ?Doctor { return $this->doctor; }
    /** Встановлює лікаря. */
    public function setDoctor(Doctor $doctor): void { $this->doctor = $doctor; }

    /** Повертає дату і час прийому. */
    public function getAppointmentAt(): ?\DateTimeImmutable { return $this->appointmentAt; }
    /** Встановлює дату і час прийому. */
    public function setAppointmentAt(\DateTimeImmutable $appointmentAt): void { $this->appointmentAt = $appointmentAt; }

    /** Повертає скарги. */
    public function getComplaints(): ?string { return $this->complaints; }
    /** Встановлює скарги. */
    public function setComplaints(?string $complaints): void { $this->complaints = $complaints; }
}