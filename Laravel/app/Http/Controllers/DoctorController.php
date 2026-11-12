<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class DoctorController extends Controller
{
    /**
     * READ: повертає список лікарів.
     * Кожна модель перетворюється на масив методом jsonSerialize().
     *
     * @return JsonResponse
     */
    public function getDoctors(): JsonResponse
    {
        return response()->json(Doctor::query()->orderBy('last_name')->get(), Response::HTTP_OK);
    }

    /**
     * READ: повертає один запис за id.
     *
     * @param string $id Ідентифікатор запису
     * @return JsonResponse
     */
    public function getDoctorById(string $id): JsonResponse
    {
        $doctor = Doctor::query()->find($id);
        if (!$doctor) {
            return response()->json(['error' => 'Лікаря не знайдено'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($doctor, Response::HTTP_OK);
    }

    /**
     * CREATE: створює новий запис із JSON-даних запиту.
     *
     * @param Request $request Запит із JSON-тілом
     * @return JsonResponse
     */
    public function createDoctor(Request $request): JsonResponse
    {
        $data = $request->json()->all();
        $errors = $this->validateData($data, true);
        if ($errors) {
            return response()->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $doctor = Doctor::create($data);

        return response()->json($doctor->fresh(), Response::HTTP_CREATED);
    }

    /**
     * UPDATE: змінює лише ті поля, які передано в JSON.
     *
     * @param string $id Ідентифікатор запису
     * @param Request $request Запит із JSON-тілом
     * @return JsonResponse
     */
    public function updateDoctorById(string $id, Request $request): JsonResponse
    {
        $doctor = Doctor::find($id);
        if (!$doctor) {
            return response()->json(['error' => 'Лікаря не знайдено'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->json()->all();
        $errors = $this->validateData($data, false, $doctor);
        if ($errors) {
            return response()->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $doctor->update($data);

        return response()->json($doctor->fresh(), Response::HTTP_OK);
    }

    /**
     * DELETE: видаляє запис і повертає 204 No Content.
     * Якщо з записом пов'язані інші дані, повертає 409 Conflict.
     *
     * @param string $id Ідентифікатор запису
     * @return JsonResponse
     */
    public function deleteDoctorById(string $id): JsonResponse
    {
        $doctor = Doctor::find($id);
        if (!$doctor) {
            return response()->json(['error' => 'Лікаря не знайдено'], Response::HTTP_NOT_FOUND);
        }

        try {
            $doctor->delete();
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
     * @param Doctor|null $doctor Наявний запис (для оновлення)
     * @return array Помилки у форматі [поле => [повідомлення]]
     */
    private function validateData(array $data, bool $isNew, ?Doctor $doctor = null): array
    {
        $r = $isNew ? 'required|' : 'sometimes|required|';

        $validator = Validator::make($data, [
            'first_name' => $r.'string|max:100',
            'last_name' => $r.'string|max:100',
            'specialty' => $r.'string|max:100',
            'phone' => $r.'string|max:20',
        ]);

        return $validator->errors()->toArray();
    }
}