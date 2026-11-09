<?php

namespace App\Security;

/**
 * Позначає контролер або його метод мінімальною роллю, потрібною для доступу.
 * Атрибут на методі має перевагу над атрибутом на класі.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class RequireRole
{
    /**
     * Зберігає потрібну роль.
     *
     * @param string $role Роль: client, manager або admin
     */
    public function __construct(public string $role)
    {
    }
}