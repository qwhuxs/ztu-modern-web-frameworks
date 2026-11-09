<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Показує список користувачів і їхніх ролей (лише для admin).
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('medical.users.index', ['users' => User::orderBy('id')->get()]);
    }

    /**
     * Змінює роль користувача (лише для admin).
     *
     * @param Request $request Дані форми з полем role
     * @param User $user Користувач, якому змінюють роль
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(array_keys(User::ROLE_LEVELS))],
        ]);

        if ($user->is($request->user())) {
            return redirect()->route('users.index')->with('error', 'Не можна змінити власну роль.');
        }

        $user->update($data);

        return redirect()->route('users.index');
    }
}