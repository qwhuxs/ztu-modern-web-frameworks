<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Для сторінок сайту замінює JSON-помилку 401 від lexik на перехід до форми входу.
 * Запити до /api і запити з заголовком Authorization отримують звичайний JSON 401.
 */
#[AsEventListener(event: Events::JWT_NOT_FOUND)]
#[AsEventListener(event: Events::JWT_INVALID)]
#[AsEventListener(event: Events::JWT_EXPIRED)]
final class JwtFailureListener
{
    /**
     * Отримує генератор адрес.
     *
     * @param UrlGeneratorInterface $urls Генератор адрес
     */
    public function __construct(private UrlGeneratorInterface $urls)
    {
    }

    /**
     * Якщо токена немає або він недійсний, веде браузер на сторінку входу й видаляє cookie.
     *
     * @param AuthenticationFailureEvent $event Подія помилки автентифікації lexik
     * @return void
     */
    public function __invoke(AuthenticationFailureEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request || str_starts_with($request->getPathInfo(), '/api/') || $request->headers->has('Authorization')) {
            return;
        }

        $response = new RedirectResponse($this->urls->generate('login'));
        $response->headers->clearCookie('jwt');
        $event->setResponse($response);
    }
}