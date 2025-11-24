<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Load UTF-8 sanitization helper early
if (file_exists(__DIR__ . '/../app/Support/Utf8Helper.php')) {
    require_once __DIR__ . '/../app/Support/Utf8Helper.php';
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle UTF-8 encoding errors (JsonException and ViewException)
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            // Check if this is a UTF-8 encoding error
            $message = $e->getMessage();
            $isUtf8Error = str_contains($message, 'Malformed UTF-8') || 
                          str_contains($message, 'incorrectly encoded') ||
                          $e instanceof \JsonException ||
                          ($e instanceof \Illuminate\View\ViewException && 
                           str_contains($message, 'Js.php'));
            
            // Check previous exception as well
            if (!$isUtf8Error && method_exists($e, 'getPrevious') && $e->getPrevious()) {
                $prev = $e->getPrevious();
                $prevMessage = $prev->getMessage();
                $isUtf8Error = str_contains($prevMessage, 'Malformed UTF-8') || 
                              str_contains($prevMessage, 'incorrectly encoded') ||
                              $prev instanceof \JsonException;
            }
            
            if ($isUtf8Error) {
                try {
                    return response()->view('errors.utf8', [
                        'message' => 'Произошла ошибка кодировки UTF-8. Пожалуйста, проверьте данные в базе данных и переменные окружения на наличие некорректных символов.',
                    ], 500);
                } catch (\Throwable $viewError) {
                    // If even the error view fails, return plain text
                    return response('Ошибка кодировки UTF-8. Проверьте данные в базе данных и переменные окружения.', 500)
                        ->header('Content-Type', 'text/plain; charset=utf-8');
                }
            }
        });
    })->create();
