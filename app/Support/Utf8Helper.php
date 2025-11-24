<?php

/**
 * Helper functions for UTF-8 sanitization
 * This file is loaded early in the bootstrap process
 */

if (!function_exists('sanitize_utf8_for_json')) {
    /**
     * Recursively sanitize data to ensure valid UTF-8 encoding for JSON.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function sanitize_utf8_for_json($value)
    {
        if (is_string($value)) {
            // Remove invalid UTF-8 characters and ensure valid encoding
            if (!mb_check_encoding($value, 'UTF-8')) {
                $detected = mb_detect_encoding($value, ['UTF-8', 'Windows-1251', 'ISO-8859-1', 'CP1251'], true);
                $value = mb_convert_encoding($value, 'UTF-8', $detected ?: 'UTF-8');
            }
            // Remove any remaining invalid UTF-8 sequences
            return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }
        
        if (is_array($value)) {
            return array_map('sanitize_utf8_for_json', $value);
        }
        
        if (is_object($value)) {
            if (method_exists($value, 'toArray')) {
                return sanitize_utf8_for_json($value->toArray());
            }
            // Convert object to array and sanitize
            $json = @json_encode($value, JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                // If encoding fails, try to convert to array first
                $array = (array) $value;
                return sanitize_utf8_for_json($array);
            }
            $array = json_decode($json, true);
            return sanitize_utf8_for_json($array);
        }
        
        return $value;
    }
}

