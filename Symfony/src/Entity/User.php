<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_CLIENT = 'client';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_ADMIN = 'admin';

    /** Доступні ролі: client, manager, admin. */
    public const ROLES = [self::ROLE_CLIENT, self::ROLE_MANAGER, self::ROLE_ADMIN];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 150, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 20)]
    private string $role = self::ROLE_CLIENT;

    /** Повертає ідентифікатор. */
    public function getId(): ?int { return $this->id; }

    /** Повертає ім'я. */
    public function getName(): ?string { return $this->name; }
    /** Встановлює ім'я. */
    public function setName(string $name): void { $this->name = $name; }

    /** Повертає пошту. */
    public function getEmail(): ?string { return $this->email; }
    /** Встановлює пошту. */
    public function setEmail(string $email): void { $this->email = mb_strtolower(trim($email)); }

    /** Повертає роль: client, manager або admin. */
    public function getRole(): string { return $this->role; }
    /** Встановлює роль: client, manager або admin. */
    public function setRole(string $role): void { $this->role = $role; }

    /**
     * Повертає роль у форматі Symfony Security, наприклад ROLE_ADMIN.
     * Права нижчих ролей додає role_hierarchy з security.yaml.
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        return ['ROLE_'.strtoupper($this->role)];
    }

    /**
     * Повертає унікальний ідентифікатор користувача для Symfony Security (пошту).
     *
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Повертає хеш пароля.
     *
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Зберігає хеш пароля, створений UserPasswordHasherInterface.
     *
     * @param string $hashedPassword Хеш пароля
     * @return void
     */
    public function setPassword(string $hashedPassword): void
    {
        $this->password = $hashedPassword;
    }

    /**
     * Очищає тимчасові секретні дані. Пароль у відкритому вигляді не зберігається, тому метод порожній.
     *
     * @return void
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * Повертає дані користувача без пароля для відповіді API.
     *
     * @return array
     */
    public function toArray(): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'email' => $this->email, 'role' => $this->role];
    }
}