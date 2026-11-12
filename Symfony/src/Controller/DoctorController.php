<?php

namespace App\Controller;

use App\Entity\Doctor;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/doctors')]
class DoctorController extends AbstractController
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
     * READ: повертає список лікарів.
     * Кожен об'єкт перетворюється на масив методом jsonSerialize() сутності.
     *
     * @return JsonResponse
     */
    #[Route('', name: 'doctor_list', methods: [Request::METHOD_GET])]
    public function getDoctors(): JsonResponse
    {
        $items = $this->em->getRepository(Doctor::class)->findBy([], ['lastName' => 'ASC']);

        return $this->json($items, Response::HTTP_OK);
    }

    /**
     * READ: повертає один запис за id.
     *
     * @param int $id Ідентифікатор запису
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'doctor_show', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function getDoctorById(int $id): JsonResponse
    {
        $doctor = $this->em->find(Doctor::class, $id);
        if (!$doctor) {
            return $this->json(['error' => 'Лікаря не знайдено'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($doctor, Response::HTTP_OK);
    }

    /**
     * CREATE: створює новий запис із JSON-даних запиту.
     *
     * @param Request $request Запит із JSON-тілом
     * @return JsonResponse
     */
    #[Route('', name: 'doctor_create', methods: [Request::METHOD_POST])]
    public function createDoctor(Request $request): JsonResponse
    {
        $doctor = new Doctor();
        $errors = $this->fill($doctor, $this->getJsonData($request), true);

        if ($errors) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->em->persist($doctor);
        $this->em->flush();

        return $this->json($doctor, Response::HTTP_CREATED);
    }

    /**
     * UPDATE: змінює лише ті поля, які передано в JSON.
     *
     * @param int $id Ідентифікатор запису
     * @param Request $request Запит із JSON-тілом
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'doctor_update', requirements: ['id' => '\d+'], methods: [Request::METHOD_PATCH])]
    public function updateDoctorById(int $id, Request $request): JsonResponse
    {
        $doctor = $this->em->find(Doctor::class, $id);
        if (!$doctor) {
            return $this->json(['error' => 'Лікаря не знайдено'], Response::HTTP_NOT_FOUND);
        }

        $errors = $this->fill($doctor, $this->getJsonData($request), false);

        if ($errors) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->em->flush();

        return $this->json($doctor, Response::HTTP_OK);
    }

    /**
     * DELETE: видаляє запис і повертає 204 No Content.
     * Якщо з записом пов'язані інші дані, повертає 409 Conflict.
     *
     * @param int $id Ідентифікатор запису
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'doctor_delete', requirements: ['id' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function deleteDoctorById(int $id): JsonResponse
    {
        $doctor = $this->em->find(Doctor::class, $id);
        if (!$doctor) {
            return $this->json(['error' => 'Лікаря не знайдено'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->em->remove($doctor);
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
     * @param Doctor $doctor Запис, який треба заповнити
     * @param array $data Дані з JSON
     * @param bool $isNew true, якщо створюється новий запис
     * @return array Помилки у форматі [поле => повідомлення]
     */
    private function fill(Doctor $doctor, array $data, bool $isNew): array
    {
        $errors = [];

        if ($isNew || array_key_exists('first_name', $data)) {
            $value = trim((string) ($data['first_name'] ?? ''));
            if ($value === '' || mb_strlen($value) > 100) {
                $errors['first_name'] = 'Поле «Ім\'я» обов\'язкове (до 100 символів).';
            } else {
                $doctor->setFirstName($value);
            }
        }
        if ($isNew || array_key_exists('last_name', $data)) {
            $value = trim((string) ($data['last_name'] ?? ''));
            if ($value === '' || mb_strlen($value) > 100) {
                $errors['last_name'] = 'Поле «Прізвище» обов\'язкове (до 100 символів).';
            } else {
                $doctor->setLastName($value);
            }
        }
        if ($isNew || array_key_exists('specialty', $data)) {
            $value = trim((string) ($data['specialty'] ?? ''));
            if ($value === '' || mb_strlen($value) > 100) {
                $errors['specialty'] = 'Поле «Спеціальність» обов\'язкове (до 100 символів).';
            } else {
                $doctor->setSpecialty($value);
            }
        }
        if ($isNew || array_key_exists('phone', $data)) {
            $value = trim((string) ($data['phone'] ?? ''));
            if ($value === '' || mb_strlen($value) > 20) {
                $errors['phone'] = 'Поле «Телефон» обов\'язкове (до 20 символів).';
            } else {
                $doctor->setPhone($value);
            }
        }

        return $errors;
    }
}