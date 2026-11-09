<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Security\JwtService;
use App\Security\RequireRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class JwtSubscriber implements EventSubscriberInterface
{
    /**
     * Отримує сервіси, потрібні для перевірки доступу.
     *
     * @param JwtService $jwt Сервіс JWT
     * @param EntityManagerInterface $em Менеджер сутностей Doctrine
     * @param UrlGeneratorInterface $urls Генератор адрес
     * @param Environment $twig Шаблонізатор Twig
     */
    public function __construct(
        private JwtService $jwt,
        private EntityManagerInterface $em,
        private UrlGeneratorInterface $urls,
        private Environment $twig,
    ) {
    }

    /**
     * Підписується на подію перед запуском контролера.
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => 'onController'];
    }

    /**
     * Визначає користувача за JWT і перевіряє, чи дозволяє його роль запустити контролер.
     * Користувач зберігається в атрибуті запиту user, щоб шаблони знали, хто увійшов.
     *
     * @param ControllerEvent $event Подія з контролером і запитом
     * @return void
     */
    public function onController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        $user = $this->resolveUser($request);
        $request->attributes->set('user', $user);

        $role = $this->requiredRole($event->getController());

        if ($role === null) {
            return;
        }

        if (!$user) {
            $response = $this->wantsJson($request)
                ? new JsonResponse(['message' => 'Потрібна автентифікація.'], 401)
                : new RedirectResponse($this->urls->generate('login'));
        } elseif (!$user->hasRole($role)) {
            $response = $this->wantsJson($request)
                ? new JsonResponse(['message' => 'Недостатньо прав для цієї дії.'], 403)
                : new Response($this->twig->render('medical/forbidden.html.twig', ['user' => $user]), 403);
        } else {
            return;
        }

        $event->setController(fn () => $response);
    }

    /**
     * Читає токен із заголовка Authorization: Bearer або з cookie jwt і знаходить користувача.
     *
     * @param Request $request Поточний запит
     * @return User|null
     */
    private function resolveUser(Request $request): ?User
    {
        $header = (string) $request->headers->get('Authorization');
        $token = str_starts_with($header, 'Bearer ') ? substr($header, 7) : $request->cookies->get('jwt');

        if (!is_string($token) || $token === '') {
            return null;
        }

        $payload = $this->jwt->decode($token);

        return $payload ? $this->em->find(User::class, (int) ($payload['sub'] ?? 0)) : null;
    }

    /**
     * Знаходить атрибут RequireRole на методі контролера, а якщо його немає, то на класі.
     *
     * @param callable $controller Контролер, який буде запущено
     * @return string|null Потрібна роль або null, якщо сторінка відкрита для всіх
     */
    private function requiredRole(callable $controller): ?string
    {
        if (!is_array($controller)) {
            return null;
        }

        $method = new \ReflectionMethod($controller[0], $controller[1]);
        $attributes = $method->getAttributes(RequireRole::class)
            ?: $method->getDeclaringClass()->getAttributes(RequireRole::class);

        return $attributes ? $attributes[0]->newInstance()->role : null;
    }

    /**
     * Визначає, чи треба відповідати у форматі JSON (запит до API).
     *
     * @param Request $request Поточний запит
     * @return bool
     */
    private function wantsJson(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api/')
            || $request->headers->has('Authorization')
            || in_array('application/json', $request->getAcceptableContentTypes(), true);
    }
}