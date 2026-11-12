<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AuthController extends AbstractController
{
    /**
     * Отримує сервіси для роботи з користувачами й токенами.
     *
     * @param EntityManagerInterface $em Менеджер сутностей Doctrine
     * @param JWTTokenManagerInterface $jwtManager Сервіс lexik, який створює й підписує JWT
     * @param UserPasswordHasherInterface $hasher Сервіс хешування паролів Symfony Security
     * @param int $tokenTtl Час життя токена в секундах (token_ttl з lexik_jwt_authentication.yaml)
     */
    public function __construct(
        private EntityManagerInterface $em,
        private JWTTokenManagerInterface $jwtManager,
        private UserPasswordHasherInterface $hasher,
        #[Autowire('%lexik_jwt_authentication.token_ttl%')] private int $tokenTtl,
    ) {
    }

    /**
     * Показує форму входу, а після надсилання перевіряє пароль, створює JWT через lexik і зберігає його в cookie.
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

            if ($user && $this->hasher->isPasswordValid($user, (string) $request->request->get('password'))) {
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
                $user->setPassword($this->hasher->hashPassword($user, $password));
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
     * API: повертає дані користувача, якому належить токен.
     * Сам вхід через API (POST /api/login) виконує json_login із security.yaml, контролер для нього не потрібен.
     *
     * @param User $user Поточний користувач, визначений за JWT
     * @return JsonResponse
     */
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(#[CurrentUser] User $user): JsonResponse
    {
        return $this->json($user->toArray());
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
        $response->headers->setCookie(Cookie::create(
            'jwt',
            $this->jwtManager->create($user),
            time() + $this->tokenTtl,
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