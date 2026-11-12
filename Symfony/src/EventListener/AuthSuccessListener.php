<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Додає дані користувача до відповіді POST /api/login (поруч із token).
 */
#[AsEventListener(event: Events::AUTHENTICATION_SUCCESS)]
final class AuthSuccessListener
{
    /**
     * Додає до відповіді логіну поле user з id, ім'ям, поштою та роллю.
     *
     * @param AuthenticationSuccessEvent $event Подія успішного входу lexik
     * @return void
     */
    public function __invoke(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $data = $event->getData();
        $data['user'] = $user->toArray();
        $event->setData($data);
    }
}