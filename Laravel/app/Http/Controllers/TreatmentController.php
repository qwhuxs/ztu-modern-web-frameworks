<?php

namespace App\Http\Controllers;

use App\Models\Diagnosis;
use App\Models\Treatment;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class TreatmentController extends Controller
{
    /**
     * Показує список записів про лікування.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('medical.treatments.index', ['treatments' => Treatment::with('diagnosis.appointment.patient')->latest()->get()]);
    }

    /**
     * Показує порожню форму, щоб додати запис про лікування.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('medical.treatments.form', ['treatment' => new Treatment(), 'diagnoses' => Diagnosis::with('appointment.patient')->latest()->get()]);
    }

    /**
     * Перевіряє дані форми та зберігає новий запис про лікування у базі.
     *
     * @param Request $request Дані, надіслані з форми
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        Treatment::create($this->validated($request));

        return redirect()->route('treatments.index');
    }

    /**
     * Показує заповнену форму, щоб змінити запис про лікування.
     *
     * @param Treatment $treatment Запис, знайдений за id з адреси
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Treatment $treatment)
    {
        return view('medical.treatments.form', ['treatment' => $treatment, 'diagnoses' => Diagnosis::with('appointment.patient')->latest()->get()]);
    }

    /**
     * Перевіряє дані форми та зберігає зміни.
     *
     * @param Request $request Дані, надіслані з форми
     * @param Treatment $treatment Запис, який треба змінити
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Treatment $treatment)
    {
        $treatment->update($this->validated($request));

        return redirect()->route('treatments.index');
    }

    /**
     * Видаляє запис про лікування, якщо з ним не пов'язані інші записи.
     *
     * @param Treatment $treatment Запис, який треба видалити
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Treatment $treatment)
    {
        try {
            $treatment->delete();
        } catch (QueryException) {
            return redirect()->route('treatments.index')
                ->with('error', 'Запис не можна видалити, бо з ним пов\'язані інші записи.');
        }

        return redirect()->route('treatments.index');
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
            'diagnosis_id' => ['required', 'exists:diagnoses,id'],
            'prescription' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
    }
}