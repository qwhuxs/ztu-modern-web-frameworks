<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\RequireRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[RequireRole('admin')]
#[Route('/users')]
class UserController extends AbstractController
{
    /**
     * Отримує менеджер сутностей для роботи з базою.
     *
     * @param EntityManagerInterface $em Менеджер сутностей Doctrine
     */
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * Показує список користувачів і їхніх ролей (лише для admin).
     *
     * @return Response
     */
    #[Route('', name: 'user_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('medical/users/index.html.twig', [
            'users' => $this->em->getRepository(User::class)->findBy([], ['id' => 'ASC']),
            'roles' => array_keys(User::ROLE_LEVELS),
        ]);
    }

    /**
     * Змінює роль користувача (лише для admin).
     *
     * @param int $id Ідентифікатор користувача
     * @param Request $request Дані форми з полем role
     * @return Response
     */
    #[Route('/{id}/role', name: 'user_role', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function changeRole(int $id, Request $request): Response
    {
        $user = $this->em->find(User::class, $id) ?? throw $this->createNotFoundException('Користувача не знайдено.');
        $role = (string) $request->request->get('role');

        if ($user === $request->attributes->get('user')) {
            return $this->redirectToRoute('user_index', ['error' => 'self']);
        }

        if (array_key_exists($role, User::ROLE_LEVELS)) {
            $user->setRole($role);
            $this->em->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}