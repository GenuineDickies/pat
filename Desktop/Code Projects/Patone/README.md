# Roadside Assistance Admin Platform

A comprehensive web-based administrative platform for managing roadside assistance operations, built with HTML, CSS, PHP, MySQLi, Python, and JavaScript.

## 🚀 Features

### Core Functionality
- **Customer Management**: Complete customer database with contact information, vehicle details, and service history
- **Service Request Management**: Track and manage all service requests from dispatch to completion
- **Driver Management**: Monitor driver availability, performance, and location tracking
- **Real-time Dashboard**: Live statistics and monitoring of operations
- **Reporting System**: Comprehensive reports and analytics
- **User Authentication**: Secure login system with role-based permissions

### Advanced Features
- **Automated Dispatch**: Intelligent assignment of requests to available drivers
- **GPS Integration**: Real-time driver location tracking
- **Customer Segmentation**: VIP customer management and loyalty programs
- **Predictive Analytics**: Demand forecasting and performance optimization
- **Mobile Responsive**: Optimized for all devices
- **API Integration**: RESTful API for third-party integrations

## 📋 Requirements

### System Requirements
- **Web Server**: Apache or Nginx
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 5.7 or higher
- **Python**: Version 3.8 or higher

### PHP Extensions
- mysqli
- session
- fileinfo
- openssl
- curl
- zip

### Python Packages
Install required packages using pip:
```bash
pip install -r python/requirements.txt
```

## 🛠️ Installation

### 1. Database Setup
1. Create a new MySQL database
2. Import the database schema:
   ```sql
   mysql -u username -p database_name < database/schema.sql
   ```
3. Update database configuration in `config.php`

### 2. Web Server Configuration
1. Place all files in your web server's document root
2. Ensure the following directories are writable:
   - `uploads/`
   - `logs/`
   - `python/` (if using Python features)

3. Configure URL rewriting by ensuring `.htaccess` is enabled

### 3. Configuration
1. Copy `config.php` and update database credentials
2. Set appropriate file permissions:
   ```bash
   chmod 755 .
   chmod 755 backend/
   chmod 755 uploads/
   chmod 644 config.php
   ```

### 4. Initial Setup
1. Access the application in your browser
2. Create the first admin user by running the database setup script
3. Run the database migration:
   ```bash
   cd database/migrations
   php 001_initial_setup.php
   ```

## 🎯 Usage

### Admin Dashboard
The main dashboard provides:
- Real-time statistics and KPIs
- Recent service requests
- Driver availability status
- Quick action buttons
- Performance metrics

### Customer Management
- View all customers with filtering and search
- Add new customers with complete information
- Edit customer details and vehicle information
- Track service history and preferences
- Import/export customer data

### Service Requests
- Create new service requests
- Assign drivers automatically or manually
- Track request status and progress
- Monitor response times and completion
- Customer communication logs

### Driver Management
- Monitor driver availability and status
- Track performance metrics
- Manage driver information and certifications
- Real-time location monitoring
- Performance analytics

### Reports and Analytics
- Daily operations reports
- Monthly performance summaries
- Customer behavior analysis
- Revenue and profitability reports
- Custom report generation

## 🔧 Configuration

### Database Configuration
Edit `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'roadside_assistance');
```

### Email Configuration
Update email settings in `python/config.py`:
```python
EMAIL_CONFIG = {
    'smtp_server': 'your_smtp_server',
    'smtp_username': 'your_email@domain.com',
    'smtp_password': 'your_password'
}
```

### API Keys
Configure third-party API keys in `python/config.py`:
```python
SMS_CONFIG = {
    'account_sid': 'your_twilio_sid',
    'auth_token': 'your_twilio_token'
}
```

## 📊 Database Schema

The system uses the following main tables:

### Core Tables
- `users` - System users and authentication
- `customers` - Customer information and details
- `drivers` - Driver information and status
- `service_requests` - Service request tracking
- `service_types` - Available service types

### Supporting Tables
- `customer_vehicles` - Customer vehicle information
- `activity_logs` - System activity tracking
- `settings` - Application configuration
- `reports` - Generated report records

## 🔐 Security Features

### Authentication
- Secure password hashing with bcrypt
- Session management with timeouts
- Remember me functionality
- Login attempt monitoring

### Authorization
- Role-based access control (Admin, Manager, Dispatcher, Driver)
- Permission-based feature access
- CSRF protection
- Input sanitization and validation

### Data Protection
- SQL injection prevention with prepared statements
- XSS protection with output escaping
- File upload security
- Secure file permissions

## 📱 API Endpoints

The system provides RESTful API endpoints:

### Authentication
- `POST /api/login` - User login
- `POST /api/logout` - User logout

### Customers
- `GET /api/customers` - List customers
- `POST /api/customers` - Create customer
- `PUT /api/customers/{id}` - Update customer
- `DELETE /api/customers/{id}` - Delete customer

### Service Requests
- `GET /api/requests` - List requests
- `POST /api/requests` - Create request
- `PUT /api/requests/{id}` - Update request

### Reports
- `GET /api/reports/daily` - Daily report
- `GET /api/reports/monthly` - Monthly report

## 🐍 Python Scripts

### Report Generation
```bash
# Generate daily report
python python/report_generator.py --type daily --date 2024-01-15

# Generate monthly report
python python/report_generator.py --type monthly --year 2024 --month 1

# Generate customer analysis
python python/report_generator.py --type customer --customer-id 123
```

### Data Analysis
```bash
# Analyze service demand
python python/data_analyzer.py --analysis demand --days 30

# Analyze driver performance
python python/data_analyzer.py --analysis drivers --days 30

# Analyze customer behavior
python python/data_analyzer.py --analysis customers --days 90

# Analyze revenue trends
python python/data_analyzer.py --analysis revenue --days 90
```

## 🎨 Customization

### Styling
- Modify `assets/css/style.css` for visual customization
- Update CSS variables in `:root` for consistent theming
- Responsive design adapts to all screen sizes

### Functionality
- Extend controllers in `backend/controllers/`
- Add new models in `backend/models/`
- Create custom views in `frontend/pages/`
- Add JavaScript modules in `frontend/js/`

## 📈 Performance

### Optimization Features
- Database query optimization
- Caching for frequently accessed data
- Lazy loading for large datasets
- Compressed static assets
- CDN-ready structure

### Monitoring
- Activity logging for all system actions
- Performance metrics tracking
- Error logging and reporting
- Database query monitoring

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

### Troubleshooting
1. Check PHP error logs
2. Verify database connection
3. Ensure proper file permissions
4. Check browser console for JavaScript errors

### Common Issues
- **Database Connection**: Verify credentials in `config.php`
- **File Uploads**: Check `uploads/` directory permissions
- **Email/SMS**: Configure API keys in `python/config.py`
- **Performance**: Enable caching and optimize database queries

## 🔄 Updates

### Version History
- **v1.0.0**: Initial release
  - Complete admin platform
  - Customer and driver management
  - Service request tracking
  - Basic reporting

### Future Enhancements
- Mobile app for drivers
- Advanced AI-powered routing
- Integration with GPS devices
- Enhanced analytics dashboard
- Multi-language support

## 📞 Contact

For support or questions:
- Email: support@roadsideassistance.com
- Phone: 1-800-ROADSIDE
- Documentation: [Link to full documentation]

---

**Built with ❤️ for roadside assistance professionals**
