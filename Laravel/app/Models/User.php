<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_CLIENT = 'client';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_ADMIN = 'admin';

    /** Рівні доступу: що більше число, то більше прав. */
    public const ROLE_LEVELS = [
        self::ROLE_CLIENT => 1,
        self::ROLE_MANAGER => 2,
        self::ROLE_ADMIN => 3,
    ];

    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    /**
     * Повертає правила перетворення полів.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
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
}