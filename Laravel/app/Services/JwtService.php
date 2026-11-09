<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    /**
     * Створює підписаний JWT-токен для користувача.
     *
     * @param User $user Користувач, для якого створюється токен
     * @return string Токен у форматі header.payload.signature
     */
    public function createToken(User $user): string
    {
        $now = time();

        return JWT::encode([
            'iss' => config('app.url'),
            'sub' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'iat' => $now,
            'exp' => $now + $this->ttlMinutes() * 60,
        ], $this->secret(), 'HS256');
    }

    /**
     * Перевіряє підпис і термін дії токена та повертає його дані.
     *
     * @param string $token Токен з cookie або заголовка Authorization
     * @return array|null Дані токена або null, якщо токен недійсний
     */
    public function decode(string $token): ?array
    {
        try {
            return (array) JWT::decode($token, new Key($this->secret(), 'HS256'));
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Повертає час життя токена у хвилинах.
     *
     * @return int
     */
    public function ttlMinutes(): int
    {
        return config('jwt.ttl', 60);
    }

    /**
     * Повертає секретний ключ із налаштувань.
     *
     * @return string
     */
    private function secret(): string
    {
        $secret = (string) config('jwt.secret');

        if (strlen($secret) < 32) {
            throw new \RuntimeException('Задайте JWT_SECRET у файлі .env (щонайменше 32 символи).');
        }

        return $secret;
    }
}