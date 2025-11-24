<?php

namespace App\Support;

use Illuminate\Support\Js as BaseJs;

class SafeJs extends BaseJs
{
    /**
     * Create a new JavaScript string from the given value with UTF-8 sanitization.
     *
     * @param  mixed  $value
     * @param  int  $flags
     * @param  int  $depth
     * @return static
     */
    public static function from($value, $flags = 0, $depth = 512)
    {
        $sanitized = self::sanitizeUtf8($value);
        return parent::from($sanitized, $flags, $depth);
    }

    /**
     * Recursively sanitize data to ensure valid UTF-8 encoding.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected static function sanitizeUtf8($value)
    {
        if (is_string($value)) {
            // Remove invalid UTF-8 characters and ensure valid encoding
            if (!mb_check_encoding($value, 'UTF-8')) {
                $value = mb_convert_encoding($value, 'UTF-8', mb_detect_encoding($value) ?: 'UTF-8');
            }
            // Remove any remaining invalid UTF-8 sequences
            return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }
        
        if (is_array($value)) {
            return array_map([self::class, 'sanitizeUtf8'], $value);
        }
        
        if (is_object($value)) {
            if (method_exists($value, 'toArray')) {
                return self::sanitizeUtf8($value->toArray());
            }
            // Convert object to array and sanitize
            $json = @json_encode($value, JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                // If encoding fails, try to convert to array first
                $array = (array) $value;
                return self::sanitizeUtf8($array);
            }
            $array = json_decode($json, true);
            return self::sanitizeUtf8($array);
        }
        
        return $value;
    }
}

