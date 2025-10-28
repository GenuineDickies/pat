# Patone v1.0 Production Deployment Security Checklist

## Pre-Deployment

### 1. Configuration Security

- [ ] **Update config.php settings**
  ```php
  // Disable error display
  error_reporting(0);
  ini_set('display_errors', 0);
  ini_set('log_errors', 1);
  
  // Enable HTTPS enforcement
  define('FORCE_HTTPS', true);
  
  // Enable CSP
  define('CSP_ENABLED', true);
  ```

- [ ] **Generate new encryption keys**
  ```php
  // Generate a new 32-character encryption key
  define('ENCRYPTION_KEY', 'GENERATE-NEW-RANDOM-32-CHAR-KEY');
  ```

- [ ] **Update database credentials**
  - Use strong, unique passwords
  - Create limited-privilege database user
  - Test database connectivity

- [ ] **Change default admin credentials**
  - Update default username
  - Set strong password (12+ characters, mixed case, numbers, special chars)
  - Document new credentials securely

### 2. File Permissions

```bash
# Navigate to application directory
cd /home/runner/work/pat/pat/Desktop/Code\ Projects/Patone/

# Set proper permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Secure sensitive files
chmod 600 config.php
chmod 600 .env  # if using .env file

# Ensure writable directories
chmod 755 uploads/
chmod 755 logs/

# Prevent execution in upload directories
echo "php_flag engine off" > uploads/.htaccess
```

- [ ] Verify file permissions are set correctly
- [ ] Test that uploads directory is writable
- [ ] Test that logs directory is writable
- [ ] Confirm config.php is not world-readable

### 3. SSL/TLS Certificate

- [ ] **Obtain SSL certificate** (Let's Encrypt, commercial CA)
- [ ] **Install certificate** on web server
- [ ] **Test HTTPS configuration**
  - Visit https://your-domain.com
  - Check for browser warnings
  - Verify certificate chain
- [ ] **Test SSL Labs rating**: https://www.ssllabs.com/ssltest/
  - Target: A or A+ rating
- [ ] **Update .htaccess for HTTPS redirect**
  ```apache
  RewriteCond %{HTTPS} off
  RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
  ```

### 4. Web Server Configuration

#### Apache (.htaccess and httpd.conf)

- [ ] **Verify .htaccess is being read**
  ```apache
  # In httpd.conf or vhost config
  <Directory "/path/to/patone">
      AllowOverride All
  </Directory>
  ```

- [ ] **Disable directory listing**
  ```apache
  Options -Indexes
  ```

- [ ] **Hide server version**
  ```apache
  ServerTokens Prod
  ServerSignature Off
  ```

- [ ] **Set security headers** (verify in config.php or .htaccess)

#### Nginx (if applicable)

```nginx
# Hide Nginx version
server_tokens off;

# Security headers
add_header X-Content-Type-Options nosniff;
add_header X-Frame-Options DENY;
add_header X-XSS-Protection "1; mode=block";
add_header Referrer-Policy strict-origin-when-cross-origin;

# Disable access to hidden files
location ~ /\. {
    deny all;
}
```

### 5. PHP Configuration

Edit `php.ini`:

```ini
; Disable dangerous functions
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source

; Hide PHP version
expose_php = Off

; Session security
session.cookie_httponly = 1
session.cookie_secure = 1
session.cookie_samesite = Strict
session.use_strict_mode = 1
session.use_only_cookies = 1

; File upload limits
upload_max_filesize = 5M
post_max_size = 6M

; Error logging
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /path/to/logs/php_errors.log
```

- [ ] Restart PHP-FPM/Apache after changes
- [ ] Verify changes with `phpinfo()` (then remove test file!)

### 6. Database Security

```sql
-- Create limited privilege user
CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'strong_random_password';

-- Grant only necessary privileges
GRANT SELECT, INSERT, UPDATE, DELETE ON patone.* TO 'app_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Verify privileges
SHOW GRANTS FOR 'app_user'@'localhost';
```

- [ ] Database user has minimum necessary privileges
- [ ] Database password is strong (16+ characters)
- [ ] Remote database access is restricted (if applicable)
- [ ] Database backups are configured
- [ ] Test database connectivity with new credentials

### 7. Firewall Configuration

```bash
# Example: UFW (Ubuntu Firewall)
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
sudo ufw enable

# Or iptables
sudo iptables -A INPUT -p tcp --dport 22 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 443 -j ACCEPT
sudo iptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT
sudo iptables -A INPUT -j DROP
```

- [ ] Firewall is enabled and configured
- [ ] Only necessary ports are open
- [ ] SSH is restricted (key-based auth, non-standard port)

## Deployment

### 8. File Deployment

- [ ] **Remove development files**
  ```bash
  rm -rf .git
  rm README_DEV.md
  rm composer.json composer.lock  # if not needed
  rm package.json package-lock.json  # if not needed
  ```

- [ ] **Remove test files**
  ```bash
  rm -rf tests/
  rm test.php
  rm phpunit.xml
  ```

- [ ] **Remove documentation with sensitive info**
  - Keep public documentation
  - Remove internal development docs

- [ ] **Verify .gitignore excludes sensitive files**
  ```
  config.php
  .env
  /logs/*.log
  /uploads/*
  !/uploads/.htaccess
  ```

### 9. Database Migration

- [ ] **Backup existing database** (if upgrading)
  ```bash
  mysqldump -u root -p patone > backup_$(date +%Y%m%d).sql
  ```

- [ ] **Run database migrations**
  ```bash
  cd database/migrations
  php 001_initial_setup.php
  ```

- [ ] **Verify all tables created**
  ```sql
  SHOW TABLES;
  ```

- [ ] **Create initial admin user** (if fresh install)
  - Use strong password
  - Document credentials securely

### 10. Initial Configuration

- [ ] **Test basic functionality**
  - Login page loads
  - Admin can log in
  - Dashboard displays correctly

- [ ] **Verify security features**
  - HTTPS redirect works
  - Security headers are present
  - CSRF tokens are generated

- [ ] **Test rate limiting**
  - Attempt multiple failed logins
  - Verify lockout occurs

## Post-Deployment

### 11. Security Verification

- [ ] **Run security scanners**
  ```bash
  # OWASP ZAP
  # Nikto
  nikto -h https://your-domain.com
  
  # SSL Labs
  # Visit: https://www.ssllabs.com/ssltest/
  ```

- [ ] **Verify HTTP headers**
  ```bash
  curl -I https://your-domain.com
  ```
  Check for:
  - X-Content-Type-Options: nosniff
  - X-Frame-Options: DENY
  - X-XSS-Protection: 1; mode=block
  - Strict-Transport-Security (HSTS)
  - Content-Security-Policy

- [ ] **Test for common vulnerabilities**
  - SQL injection attempts
  - XSS attempts
  - CSRF attacks
  - Directory traversal
  - File upload bypasses

### 12. Monitoring Setup

- [ ] **Configure log rotation**
  ```bash
  # /etc/logrotate.d/patone
  /path/to/patone/logs/*.log {
      daily
      rotate 30
      compress
      missingok
      notifempty
      create 0644 www-data www-data
  }
  ```

- [ ] **Set up error monitoring**
  - Configure email alerts for critical errors
  - Or use service like Sentry, Rollbar

- [ ] **Configure uptime monitoring**
  - Use service like UptimeRobot, Pingdom
  - Set up alerts for downtime

- [ ] **Set up security event monitoring**
  ```sql
  -- Create view for suspicious activity
  CREATE VIEW suspicious_activity AS
  SELECT * FROM security_log 
  WHERE event_type IN ('rate_limit_exceeded', 'csrf_token_invalid', 'blocked_ip_access_attempt')
  ORDER BY created_at DESC;
  ```

### 13. Backup Configuration

- [ ] **Configure automated database backups**
  ```bash
  # Add to crontab
  0 2 * * * /usr/bin/mysqldump -u backup_user -p'password' patone | gzip > /backups/patone_$(date +\%Y\%m\%d_\%H\%M\%S).sql.gz
  ```

- [ ] **Configure file backups**
  ```bash
  # Backup uploaded files
  0 3 * * * tar -czf /backups/uploads_$(date +\%Y\%m\%d).tar.gz /path/to/patone/uploads/
  ```

- [ ] **Test backup restoration**
  - Restore database to test environment
  - Verify data integrity
  - Document restoration procedure

- [ ] **Set up offsite backup storage**
  - Amazon S3, Google Cloud Storage, etc.
  - Encrypt backups before transmission

### 14. Documentation

- [ ] **Document deployment**
  - Server specifications
  - Software versions
  - Configuration changes
  - Credentials (encrypted vault)

- [ ] **Create runbook**
  - Common tasks
  - Troubleshooting procedures
  - Emergency contacts

- [ ] **Update security documentation**
  - Document security configurations
  - List enabled security features
  - Note any deviations from best practices

## Ongoing Maintenance

### 15. Regular Security Tasks

**Daily**
- [ ] Monitor error logs for anomalies
- [ ] Check security event logs
- [ ] Verify backups completed successfully

**Weekly**
- [ ] Review failed login attempts
- [ ] Check for unusual traffic patterns
- [ ] Review rate limiting logs

**Monthly**
- [ ] Update dependencies
  ```bash
  composer update  # if using Composer
  npm update      # if using npm
  ```
- [ ] Review and update firewall rules
- [ ] Check SSL certificate expiration
- [ ] Review user accounts and permissions
- [ ] Scan for vulnerabilities

**Quarterly**
- [ ] Change database passwords
- [ ] Review and update security policies
- [ ] Conduct security training
- [ ] Perform penetration testing
- [ ] Audit user access logs

**Annually**
- [ ] Major security audit
- [ ] Review and update incident response plan
- [ ] Renew SSL certificates (if not auto-renewing)
- [ ] Review and update disaster recovery plan

### 16. Incident Response Plan

- [ ] **Define security incident**
  - Unauthorized access attempts
  - Data breach
  - Service disruption
  - Malware detection

- [ ] **Document response procedures**
  1. Identify and contain threat
  2. Preserve evidence
  3. Assess damage
  4. Notify stakeholders
  5. Remediate vulnerability
  6. Document incident
  7. Conduct post-mortem

- [ ] **Establish communication plan**
  - Internal contacts
  - External contacts (hosting, law enforcement)
  - Customer notification procedures

## Security Testing Checklist

### Manual Testing

- [ ] **Authentication**
  - Attempt login with invalid credentials
  - Test account lockout after failed attempts
  - Test password reset flow
  - Test "remember me" functionality

- [ ] **Authorization**
  - Attempt to access restricted pages without permission
  - Test role-based access controls
  - Verify users can only access their own data

- [ ] **Session Management**
  - Test session timeout
  - Verify session regeneration after login
  - Test logout functionality
  - Attempt session fixation

- [ ] **Input Validation**
  - Test all forms with invalid data
  - Attempt SQL injection in all input fields
  - Attempt XSS in all input fields
  - Test file upload restrictions

- [ ] **CSRF Protection**
  - Submit forms without CSRF token
  - Submit forms with invalid CSRF token
  - Reuse CSRF token

- [ ] **Rate Limiting**
  - Exceed rate limits on login
  - Exceed rate limits on API endpoints

### Automated Testing

- [ ] Run security test suite
  ```bash
  php tests/SecurityTest.php
  ```

- [ ] OWASP ZAP scan
- [ ] Nikto scan
- [ ] Dependency vulnerability scan
  ```bash
  composer audit  # if using Composer
  npm audit      # if using npm
  ```

## Quick Reference

### Emergency Procedures

**If compromised:**
1. Disconnect affected systems from network
2. Preserve evidence (logs, files)
3. Change all passwords
4. Review access logs
5. Notify stakeholders
6. Restore from clean backup
7. Conduct security audit

**Contact Information:**
- System Administrator: [CONTACT]
- Security Team: [CONTACT]
- Hosting Provider: [CONTACT]
- Database Administrator: [CONTACT]

### Important Commands

```bash
# Check security headers
curl -I https://your-domain.com

# View failed login attempts
mysql -u root -p -e "SELECT * FROM login_attempts ORDER BY attempted_at DESC LIMIT 100;" patone

# View security logs
mysql -u root -p -e "SELECT * FROM security_log ORDER BY created_at DESC LIMIT 100;" patone

# Check rate limiting
mysql -u root -p -e "SELECT action, COUNT(*) as count FROM rate_limit_log WHERE created_at > NOW() - INTERVAL 1 HOUR GROUP BY action;" patone

# View blocked IPs
mysql -u root -p -e "SELECT * FROM blocked_ips WHERE expires_at IS NULL OR expires_at > NOW();" patone
```

---

## Sign-Off

- [ ] All checklist items completed
- [ ] Security testing passed
- [ ] Backups verified
- [ ] Monitoring configured
- [ ] Documentation updated
- [ ] Team trained on security procedures

**Deployment Date:** _______________  
**Deployed By:** _______________  
**Reviewed By:** _______________

---

**Last Updated**: 2024-10-28  
**Version**: 1.0.0
