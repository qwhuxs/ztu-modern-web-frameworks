<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Diagnosis;
use App\Filter\FiltersRecords;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/diagnoses')]
class DiagnosisController extends AbstractController
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
     * Показує список діагнозів із фільтрами та пагінацією.
     *
     * @param Request $request Поточний запит
     * @return Response
     */
    #[Route('', name: 'diagnosis_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $qb = $this->em->createQueryBuilder()
            ->select('r')
            ->from(Diagnosis::class, 'r')
            ->orderBy('r.id', 'DESC');

        $this->applyFilters($qb, $request, [
            'id' => ['r.id', 'exact'],
            'appointment_id' => ['r.appointment', 'exact'],
            'name' => ['r.name', 'text'],
            'description' => ['r.description', 'text'],
        ]);

        return $this->render('medical/diagnoses/index.html.twig', [
            'pagination' => $this->paginate($qb, $request),
            'appointments' => $this->em->getRepository(Appointment::class)->findBy([], ['appointmentAt' => 'DESC']),
        ]);
    }

    /**
     * Показує порожню форму та зберігає новий запис: діагноз.
     *
     * @param Request $request Поточний запит
     * @return Response
     */
    #[Route('/new', name: 'diagnosis_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleForm(new Diagnosis(), $request);
    }

    /**
     * Показує заповнену форму та зберігає зміни: діагноз.
     *
     * @param int $id Ідентифікатор запису
     * @param Request $request Поточний запит
     * @return Response
     */
    #[Route('/{id}/edit', name: 'diagnosis_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        return $this->handleForm($this->findOr404($id), $request);
    }

    /**
     * Видаляє діагноз, якщо з ним не пов'язані інші записи.
     *
     * @param int $id Ідентифікатор запису
     * @return Response
     */
    #[Route('/{id}/delete', name: 'diagnosis_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id): Response
    {
        $this->em->remove($this->findOr404($id));

        try {
            $this->em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            return $this->redirectToRoute('diagnosis_index', ['error' => 1]);
        }

        return $this->redirectToRoute('diagnosis_index');
    }

    /**
     * Показує форму, а після надсилання перевіряє та зберігає дані.
     *
     * @param Diagnosis $diagnosis Новий або наявний запис
     * @param Request $request Поточний запит
     * @return Response
     */
    private function handleForm(Diagnosis $diagnosis, Request $request): Response
    {
        $errors = [];

        if ($request->isMethod('POST')) {
            $errors = $this->fill($diagnosis, $request);

            if (!$errors) {
                $this->em->persist($diagnosis);
                $this->em->flush();

                return $this->redirectToRoute('diagnosis_index');
            }
        }

        return $this->render('medical/diagnoses/form.html.twig', [
            'diagnosis' => $diagnosis,
            'errors' => $errors,
            'appointments' => $this->em->getRepository(Appointment::class)->findBy([], ['appointmentAt' => 'DESC']),
        ]);
    }

    /**
     * Переносить дані з форми в об'єкт і збирає помилки.
     *
     * @param Diagnosis $diagnosis Запис, який треба заповнити
     * @param Request $request Поточний запит
     * @return array Список помилок
     */
    private function fill(Diagnosis $diagnosis, Request $request): array
    {
        $errors = [];

        $appointment = $this->em->find(Appointment::class, (int) $request->request->get('appointment_id'));
        if (!$appointment) {
            $errors[] = 'Виберіть прийом.';
        } else {
            $diagnosis->setAppointment($appointment);
        }

        $name = trim((string) $request->request->get('name'));
        if ($name === '' || mb_strlen($name) > 150) {
            $errors[] = 'Заповніть поле «Назва діагнозу» (до 150 символів).';
        } else {
            $diagnosis->setName($name);
        }

        $diagnosis->setDescription(trim((string) $request->request->get('description')) ?: null);

        return $errors;
    }

    /**
     * Шукає запис за id або показує сторінку 404.
     *
     * @param int $id Ідентифікатор запису
     * @return Diagnosis
     */
    private function findOr404(int $id): Diagnosis
    {
        return $this->em->find(Diagnosis::class, $id)
            ?? throw $this->createNotFoundException('Запис не знайдено.');
    }
}