<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Doctor;
use App\Entity\Patient;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/appointments')]
class AppointmentController extends AbstractController
{
    /**
     * Менеджер сутностей Doctrine для роботи з базою даних.
     *
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * Отримує менеджер сутностей.
     *
     * @param EntityManagerInterface $em Менеджер сутностей Doctrine
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * READ: повертає список прийомів.
     * Кожен об'єкт перетворюється на масив методом jsonSerialize() сутності.
     *
     * @return JsonResponse
     */
    #[Route('', name: 'appointment_list', methods: [Request::METHOD_GET])]
    public function getAppointments(): JsonResponse
    {
        $items = $this->em->getRepository(Appointment::class)->findBy([], ['appointmentAt' => 'DESC']);

        return $this->json($items, Response::HTTP_OK);
    }

    /**
     * READ: повертає один запис за id.
     *
     * @param int $id Ідентифікатор запису
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'appointment_show', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function getAppointmentById(int $id): JsonResponse
    {
        $appointment = $this->em->find(Appointment::class, $id);
        if (!$appointment) {
            return $this->json(['error' => 'Прийом не знайдено'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($appointment, Response::HTTP_OK);
    }

    /**
     * CREATE: створює новий запис із JSON-даних запиту.
     *
     * @param Request $request Запит із JSON-тілом
     * @return JsonResponse
     */
    #[Route('', name: 'appointment_create', methods: [Request::METHOD_POST])]
    public function createAppointment(Request $request): JsonResponse
    {
        $appointment = new Appointment();
        $errors = $this->fill($appointment, $this->getJsonData($request), true);

        if ($errors) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->em->persist($appointment);
        $this->em->flush();

        return $this->json($appointment, Response::HTTP_CREATED);
    }

    /**
     * UPDATE: змінює лише ті поля, які передано в JSON.
     *
     * @param int $id Ідентифікатор запису
     * @param Request $request Запит із JSON-тілом
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'appointment_update', requirements: ['id' => '\d+'], methods: [Request::METHOD_PATCH])]
    public function updateAppointmentById(int $id, Request $request): JsonResponse
    {
        $appointment = $this->em->find(Appointment::class, $id);
        if (!$appointment) {
            return $this->json(['error' => 'Прийом не знайдено'], Response::HTTP_NOT_FOUND);
        }

        $errors = $this->fill($appointment, $this->getJsonData($request), false);

        if ($errors) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->em->flush();

        return $this->json($appointment, Response::HTTP_OK);
    }

    /**
     * DELETE: видаляє запис і повертає 204 No Content.
     * Якщо з записом пов'язані інші дані, повертає 409 Conflict.
     *
     * @param int $id Ідентифікатор запису
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'appointment_delete', requirements: ['id' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function deleteAppointmentById(int $id): JsonResponse
    {
        $appointment = $this->em->find(Appointment::class, $id);
        if (!$appointment) {
            return $this->json(['error' => 'Прийом не знайдено'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->em->remove($appointment);
            $this->em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            return $this->json(['error' => 'Запис не можна видалити, бо з ним пов\'язані інші записи'], Response::HTTP_CONFLICT);
        }

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Читає JSON-тіло запиту як масив.
     *
     * @param Request $request Поточний запит
     * @return array
     */
    private function getJsonData(Request $request): array
    {
        $data = json_decode($request->getContent(), true);

        return is_array($data) ? $data : [];
    }

    /**
     * Переносить дані з JSON в об'єкт і повертає помилки перевірки.
     * Для нового запису всі обов'язкові поля потрібні, для оновлення перевіряються лише передані.
     *
     * @param Appointment $appointment Запис, який треба заповнити
     * @param array $data Дані з JSON
     * @param bool $isNew true, якщо створюється новий запис
     * @return array Помилки у форматі [поле => повідомлення]
     */
    private function fill(Appointment $appointment, array $data, bool $isNew): array
    {
        $errors = [];

        if ($isNew || array_key_exists('patient_id', $data)) {
            $related = $this->em->find(Patient::class, (int) ($data['patient_id'] ?? 0));
            if (!$related) {
                $errors['patient_id'] = 'Запис «Пацієнт» з таким id не знайдено.';
            } else {
                $appointment->setPatient($related);
            }
        }
        if ($isNew || array_key_exists('doctor_id', $data)) {
            $related = $this->em->find(Doctor::class, (int) ($data['doctor_id'] ?? 0));
            if (!$related) {
                $errors['doctor_id'] = 'Запис «Лікар» з таким id не знайдено.';
            } else {
                $appointment->setDoctor($related);
            }
        }
        if ($isNew || array_key_exists('appointment_at', $data)) {
            $value = \DateTimeImmutable::createFromFormat('!Y-m-d H:i', (string) ($data['appointment_at'] ?? ''));
            if (!$value) {
                $errors['appointment_at'] = 'Поле «Дата і час прийому» має бути у форматі РРРР-ММ-ДД ГГ:ХХ.';
            } else {
                $appointment->setAppointmentAt($value);
            }
        }
        if ($isNew || array_key_exists('complaints', $data)) {
            $value = trim((string) ($data['complaints'] ?? ''));
            $appointment->setComplaints($value === '' ? null : $value);
        }

        return $errors;
    }
}