<?php

namespace App\Entity;

use App\Repository\PatientRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: PatientRepository::class)]
#[ORM\Table(name: 'appointments')]
class Appointment implements JsonSerializable
{
    /**
     * Ідентифікатор запису.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Пацієнт.
     * Зв'язок: багато прийомів можуть належати одному пацієнту.
     *
     * @var Patient|null
     */
    #[ORM\ManyToOne(targetEntity: Patient::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    /**
     * Лікар.
     * Зв'язок: багато прийомів можуть належати одному лікарю.
     *
     * @var Doctor|null
     */
    #[ORM\ManyToOne(targetEntity: Doctor::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Doctor $doctor = null;

    /**
     * Дата і час прийому.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $appointmentAt = null;

    /**
     * Скарги пацієнта.
     *
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $complaints = null;

    /**
     * Повертає ідентифікатор.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Повертає пацієнта.
     *
     * @return Patient|null
     */
    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    /**
     * Встановлює пацієнта.
     *
     * @param Patient $patient Нове значення
     * @return self
     */
    public function setPatient(Patient $patient): self
    {
        $this->patient = $patient;

        return $this;
    }

    /**
     * Повертає лікаря.
     *
     * @return Doctor|null
     */
    public function getDoctor(): ?Doctor
    {
        return $this->doctor;
    }

    /**
     * Встановлює лікаря.
     *
     * @param Doctor $doctor Нове значення
     * @return self
     */
    public function setDoctor(Doctor $doctor): self
    {
        $this->doctor = $doctor;

        return $this;
    }

    /**
     * Повертає дату і час прийому.
     *
     * @return \DateTimeImmutable|null
     */
    public function getAppointmentAt(): ?\DateTimeImmutable
    {
        return $this->appointmentAt;
    }

    /**
     * Встановлює дату і час прийому.
     *
     * @param \DateTimeImmutable $appointmentAt Нове значення
     * @return self
     */
    public function setAppointmentAt(\DateTimeImmutable $appointmentAt): self
    {
        $this->appointmentAt = $appointmentAt;

        return $this;
    }

    /**
     * Повертає скарги пацієнта.
     *
     * @return string|null
     */
    public function getComplaints(): ?string
    {
        return $this->complaints;
    }

    /**
     * Встановлює скарги пацієнта.
     *
     * @param string|null $complaints Нове значення
     * @return self
     */
    public function setComplaints(?string $complaints): self
    {
        $this->complaints = $complaints;

        return $this;
    }

    /**
     * Повертає дані запису у вигляді масиву для JSON-відповіді.
     * Викликається автоматично, коли об'єкт передається в json_encode() або $this->json().
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'patient' => $this->patient ? [
                'id' => $this->patient->getId(),
                'full_name' => $this->patient->getLastName().' '.$this->patient->getFirstName(),
            ] : null,
            'doctor' => $this->doctor ? [
                'id' => $this->doctor->getId(),
                'full_name' => $this->doctor->getLastName().' '.$this->doctor->getFirstName(),
                'specialty' => $this->doctor->getSpecialty(),
            ] : null,
            'appointment_at' => $this->appointmentAt?->format('Y-m-d H:i'),
            'complaints' => $this->complaints,
        ];
    }
}