<?php

namespace Tests\Security;

use Tests\TestCase;

/**
 * XSS (Cross-Site Scripting) Security Tests
 * Tests protection against XSS attacks
 */
class XssProtectionTest extends TestCase
{
    /**
     * Test basic XSS script tag injection
     */
    public function testScriptTagXss(): void
    {
        $maliciousInputs = [
            '<script>alert("XSS")</script>',
            '<script src="http://evil.com/xss.js"></script>',
            '<SCRIPT>alert("XSS")</SCRIPT>',
            '<script>document.cookie</script>'
        ];
        
        foreach ($maliciousInputs as $input) {
            $escaped = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            
            $this->assertStringNotContainsString('<script>', strtolower($escaped));
            $this->assertStringContainsString('&lt;', $escaped);
        }
    }

    /**
     * Test XSS via event handlers
     */
    public function testEventHandlerXss(): void
    {
        $maliciousInputs = [
            '<img src="x" onerror="alert(\'XSS\')">',
            '<body onload="alert(\'XSS\')">',
            '<input onfocus="alert(\'XSS\')" autofocus>',
            '<svg onload="alert(\'XSS\')">',
            '<div onmouseover="alert(\'XSS\')">Test</div>'
        ];
        
        foreach ($maliciousInputs as $input) {
            $escaped = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            
            // After encoding, the string won't contain actual event handlers
            $this->assertNotEquals($input, $escaped);
            $this->assertStringContainsString('&lt;', $escaped);
        }
    }

    /**
     * Test XSS via JavaScript protocol
     */
    public function testJavascriptProtocolXss(): void
    {
        $maliciousInputs = [
            'javascript:alert("XSS")',
            'javascript:void(document.cookie)',
            'jAvAsCrIpT:alert("XSS")',
            'javascript&#58;alert("XSS")'
        ];
        
        foreach ($maliciousInputs as $input) {
            $containsJsProtocol = stripos($input, 'javascript') !== false;
            $this->assertTrue($containsJsProtocol, "Should detect javascript: protocol in: $input");
            
            // URL validation should reject these
            $isValidUrl = filter_var($input, FILTER_VALIDATE_URL) && 
                         !preg_match('/javascript:/i', $input);
            $this->assertFalse($isValidUrl);
        }
    }

    /**
     * Test XSS via HTML attributes
     */
    public function testHtmlAttributeXss(): void
    {
        $maliciousInputs = [
            '" onclick="alert(\'XSS\')"',
            '\' onclick=\'alert("XSS")\'',
            '"><script>alert("XSS")</script>',
        ];
        
        foreach ($maliciousInputs as $input) {
            $escaped = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            
            // After encoding, the input should be safe
            $this->assertNotEquals($input, $escaped);
            $this->assertStringContainsString('&quot;', $escaped);
        }
    }

    /**
     * Test XSS via CSS
     */
    public function testCssXss(): void
    {
        $maliciousInputs = [
            '<style>body{background:url("javascript:alert(\'XSS\')")}</style>',
            'expression(alert("XSS"))',
            'background: url(javascript:alert("XSS"))'
        ];
        
        foreach ($maliciousInputs as $input) {
            $escaped = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            
            $this->assertNotEquals($input, $escaped);
            $this->assertStringNotContainsString('<style>', $escaped);
        }
    }

    /**
     * Test proper encoding of special characters
     */
    public function testSpecialCharacterEncoding(): void
    {
        $specialChars = [
            '<' => '&lt;',
            '>' => '&gt;',
            '"' => '&quot;',
            "'" => '&#039;',
            '&' => '&amp;'
        ];
        
        foreach ($specialChars as $char => $encoded) {
            $escaped = htmlspecialchars($char, ENT_QUOTES, 'UTF-8');
            $this->assertEquals($encoded, $escaped, "Character $char should be encoded to $encoded");
        }
    }

    /**
     * Test XSS in JSON output
     */
    public function testJsonXss(): void
    {
        $data = [
            'message' => '<script>alert("XSS")</script>',
            'user' => 'admin<script>',
        ];
        
        // When outputting JSON, it should be properly encoded
        $json = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        
        $this->assertStringNotContainsString('<script>', $json);
        $this->assertIsString($json);
    }

    /**
     * Test XSS via SVG
     */
    public function testSvgXss(): void
    {
        $maliciousInputs = [
            '<svg onload="alert(\'XSS\')">',
            '<svg><script>alert("XSS")</script></svg>',
            '<svg><animate onbegin="alert(\'XSS\')"/>',
        ];
        
        foreach ($maliciousInputs as $input) {
            $escaped = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            
            // After encoding, the string won't contain actual SVG tags
            $this->assertStringNotContainsString('<svg', strtolower($escaped));
            $this->assertStringContainsString('&lt;', $escaped);
        }
    }

    /**
     * Test URL sanitization
     */
    public function testUrlSanitization(): void
    {
        $dangerousUrls = [
            'javascript:alert("XSS")',
            'data:text/html,<script>alert("XSS")</script>',
            'vbscript:msgbox("XSS")',
        ];
        
        $safeUrls = [
            'http://example.com',
            'https://example.com',
            '/relative/path',
            'mailto:test@example.com'
        ];
        
        foreach ($dangerousUrls as $url) {
            $isDangerous = preg_match('/^(javascript|data|vbscript):/i', $url);
            $this->assertGreaterThan(0, $isDangerous, "URL should be flagged as dangerous: $url");
        }
        
        foreach ($safeUrls as $url) {
            $isDangerous = preg_match('/^(javascript|data|vbscript):/i', $url);
            $this->assertEquals(0, $isDangerous, "URL should be safe: $url");
        }
    }
}
