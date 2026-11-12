<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'patients')]
class Patient implements JsonSerializable
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
     * Ім'я пацієнта.
     *
     * @var string|null
     */
    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    /**
     * Прізвище пацієнта.
     *
     * @var string|null
     */
    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    /**
     * Дата народження.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $birthDate = null;

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
     * Повертає ім'я пацієнта.
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Встановлює ім'я пацієнта.
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
     * Повертає прізвище пацієнта.
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Встановлює прізвище пацієнта.
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
     * Повертає дату народження.
     *
     * @return \DateTimeImmutable|null
     */
    public function getBirthDate(): ?\DateTimeImmutable
    {
        return $this->birthDate;
    }

    /**
     * Встановлює дату народження.
     *
     * @param \DateTimeImmutable $birthDate Нове значення
     * @return self
     */
    public function setBirthDate(\DateTimeImmutable $birthDate): self
    {
        $this->birthDate = $birthDate;

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
            'birth_date' => $this->birthDate?->format('Y-m-d'),
            'phone' => $this->phone,
        ];
    }
}