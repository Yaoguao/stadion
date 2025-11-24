<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Prepare exception data for rendering.
     * This sanitizes UTF-8 data before it's passed to the exception renderer.
     */
    protected function prepareExceptionData(Throwable $e): array
    {
        $data = parent::prepareExceptionData($e);
        
        // Sanitize all string values in the exception data
        return $this->sanitizeUtf8($data);
    }

    /**
     * Recursively sanitize data to ensure valid UTF-8 encoding.
     *
     * @param  mixed  $data
     * @return mixed
     */
    protected function sanitizeUtf8($data)
    {
        if (is_string($data)) {
            if (!mb_check_encoding($data, 'UTF-8')) {
                $detected = mb_detect_encoding($data, ['UTF-8', 'Windows-1251', 'ISO-8859-1', 'CP1251'], true);
                $data = mb_convert_encoding($data, 'UTF-8', $detected ?: 'UTF-8');
            }
            // Remove any remaining invalid UTF-8 sequences
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        }
        
        if (is_array($data)) {
            return array_map([$this, 'sanitizeUtf8'], $data);
        }
        
        if (is_object($data)) {
            if (method_exists($data, 'toArray')) {
                return $this->sanitizeUtf8($data->toArray());
            }
            // Convert object to array and sanitize
            $json = @json_encode($data, JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                $array = (array) $data;
                return $this->sanitizeUtf8($array);
            }
            $array = json_decode($json, true);
            return $this->sanitizeUtf8($array);
        }
        
        return $data;
    }
}

