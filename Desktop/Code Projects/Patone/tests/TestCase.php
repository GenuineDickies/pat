<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base Test Case
 * Provides common functionality for all tests
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Set up test environment before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Start output buffering to capture any output
        if (!ob_get_level()) {
            ob_start();
        }
    }

    /**
     * Clean up after each test
     */
    protected function tearDown(): void
    {
        // Clean output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        parent::tearDown();
    }

    /**
     * Mock a database connection for testing
     * 
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMockDatabase()
    {
        // Create a mock that doesn't require actual database
        $mock = $this->createMock(\Database::class);
        return $mock;
    }

    /**
     * Assert that a value is a valid date
     * 
     * @param mixed $value
     * @param string $message
     */
    protected function assertIsValidDate($value, string $message = ''): void
    {
        $this->assertNotNull($value, $message);
        $this->assertNotFalse(strtotime($value), $message ?: "$value is not a valid date");
    }

    /**
     * Assert that an array has the expected keys
     * 
     * @param array $expectedKeys
     * @param array $array
     * @param string $message
     */
    protected function assertArrayHasKeys(array $expectedKeys, array $array, string $message = ''): void
    {
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $array, $message ?: "Array is missing key: $key");
        }
    }
}
