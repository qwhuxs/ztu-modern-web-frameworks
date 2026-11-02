<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    /**
     * Показує список лікарів.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('medical.doctors.index', ['doctors' => Doctor::orderBy('last_name')->get()]);
    }

    /**
     * Показує порожню форму, щоб додати лікаря.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('medical.doctors.form', ['doctor' => new Doctor()]);
    }

    /**
     * Перевіряє дані форми та зберігає нового лікаря у базі.
     *
     * @param Request $request Дані, надіслані з форми
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        Doctor::create($this->validated($request));

        return redirect()->route('doctors.index');
    }

    /**
     * Показує заповнену форму, щоб змінити лікаря.
     *
     * @param Doctor $doctor Запис, знайдений за id з адреси
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Doctor $doctor)
    {
        return view('medical.doctors.form', ['doctor' => $doctor]);
    }

    /**
     * Перевіряє дані форми та зберігає зміни.
     *
     * @param Request $request Дані, надіслані з форми
     * @param Doctor $doctor Запис, який треба змінити
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Doctor $doctor)
    {
        $doctor->update($this->validated($request));

        return redirect()->route('doctors.index');
    }

    /**
     * Видаляє лікаря, якщо з ним не пов'язані інші записи.
     *
     * @param Doctor $doctor Запис, який треба видалити
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Doctor $doctor)
    {
        try {
            $doctor->delete();
        } catch (QueryException) {
            return redirect()->route('doctors.index')
                ->with('error', 'Запис не можна видалити, бо з ним пов\'язані інші записи.');
        }

        return redirect()->route('doctors.index');
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
            'specialty' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
        ]);
    }
}