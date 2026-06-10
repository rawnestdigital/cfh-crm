<?php

namespace App\Security;

/**
 * Input Validation and Sanitization
 * Prevents XSS and injection attacks
 */
class InputValidator
{
    /**
     * Validate and sanitize single input
     */
    public static function validate(mixed $input, string $type = 'string'): mixed
    {
        if (is_null($input)) {
            return null;
        }

        $input = self::trim($input);

        switch ($type) {
            case 'email':
                return self::validateEmail($input);

            case 'integer':
                return self::validateInteger($input);

            case 'float':
                return self::validateFloat($input);

            case 'url':
                return self::validateUrl($input);

            case 'phone':
                return self::validatePhone($input);

            case 'username':
                return self::validateUsername($input);

            case 'password':
                return self::validatePassword($input);

            case 'string':
            default:
                return self::sanitizeString($input);
        }
    }

    /**
     * Validate email address
     */
    private static function validateEmail(string $input): ?string
    {
        $email = filter_var($input, FILTER_VALIDATE_EMAIL);
        return $email ? strtolower($email) : null;
    }

    /**
     * Validate integer
     */
    private static function validateInteger(string $input): ?int
    {
        $int = filter_var($input, FILTER_VALIDATE_INT);
        return $int !== false ? (int)$int : null;
    }

    /**
     * Validate float
     */
    private static function validateFloat(string $input): ?float
    {
        $float = filter_var($input, FILTER_VALIDATE_FLOAT);
        return $float !== false ? (float)$float : null;
    }

    /**
     * Validate URL
     */
    private static function validateUrl(string $input): ?string
    {
        $url = filter_var($input, FILTER_VALIDATE_URL);
        if ($url && self::isValidHttpUrl($input)) {
            return $url;
        }
        return null;
    }

    /**
     * Validate phone number
     */
    private static function validatePhone(string $input): ?string
    {
        if (preg_match('/^[\d\s\-\+\(\)]{10,}$/', $input)) {
            return preg_replace('/[^\d\+]/', '', $input);
        }
        return null;
    }

    /**
     * Validate username
     */
    private static function validateUsername(string $input): ?string
    {
        if (preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $input)) {
            return $input;
        }
        return null;
    }

    /**
     * Validate password (basic)
     */
    private static function validatePassword(string $input): ?string
    {
        if (strlen($input) >= 8) {
            return $input;
        }
        return null;
    }

    /**
     * Sanitize string input
     */
    private static function sanitizeString(string $input): string
    {
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);

        // Remove control characters
        $input = preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', $input);

        // Trim whitespace
        return trim($input);
    }

    /**
     * Trim input value
     */
    private static function trim(mixed $input): string
    {
        if (is_array($input)) {
            return json_encode($input);
        }
        return trim((string)$input);
    }

    /**
     * Validate HTTP(S) URL
     */
    private static function isValidHttpUrl(string $url): bool
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        return in_array($scheme, ['http', 'https']);
    }

    /**
     * Validate multiple inputs
     */
    public static function validateArray(array $inputs, array $rules): array
    {
        $validated = [];

        foreach ($inputs as $key => $value) {
            if (!isset($rules[$key])) {
                continue;
            }

            $validated[$key] = self::validate($value, $rules[$key]);
        }

        return $validated;
    }

    /**
     * Check for SQL injection patterns
     */
    public static function hasSqlPatterns(string $input): bool
    {
        $patterns = [
            '/union\s+select/i',
            '/exec\s*\(/i',
            '/execute\s*\(/i',
            '/script/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/drop\s+table/i',
            '/delete\s+from/i',
            '/insert\s+into/i',
            '/update\s+\w+\s+set/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }
}
