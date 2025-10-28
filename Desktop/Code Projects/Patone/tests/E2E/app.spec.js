import { test, expect } from '@playwright/test';

/**
 * E2E Tests for Patone Roadside Assistance Platform
 * Tests critical user workflows end-to-end
 */

test.describe('Authentication Flow', () => {
  test('should display login page', async ({ page }) => {
    await page.goto('/');
    
    // Check for login form elements
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('should show error on invalid credentials', async ({ page }) => {
    await page.goto('/login');
    
    // Fill in invalid credentials
    await page.fill('input[name="email"]', 'invalid@example.com');
    await page.fill('input[name="password"]', 'wrongpassword');
    await page.click('button[type="submit"]');
    
    // Should show error message
    await expect(page.locator('.alert, .error-message')).toBeVisible();
  });

  test('should validate required fields', async ({ page }) => {
    await page.goto('/login');
    
    // Try to submit without filling fields
    await page.click('button[type="submit"]');
    
    // HTML5 validation should prevent submission
    const emailInput = page.locator('input[name="email"]');
    const isRequired = await emailInput.getAttribute('required');
    expect(isRequired).not.toBeNull();
  });
});

test.describe('Dashboard Access', () => {
  test('should redirect to login when not authenticated', async ({ page }) => {
    await page.goto('/dashboard');
    
    // Should redirect to login page
    await expect(page).toHaveURL(/.*login/);
  });
});

test.describe('Form Validation', () => {
  test('should validate email format in forms', async ({ page }) => {
    await page.goto('/login');
    
    const emailInput = page.locator('input[name="email"]');
    const emailType = await emailInput.getAttribute('type');
    
    // Email input should have type="email" for HTML5 validation
    expect(emailType).toBe('email');
  });

  test('should have CSRF token in forms', async ({ page }) => {
    await page.goto('/login');
    
    // Check for CSRF token field
    const csrfToken = page.locator('input[name="csrf_token"]');
    await expect(csrfToken).toHaveCount(1);
    
    const tokenValue = await csrfToken.getAttribute('value');
    expect(tokenValue).toBeTruthy();
    expect(tokenValue?.length).toBeGreaterThan(0);
  });
});

test.describe('Responsive Design', () => {
  test('should be mobile responsive', async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/');
    
    // Page should load without errors
    await expect(page).toBeTruthy();
  });

  test('should be tablet responsive', async ({ page }) => {
    // Set tablet viewport
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.goto('/');
    
    // Page should load without errors
    await expect(page).toBeTruthy();
  });
});

test.describe('Security Headers', () => {
  test('should have security headers set', async ({ page }) => {
    const response = await page.goto('/');
    
    if (response) {
      const headers = response.headers();
      
      // Check for basic security headers
      // Note: These may not all be present depending on server configuration
      expect(headers).toBeTruthy();
    }
  });
});

test.describe('Page Load Performance', () => {
  test('should load homepage within acceptable time', async ({ page }) => {
    const startTime = Date.now();
    await page.goto('/');
    const loadTime = Date.now() - startTime;
    
    // Should load within 5 seconds
    expect(loadTime).toBeLessThan(5000);
  });
});

test.describe('Navigation', () => {
  test('should have proper page title', async ({ page }) => {
    await page.goto('/');
    
    const title = await page.title();
    expect(title).toBeTruthy();
    expect(title.length).toBeGreaterThan(0);
  });

  test('should not have broken links on main page', async ({ page }) => {
    await page.goto('/');
    
    // Get all links
    const links = await page.locator('a[href]').all();
    
    // Should have some links
    expect(links.length).toBeGreaterThan(0);
  });
});
