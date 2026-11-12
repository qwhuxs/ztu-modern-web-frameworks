<?php

namespace App\Entity;

use App\Repository\DoctorRepository;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: DoctorRepository::class)]
#[ORM\Table(name: 'doctors')]
class Doctor implements JsonSerializable
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
     * Ім'я лікаря.
     *
     * @var string|null
     */
    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    /**
     * Прізвище лікаря.
     *
     * @var string|null
     */
    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    /**
     * Спеціальність.
     *
     * @var string|null
     */
    #[ORM\Column(length: 100)]
    private ?string $specialty = null;

    /**
     * Телефон.
     *
     * @var string|null
     */
    #[ORM\Column(length: 20)]
    private ?string $phone = null;

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
     * Повертає ім'я лікаря.
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Встановлює ім'я лікаря.
     *
     * @param string $firstName Нове значення
     * @return self
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Повертає прізвище лікаря.
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Встановлює прізвище лікаря.
     *
     * @param string $lastName Нове значення
     * @return self
     */
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Повертає спеціальність.
     *
     * @return string|null
     */
    public function getSpecialty(): ?string
    {
        return $this->specialty;
    }

    /**
     * Встановлює спеціальність.
     *
     * @param string $specialty Нове значення
     * @return self
     */
    public function setSpecialty(string $specialty): self
    {
        $this->specialty = $specialty;

        return $this;
    }

    /**
     * Повертає телефон.
     *
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * Встановлює телефон.
     *
     * @param string $phone Нове значення
     * @return self
     */
    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

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
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'specialty' => $this->specialty,
            'phone' => $this->phone,
        ];
    }
}