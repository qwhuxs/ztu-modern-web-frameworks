<?php

namespace App\Controller;

use App\Filter\FiltersRecords;
use App\Entity\Appointment;
use App\Entity\Doctor;
use App\Entity\Patient;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CLIENT')]
#[Route('/appointments')]
class AppointmentController extends AbstractController
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
     * Показує список прийомів із фільтрами та пагінацією.
     *
     * @param Request $request Поточний запит
     * @return Response
     */
    #[Route('', name: 'appointment_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $qb = $this->em->createQueryBuilder()
            ->select('r')
            ->from(Appointment::class, 'r')
            ->orderBy('r.appointmentAt', 'DESC');

        $this->applyFilters($qb, $request, [
            'id' => ['r.id', 'exact'],
            'patient_id' => ['r.patient', 'exact'],
            'doctor_id' => ['r.doctor', 'exact'],
            'appointment_at' => ['r.appointmentAt', 'date'],
            'complaints' => ['r.complaints', 'text'],
        ]);

        return $this->render('medical/appointments/index.html.twig', [
            'pagination' => $this->paginate($qb, $request),
            'patients' => $this->em->getRepository(Patient::class)->findBy([], ['lastName' => 'ASC']),
            'doctors' => $this->em->getRepository(Doctor::class)->findBy([], ['lastName' => 'ASC']),
        ]);
    }

    /**
     * Показує порожню форму та зберігає новий запис: прийом.
     *
     * @param Request $request Поточний запит
     * @return Response
     */
    #[IsGranted('ROLE_MANAGER')]
    #[Route('/new', name: 'appointment_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleForm(new Appointment(), $request);
    }

    /**
     * Показує заповнену форму та зберігає зміни: прийом.
     *
     * @param int $id Ідентифікатор запису
     * @param Request $request Поточний запит
     * @return Response
     */
    #[IsGranted('ROLE_MANAGER')]
    #[Route('/{id}/edit', name: 'appointment_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        return $this->handleForm($this->findOr404($id), $request);
    }

    /**
     * Видаляє прийом, якщо з ним не пов'язані інші записи.
     *
     * @param int $id Ідентифікатор запису
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/delete', name: 'appointment_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id): Response
    {
        $this->em->remove($this->findOr404($id));

        try {
            $this->em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            return $this->redirectToRoute('appointment_index', ['error' => 1]);
        }

        return $this->redirectToRoute('appointment_index');
    }

    /**
     * Показує форму, а після надсилання перевіряє та зберігає дані.
     *
     * @param Appointment $appointment Новий або наявний запис
     * @param Request $request Поточний запит
     * @return Response
     */
    private function handleForm(Appointment $appointment, Request $request): Response
    {
        $errors = [];

        if ($request->isMethod('POST')) {
            $errors = $this->fill($appointment, $request);

            if (!$errors) {
                $this->em->persist($appointment);
                $this->em->flush();

                return $this->redirectToRoute('appointment_index');
            }
        }

        return $this->render('medical/appointments/form.html.twig', [
            'appointment' => $appointment,
            'errors' => $errors,
            'patients' => $this->em->getRepository(Patient::class)->findBy([], ['lastName' => 'ASC']),
            'doctors' => $this->em->getRepository(Doctor::class)->findBy([], ['lastName' => 'ASC']),
        ]);
    }

    /**
     * Переносить дані з форми в об'єкт і збирає помилки.
     *
     * @param Appointment $appointment Запис, який треба заповнити
     * @param Request $request Поточний запит
     * @return array Список помилок
     */
    private function fill(Appointment $appointment, Request $request): array
    {
        $errors = [];

        $patient = $this->em->find(Patient::class, (int) $request->request->get('patient_id'));
        if (!$patient) {
            $errors[] = 'Виберіть пацієнта.';
        } else {
            $appointment->setPatient($patient);
        }

        $doctor = $this->em->find(Doctor::class, (int) $request->request->get('doctor_id'));
        if (!$doctor) {
            $errors[] = 'Виберіть лікаря.';
        } else {
            $appointment->setDoctor($doctor);
        }

        $appointmentAt = \DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', (string) $request->request->get('appointment_at'));
        if (!$appointmentAt) {
            $errors[] = 'Вкажіть правильну дату і час прийому.';
        } else {
            $appointment->setAppointmentAt($appointmentAt);
        }

        $appointment->setComplaints(trim((string) $request->request->get('complaints')) ?: null);

        return $errors;
    }

    /**
     * Шукає запис за id або показує сторінку 404.
     *
     * @param int $id Ідентифікатор запису
     * @return Appointment
     */
    private function findOr404(int $id): Appointment
    {
        return $this->em->find(Appointment::class, $id)
            ?? throw $this->createNotFoundException('Запис не знайдено.');
    }
}