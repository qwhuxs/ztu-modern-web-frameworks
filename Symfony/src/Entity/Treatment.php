<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'treatments')]
class Treatment implements JsonSerializable
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
     * Діагноз, за яким призначено лікування.
     * Зв'язок: лікування призначено за конкретним діагнозом.
     *
     * @var Diagnosis|null
     */
    #[ORM\ManyToOne(targetEntity: Diagnosis::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Diagnosis $diagnosis = null;

    /**
     * Призначення.
     *
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT)]
    private ?string $prescription = null;

    /**
     * Дата початку лікування.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $startDate = null;

    /**
     * Дата завершення лікування.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

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
     * Повертає діагноз, за яким призначено лікування.
     *
     * @return Diagnosis|null
     */
    public function getDiagnosis(): ?Diagnosis
    {
        return $this->diagnosis;
    }

    /**
     * Встановлює діагноз, за яким призначено лікування.
     *
     * @param Diagnosis $diagnosis Нове значення
     * @return self
     */
    public function setDiagnosis(Diagnosis $diagnosis): self
    {
        $this->diagnosis = $diagnosis;

        return $this;
    }

    /**
     * Повертає призначення.
     *
     * @return string|null
     */
    public function getPrescription(): ?string
    {
        return $this->prescription;
    }

    /**
     * Встановлює призначення.
     *
     * @param string $prescription Нове значення
     * @return self
     */
    public function setPrescription(string $prescription): self
    {
        $this->prescription = $prescription;

        return $this;
    }

    /**
     * Повертає дату початку лікування.
     *
     * @return \DateTimeImmutable|null
     */
    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    /**
     * Встановлює дату початку лікування.
     *
     * @param \DateTimeImmutable $startDate Нове значення
     * @return self
     */
    public function setStartDate(\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Повертає дату завершення лікування.
     *
     * @return \DateTimeImmutable|null
     */
    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    /**
     * Встановлює дату завершення лікування.
     *
     * @param \DateTimeImmutable|null $endDate Нове значення
     * @return self
     */
    public function setEndDate(?\DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

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
            'diagnosis' => $this->diagnosis ? [
                'id' => $this->diagnosis->getId(),
                'name' => $this->diagnosis->getName(),
            ] : null,
            'prescription' => $this->prescription,
            'start_date' => $this->startDate?->format('Y-m-d'),
            'end_date' => $this->endDate?->format('Y-m-d'),
        ];
    }
}