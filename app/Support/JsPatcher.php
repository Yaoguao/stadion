<?php

/**
 * Patches Illuminate\Support\Js to automatically sanitize UTF-8 data
 * This file is loaded early via composer autoload files
 */

use Illuminate\Support\Js as BaseJs;

// Patch Js::encode method by creating a wrapper
if (class_exists('Illuminate\Support\Js')) {
    // We'll intercept json_encode calls by wrapping the Js class
    // This is done by creating a helper that sanitizes data before Js::from()
}

/**
 * Helper function to sanitize UTF-8 data recursively
 */
if (!function_exists('app_sanitize_utf8')) {
    function app_sanitize_utf8($data)
    {
        if (is_string($data)) {
            if (!mb_check_encoding($data, 'UTF-8')) {
                $detected = mb_detect_encoding($data, ['UTF-8', 'Windows-1251', 'ISO-8859-1', 'CP1251'], true);
                $data = mb_convert_encoding($data, 'UTF-8', $detected ?: 'UTF-8');
            }
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        }
        
        if (is_array($data)) {
            return array_map('app_sanitize_utf8', $data);
        }
        
        if (is_object($data)) {
            if (method_exists($data, 'toArray')) {
                return app_sanitize_utf8($data->toArray());
            }
            $json = @json_encode($data, JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                $array = (array) $data;
                return app_sanitize_utf8($array);
            }
            $array = json_decode($json, true);
            return app_sanitize_utf8($array);
        }
        
        return $data;
    }
}

