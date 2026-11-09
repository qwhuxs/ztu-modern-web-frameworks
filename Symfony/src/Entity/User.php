<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User
{
    public const ROLE_CLIENT = 'client';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_ADMIN = 'admin';

    /** Рівні доступу: що більше число, то більше прав. */
    public const ROLE_LEVELS = [
        self::ROLE_CLIENT => 1,
        self::ROLE_MANAGER => 2,
        self::ROLE_ADMIN => 3,
    ];

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

    /** Повертає роль. */
    public function getRole(): string { return $this->role; }
    /** Встановлює роль. */
    public function setRole(string $role): void { $this->role = $role; }

    /**
     * Зберігає пароль у вигляді хешу (сам пароль у базі не зберігається).
     *
     * @param string $plainPassword Пароль, який ввів користувач
     * @return void
     */
    public function setPlainPassword(string $plainPassword): void
    {
        $this->password = password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    /**
     * Перевіряє, чи збігається пароль із збереженим хешем.
     *
     * @param string $plainPassword Пароль, який ввів користувач
     * @return bool
     */
    public function checkPassword(string $plainPassword): bool
    {
        return $this->password !== null && password_verify($plainPassword, $this->password);
    }

    /**
     * Перевіряє, чи має користувач указану роль або вищу.
     * Наприклад, admin має всі права manager і client.
     *
     * @param string $role Мінімальна потрібна роль
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return (self::ROLE_LEVELS[$this->role] ?? 0) >= (self::ROLE_LEVELS[$role] ?? PHP_INT_MAX);
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