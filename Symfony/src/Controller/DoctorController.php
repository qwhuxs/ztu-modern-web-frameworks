<?php

namespace App\Controller;

use App\Entity\Doctor;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/doctors')]
class DoctorController extends AbstractController
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
     * Показує список лікарів.
     *
     * @return Response
     */
    #[Route('', name: 'doctor_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('medical/doctors/index.html.twig', [
            'doctors' => $this->em->getRepository(Doctor::class)->findBy([], ['lastName' => 'ASC']),
        ]);
    }

    /**
     * Показує порожню форму та зберігає новий запис: лікаря.
     *
     * @param Request $request Поточний запит
     * @return Response
     */
    #[Route('/new', name: 'doctor_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleForm(new Doctor(), $request);
    }

    /**
     * Показує заповнену форму та зберігає зміни: лікаря.
     *
     * @param int $id Ідентифікатор запису
     * @param Request $request Поточний запит
     * @return Response
     */
    #[Route('/{id}/edit', name: 'doctor_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        return $this->handleForm($this->findOr404($id), $request);
    }

    /**
     * Видаляє лікаря, якщо з ним не пов'язані інші записи.
     *
     * @param int $id Ідентифікатор запису
     * @return Response
     */
    #[Route('/{id}/delete', name: 'doctor_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id): Response
    {
        $this->em->remove($this->findOr404($id));

        try {
            $this->em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            return $this->redirectToRoute('doctor_index', ['error' => 1]);
        }

        return $this->redirectToRoute('doctor_index');
    }

    /**
     * Показує форму, а після надсилання перевіряє та зберігає дані.
     *
     * @param Doctor $doctor Новий або наявний запис
     * @param Request $request Поточний запит
     * @return Response
     */
    private function handleForm(Doctor $doctor, Request $request): Response
    {
        $errors = [];

        if ($request->isMethod('POST')) {
            $errors = $this->fill($doctor, $request);

            if (!$errors) {
                $this->em->persist($doctor);
                $this->em->flush();

                return $this->redirectToRoute('doctor_index');
            }
        }

        return $this->render('medical/doctors/form.html.twig', [
            'doctor' => $doctor,
            'errors' => $errors,
        ]);
    }

    /**
     * Переносить дані з форми в об'єкт і збирає помилки.
     *
     * @param Doctor $doctor Запис, який треба заповнити
     * @param Request $request Поточний запит
     * @return array Список помилок
     */
    private function fill(Doctor $doctor, Request $request): array
    {
        $errors = [];

        $firstName = trim((string) $request->request->get('first_name'));
        if ($firstName === '' || mb_strlen($firstName) > 100) {
            $errors[] = 'Заповніть поле «Ім\'я» (до 100 символів).';
        } else {
            $doctor->setFirstName($firstName);
        }

        $lastName = trim((string) $request->request->get('last_name'));
        if ($lastName === '' || mb_strlen($lastName) > 100) {
            $errors[] = 'Заповніть поле «Прізвище» (до 100 символів).';
        } else {
            $doctor->setLastName($lastName);
        }

        $specialty = trim((string) $request->request->get('specialty'));
        if ($specialty === '' || mb_strlen($specialty) > 100) {
            $errors[] = 'Заповніть поле «Спеціальність» (до 100 символів).';
        } else {
            $doctor->setSpecialty($specialty);
        }

        $phone = trim((string) $request->request->get('phone'));
        if ($phone === '' || mb_strlen($phone) > 20) {
            $errors[] = 'Заповніть поле «Телефон» (до 20 символів).';
        } else {
            $doctor->setPhone($phone);
        }

        return $errors;
    }

    /**
     * Шукає запис за id або показує сторінку 404.
     *
     * @param int $id Ідентифікатор запису
     * @return Doctor
     */
    private function findOr404(int $id): Doctor
    {
        return $this->em->find(Doctor::class, $id)
            ?? throw $this->createNotFoundException('Запис не знайдено.');
    }
}