<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'treatments')]
class Treatment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Diagnosis::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Diagnosis $diagnosis = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $prescription = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    /** Повертає ідентифікатор. */
    public function getId(): ?int { return $this->id; }

    /** Повертає діагноз. */
    public function getDiagnosis(): ?Diagnosis { return $this->diagnosis; }
    /** Встановлює діагноз. */
    public function setDiagnosis(Diagnosis $diagnosis): void { $this->diagnosis = $diagnosis; }

    /** Повертає призначення. */
    public function getPrescription(): ?string { return $this->prescription; }
    /** Встановлює призначення. */
    public function setPrescription(string $prescription): void { $this->prescription = $prescription; }

    /** Повертає дату початку. */
    public function getStartDate(): ?\DateTimeImmutable { return $this->startDate; }
    /** Встановлює дату початку. */
    public function setStartDate(\DateTimeImmutable $startDate): void { $this->startDate = $startDate; }

    /** Повертає дату завершення. */
    public function getEndDate(): ?\DateTimeImmutable { return $this->endDate; }
    /** Встановлює дату завершення. */
    public function setEndDate(?\DateTimeImmutable $endDate): void { $this->endDate = $endDate; }
}