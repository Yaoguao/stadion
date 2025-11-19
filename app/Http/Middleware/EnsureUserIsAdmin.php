<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем, что пользователь авторизован
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Проверяем, что пользователь является админом
        if (!auth()->user()->isAdmin()) {
            // Если не админ, перенаправляем на главную с сообщением
            return redirect()->route('home')->with('error', 'У вас нет доступа к админ-панели.');
        }

        return $next($request);
    }
}
