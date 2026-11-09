<?php

namespace App\Security;

use App\Entity\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class JwtService
{
    public const TTL = 3600;

    /**
     * Отримує секретний ключ для підпису токенів зі змінної середовища JWT_SECRET.
     *
     * @param string $secret Секретний ключ
     */
    public function __construct(#[Autowire('%env(JWT_SECRET)%')] private string $secret)
    {
    }

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
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'iat' => $now,
            'exp' => $now + self::TTL,
        ], $this->key(), 'HS256');
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
            return (array) JWT::decode($token, new Key($this->key(), 'HS256'));
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Повертає секретний ключ і перевіряє його довжину.
     *
     * @return string
     */
    private function key(): string
    {
        if (strlen($this->secret) < 32) {
            throw new \RuntimeException('Задайте JWT_SECRET у файлі .env.local (щонайменше 32 символи).');
        }

        return $this->secret;
    }
}