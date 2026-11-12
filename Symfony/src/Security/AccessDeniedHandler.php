<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Twig\Environment;

/**
 * Показує помилку 403, коли роль користувача не дозволяє дію.
 * Підключений у config/packages/security.yaml (access_denied_handler).
 */
class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    /**
     * Отримує шаблонізатор для сторінки помилки.
     *
     * @param Environment $twig Шаблонізатор Twig
     */
    public function __construct(private Environment $twig)
    {
    }

    /**
     * Повертає 403 у форматі JSON для API або HTML-сторінку для браузера.
     *
     * @param Request $request Поточний запит
     * @param AccessDeniedException $accessDeniedException Причина відмови
     * @return Response|null
     */
    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        if (str_starts_with($request->getPathInfo(), '/api/') || $request->headers->has('Authorization')) {
            return new JsonResponse(['message' => 'Недостатньо прав для цієї дії.'], 403);
        }

        return new Response($this->twig->render('medical/forbidden.html.twig'), 403);
    }
}