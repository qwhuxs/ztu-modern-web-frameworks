<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Diagnosis;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class DiagnosisController extends Controller
{
    /**
     * Показує список діагнозів.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('medical.diagnoses.index', ['diagnoses' => Diagnosis::with('appointment.patient')->latest()->get()]);
    }

    /**
     * Показує порожню форму, щоб додати діагноз.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('medical.diagnoses.form', ['diagnosis' => new Diagnosis(), 'appointments' => Appointment::with(['patient', 'doctor'])->latest('appointment_at')->get()]);
    }

    /**
     * Перевіряє дані форми та зберігає новий діагноз у базі.
     *
     * @param Request $request Дані, надіслані з форми
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        Diagnosis::create($this->validated($request));

        return redirect()->route('diagnoses.index');
    }

    /**
     * Показує заповнену форму, щоб змінити діагноз.
     *
     * @param Diagnosis $diagnosis Запис, знайдений за id з адреси
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Diagnosis $diagnosis)
    {
        return view('medical.diagnoses.form', ['diagnosis' => $diagnosis, 'appointments' => Appointment::with(['patient', 'doctor'])->latest('appointment_at')->get()]);
    }

    /**
     * Перевіряє дані форми та зберігає зміни.
     *
     * @param Request $request Дані, надіслані з форми
     * @param Diagnosis $diagnosis Запис, який треба змінити
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Diagnosis $diagnosis)
    {
        $diagnosis->update($this->validated($request));

        return redirect()->route('diagnoses.index');
    }

    /**
     * Видаляє діагноз, якщо з ним не пов'язані інші записи.
     *
     * @param Diagnosis $diagnosis Запис, який треба видалити
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Diagnosis $diagnosis)
    {
        try {
            $diagnosis->delete();
        } catch (QueryException) {
            return redirect()->route('diagnoses.index')
                ->with('error', 'Запис не можна видалити, бо з ним пов\'язані інші записи.');
        }

        return redirect()->route('diagnoses.index');
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
            'appointment_id' => ['required', 'exists:appointments,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);
    }
}