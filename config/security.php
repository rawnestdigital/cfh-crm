<?php

/**
 * Security Configuration
 * Production-ready security settings
 */

return [
    /**
     * Database Security
     */
    'database' => [
        'use_prepared_statements' => true,
        'charset' => 'utf8mb4',
        'ssl_verify' => true,
        'connection_timeout' => 10,
        'query_timeout' => 30,
    ],

    /**
     * Session Security
     */
    'session' => [
        'lifetime' => 3600,           // 1 hour
        'secure_cookies' => true,     // HTTPS only
        'httponly' => true,           // No JS access
        'samesite' => 'Strict',       // CSRF protection
        'regenerate_on_login' => true,
        'cookie_name' => 'CFHSESSID',
        'use_strict_mode' => true,
        'use_only_cookies' => true,
        'use_trans_sid' => false,
    ],

    /**
     * CSRF Protection
     */
    'csrf' => [
        'enabled' => true,
        'token_length' => 32,
        'token_expiry' => 3600,
        'token_name' => '_csrf_token',
        'header_name' => 'X-CSRF-Token',
    ],

    /**
     * File Upload Security
     */
    'uploads' => [
        'max_size' => 5 * 1024 * 1024,  // 5MB
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'docx'],
        'store_outside_root' => true,
        'upload_directory' => '/var/www/secure_uploads',
        'scan_for_malware' => true,
        'validate_magic_bytes' => true,
    ],

    /**
     * Input Validation
     */
    'validation' => [
        'strict_types' => true,
        'sanitize_input' => true,
        'escape_output' => true,
        'max_input_size' => 1024 * 1024, // 1MB
    ],

    /**
     * Security Headers
     */
    'headers' => [
        'X-Frame-Options' => 'DENY',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self';",
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
    ],

    /**
     * Password Policy
     */
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special_chars' => true,
        'hash_algorithm' => PASSWORD_BCRYPT,
        'hash_cost' => 12,
        'expiry_days' => 90,
    ],

    /**
     * Rate Limiting
     */
    'rate_limit' => [
        'enabled' => true,
        'login_attempts' => 5,
        'login_lockout_minutes' => 15,
        'api_requests_per_minute' => 60,
        'api_requests_per_hour' => 1000,
    ],

    /**
     * Logging
     */
    'logging' => [
        'enabled' => true,
        'log_file' => '/var/log/cfh-crm/app.log',
        'error_log_file' => '/var/log/cfh-crm/error.log',
        'security_log_file' => '/var/log/cfh-crm/security.log',
        'max_log_size' => 10 * 1024 * 1024, // 10MB
        'log_rotation' => true,
        'retention_days' => 90,
    ],

    /**
     * API Security
     */
    'api' => [
        'require_api_key' => true,
        'api_key_header' => 'X-API-Key',
        'cors_enabled' => false,
        'allowed_origins' => [],
    ],
];
