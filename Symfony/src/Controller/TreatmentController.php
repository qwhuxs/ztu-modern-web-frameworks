<?php

namespace App\Controller;

use App\Entity\Diagnosis;
use App\Entity\Treatment;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/treatments')]
class TreatmentController extends AbstractController
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
     * READ: повертає список записів про лікування.
     * Кожен об'єкт перетворюється на масив методом jsonSerialize() сутності.
     *
     * @return JsonResponse
     */
    #[Route('', name: 'treatment_list', methods: [Request::METHOD_GET])]
    public function getTreatments(): JsonResponse
    {
        $items = $this->em->getRepository(Treatment::class)->findBy([], ['id' => 'DESC']);

        return $this->json($items, Response::HTTP_OK);
    }

    /**
     * READ: повертає один запис за id.
     *
     * @param int $id Ідентифікатор запису
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'treatment_show', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function getTreatmentById(int $id): JsonResponse
    {
        $treatment = $this->em->find(Treatment::class, $id);
        if (!$treatment) {
            return $this->json(['error' => 'Запис про лікування не знайдено'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($treatment, Response::HTTP_OK);
    }

    /**
     * CREATE: створює новий запис із JSON-даних запиту.
     *
     * @param Request $request Запит із JSON-тілом
     * @return JsonResponse
     */
    #[Route('', name: 'treatment_create', methods: [Request::METHOD_POST])]
    public function createTreatment(Request $request): JsonResponse
    {
        $treatment = new Treatment();
        $errors = $this->fill($treatment, $this->getJsonData($request), true);

        if ($errors) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->em->persist($treatment);
        $this->em->flush();

        return $this->json($treatment, Response::HTTP_CREATED);
    }

    /**
     * UPDATE: змінює лише ті поля, які передано в JSON.
     *
     * @param int $id Ідентифікатор запису
     * @param Request $request Запит із JSON-тілом
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'treatment_update', requirements: ['id' => '\d+'], methods: [Request::METHOD_PATCH])]
    public function updateTreatmentById(int $id, Request $request): JsonResponse
    {
        $treatment = $this->em->find(Treatment::class, $id);
        if (!$treatment) {
            return $this->json(['error' => 'Запис про лікування не знайдено'], Response::HTTP_NOT_FOUND);
        }

        $errors = $this->fill($treatment, $this->getJsonData($request), false);

        if ($errors) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->em->flush();

        return $this->json($treatment, Response::HTTP_OK);
    }

    /**
     * DELETE: видаляє запис і повертає 204 No Content.
     * Якщо з записом пов'язані інші дані, повертає 409 Conflict.
     *
     * @param int $id Ідентифікатор запису
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'treatment_delete', requirements: ['id' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function deleteTreatmentById(int $id): JsonResponse
    {
        $treatment = $this->em->find(Treatment::class, $id);
        if (!$treatment) {
            return $this->json(['error' => 'Запис про лікування не знайдено'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->em->remove($treatment);
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
     * @param Treatment $treatment Запис, який треба заповнити
     * @param array $data Дані з JSON
     * @param bool $isNew true, якщо створюється новий запис
     * @return array Помилки у форматі [поле => повідомлення]
     */
    private function fill(Treatment $treatment, array $data, bool $isNew): array
    {
        $errors = [];

        if ($isNew || array_key_exists('diagnosis_id', $data)) {
            $related = $this->em->find(Diagnosis::class, (int) ($data['diagnosis_id'] ?? 0));
            if (!$related) {
                $errors['diagnosis_id'] = 'Запис «Діагноз» з таким id не знайдено.';
            } else {
                $treatment->setDiagnosis($related);
            }
        }
        if ($isNew || array_key_exists('prescription', $data)) {
            $value = trim((string) ($data['prescription'] ?? ''));
            if ($value === '') {
                $errors['prescription'] = 'Поле «Призначення» обов\'язкове.';
            } else {
                $treatment->setPrescription($value);
            }
        }
        if ($isNew || array_key_exists('start_date', $data)) {
            $value = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($data['start_date'] ?? ''));
            if (!$value) {
                $errors['start_date'] = 'Поле «Дата початку» має бути у форматі РРРР-ММ-ДД.';
            } else {
                $treatment->setStartDate($value);
            }
        }
        if ($isNew || array_key_exists('end_date', $data)) {
            $text = (string) ($data['end_date'] ?? '');
            $value = $text === '' ? null : \DateTimeImmutable::createFromFormat('!Y-m-d', $text);
            if ($value === false) {
                $errors['end_date'] = 'Поле «Дата завершення» має бути у форматі РРРР-ММ-ДД або порожнім.';
            } else {
                $treatment->setEndDate($value);
            }
        }

        // Дата завершення не може бути раніше дати початку.
        $start = $treatment->getStartDate();
        $end = $treatment->getEndDate();
        if ($start && $end && $end < $start) {
            $errors['end_date'] = 'Дата завершення не може бути раніше дати початку.';
        }

        return $errors;
    }
}