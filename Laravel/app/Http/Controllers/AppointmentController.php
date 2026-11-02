<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Показує список прийомів.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('medical.appointments.index', ['appointments' => Appointment::with(['patient', 'doctor'])->latest('appointment_at')->get()]);
    }

    /**
     * Показує порожню форму, щоб додати прийом.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('medical.appointments.form', ['appointment' => new Appointment(), 'patients' => Patient::orderBy('last_name')->get(), 'doctors' => Doctor::orderBy('last_name')->get()]);
    }

    /**
     * Перевіряє дані форми та зберігає новий прийом у базі.
     *
     * @param Request $request Дані, надіслані з форми
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        Appointment::create($this->validated($request));

        return redirect()->route('appointments.index');
    }

    /**
     * Показує заповнену форму, щоб змінити прийом.
     *
     * @param Appointment $appointment Запис, знайдений за id з адреси
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Appointment $appointment)
    {
        return view('medical.appointments.form', ['appointment' => $appointment, 'patients' => Patient::orderBy('last_name')->get(), 'doctors' => Doctor::orderBy('last_name')->get()]);
    }

    /**
     * Перевіряє дані форми та зберігає зміни.
     *
     * @param Request $request Дані, надіслані з форми
     * @param Appointment $appointment Запис, який треба змінити
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Appointment $appointment)
    {
        $appointment->update($this->validated($request));

        return redirect()->route('appointments.index');
    }

    /**
     * Видаляє прийом, якщо з ним не пов'язані інші записи.
     *
     * @param Appointment $appointment Запис, який треба видалити
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Appointment $appointment)
    {
        try {
            $appointment->delete();
        } catch (QueryException) {
            return redirect()->route('appointments.index')
                ->with('error', 'Запис не можна видалити, бо з ним пов\'язані інші записи.');
        }

        return redirect()->route('appointments.index');
    }

    /**
     * Перевіряє дані форми за правилами.
     *
     * @param Request $request Дані, надіслані з форми
     * @return array Перевірені дані
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['required', 'exists:doctors,id'],
            'appointment_at' => ['required', 'date'],
            'complaints' => ['nullable', 'string'],
        ]);
    }
}