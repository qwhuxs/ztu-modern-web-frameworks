<?php

namespace App\Controller;

use App\Filter\FiltersRecords;
use App\Entity\Patient;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CLIENT')]
#[Route('/patients')]
class PatientController extends AbstractController
{
    use FiltersRecords;

    /**
     * Отримує менеджер сутностей для роботи з базою.
     *
     * @param EntityManagerInterface $em Менеджер сутностей Doctrine
     */
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * Показує список пацієнтів із фільтрами та пагінацією.
     *
     * @param Request $request Поточний запит
     * @return Response
     */
    #[Route('', name: 'patient_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $qb = $this->em->createQueryBuilder()
            ->select('r')
            ->from(Patient::class, 'r')
            ->orderBy('r.lastName', 'ASC');

        $this->applyFilters($qb, $request, [
            'id' => ['r.id', 'exact'],
            'first_name' => ['r.firstName', 'text'],
            'last_name' => ['r.lastName', 'text'],
            'birth_date' => ['r.birthDate', 'date'],
            'phone' => ['r.phone', 'text'],
        ]);

        return $this->render('medical/patients/index.html.twig', [
            'pagination' => $this->paginate($qb, $request),
        ]);
    }

    /**
     * Показує порожню форму та зберігає новий запис: пацієнта.
     *
     * @param Request $request Поточний запит
     * @return Response
     */
    #[IsGranted('ROLE_MANAGER')]
    #[Route('/new', name: 'patient_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleForm(new Patient(), $request);
    }

    /**
     * Показує заповнену форму та зберігає зміни: пацієнта.
     *
     * @param int $id Ідентифікатор запису
     * @param Request $request Поточний запит
     * @return Response
     */
    #[IsGranted('ROLE_MANAGER')]
    #[Route('/{id}/edit', name: 'patient_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        return $this->handleForm($this->findOr404($id), $request);
    }

    /**
     * Видаляє пацієнта, якщо з ним не пов'язані інші записи.
     *
     * @param int $id Ідентифікатор запису
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/delete', name: 'patient_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id): Response
    {
        $this->em->remove($this->findOr404($id));

        try {
            $this->em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            return $this->redirectToRoute('patient_index', ['error' => 1]);
        }

        return $this->redirectToRoute('patient_index');
    }

    /**
     * Показує форму, а після надсилання перевіряє та зберігає дані.
     *
     * @param Patient $patient Новий або наявний запис
     * @param Request $request Поточний запит
     * @return Response
     */
    private function handleForm(Patient $patient, Request $request): Response
    {
        $errors = [];

        if ($request->isMethod('POST')) {
            $errors = $this->fill($patient, $request);

            if (!$errors) {
                $this->em->persist($patient);
                $this->em->flush();

                return $this->redirectToRoute('patient_index');
            }
        }

        return $this->render('medical/patients/form.html.twig', [
            'patient' => $patient,
            'errors' => $errors,
        ]);
    }

    /**
     * Переносить дані з форми в об'єкт і збирає помилки.
     *
     * @param Patient $patient Запис, який треба заповнити
     * @param Request $request Поточний запит
     * @return array Список помилок
     */
    private function fill(Patient $patient, Request $request): array
    {
        $errors = [];

        $firstName = trim((string) $request->request->get('first_name'));
        if ($firstName === '' || mb_strlen($firstName) > 100) {
            $errors[] = 'Заповніть поле «Ім\'я» (до 100 символів).';
        } else {
            $patient->setFirstName($firstName);
        }

        $lastName = trim((string) $request->request->get('last_name'));
        if ($lastName === '' || mb_strlen($lastName) > 100) {
            $errors[] = 'Заповніть поле «Прізвище» (до 100 символів).';
        } else {
            $patient->setLastName($lastName);
        }

        $birthDate = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $request->request->get('birth_date'));
        if (!$birthDate || $birthDate > new \DateTimeImmutable('today')) {
            $errors[] = 'Вкажіть правильну дату народження.';
        } else {
            $patient->setBirthDate($birthDate);
        }

        $phone = trim((string) $request->request->get('phone'));
        if ($phone === '' || mb_strlen($phone) > 20) {
            $errors[] = 'Заповніть поле «Телефон» (до 20 символів).';
        } else {
            $patient->setPhone($phone);
        }

        return $errors;
    }

    /**
     * Шукає запис за id або показує сторінку 404.
     *
     * @param int $id Ідентифікатор запису
     * @return Patient
     */
    private function findOr404(int $id): Patient
    {
        return $this->em->find(Patient::class, $id)
            ?? throw $this->createNotFoundException('Запис не знайдено.');
    }
}