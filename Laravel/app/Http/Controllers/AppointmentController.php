<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AppointmentController extends Controller
{
    /**
     * READ: повертає список прийомів.
     * Кожна модель перетворюється на масив методом jsonSerialize().
     *
     * @return JsonResponse
     */
    public function getAppointments(): JsonResponse
    {
        return response()->json(Appointment::with(['patient', 'doctor'])->latest('appointment_at')->get(), Response::HTTP_OK);
    }

    /**
     * READ: повертає один запис за id.
     *
     * @param string $id Ідентифікатор запису
     * @return JsonResponse
     */
    public function getAppointmentById(string $id): JsonResponse
    {
        $appointment = Appointment::with(['patient', 'doctor'])->find($id);
        if (!$appointment) {
            return response()->json(['error' => 'Прийом не знайдено'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($appointment, Response::HTTP_OK);
    }

    /**
     * CREATE: створює новий запис із JSON-даних запиту.
     *
     * @param Request $request Запит із JSON-тілом
     * @return JsonResponse
     */
    public function createAppointment(Request $request): JsonResponse
    {
        $data = $request->json()->all();
        $errors = $this->validateData($data, true);
        if ($errors) {
            return response()->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $appointment = Appointment::create($data);

        return response()->json($appointment->fresh(), Response::HTTP_CREATED);
    }

    /**
     * UPDATE: змінює лише ті поля, які передано в JSON.
     *
     * @param string $id Ідентифікатор запису
     * @param Request $request Запит із JSON-тілом
     * @return JsonResponse
     */
    public function updateAppointmentById(string $id, Request $request): JsonResponse
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return response()->json(['error' => 'Прийом не знайдено'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->json()->all();
        $errors = $this->validateData($data, false, $appointment);
        if ($errors) {
            return response()->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $appointment->update($data);

        return response()->json($appointment->fresh(), Response::HTTP_OK);
    }

    /**
     * DELETE: видаляє запис і повертає 204 No Content.
     * Якщо з записом пов'язані інші дані, повертає 409 Conflict.
     *
     * @param string $id Ідентифікатор запису
     * @return JsonResponse
     */
    public function deleteAppointmentById(string $id): JsonResponse
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return response()->json(['error' => 'Прийом не знайдено'], Response::HTTP_NOT_FOUND);
        }

        try {
            $appointment->delete();
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
     * @param Appointment|null $appointment Наявний запис (для оновлення)
     * @return array Помилки у форматі [поле => [повідомлення]]
     */
    private function validateData(array $data, bool $isNew, ?Appointment $appointment = null): array
    {
        $r = $isNew ? 'required|' : 'sometimes|required|';

        $validator = Validator::make($data, [
            'patient_id' => $r.'integer|exists:patients,id',
            'doctor_id' => $r.'integer|exists:doctors,id',
            'appointment_at' => $r.'date_format:Y-m-d H:i',
            'complaints' => 'nullable|string',
        ]);

        return $validator->errors()->toArray();
    }
}