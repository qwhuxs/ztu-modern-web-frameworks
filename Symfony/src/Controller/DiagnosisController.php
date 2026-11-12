<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Diagnosis;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/diagnoses')]
class DiagnosisController extends AbstractController
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
     * READ: повертає список діагнозів.
     * Кожен об'єкт перетворюється на масив методом jsonSerialize() сутності.
     *
     * @return JsonResponse
     */
    #[Route('', name: 'diagnosis_list', methods: [Request::METHOD_GET])]
    public function getDiagnoses(): JsonResponse
    {
        $items = $this->em->getRepository(Diagnosis::class)->findBy([], ['id' => 'DESC']);

        return $this->json($items, Response::HTTP_OK);
    }

    /**
     * READ: повертає один запис за id.
     *
     * @param int $id Ідентифікатор запису
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'diagnosis_show', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function getDiagnosisById(int $id): JsonResponse
    {
        $diagnosis = $this->em->find(Diagnosis::class, $id);
        if (!$diagnosis) {
            return $this->json(['error' => 'Діагноз не знайдено'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($diagnosis, Response::HTTP_OK);
    }

    /**
     * CREATE: створює новий запис із JSON-даних запиту.
     *
     * @param Request $request Запит із JSON-тілом
     * @return JsonResponse
     */
    #[Route('', name: 'diagnosis_create', methods: [Request::METHOD_POST])]
    public function createDiagnosis(Request $request): JsonResponse
    {
        $diagnosis = new Diagnosis();
        $errors = $this->fill($diagnosis, $this->getJsonData($request), true);

        if ($errors) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->em->persist($diagnosis);
        $this->em->flush();

        return $this->json($diagnosis, Response::HTTP_CREATED);
    }

    /**
     * UPDATE: змінює лише ті поля, які передано в JSON.
     *
     * @param int $id Ідентифікатор запису
     * @param Request $request Запит із JSON-тілом
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'diagnosis_update', requirements: ['id' => '\d+'], methods: [Request::METHOD_PATCH])]
    public function updateDiagnosisById(int $id, Request $request): JsonResponse
    {
        $diagnosis = $this->em->find(Diagnosis::class, $id);
        if (!$diagnosis) {
            return $this->json(['error' => 'Діагноз не знайдено'], Response::HTTP_NOT_FOUND);
        }

        $errors = $this->fill($diagnosis, $this->getJsonData($request), false);

        if ($errors) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->em->flush();

        return $this->json($diagnosis, Response::HTTP_OK);
    }

    /**
     * DELETE: видаляє запис і повертає 204 No Content.
     * Якщо з записом пов'язані інші дані, повертає 409 Conflict.
     *
     * @param int $id Ідентифікатор запису
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'diagnosis_delete', requirements: ['id' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function deleteDiagnosisById(int $id): JsonResponse
    {
        $diagnosis = $this->em->find(Diagnosis::class, $id);
        if (!$diagnosis) {
            return $this->json(['error' => 'Діагноз не знайдено'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->em->remove($diagnosis);
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
     * @param Diagnosis $diagnosis Запис, який треба заповнити
     * @param array $data Дані з JSON
     * @param bool $isNew true, якщо створюється новий запис
     * @return array Помилки у форматі [поле => повідомлення]
     */
    private function fill(Diagnosis $diagnosis, array $data, bool $isNew): array
    {
        $errors = [];

        if ($isNew || array_key_exists('appointment_id', $data)) {
            $related = $this->em->find(Appointment::class, (int) ($data['appointment_id'] ?? 0));
            if (!$related) {
                $errors['appointment_id'] = 'Запис «Прийом» з таким id не знайдено.';
            } else {
                $diagnosis->setAppointment($related);
            }
        }
        if ($isNew || array_key_exists('name', $data)) {
            $value = trim((string) ($data['name'] ?? ''));
            if ($value === '' || mb_strlen($value) > 150) {
                $errors['name'] = 'Поле «Назва діагнозу» обов\'язкове (до 150 символів).';
            } else {
                $diagnosis->setName($value);
            }
        }
        if ($isNew || array_key_exists('description', $data)) {
            $value = trim((string) ($data['description'] ?? ''));
            $diagnosis->setDescription($value === '' ? null : $value);
        }

        return $errors;
    }
}