<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\JwtService;
use App\Security\RequireRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    /**
     * Отримує сервіси для роботи з користувачами й токенами.
     *
     * @param EntityManagerInterface $em Менеджер сутностей Doctrine
     * @param JwtService $jwt Сервіс JWT
     */
    public function __construct(private EntityManagerInterface $em, private JwtService $jwt)
    {
    }

    /**
     * Показує форму входу, а після надсилання перевіряє пароль і зберігає JWT у cookie.
     *
     * @param Request $request Поточний запит
     * @return Response
     */
    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        $errors = [];
        $email = trim((string) $request->request->get('email'));

        if ($request->isMethod('POST')) {
            $user = $this->findUser($email);

            if ($user && $user->checkPassword((string) $request->request->get('password'))) {
                return $this->withTokenCookie($this->redirectToRoute('medical_home'), $user, $request);
            }

            $errors[] = 'Неправильна пошта або пароль.';
        }

        return $this->render('medical/auth/login.html.twig', ['errors' => $errors, 'email' => $email]);
    }

    /**
     * Показує форму реєстрації, а після надсилання створює користувача з роллю client.
     *
     * @param Request $request Поточний запит
     * @return Response
     */
    #[Route('/register', name: 'register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        $errors = [];
        $name = trim((string) $request->request->get('name'));
        $email = trim((string) $request->request->get('email'));

        if ($request->isMethod('POST')) {
            $password = (string) $request->request->get('password');

            if ($name === '' || mb_strlen($name) > 100) {
                $errors[] = 'Заповніть поле «Ім\'я» (до 100 символів).';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Вкажіть правильну пошту.';
            } elseif ($this->findUser($email)) {
                $errors[] = 'Користувач із такою поштою вже існує.';
            }
            if (mb_strlen($password) < 8) {
                $errors[] = 'Пароль має містити щонайменше 8 символів.';
            } elseif ($password !== $request->request->get('password_confirmation')) {
                $errors[] = 'Паролі не збігаються.';
            }

            if (!$errors) {
                $user = new User();
                $user->setName($name);
                $user->setEmail($email);
                $user->setPlainPassword($password);
                $this->em->persist($user);
                $this->em->flush();

                return $this->withTokenCookie($this->redirectToRoute('medical_home'), $user, $request);
            }
        }

        return $this->render('medical/auth/register.html.twig', ['errors' => $errors, 'name' => $name, 'email' => $email]);
    }

    /**
     * Виконує вихід: видаляє cookie з токеном.
     *
     * @return Response
     */
    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(): Response
    {
        $response = $this->redirectToRoute('login');
        $response->headers->clearCookie('jwt');

        return $response;
    }

    /**
     * API: перевіряє пошту й пароль і повертає JWT у форматі JSON.
     *
     * @param Request $request JSON з полями email і password
     * @return JsonResponse
     */
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function apiLogin(Request $request): JsonResponse
    {
        try {
            $data = $request->toArray();
        } catch (\Throwable) {
            $data = $request->request->all();
        }

        $user = $this->findUser((string) ($data['email'] ?? ''));

        if (!$user || !$user->checkPassword((string) ($data['password'] ?? ''))) {
            return $this->json(['message' => 'Неправильна пошта або пароль.'], 401);
        }

        return $this->json([
            'token' => $this->jwt->createToken($user),
            'token_type' => 'Bearer',
            'expires_in' => JwtService::TTL,
            'user' => $user->toArray(),
        ]);
    }

    /**
     * API: повертає дані користувача, якому належить токен.
     *
     * @param Request $request Запит із заголовком Authorization: Bearer
     * @return JsonResponse
     */
    #[RequireRole('client')]
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(Request $request): JsonResponse
    {
        return $this->json($request->attributes->get('user')->toArray());
    }

    /**
     * Шукає користувача за поштою.
     *
     * @param string $email Пошта
     * @return User|null
     */
    private function findUser(string $email): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['email' => mb_strtolower(trim($email))]);
    }

    /**
     * Додає до відповіді cookie jwt з новим токеном.
     *
     * @param Response $response Відповідь-перенаправлення
     * @param User $user Користувач, який увійшов
     * @param Request $request Поточний запит
     * @return Response
     */
    private function withTokenCookie(Response $response, User $user, Request $request): Response
    {
        // HttpOnly: JavaScript не може прочитати токен; SameSite=Lax захищає від запитів з інших сайтів.
        $response->headers->setCookie(Cookie::create(
            'jwt',
            $this->jwt->createToken($user),
            time() + JwtService::TTL,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            Cookie::SAMESITE_LAX
        ));

        return $response;
    }
}