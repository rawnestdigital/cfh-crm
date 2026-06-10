<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Security\InputValidator;
use App\Security\OutputEncoder;
use App\Security\PasswordService;
use App\Security\CsrfTokenManager;

/**
 * Security Test Suite
 * Tests for security implementations
 */
class SecurityTest extends TestCase
{
    protected function setUp(): void
    {
        // Initialize session for CSRF tests
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Test input validation
     */
    public function testEmailValidation()
    {
        // Valid emails
        $this->assertNotNull(InputValidator::validate('user@example.com', 'email'));
        $this->assertNotNull(InputValidator::validate('test.email@domain.co.uk', 'email'));

        // Invalid emails
        $this->assertNull(InputValidator::validate('invalid-email', 'email'));
        $this->assertNull(InputValidator::validate('user@', 'email'));
        $this->assertNull(InputValidator::validate('@example.com', 'email'));
    }

    public function testIntegerValidation()
    {
        // Valid integers
        $this->assertSame(123, InputValidator::validate('123', 'integer'));
        $this->assertSame(0, InputValidator::validate('0', 'integer'));
        $this->assertSame(-456, InputValidator::validate('-456', 'integer'));

        // Invalid integers
        $this->assertNull(InputValidator::validate('abc', 'integer'));
        $this->assertNull(InputValidator::validate('12.34', 'integer'));
    }

    public function testUsernameValidation()
    {
        // Valid usernames
        $this->assertNotNull(InputValidator::validate('user_123', 'username'));
        $this->assertNotNull(InputValidator::validate('john-doe', 'username'));
        $this->assertNotNull(InputValidator::validate('admin123', 'username'));

        // Invalid usernames
        $this->assertNull(InputValidator::validate('ab', 'username')); // Too short
        $this->assertNull(InputValidator::validate('user@name', 'username')); // Invalid char
        $this->assertNull(InputValidator::validate('a', 'username')); // Too short
    }

    /**
     * Test output encoding
     */
    public function testHtmlEncoding()
    {
        $dangerous = '<script>alert("XSS")</script>';
        $safe = OutputEncoder::html($dangerous);

        $this->assertStringNotContainsString('<script>', $safe);
        $this->assertStringContainsString('&lt;script&gt;', $safe);
    }

    public function testAttributeEncoding()
    {
        $dangerous = '" onclick="alert(1)"';
        $safe = OutputEncoder::attribute($dangerous);

        $this->assertStringNotContainsString('onclick', $safe);
    }

    public function testJavaScriptEncoding()
    {
        $string = '"<script>alert(1)</script>';
        $safe = OutputEncoder::javascript($string);

        // Should be JSON encoded
        $this->assertStringContainsString('\\u', $safe);
    }

    /**
     * Test password services
     */
    public function testPasswordHashing()
    {
        $password = 'SecurePassword123!';
        $hash = PasswordService::hash($password);

        // Verify correct password
        $this->assertTrue(PasswordService::verify($password, $hash));

        // Verify incorrect password
        $this->assertFalse(PasswordService::verify('WrongPassword', $hash));
    }

    public function testPasswordStrengthValidation()
    {
        // Weak password
        $weak = PasswordService::validateStrength('weak');
        $this->assertFalse($weak['valid']);
        $this->assertNotEmpty($weak['errors']);

        // Strong password
        $strong = PasswordService::validateStrength('SecurePass123!');
        $this->assertTrue($strong['valid']);
        $this->assertEmpty($strong['errors']);
    }

    /**
     * Test CSRF token
     */
    public function testCsrfTokenGeneration()
    {
        $token = CsrfTokenManager::generate();

        $this->assertNotEmpty($token);
        $this->assertEqual(strlen($token), 64); // 32 bytes = 64 hex chars
    }

    public function testCsrfTokenValidation()
    {
        $token = CsrfTokenManager::generate();

        // Valid token
        $this->assertTrue(CsrfTokenManager::validate($token));

        // Invalid token
        $this->assertFalse(CsrfTokenManager::validate('invalid-token'));
    }

    public function testCsrfTokenRegeneration()
    {
        $token1 = CsrfTokenManager::generate();
        $token2 = CsrfTokenManager::regenerate();

        // Tokens should be different
        $this->assertNotEqual($token1, $token2);

        // Old token should not be valid
        $this->assertFalse(CsrfTokenManager::validate($token1));
    }

    /**
     * Test SQL injection prevention
     */
    public function testSqlInjectionDetection()
    {
        // Dangerous patterns should be detected
        $this->assertTrue(InputValidator::hasSqlPatterns("' UNION SELECT * FROM users"));
        $this->assertTrue(InputValidator::hasSqlPatterns("1'; DROP TABLE users; --"));
        $this->assertTrue(InputValidator::hasSqlPatterns("admin' OR '1'='1"));

        // Safe strings should not be detected
        $this->assertFalse(InputValidator::hasSqlPatterns('John Doe'));
        $this->assertFalse(InputValidator::hasSqlPatterns('user@example.com'));
    }

    /**
     * Test XSS prevention
     */
    public function testXssPreventionInHtml()
    {
        $dangerous = '<img src=x onerror="alert(1)">';
        $safe = OutputEncoder::html($dangerous);

        // JavaScript should be escaped
        $this->assertStringNotContainsString('onerror', $safe);
    }
}
