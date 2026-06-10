<?php

namespace App\Security;

use Exception;

/**
 * CSRF Token Management
 * Protects against Cross-Site Request Forgery attacks
 */
class CsrfTokenManager
{
    private const TOKEN_LENGTH = 32;
    private const TOKEN_NAME = '_csrf_token';
    private const TOKEN_TIME = '_csrf_time';
    private const TOKEN_EXPIRY = 3600; // 1 hour

    /**
     * Generate new CSRF token
     */
    public static function generate(): string
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::TOKEN_NAME] = $token;
        $_SESSION[self::TOKEN_TIME] = time();
        return $token;
    }

    /**
     * Get current token
     */
    public static function getToken(): string
    {
        if (!isset($_SESSION[self::TOKEN_NAME])) {
            return self::generate();
        }

        // Check if token has expired
        if (isset($_SESSION[self::TOKEN_TIME])) {
            if (time() - $_SESSION[self::TOKEN_TIME] > self::TOKEN_EXPIRY) {
                return self::regenerate();
            }
        }

        return $_SESSION[self::TOKEN_NAME];
    }

    /**
     * Validate token
     */
    public static function validate(string $token): bool
    {
        if (!isset($_SESSION[self::TOKEN_NAME])) {
            return false;
        }

        // Check token expiry
        if (isset($_SESSION[self::TOKEN_TIME])) {
            if (time() - $_SESSION[self::TOKEN_TIME] > self::TOKEN_EXPIRY) {
                return false;
            }
        }

        // Use constant-time comparison to prevent timing attacks
        return hash_equals($_SESSION[self::TOKEN_NAME], $token);
    }

    /**
     * Regenerate token
     */
    public static function regenerate(): string
    {
        unset($_SESSION[self::TOKEN_NAME]);
        unset($_SESSION[self::TOKEN_TIME]);
        return self::generate();
    }

    /**
     * Invalidate token
     */
    public static function invalidate(): void
    {
        unset($_SESSION[self::TOKEN_NAME]);
        unset($_SESSION[self::TOKEN_TIME]);
    }
}
