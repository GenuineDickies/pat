# Deployment Guide

Complete guide for deploying Patone to production.

## Pre-Deployment Checklist

### Security
- [ ] Change all default passwords
- [ ] Generate new encryption keys
- [ ] Enable HTTPS/SSL
- [ ] Configure firewall rules
- [ ] Set up regular backups
- [ ] Disable debug mode
- [ ] Configure error logging
- [ ] Review file permissions
- [ ] Enable rate limiting
- [ ] Configure CORS properly

### Infrastructure
- [ ] Server meets minimum requirements
- [ ] Database server configured
- [ ] Web server configured
- [ ] PHP configured properly
- [ ] SSL certificates installed
- [ ] Domain DNS configured
- [ ] Backup system in place
- [ ] Monitoring tools installed

### Application
- [ ] Database migrated
- [ ] Configuration files updated
- [ ] Dependencies installed
- [ ] File uploads directory writable
- [ ] Logs directory writable
- [ ] Test all core features
- [ ] Run test suite
- [ ] Performance testing completed

## Server Requirements

### Minimum Requirements

**Web Server:**
- CPU: 2 cores
- RAM: 4 GB
- Storage: 20 GB SSD
- Network: 100 Mbps

**Database Server:**
- CPU: 2 cores
- RAM: 4 GB
- Storage: 50 GB SSD
- Network: 100 Mbps

### Recommended for Production

**Web Server:**
- CPU: 4+ cores
- RAM: 8+ GB
- Storage: 50+ GB SSD
- Network: 1 Gbps

**Database Server:**
- CPU: 4+ cores
- RAM: 16+ GB
- Storage: 100+ GB SSD (with RAID)
- Network: 1 Gbps

### Software Requirements

**Operating System:**
- Ubuntu 20.04/22.04 LTS (recommended)
- Debian 10/11
- CentOS 7/8
- Rocky Linux 8+

**Web Server:**
- Apache 2.4+ with mod_rewrite
- OR Nginx 1.18+

**PHP:**
- PHP 7.4+ (PHP 8.0+ recommended)
- Required extensions: mysqli, pdo, session, json, curl, openssl, fileinfo, zip

**Database:**
- MySQL 5.7+ or 8.0+
- OR MariaDB 10.3+

**Python:**
- Python 3.8+ (for analytics scripts)
- pip package manager

## Installation Steps

### 1. Server Setup

#### Update System

```bash
# Ubuntu/Debian
sudo apt update
sudo apt upgrade -y

# CentOS/Rocky
sudo yum update -y
```

#### Install Required Software

**Ubuntu/Debian:**
```bash
# Web server (choose one)
sudo apt install apache2  # Apache
# OR
sudo apt install nginx    # Nginx

# PHP
sudo apt install php7.4 php7.4-fpm php7.4-mysql php7.4-cli \
                 php7.4-curl php7.4-json php7.4-mbstring \
                 php7.4-xml php7.4-zip

# MySQL
sudo apt install mysql-server

# Python
sudo apt install python3 python3-pip python3-venv

# Other tools
sudo apt install git curl unzip
```

**CentOS/Rocky:**
```bash
# Enable EPEL repository
sudo yum install epel-release -y

# PHP
sudo yum install php php-fpm php-mysql php-cli php-curl \
                 php-json php-mbstring php-xml php-zip

# MySQL
sudo yum install mysql-server

# Python
sudo yum install python3 python3-pip

# Web server
sudo yum install httpd  # Apache
```

### 2. Database Setup

#### Secure MySQL Installation

```bash
sudo mysql_secure_installation
```

Answer prompts:
- Set root password: YES
- Remove anonymous users: YES
- Disallow root login remotely: YES
- Remove test database: YES
- Reload privilege tables: YES

#### Create Database and User

```bash
# Login to MySQL
sudo mysql -u root -p

# Create database
CREATE DATABASE roadside_assistance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user with strong password
CREATE USER 'patone_user'@'localhost' IDENTIFIED BY 'your-strong-password-here';

# Grant privileges
GRANT ALL PRIVILEGES ON roadside_assistance.* TO 'patone_user'@'localhost';
FLUSH PRIVILEGES;

# Exit
EXIT;
```

### 3. Application Deployment

#### Clone Repository

```bash
# Create application directory
sudo mkdir -p /var/www/patone
cd /var/www/patone

# Clone from Git (or upload files)
sudo git clone https://github.com/GenuineDickies/pat.git .

# Move to correct directory
cd "Desktop/Code Projects/Patone"
```

#### Configure Application

```bash
# Copy and edit configuration
sudo cp config.php config.php.backup
sudo nano config.php
```

Update configuration:

```php
<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'patone_user');
define('DB_PASS', 'your-strong-password-here');
define('DB_NAME', 'roadside_assistance');

// Security
define('ENCRYPTION_KEY', 'generate-a-32-character-random-key');
define('SESSION_SECRET', 'generate-another-random-key');

// Production settings
define('DEBUG_MODE', false);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/patone/error.log');

// Base URL
define('BASE_URL', 'https://your-domain.com');
```

#### Set File Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/patone

# Set directory permissions
sudo find /var/www/patone -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/patone -type f -exec chmod 644 {} \;

# Writable directories
sudo chmod 775 /var/www/patone/uploads
sudo chmod 775 /var/www/patone/logs

# Protect sensitive files
sudo chmod 600 /var/www/patone/config.php
```

#### Run Database Migration

```bash
cd /var/www/patone/database/migrations
sudo -u www-data php 001_initial_setup.php
```

#### Install Python Dependencies

```bash
cd /var/www/patone/python

# Create virtual environment
python3 -m venv venv

# Activate and install
source venv/bin/activate
pip install -r requirements.txt
deactivate
```

### 4. Web Server Configuration

#### Apache Configuration

Create virtual host file:

```bash
sudo nano /etc/apache2/sites-available/patone.conf
```

Add configuration:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAdmin admin@your-domain.com
    
    DocumentRoot /var/www/patone
    
    <Directory /var/www/patone>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/patone-error.log
    CustomLog ${APACHE_LOG_DIR}/patone-access.log combined
</VirtualHost>
```

Enable site and modules:

```bash
# Enable site
sudo a2ensite patone.conf

# Enable required modules
sudo a2enmod rewrite ssl headers

# Test configuration
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

#### Nginx Configuration

Create server block:

```bash
sudo nano /etc/nginx/sites-available/patone
```

Add configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/patone;
    index index.php index.html;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Logging
    access_log /var/log/nginx/patone-access.log;
    error_log /var/log/nginx/patone-error.log;
    
    # PHP handling
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ /config.php$ {
        deny all;
    }
}
```

Enable site:

```bash
# Create symbolic link
sudo ln -s /etc/nginx/sites-available/patone /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
sudo systemctl restart php7.4-fpm
```

### 5. SSL/HTTPS Setup

#### Install Certbot (Let's Encrypt)

```bash
# Ubuntu/Debian
sudo apt install certbot python3-certbot-apache  # For Apache
# OR
sudo apt install certbot python3-certbot-nginx   # For Nginx
```

#### Obtain SSL Certificate

```bash
# For Apache
sudo certbot --apache -d your-domain.com

# For Nginx
sudo certbot --nginx -d your-domain.com
```

Follow prompts:
- Enter email address
- Agree to terms
- Choose to redirect HTTP to HTTPS: YES

#### Auto-renewal

```bash
# Test renewal
sudo certbot renew --dry-run

# Certbot automatically sets up renewal, verify:
sudo systemctl status certbot.timer
```

### 6. Firewall Configuration

```bash
# UFW (Ubuntu)
sudo ufw allow 'Apache Full'  # or 'Nginx Full'
sudo ufw allow 22/tcp         # SSH
sudo ufw enable

# Firewalld (CentOS/Rocky)
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --permanent --add-service=ssh
sudo firewall-cmd --reload
```

### 7. Setup Backup System

#### Database Backup Script

Create backup script:

```bash
sudo nano /usr/local/bin/patone-backup.sh
```

Add:

```bash
#!/bin/bash
BACKUP_DIR="/backups/patone"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="roadside_assistance"
DB_USER="patone_user"
DB_PASS="your-password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/patone/uploads

# Remove backups older than 30 days
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

Make executable and schedule:

```bash
# Make executable
sudo chmod +x /usr/local/bin/patone-backup.sh

# Add to crontab for daily backups at 2 AM
sudo crontab -e

# Add line:
0 2 * * * /usr/local/bin/patone-backup.sh >> /var/log/patone-backup.log 2>&1
```

### 8. Monitoring Setup

#### Install Monitoring Tools

```bash
# Install monitoring tools
sudo apt install htop iotop nethogs

# For advanced monitoring, consider:
# - Nagios
# - Zabbix
# - Prometheus + Grafana
```

#### Setup Log Rotation

Create logrotate configuration:

```bash
sudo nano /etc/logrotate.d/patone
```

Add:

```
/var/www/patone/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
    sharedscripts
    postrotate
        systemctl reload apache2 > /dev/null 2>&1 || true
    endscript
}
```

### 9. Performance Optimization

#### PHP Configuration

Edit PHP-FPM configuration:

```bash
sudo nano /etc/php/7.4/fpm/php.ini
```

Recommended settings:

```ini
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
post_max_size = 64M
upload_max_filesize = 64M
max_file_uploads = 20

opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

#### MySQL Optimization

Edit MySQL configuration:

```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Add under `[mysqld]`:

```ini
max_connections = 200
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M
innodb_flush_log_at_trx_commit = 2
query_cache_size = 64M
query_cache_type = 1
```

Restart services:

```bash
sudo systemctl restart php7.4-fpm
sudo systemctl restart mysql
```

## Post-Deployment

### 1. Initial Admin Setup

1. Access application: `https://your-domain.com`
2. Login with default credentials:
   - Username: `admin`
   - Password: `admin123`
3. **IMMEDIATELY** change password
4. Create additional admin users
5. Disable or delete default admin account

### 2. Configure Settings

Navigate to Settings page and configure:
- Site name
- Business hours
- GPS tracking (if using)
- Email/SMS notifications (if configured)
- Default service radius

### 3. Add Initial Data

1. Add service types
2. Add drivers
3. Add customers (or import if migrating)
4. Test service request workflow

### 4. Test Everything

- [ ] User login/logout
- [ ] Customer management
- [ ] Driver management
- [ ] Service request creation
- [ ] Driver assignment
- [ ] Request status updates
- [ ] Reports generation
- [ ] API endpoints
- [ ] File uploads
- [ ] Email notifications (if configured)

### 5. Monitor for Issues

```bash
# Check logs regularly
sudo tail -f /var/log/patone/error.log
sudo tail -f /var/log/apache2/patone-error.log  # or nginx
sudo tail -f /var/log/mysql/error.log

# Monitor system resources
htop
df -h
```

## Troubleshooting

### 500 Internal Server Error

**Check:**
- PHP error logs
- Apache/Nginx error logs
- File permissions
- .htaccess file exists and is correct

### Database Connection Failed

**Check:**
- MySQL is running: `sudo systemctl status mysql`
- Database credentials in config.php
- Database user permissions
- Firewall rules

### File Upload Fails

**Check:**
- `uploads/` directory exists and is writable
- PHP upload limits in php.ini
- Web server user owns uploads directory

### Performance Issues

**Solutions:**
- Enable PHP OPcache
- Optimize database queries
- Add database indexes
- Use caching (Redis/Memcached)
- Consider CDN for static assets

## Security Hardening

See [SECURITY.md](./SECURITY.md) for comprehensive security hardening guide.

### Quick Security Checklist

- [ ] HTTPS enabled with valid certificate
- [ ] Default passwords changed
- [ ] Firewall configured
- [ ] SSH key authentication only
- [ ] Database users have minimum required privileges
- [ ] File permissions set correctly
- [ ] Debug mode disabled
- [ ] Error reporting disabled in production
- [ ] Backups automated and tested
- [ ] Monitoring and alerting configured

## Maintenance

### Daily
- Monitor logs for errors
- Check disk space
- Verify backups completed

### Weekly
- Review access logs for suspicious activity
- Check application performance
- Review pending service requests

### Monthly
- Update software packages
- Review and optimize database
- Test backup restoration
- Security audit
- Performance review

### Quarterly
- Update SSL certificates (automatic with Let's Encrypt)
- Review user accounts and permissions
- Penetration testing
- Disaster recovery drill

## Scaling

### Horizontal Scaling

For high traffic, consider:
1. Load balancer (HAProxy, Nginx)
2. Multiple web servers
3. Separate database server
4. Redis for session storage
5. CDN for static assets

### Database Scaling

- Master-slave replication
- Read replicas for reporting
- Database connection pooling

## Rollback Procedure

If deployment fails:

1. **Stop web server**
   ```bash
   sudo systemctl stop apache2  # or nginx
   ```

2. **Restore database backup**
   ```bash
   mysql -u root -p roadside_assistance < /backups/patone/db_YYYYMMDD.sql
   ```

3. **Restore files**
   ```bash
   cd /var/www
   sudo rm -rf patone
   sudo tar -xzf /backups/patone/files_YYYYMMDD.tar.gz
   ```

4. **Start web server**
   ```bash
   sudo systemctl start apache2  # or nginx
   ```

## Support

For deployment support:
- Documentation: Check this guide and other docs
- Issues: GitHub repository
- Emergency: Contact system administrator

---

**Deployment completed successfully? Mark this checklist:**

- [ ] Application accessible via HTTPS
- [ ] All tests passing
- [ ] Monitoring configured
- [ ] Backups scheduled
- [ ] Documentation updated
- [ ] Team notified
- [ ] Post-deployment review scheduled

**Congratulations on your deployment! 🎉**
