<?php

namespace App\Controller;

use App\Entity\Diagnosis;
use App\Entity\Treatment;
use App\Filter\FiltersRecords;
use App\Security\RequireRole;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Доступ: client переглядає, manager додає та змінює, admin видаляє.
#[RequireRole('client')]
#[Route('/treatments')]
class TreatmentController extends AbstractController
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
     * Показує список записів про лікування із фільтрами та пагінацією.
     *
     * @param Request $request Поточний запит
     * @return Response
     */
    #[Route('', name: 'treatment_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $qb = $this->em->createQueryBuilder()
            ->select('r')
            ->from(Treatment::class, 'r')
            ->orderBy('r.id', 'DESC');

        $this->applyFilters($qb, $request, [
            'id' => ['r.id', 'exact'],
            'diagnosis_id' => ['r.diagnosis', 'exact'],
            'prescription' => ['r.prescription', 'text'],
            'start_date' => ['r.startDate', 'date'],
            'end_date' => ['r.endDate', 'date'],
        ]);

        return $this->render('medical/treatments/index.html.twig', [
            'pagination' => $this->paginate($qb, $request),
            'diagnoses' => $this->em->getRepository(Diagnosis::class)->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * Показує порожню форму та зберігає новий запис про лікування.
     *
     * @param Request $request Поточний запит
     * @return Response
     */
    #[RequireRole('manager')]
    #[Route('/new', name: 'treatment_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleForm(new Treatment(), $request);
    }

    /**
     * Показує заповнену форму та зберігає зміни запису про лікування.
     *
     * @param int $id Ідентифікатор запису
     * @param Request $request Поточний запит
     * @return Response
     */
    #[RequireRole('manager')]
    #[Route('/{id}/edit', name: 'treatment_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        return $this->handleForm($this->findOr404($id), $request);
    }

    /**
     * Видаляє запис про лікування, якщо з ним не пов'язані інші записи.
     *
     * @param int $id Ідентифікатор запису
     * @return Response
     */
    #[RequireRole('admin')]
    #[Route('/{id}/delete', name: 'treatment_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id): Response
    {
        $this->em->remove($this->findOr404($id));

        try {
            $this->em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            return $this->redirectToRoute('treatment_index', ['error' => 1]);
        }

        return $this->redirectToRoute('treatment_index');
    }

    /**
     * Показує форму, а після надсилання перевіряє та зберігає дані.
     *
     * @param Treatment $treatment Новий або наявний запис
     * @param Request $request Поточний запит
     * @return Response
     */
    private function handleForm(Treatment $treatment, Request $request): Response
    {
        $errors = [];

        if ($request->isMethod('POST')) {
            $errors = $this->fill($treatment, $request);

            if (!$errors) {
                $this->em->persist($treatment);
                $this->em->flush();

                return $this->redirectToRoute('treatment_index');
            }
        }

        return $this->render('medical/treatments/form.html.twig', [
            'treatment' => $treatment,
            'errors' => $errors,
            'diagnoses' => $this->em->getRepository(Diagnosis::class)->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * Переносить дані з форми в об'єкт і збирає помилки.
     *
     * @param Treatment $treatment Запис, який треба заповнити
     * @param Request $request Поточний запит
     * @return array Список помилок
     */
    private function fill(Treatment $treatment, Request $request): array
    {
        $errors = [];

        $diagnosis = $this->em->find(Diagnosis::class, (int) $request->request->get('diagnosis_id'));
        if (!$diagnosis) {
            $errors[] = 'Виберіть діагноз.';
        } else {
            $treatment->setDiagnosis($diagnosis);
        }

        $prescription = trim((string) $request->request->get('prescription'));
        if ($prescription === '') {
            $errors[] = 'Заповніть поле «Призначення».';
        } else {
            $treatment->setPrescription($prescription);
        }

        $startDate = \DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            (string) $request->request->get('start_date')
        );

        if (!$startDate) {
            $errors[] = 'Вкажіть правильну дату початку.';
        } else {
            $treatment->setStartDate($startDate);
        }

        // Дата завершення необов'язкова, але не може бути раніше дати початку.
        $endDateText = (string) $request->request->get('end_date');
        $endDate = $endDateText === ''
            ? null
            : \DateTimeImmutable::createFromFormat('!Y-m-d', $endDateText);

        if ($endDate === false || ($endDate && $startDate && $endDate < $startDate)) {
            $errors[] = 'Дата завершення неправильна або раніша за дату початку.';
        } else {
            $treatment->setEndDate($endDate);
        }

        return $errors;
    }

    /**
     * Шукає запис за id або показує сторінку 404.
     *
     * @param int $id Ідентифікатор запису
     * @return Treatment
     */
    private function findOr404(int $id): Treatment
    {
        return $this->em->find(Treatment::class, $id)
            ?? throw $this->createNotFoundException('Запис не знайдено.');
    }
}