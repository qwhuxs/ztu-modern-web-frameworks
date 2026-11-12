<?php

namespace App\Entity;

use App\Repository\DiagnosisRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: DiagnosisRepository::class)]
#[ORM\Table(name: 'diagnoses')]
class Diagnosis implements JsonSerializable
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
     * Прийом, на якому поставлено діагноз.
     * Зв'язок: діагноз поставлено на конкретному прийомі.
     *
     * @var Appointment|null
     */
    #[ORM\ManyToOne(targetEntity: Appointment::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Appointment $appointment = null;

    /**
     * Назва діагнозу.
     *
     * @var string|null
     */
    #[ORM\Column(length: 150)]
    private ?string $name = null;

    /**
     * Опис діагнозу.
     *
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

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
     * Повертає прийом, на якому поставлено діагноз.
     *
     * @return Appointment|null
     */
    public function getAppointment(): ?Appointment
    {
        return $this->appointment;
    }

    /**
     * Встановлює прийом, на якому поставлено діагноз.
     *
     * @param Appointment $appointment Нове значення
     * @return self
     */
    public function setAppointment(Appointment $appointment): self
    {
        $this->appointment = $appointment;

        return $this;
    }

    /**
     * Повертає назву діагнозу.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Встановлює назву діагнозу.
     *
     * @param string $name Нове значення
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Повертає опис діагнозу.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Встановлює опис діагнозу.
     *
     * @param string|null $description Нове значення
     * @return self
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
            'appointment' => $this->appointment ? [
                'id' => $this->appointment->getId(),
                'appointment_at' => $this->appointment->getAppointmentAt()?->format('Y-m-d H:i'),
                'patient_id' => $this->appointment->getPatient()?->getId(),
            ] : null,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}