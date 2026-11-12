<?php

namespace App\Controller;

use App\Entity\Patient;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/patients')]
class PatientController extends AbstractController
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
     * READ: повертає список пацієнтів.
     * Кожен об'єкт перетворюється на масив методом jsonSerialize() сутності.
     *
     * @return JsonResponse
     */
    #[Route('', name: 'patient_list', methods: [Request::METHOD_GET])]
    public function getPatients(): JsonResponse
    {
        $items = $this->em->getRepository(Patient::class)->findBy([], ['lastName' => 'ASC']);

        return $this->json($items, Response::HTTP_OK);
    }

    /**
     * READ: повертає один запис за id.
     *
     * @param int $id Ідентифікатор запису
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'patient_show', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function getPatientById(int $id): JsonResponse
    {
        $patient = $this->em->find(Patient::class, $id);
        if (!$patient) {
            return $this->json(['error' => 'Пацієнта не знайдено'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($patient, Response::HTTP_OK);
    }

    /**
     * CREATE: створює новий запис із JSON-даних запиту.
     *
     * @param Request $request Запит із JSON-тілом
     * @return JsonResponse
     */
    #[Route('', name: 'patient_create', methods: [Request::METHOD_POST])]
    public function createPatient(Request $request): JsonResponse
    {
        $patient = new Patient();
        $errors = $this->fill($patient, $this->getJsonData($request), true);

        if ($errors) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->em->persist($patient);
        $this->em->flush();

        return $this->json($patient, Response::HTTP_CREATED);
    }

    /**
     * UPDATE: змінює лише ті поля, які передано в JSON.
     *
     * @param int $id Ідентифікатор запису
     * @param Request $request Запит із JSON-тілом
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'patient_update', requirements: ['id' => '\d+'], methods: [Request::METHOD_PATCH])]
    public function updatePatientById(int $id, Request $request): JsonResponse
    {
        $patient = $this->em->find(Patient::class, $id);
        if (!$patient) {
            return $this->json(['error' => 'Пацієнта не знайдено'], Response::HTTP_NOT_FOUND);
        }

        $errors = $this->fill($patient, $this->getJsonData($request), false);

        if ($errors) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->em->flush();

        return $this->json($patient, Response::HTTP_OK);
    }

    /**
     * DELETE: видаляє запис і повертає 204 No Content.
     * Якщо з записом пов'язані інші дані, повертає 409 Conflict.
     *
     * @param int $id Ідентифікатор запису
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'patient_delete', requirements: ['id' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function deletePatientById(int $id): JsonResponse
    {
        $patient = $this->em->find(Patient::class, $id);
        if (!$patient) {
            return $this->json(['error' => 'Пацієнта не знайдено'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->em->remove($patient);
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
     * @param Patient $patient Запис, який треба заповнити
     * @param array $data Дані з JSON
     * @param bool $isNew true, якщо створюється новий запис
     * @return array Помилки у форматі [поле => повідомлення]
     */
    private function fill(Patient $patient, array $data, bool $isNew): array
    {
        $errors = [];

        if ($isNew || array_key_exists('first_name', $data)) {
            $value = trim((string) ($data['first_name'] ?? ''));
            if ($value === '' || mb_strlen($value) > 100) {
                $errors['first_name'] = 'Поле «Ім\'я» обов\'язкове (до 100 символів).';
            } else {
                $patient->setFirstName($value);
            }
        }
        if ($isNew || array_key_exists('last_name', $data)) {
            $value = trim((string) ($data['last_name'] ?? ''));
            if ($value === '' || mb_strlen($value) > 100) {
                $errors['last_name'] = 'Поле «Прізвище» обов\'язкове (до 100 символів).';
            } else {
                $patient->setLastName($value);
            }
        }
        if ($isNew || array_key_exists('birth_date', $data)) {
            $value = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($data['birth_date'] ?? ''));
            if (!$value) {
                $errors['birth_date'] = 'Поле «Дата народження» має бути у форматі РРРР-ММ-ДД.';
            } else {
                $patient->setBirthDate($value);
            }
        }
        if ($isNew || array_key_exists('phone', $data)) {
            $value = trim((string) ($data['phone'] ?? ''));
            if ($value === '' || mb_strlen($value) > 20) {
                $errors['phone'] = 'Поле «Телефон» обов\'язкове (до 20 символів).';
            } else {
                $patient->setPhone($value);
            }
        }

        return $errors;
    }
}