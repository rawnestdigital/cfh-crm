<?php

namespace App\Security;

/**
 * Session Management
 * Secure session handling with hijacking detection
 */
class SessionManager
{
    private const SESSION_CONFIG = [
        'lifetime' => 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict',
    ];

    private const SESSION_KEYS = [
        'init' => '_session_init',
        'user_agent' => '_user_agent',
        'ip_address' => '_ip_address',
        'fingerprint' => '_session_fingerprint',
        'created_at' => '_session_created',
    ];

    /**
     * Initialize secure session
     */
    public static function initialize(): void
    {
        // Set cookie parameters before session_start
        session_set_cookie_params(self::SESSION_CONFIG);

        // Set session options
        ini_set('session.name', 'CFHSESSID');
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_trans_sid', 0);
        ini_set('session.cache_limiter', 'nocache');
        ini_set('session.gc_maxlifetime', self::SESSION_CONFIG['lifetime']);

        session_start();

        // Validate session integrity
        self::validateIntegrity();
    }

    /**
     * Validate session integrity
     */
    private static function validateIntegrity(): void
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ipAddress = self::getClientIp();
        $fingerprint = self::generateFingerprint();

        // First time visiting
        if (!isset($_SESSION[self::SESSION_KEYS['init']])) {
            $_SESSION[self::SESSION_KEYS['init']] = true;
            $_SESSION[self::SESSION_KEYS['user_agent']] = $userAgent;
            $_SESSION[self::SESSION_KEYS['ip_address']] = $ipAddress;
            $_SESSION[self::SESSION_KEYS['fingerprint']] = $fingerprint;
            $_SESSION[self::SESSION_KEYS['created_at']] = time();
            return;
        }

        // Validate user agent
        if ($_SESSION[self::SESSION_KEYS['user_agent']] !== $userAgent) {
            self::destroy();
            die('Session validation failed: User agent mismatch');
        }

        // Validate IP address (strict mode)
        if ($_SESSION[self::SESSION_KEYS['ip_address']] !== $ipAddress) {
            self::destroy();
            die('Session validation failed: IP address mismatch');
        }

        // Validate fingerprint
        if ($_SESSION[self::SESSION_KEYS['fingerprint']] !== $fingerprint) {
            self::destroy();
            die('Session validation failed: Fingerprint mismatch');
        }
    }

    /**
     * Generate session fingerprint
     */
    private static function generateFingerprint(): string
    {
        $data = [
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
            $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
        ];

        return hash('sha256', implode('|', $data));
    }

    /**
     * Get client IP address
     */
    private static function getClientIp(): string
    {
        $ip = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Handle multiple IPs
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }

        // Validate IP format
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = 'unknown';
        }

        return $ip;
    }

    /**
     * Set user data in session
     */
    public static function setUser(array $userData): void
    {
        $_SESSION['user'] = $userData;
        $_SESSION['user_id'] = $userData['id'] ?? null;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
    }

    /**
     * Get user data from session
     */
    public static function getUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool
    {
        return $_SESSION['logged_in'] ?? false;
    }

    /**
     * Check session timeout
     */
    public static function checkTimeout(int $timeout = 1800): bool
    {
        $lastActivity = $_SESSION['last_activity'] ?? time();

        if (time() - $lastActivity > $timeout) {
            self::destroy();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    /**
     * Regenerate session ID
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /**
     * Destroy session
     */
    public static function destroy(): void
    {
        $_SESSION = [];
        session_destroy();
        setcookie('CFHSESSID', '', time() - 3600, '/', '', true, true);
    }
}
