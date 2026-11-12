<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersRecords;
use App\Models\Treatment;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class TreatmentController extends Controller
{
    use FiltersRecords;

    /**
     * Поля, за якими можна фільтрувати список: [поле таблиці => тип пошуку].
     * EQUAL - точний збіг, LIKE - частина тексту, BETWEEN - діапазон (field[gte], field[lte]) або точна дата.
     *
     * @var array<string, string>
     */
    public const FIELDS = [
        'id' => 'EQUAL',
        'diagnosis_id' => 'EQUAL',
        'prescription' => 'LIKE',
        'start_date' => 'BETWEEN',
        'end_date' => 'BETWEEN',
        'created_at' => 'BETWEEN',
        'updated_at' => 'BETWEEN',
    ];

    /**
     * READ: повертає список записів про лікування з фільтрацією та пагінацією.
     * Фільтри задаються параметрами запиту за полями з FIELDS,
     * а пагінація - параметрами page та itemsPerPage.
     *
     * @param Request $request Запит із параметрами фільтра
     * @return JsonResponse
     */
    public function getTreatments(Request $request): JsonResponse
    {
        $criteria = $request->query();
        $query = $this->filterData(Treatment::with(['diagnosis'])->latest('id'), $criteria, self::FIELDS);

        return response()->json($this->paginate($query, $criteria), Response::HTTP_OK);
    }

    /**
     * READ: повертає один запис за id.
     *
     * @param string $id Ідентифікатор запису
     * @return JsonResponse
     */
    public function getTreatmentById(string $id): JsonResponse
    {
        $treatment = Treatment::with(['diagnosis'])->find($id);
        if (!$treatment) {
            return response()->json(['error' => 'Запис про лікування не знайдено'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($treatment, Response::HTTP_OK);
    }

    /**
     * CREATE: створює новий запис із JSON-даних запиту.
     *
     * @param Request $request Запит із JSON-тілом
     * @return JsonResponse
     */
    public function createTreatment(Request $request): JsonResponse
    {
        $data = $request->json()->all();
        $errors = $this->validateData($data, true);
        if ($errors) {
            return response()->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $treatment = Treatment::create($data);

        return response()->json($treatment->fresh(), Response::HTTP_CREATED);
    }

    /**
     * UPDATE: змінює лише ті поля, які передано в JSON.
     *
     * @param string $id Ідентифікатор запису
     * @param Request $request Запит із JSON-тілом
     * @return JsonResponse
     */
    public function updateTreatmentById(string $id, Request $request): JsonResponse
    {
        $treatment = Treatment::find($id);
        if (!$treatment) {
            return response()->json(['error' => 'Запис про лікування не знайдено'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->json()->all();
        $errors = $this->validateData($data, false, $treatment);
        if ($errors) {
            return response()->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $treatment->update($data);

        return response()->json($treatment->fresh(), Response::HTTP_OK);
    }

    /**
     * DELETE: видаляє запис і повертає 204 No Content.
     * Якщо з записом пов'язані інші дані, повертає 409 Conflict.
     *
     * @param string $id Ідентифікатор запису
     * @return JsonResponse
     */
    public function deleteTreatmentById(string $id): JsonResponse
    {
        $treatment = Treatment::find($id);
        if (!$treatment) {
            return response()->json(['error' => 'Запис про лікування не знайдено'], Response::HTTP_NOT_FOUND);
        }

        try {
            $treatment->delete();
        } catch (QueryException) {
            return response()->json(['error' => 'Запис не можна видалити, бо з ним пов\'язані інші записи'], Response::HTTP_CONFLICT);
        }

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Перевіряє дані з JSON. Для нового запису обов'язкові всі поля, для оновлення перевіряються лише передані.
     *
     * @param array $data Дані з JSON
     * @param bool $isNew true, якщо створюється новий запис
     * @param Treatment|null $treatment Наявний запис (для оновлення)
     * @return array Помилки у форматі [поле => [повідомлення]]
     */
    private function validateData(array $data, bool $isNew, ?Treatment $treatment = null): array
    {
        $r = $isNew ? 'required|' : 'sometimes|required|';

        $validator = Validator::make($data, [
            'diagnosis_id' => $r.'integer|exists:diagnoses,id',
            'prescription' => $r.'string',
            'start_date' => $r.'date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
        ]);

        // Дата завершення не може бути раніше дати початку (з урахуванням уже збережених даних).
        $start = $data['start_date'] ?? $treatment?->start_date?->format('Y-m-d');
        $end = array_key_exists('end_date', $data) ? $data['end_date'] : $treatment?->end_date?->format('Y-m-d');
        if ($start && $end && $end < $start) {
            $validator->errors()->add('end_date', 'Дата завершення не може бути раніше дати початку.');
        }

        return $validator->errors()->toArray();
    }
}