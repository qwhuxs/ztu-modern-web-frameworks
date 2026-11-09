<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthenticate
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
     * Знаходить користувача за JWT і перевіряє його роль.
     * Без параметра $role лише визначає, хто увійшов; з параметром вимагає цю роль або вищу.
     *
     * @param Request $request Поточний запит
     * @param Closure $next Наступний обробник запиту
     * @param string|null $role Мінімальна потрібна роль: client, manager або admin
     * @return Response
     */
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        $user = $this->resolveUser($request);

        if ($user) {
            Auth::setUser($user);
        }

        if ($role === null) {
            return $next($request);
        }

        if (!$user) {
            return $this->wantsJson($request)
                ? response()->json(['message' => 'Потрібна автентифікація.'], 401)
                : redirect()->route('login')->with('error', 'Увійдіть, щоб продовжити.');
        }

        if (!$user->hasRole($role)) {
            return $this->wantsJson($request)
                ? response()->json(['message' => 'Недостатньо прав для цієї дії.'], 403)
                : response()->view('medical.forbidden', [], 403);
        }

        return $next($request);
    }

    /**
     * Читає токен із заголовка Authorization: Bearer або з cookie jwt і знаходить користувача.
     *
     * @param Request $request Поточний запит
     * @return User|null
     */
    private function resolveUser(Request $request): ?User
    {
        $token = $request->bearerToken() ?: $request->cookie('jwt');

        if (!is_string($token) || $token === '') {
            return null;
        }

        $payload = $this->jwt->decode($token);

        return $payload ? User::find($payload['sub'] ?? 0) : null;
    }

    /**
     * Визначає, чи треба відповідати у форматі JSON (запит до API).
     *
     * @param Request $request Поточний запит
     * @return bool
     */
    private function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*') || $request->bearerToken() !== null;
    }
}