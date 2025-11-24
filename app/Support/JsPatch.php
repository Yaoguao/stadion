<?php

namespace App\Support;

use Illuminate\Support\Js;

/**
 * Patch for Illuminate\Support\Js to automatically sanitize UTF-8 data
 * This file should be loaded early in the application lifecycle
 */
class JsPatch
{
    /**
     * Apply the patch to Js class
     */
    public static function apply(): void
    {
        // We'll use a different approach - override the encode method via reflection
        // or create a wrapper that intercepts calls
    }

    /**
     * Sanitize data for UTF-8 encoding
     *
     * @param  mixed  $data
     * @return mixed
     */
    public static function sanitize($data)
    {
        if (is_string($data)) {
            if (!mb_check_encoding($data, 'UTF-8')) {
                $detected = mb_detect_encoding($data, ['UTF-8', 'Windows-1251', 'ISO-8859-1', 'CP1251'], true);
                $data = mb_convert_encoding($data, 'UTF-8', $detected ?: 'UTF-8');
            }
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        }
        
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        
        if (is_object($data)) {
            if (method_exists($data, 'toArray')) {
                return self::sanitize($data->toArray());
            }
            $json = @json_encode($data, JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                $array = (array) $data;
                return self::sanitize($array);
            }
            $array = json_decode($json, true);
            return self::sanitize($array);
        }
        
        return $data;
    }
}

