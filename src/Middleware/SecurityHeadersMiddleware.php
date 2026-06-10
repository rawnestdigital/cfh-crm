<?php

namespace App\Middleware;

/**
 * Security Headers Middleware
 * Applies security headers to all responses
 */
class SecurityHeadersMiddleware
{
    /**
     * Apply security headers
     */
    public static function apply(): void
    {
        // Prevent clickjacking attacks
        header('X-Frame-Options: DENY');

        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');

        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');

        // Force HTTPS
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');

        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self';");

        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Feature Policy (Permissions-Policy)
        header("Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()");

        // Remove server information
        header_remove('Server');
        header_remove('X-Powered-By');
        header_remove('X-AspNet-Version');

        // Set UTF-8 charset
        header('Content-Type: text/html; charset=utf-8');
    }
}
