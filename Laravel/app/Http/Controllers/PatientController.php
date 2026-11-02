<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersRecords;
use App\Models\Patient;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    use FiltersRecords;

    /**
     * Показує список пацієнтів із фільтрами та пагінацією.
     *
     * @param Request $request Поточний запит
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = Patient::query()->orderBy('last_name');

        $this->applyFilters($query, $request, [
            'id' => 'exact',
            'first_name' => 'text',
            'last_name' => 'text',
            'birth_date' => 'date',
            'phone' => 'text',
            'created_at' => 'date',
            'updated_at' => 'date',
        ]);

        return view('medical.patients.index', [
            'patients' => $query->paginate($this->itemsPerPage($request))->withQueryString(),
        ]);
    }

    /**
     * Показує порожню форму, щоб додати пацієнта.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('medical.patients.form', ['patient' => new Patient()]);
    }

    /**
     * Перевіряє дані форми та зберігає нового пацієнта у базі.
     *
     * @param Request $request Дані, надіслані з форми
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        Patient::create($this->validated($request));

        return redirect()->route('patients.index');
    }

    /**
     * Показує заповнену форму, щоб змінити пацієнта.
     *
     * @param Patient $patient Запис, знайдений за id з адреси
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Patient $patient)
    {
        return view('medical.patients.form', ['patient' => $patient]);
    }

    /**
     * Перевіряє дані форми та зберігає зміни.
     *
     * @param Request $request Дані, надіслані з форми
     * @param Patient $patient Запис, який треба змінити
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Patient $patient)
    {
        $patient->update($this->validated($request));

        return redirect()->route('patients.index');
    }

    /**
     * Видаляє пацієнта, якщо з ним не пов'язані інші записи.
     *
     * @param Patient $patient Запис, який треба видалити
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Patient $patient)
    {
        try {
            $patient->delete();
        } catch (QueryException) {
            return redirect()->route('patients.index')
                ->with('error', 'Запис не можна видалити, бо з ним пов\'язані інші записи.');
        }

        return redirect()->route('patients.index');
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
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'phone' => ['required', 'string', 'max:20'],
        ]);
    }
}