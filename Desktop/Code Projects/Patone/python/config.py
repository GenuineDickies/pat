"""
Roadside Assistance Admin Platform - Python Configuration
Configuration settings for Python scripts and automation
"""

import os
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

# Database Configuration
DB_CONFIG = {
    'host': os.getenv('DB_HOST', 'localhost'),
    'user': os.getenv('DB_USER', 'root'),
    'password': os.getenv('DB_PASSWORD', ''),
    'database': os.getenv('DB_NAME', 'roadside_assistance'),
    'charset': 'utf8mb4'
}

# API Configuration
API_CONFIG = {
    'base_url': os.getenv('API_BASE_URL', 'http://localhost/roadside-admin/'),
    'api_key': os.getenv('API_KEY', ''),
    'timeout': int(os.getenv('API_TIMEOUT', '30'))
}

# Email Configuration
EMAIL_CONFIG = {
    'smtp_server': os.getenv('SMTP_SERVER', 'smtp.gmail.com'),
    'smtp_port': int(os.getenv('SMTP_PORT', '587')),
    'smtp_username': os.getenv('SMTP_USERNAME', ''),
    'smtp_password': os.getenv('SMTP_PASSWORD', ''),
    'from_email': os.getenv('FROM_EMAIL', 'noreply@roadsideassistance.com'),
    'from_name': os.getenv('FROM_NAME', 'Roadside Assistance System')
}

# SMS Configuration (Twilio)
SMS_CONFIG = {
    'account_sid': os.getenv('TWILIO_ACCOUNT_SID', ''),
    'auth_token': os.getenv('TWILIO_AUTH_TOKEN', ''),
    'from_number': os.getenv('TWILIO_FROM_NUMBER', ''),
    'enabled': os.getenv('SMS_ENABLED', 'false').lower() == 'true'
}

# File Paths
PATHS = {
    'reports': os.path.join(os.path.dirname(__file__), '..', 'uploads', 'reports'),
    'exports': os.path.join(os.path.dirname(__file__), '..', 'uploads', 'exports'),
    'logs': os.path.join(os.path.dirname(__file__), '..', 'logs'),
    'backups': os.path.join(os.path.dirname(__file__), '..', 'backups')
}

# Report Configuration
REPORT_CONFIG = {
    'default_format': os.getenv('DEFAULT_REPORT_FORMAT', 'pdf'),
    'logo_path': os.path.join(os.path.dirname(__file__), '..', 'assets', 'images', 'logo.png'),
    'company_name': os.getenv('COMPANY_NAME', 'Roadside Assistance Company'),
    'company_address': os.getenv('COMPANY_ADDRESS', '123 Service Road, City, State 12345'),
    'company_phone': os.getenv('COMPANY_PHONE', '1-800-ROADSIDE'),
    'company_email': os.getenv('COMPANY_EMAIL', 'info@roadsideassistance.com')
}

# Geolocation Configuration
GEO_CONFIG = {
    'default_latitude': float(os.getenv('DEFAULT_LATITUDE', '40.7128')),
    'default_longitude': float(os.getenv('DEFAULT_LONGITUDE', '-74.0060')),
    'geocoding_api_key': os.getenv('GEOCODING_API_KEY', ''),
    'maps_api_key': os.getenv('MAPS_API_KEY', '')
}

# Automation Settings
AUTOMATION_CONFIG = {
    'auto_backup': os.getenv('AUTO_BACKUP', 'true').lower() == 'true',
    'backup_frequency': os.getenv('BACKUP_FREQUENCY', 'daily'),
    'report_generation_time': os.getenv('REPORT_GENERATION_TIME', '06:00'),
    'cleanup_old_files': os.getenv('CLEANUP_OLD_FILES', 'true').lower() == 'true',
    'max_file_age_days': int(os.getenv('MAX_FILE_AGE_DAYS', '90'))
}

# Logging Configuration
LOG_CONFIG = {
    'level': os.getenv('LOG_LEVEL', 'INFO'),
    'file_path': os.path.join(PATHS['logs'], 'python.log'),
    'max_file_size': int(os.getenv('LOG_MAX_SIZE', '10')) * 1024 * 1024,  # 10MB
    'backup_count': int(os.getenv('LOG_BACKUP_COUNT', '5'))
}

# Security Settings
SECURITY_CONFIG = {
    'encryption_key': os.getenv('ENCRYPTION_KEY', 'your-32-character-encryption-key-here'),
    'password_min_length': int(os.getenv('PASSWORD_MIN_LENGTH', '8')),
    'session_timeout': int(os.getenv('SESSION_TIMEOUT', '3600'))
}

# Feature Flags
FEATURES = {
    'advanced_analytics': os.getenv('ADVANCED_ANALYTICS', 'false').lower() == 'true',
    'predictive_maintenance': os.getenv('PREDICTIVE_MAINTENANCE', 'false').lower() == 'true',
    'customer_segmentation': os.getenv('CUSTOMER_SEGMENTATION', 'false').lower() == 'true',
    'automated_routing': os.getenv('AUTOMATED_ROUTING', 'false').lower() == 'true',
    'real_time_tracking': os.getenv('REAL_TIME_TRACKING', 'false').lower() == 'true'
}
