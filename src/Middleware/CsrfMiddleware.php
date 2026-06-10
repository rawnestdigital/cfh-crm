<?php

namespace App\Middleware;

use App\Security\CsrfTokenManager;

/**
 * CSRF Middleware
 * Protects against Cross-Site Request Forgery
 */
class CsrfMiddleware
{
    private const EXEMPT_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    /**
     * Handle CSRF token validation
     */
    public static function handle(): bool
    {
        $method = $_SERVER['REQUEST_METHOD'];

        // Skip validation for safe methods
        if (in_array($method, self::EXEMPT_METHODS)) {
            return true;
        }

        // Skip validation for API endpoints with API key
        if (self::hasValidApiKey()) {
            return true;
        }

        // Get token from request
        $token = self::getToken();

        if (!$token || !CsrfTokenManager::validate($token)) {
            http_response_code(403);
            die(json_encode(['error' => 'CSRF token validation failed']));
        }

        return true;
    }

    /**
     * Get token from request
     */
    private static function getToken(): ?string
    {
        // Check POST data
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_csrf_token'])) {
            return $_POST['_csrf_token'];
        }

        // Check headers
        if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            return $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        // Check JSON body
        $json = json_decode(file_get_contents('php://input'), true);
        if (is_array($json) && isset($json['_csrf_token'])) {
            return $json['_csrf_token'];
        }

        return null;
    }

    /**
     * Check if request has valid API key
     */
    private static function hasValidApiKey(): bool
    {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? null;

        if (!$apiKey) {
            return false;
        }

        // Verify API key (implement your API key validation logic)
        // This is a placeholder
        return false;
    }
}
