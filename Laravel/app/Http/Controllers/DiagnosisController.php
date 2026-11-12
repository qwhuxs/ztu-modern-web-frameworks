<?php

namespace App\Http\Controllers;

use App\Models\Diagnosis;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class DiagnosisController extends Controller
{
    /**
     * READ: повертає список діагнозів.
     * Кожна модель перетворюється на масив методом jsonSerialize().
     *
     * @return JsonResponse
     */
    public function getDiagnoses(): JsonResponse
    {
        return response()->json(Diagnosis::with(['appointment'])->latest('id')->get(), Response::HTTP_OK);
    }

    /**
     * READ: повертає один запис за id.
     *
     * @param string $id Ідентифікатор запису
     * @return JsonResponse
     */
    public function getDiagnosisById(string $id): JsonResponse
    {
        $diagnosis = Diagnosis::with(['appointment'])->find($id);
        if (!$diagnosis) {
            return response()->json(['error' => 'Діагноз не знайдено'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($diagnosis, Response::HTTP_OK);
    }

    /**
     * CREATE: створює новий запис із JSON-даних запиту.
     *
     * @param Request $request Запит із JSON-тілом
     * @return JsonResponse
     */
    public function createDiagnosis(Request $request): JsonResponse
    {
        $data = $request->json()->all();
        $errors = $this->validateData($data, true);
        if ($errors) {
            return response()->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $diagnosis = Diagnosis::create($data);

        return response()->json($diagnosis->fresh(), Response::HTTP_CREATED);
    }

    /**
     * UPDATE: змінює лише ті поля, які передано в JSON.
     *
     * @param string $id Ідентифікатор запису
     * @param Request $request Запит із JSON-тілом
     * @return JsonResponse
     */
    public function updateDiagnosisById(string $id, Request $request): JsonResponse
    {
        $diagnosis = Diagnosis::find($id);
        if (!$diagnosis) {
            return response()->json(['error' => 'Діагноз не знайдено'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->json()->all();
        $errors = $this->validateData($data, false, $diagnosis);
        if ($errors) {
            return response()->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $diagnosis->update($data);

        return response()->json($diagnosis->fresh(), Response::HTTP_OK);
    }

    /**
     * DELETE: видаляє запис і повертає 204 No Content.
     * Якщо з записом пов'язані інші дані, повертає 409 Conflict.
     *
     * @param string $id Ідентифікатор запису
     * @return JsonResponse
     */
    public function deleteDiagnosisById(string $id): JsonResponse
    {
        $diagnosis = Diagnosis::find($id);
        if (!$diagnosis) {
            return response()->json(['error' => 'Діагноз не знайдено'], Response::HTTP_NOT_FOUND);
        }

        try {
            $diagnosis->delete();
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
     * @param Diagnosis|null $diagnosis Наявний запис (для оновлення)
     * @return array Помилки у форматі [поле => [повідомлення]]
     */
    private function validateData(array $data, bool $isNew, ?Diagnosis $diagnosis = null): array
    {
        $r = $isNew ? 'required|' : 'sometimes|required|';

        $validator = Validator::make($data, [
            'appointment_id' => $r.'integer|exists:appointments,id',
            'name' => $r.'string|max:150',
            'description' => 'nullable|string',
        ]);

        return $validator->errors()->toArray();
    }
}