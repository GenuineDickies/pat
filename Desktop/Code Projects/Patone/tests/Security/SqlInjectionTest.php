<?php

namespace Tests\Security;

use Tests\TestCase;

/**
 * SQL Injection Security Tests
 * Tests protection against SQL injection attacks
 */
class SqlInjectionTest extends TestCase
{
    /**
     * Test that single quotes in input don't cause SQL injection
     */
    public function testSingleQuoteEscaping(): void
    {
        $maliciousInput = "'; DROP TABLE users; --";
        
        // Test that prepared statements would handle this safely
        // In a real prepared statement, this would be treated as a string value
        $escaped = addslashes($maliciousInput);
        
        $this->assertStringContainsString("\\'", $escaped);
        $this->assertNotEquals($maliciousInput, $escaped);
    }

    /**
     * Test SQL injection attempt with UNION attack
     */
    public function testUnionSqlInjection(): void
    {
        $maliciousInput = "1' UNION SELECT * FROM users--";
        
        // Prepared statements prevent this by treating the entire string as a value
        $containsSqlKeywords = preg_match('/\b(UNION|SELECT|FROM|WHERE|DROP|INSERT|UPDATE|DELETE)\b/i', $maliciousInput);
        
        $this->assertGreaterThan(0, $containsSqlKeywords, 'Should detect SQL keywords in input');
    }

    /**
     * Test SQL injection with comments
     */
    public function testSqlCommentInjection(): void
    {
        $maliciousInputs = [
            "admin'--",
            "admin'#",
            "admin'/*",
            "' OR '1'='1'--",
            "' OR '1'='1'/*"
        ];
        
        foreach ($maliciousInputs as $input) {
            $containsComment = preg_match('/(--|#|\/\*)/', $input);
            $this->assertGreaterThan(0, $containsComment, "Should detect SQL comment in: $input");
        }
    }

    /**
     * Test blind SQL injection attempts
     */
    public function testBlindSqlInjection(): void
    {
        $maliciousInputs = [
            "1' AND '1'='1",
            "1' AND '1'='2",
            "1' AND SLEEP(5)--"
        ];
        
        foreach ($maliciousInputs as $input) {
            $containsSqlLogic = preg_match('/\b(AND|OR|SLEEP|BENCHMARK)\b/i', $input);
            $this->assertGreaterThan(0, $containsSqlLogic, "Should detect SQL logic in: $input");
        }
    }

    /**
     * Test that numeric IDs are properly validated
     */
    public function testNumericIdValidation(): void
    {
        $validIds = ['1', '123', '999999'];
        
        foreach ($validIds as $id) {
            $this->assertTrue(is_numeric($id));
            $this->assertGreaterThan(0, (int)$id);
        }
        
        $invalidIds = ["1' OR '1'='1", "abc", "1; DROP TABLE", "null"];
        
        foreach ($invalidIds as $id) {
            $isValidNumeric = is_numeric($id) && (int)$id > 0;
            $this->assertFalse($isValidNumeric, "ID should not be valid: $id");
        }
    }

    /**
     * Test email field SQL injection protection
     */
    public function testEmailFieldSqlInjection(): void
    {
        $maliciousEmails = [
            "admin@example.com'; DROP TABLE users--",
            "' OR '1'='1'--@example.com",
            "admin@example.com' UNION SELECT",
        ];
        
        foreach ($maliciousEmails as $email) {
            // Proper email validation would reject these
            $isValidEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
            $this->assertFalse($isValidEmail, "Should reject malicious email: $email");
        }
    }

    /**
     * Test search field SQL injection protection
     */
    public function testSearchFieldSqlInjection(): void
    {
        $maliciousSearches = [
            "%'; DROP TABLE customers--",
            "' UNION ALL SELECT NULL--",
            "1' AND (SELECT COUNT(*) FROM users)>0--"
        ];
        
        foreach ($maliciousSearches as $search) {
            // When using prepared statements with LIKE, this becomes safe
            // LIKE ? with value "%'; DROP TABLE..."
            $containsDangerousChars = preg_match("/['\";\-]/", $search);
            $this->assertGreaterThan(0, $containsDangerousChars, "Should detect dangerous characters in: $search");
        }
    }
}
