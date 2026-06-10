<?php

namespace App\Services;

use Exception;

/**
 * Password Service
 * Secure password handling and validation
 */
class PasswordService
{
    private const HASH_ALGO = PASSWORD_BCRYPT;
    private const HASH_COST = 12;
    private const MIN_LENGTH = 8;

    /**
     * Hash password securely
     */
    public static function hash(string $password): string
    {
        if (strlen($password) < self::MIN_LENGTH) {
            throw new Exception('Password must be at least ' . self::MIN_LENGTH . ' characters');
        }

        return password_hash($password, self::HASH_ALGO, ['cost' => self::HASH_COST]);
    }

    /**
     * Verify password
     */
    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if password needs rehashing
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, self::HASH_ALGO, ['cost' => self::HASH_COST]);
    }

    /**
     * Validate password strength
     */
    public static function validateStrength(string $password): array
    {
        $errors = [];
        $strength = 0;

        // Length check
        if (strlen($password) < self::MIN_LENGTH) {
            $errors[] = 'Password must be at least ' . self::MIN_LENGTH . ' characters';
        } else {
            $strength++;
        }

        // Lowercase letters
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain lowercase letters';
        } else {
            $strength++;
        }

        // Uppercase letters
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain uppercase letters';
        } else {
            $strength++;
        }

        // Numbers
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain numbers';
        } else {
            $strength++;
        }

        // Special characters
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
            $errors[] = 'Password must contain special characters';
        } else {
            $strength++;
        }

        return [
            'valid' => empty($errors),
            'strength' => $strength,
            'errors' => $errors,
        ];
    }

    /**
     * Generate random password
     */
    public static function generate(int $length = 16): string
    {
        $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $charset[random_int(0, strlen($charset) - 1)];
        }

        return $password;
    }
}
