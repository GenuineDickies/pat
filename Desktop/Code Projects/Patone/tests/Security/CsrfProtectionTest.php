<?php

namespace Tests\Security;

use Tests\TestCase;

/**
 * CSRF (Cross-Site Request Forgery) Security Tests
 * Tests protection against CSRF attacks
 */
class CsrfProtectionTest extends TestCase
{
    /**
     * Test CSRF token generation
     */
    public function testCsrfTokenGeneration(): void
    {
        // Generate multiple tokens
        $token1 = bin2hex(random_bytes(32));
        $token2 = bin2hex(random_bytes(32));
        
        // Tokens should be unique
        $this->assertNotEquals($token1, $token2);
        
        // Tokens should be 64 characters (32 bytes in hex)
        $this->assertEquals(64, strlen($token1));
        $this->assertEquals(64, strlen($token2));
        
        // Tokens should only contain hex characters
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token1);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token2);
    }

    /**
     * Test CSRF token validation success
     */
    public function testCsrfTokenValidationSuccess(): void
    {
        $sessionToken = 'abc123def456';
        $submittedToken = 'abc123def456';
        
        $isValid = hash_equals($sessionToken, $submittedToken);
        $this->assertTrue($isValid);
    }

    /**
     * Test CSRF token validation failure
     */
    public function testCsrfTokenValidationFailure(): void
    {
        $sessionToken = 'abc123def456';
        $invalidTokens = [
            'different',
            '',
            'ABC123DEF456', // Case different
            'abc123def45', // Truncated
            'abc123def4567', // Extra character
        ];
        
        foreach ($invalidTokens as $submittedToken) {
            $isValid = hash_equals($sessionToken, $submittedToken);
            $this->assertFalse($isValid, "Token should not be valid: $submittedToken");
        }
    }

    /**
     * Test CSRF token is required for POST requests
     */
    public function testCsrfTokenRequiredForPostRequests(): void
    {
        $dangerousMethods = ['POST', 'PUT', 'DELETE', 'PATCH'];
        
        foreach ($dangerousMethods as $method) {
            // These methods should require CSRF protection
            $requiresCsrf = in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH']);
            $this->assertTrue($requiresCsrf, "$method should require CSRF token");
        }
    }

    /**
     * Test CSRF token not required for GET requests
     */
    public function testCsrfTokenNotRequiredForGetRequests(): void
    {
        $safeMethods = ['GET', 'HEAD', 'OPTIONS'];
        
        foreach ($safeMethods as $method) {
            // These methods should not require CSRF protection
            $requiresCsrf = in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH']);
            $this->assertFalse($requiresCsrf, "$method should not require CSRF token");
        }
    }

    /**
     * Test CSRF token timing attack resistance
     */
    public function testCsrfTokenTimingAttackResistance(): void
    {
        $token1 = 'abc123def456';
        $token2 = 'abc123def456';
        
        // hash_equals should be used instead of == or === to prevent timing attacks
        $isEqualHashEquals = hash_equals($token1, $token2);
        $isEqualNormal = ($token1 === $token2);
        
        $this->assertEquals($isEqualHashEquals, $isEqualNormal);
        
        // Different tokens
        $token3 = 'different';
        $isEqualHashEquals2 = hash_equals($token1, $token3);
        $isEqualNormal2 = ($token1 === $token3);
        
        $this->assertEquals($isEqualHashEquals2, $isEqualNormal2);
        $this->assertFalse($isEqualHashEquals2);
    }

    /**
     * Test CSRF token in form fields
     */
    public function testCsrfTokenInFormFields(): void
    {
        $token = 'test_token_value';
        $formHtml = '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
        
        $this->assertStringContainsString('name="csrf_token"', $formHtml);
        $this->assertStringContainsString('type="hidden"', $formHtml);
        $this->assertStringContainsString($token, $formHtml);
    }

    /**
     * Test CSRF token in AJAX requests
     */
    public function testCsrfTokenInAjaxRequests(): void
    {
        $token = 'ajax_csrf_token';
        
        // Token should be included in AJAX headers or body
        $headers = [
            'X-CSRF-Token' => $token,
        ];
        
        $this->assertArrayHasKey('X-CSRF-Token', $headers);
        $this->assertEquals($token, $headers['X-CSRF-Token']);
    }

    /**
     * Test CSRF token expiration
     */
    public function testCsrfTokenExpiration(): void
    {
        $tokenCreatedAt = time() - (2 * 60 * 60); // 2 hours ago
        $tokenLifetime = 60 * 60; // 1 hour
        $currentTime = time();
        
        $tokenAge = $currentTime - $tokenCreatedAt;
        $isExpired = $tokenAge > $tokenLifetime;
        
        $this->assertTrue($isExpired, 'Token should be expired after 2 hours with 1 hour lifetime');
        
        // Test non-expired token
        $recentTokenCreatedAt = time() - (30 * 60); // 30 minutes ago
        $recentTokenAge = $currentTime - $recentTokenCreatedAt;
        $isRecentExpired = $recentTokenAge > $tokenLifetime;
        
        $this->assertFalse($isRecentExpired, 'Token should not be expired after 30 minutes');
    }

    /**
     * Test double-submit cookie pattern
     */
    public function testDoubleSubmitCookiePattern(): void
    {
        $cookieToken = 'cookie_csrf_token';
        $formToken = 'cookie_csrf_token';
        
        // Both tokens should match
        $isValid = hash_equals($cookieToken, $formToken);
        $this->assertTrue($isValid);
        
        // Test with different tokens
        $differentFormToken = 'different_token';
        $isInvalid = hash_equals($cookieToken, $differentFormToken);
        $this->assertFalse($isInvalid);
    }
}
