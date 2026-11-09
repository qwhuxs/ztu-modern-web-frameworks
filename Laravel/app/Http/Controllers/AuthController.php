<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Отримує сервіс для роботи з токенами.
     *
     * @param JwtService $jwt Сервіс JWT
     */
    public function __construct(private JwtService $jwt)
    {
    }

    /**
     * Показує форму входу.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showLogin()
    {
        return view('medical.auth.login');
    }

    /**
     * Перевіряє пошту й пароль, створює JWT і зберігає його в cookie.
     *
     * @param Request $request Дані форми входу
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['email' => 'Неправильна пошта або пароль.'])->onlyInput('email');
        }

        return $this->withTokenCookie(redirect()->route('medical.home'), $user, $request);
    }

    /**
     * Показує форму реєстрації.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showRegister()
    {
        return view('medical.auth.register');
    }

    /**
     * Реєструє нового користувача з роллю client і одразу виконує вхід.
     *
     * @param Request $request Дані форми реєстрації
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($data + ['role' => User::ROLE_CLIENT]);

        return $this->withTokenCookie(redirect()->route('medical.home'), $user, $request);
    }

    /**
     * Виконує вихід: видаляє cookie з токеном.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        return redirect()->route('login')->withCookie(cookie()->forget('jwt'));
    }

    /**
     * API: перевіряє пошту й пароль і повертає JWT у форматі JSON.
     *
     * @param Request $request JSON з полями email і password
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiLogin(Request $request)
    {
        $email = (string) $request->input('email');
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check((string) $request->input('password'), $user->password)) {
            return response()->json(['message' => 'Неправильна пошта або пароль.'], 401);
        }

        return response()->json([
            'token' => $this->jwt->createToken($user),
            'token_type' => 'Bearer',
            'expires_in' => $this->jwt->ttlMinutes() * 60,
            'user' => $user->only(['id', 'name', 'email', 'role']),
        ]);
    }

    /**
     * API: повертає дані користувача, якому належить токен.
     *
     * @param Request $request Запит із заголовком Authorization: Bearer
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        return response()->json($request->user()->only(['id', 'name', 'email', 'role']));
    }

    /**
     * Додає до відповіді cookie jwt з новим токеном.
     *
     * @param \Illuminate\Http\RedirectResponse $response Відповідь-перенаправлення
     * @param User $user Користувач, який увійшов
     * @param Request $request Поточний запит
     * @return \Illuminate\Http\RedirectResponse
     */
    private function withTokenCookie($response, User $user, Request $request)
    {
        // HttpOnly: JavaScript не може прочитати токен; SameSite=Lax захищає від запитів з інших сайтів.
        return $response->withCookie(cookie(
            'jwt',
            $this->jwt->createToken($user),
            $this->jwt->ttlMinutes(),
            null,
            null,
            $request->isSecure(),
            true,
            false,
            'lax'
        ));
    }
}