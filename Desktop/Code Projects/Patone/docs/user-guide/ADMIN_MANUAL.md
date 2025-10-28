# Administrator Manual

Complete guide for system administrators to manage the Patone platform.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Dashboard](#dashboard)
3. [Customer Management](#customer-management)
4. [Driver Management](#driver-management)
5. [Service Request Management](#service-request-management)
6. [Reports and Analytics](#reports-and-analytics)
7. [System Settings](#system-settings)
8. [User Management](#user-management)
9. [Best Practices](#best-practices)
10. [Troubleshooting](#troubleshooting)

## Getting Started

### Logging In

1. Navigate to your Patone installation URL
2. Enter your username and password
3. Click "Login"

**Default credentials** (change immediately):
- Username: `admin`
- Password: `admin123`

### First-Time Setup

After first login, immediately:

1. **Change your password**
   - Click on your profile
   - Select "Change Password"
   - Use a strong password (min 12 characters)

2. **Configure system settings**
   - Navigate to Settings
   - Update site information
   - Configure business hours
   - Set default options

3. **Create additional users**
   - Add managers and dispatchers
   - Assign appropriate roles

## Dashboard

The dashboard provides real-time overview of operations.

### Key Metrics

**Service Requests**
- Total Requests: All-time request count
- Pending: Awaiting driver assignment
- Active: Currently in progress
- Completed Today: Today's completed services

**Drivers**
- Available: Ready for assignment
- Busy: Currently on service calls
- Offline: Not working
- On Break: Temporarily unavailable

**Customers**
- Total Customers: All registered customers
- VIP Customers: Priority customers
- New This Month: Recent registrations

### Quick Actions

- Create New Request
- Add Customer
- Add Driver
- View Reports
- System Settings

### Recent Activity

Shows last 10 activities including:
- New service requests
- Completed services
- Driver assignments
- Customer registrations

## Customer Management

### Viewing Customers

**Navigate:** Main Menu → Customers

Features:
- **Search**: Find customers by name, email, or phone
- **Filter**: Filter by status (Active, Inactive, VIP)
- **Sort**: Click column headers to sort
- **Pagination**: Navigate through customer list

### Adding a Customer

1. Click "Add Customer" button
2. Fill in required information:
   - **Personal Info**: First name, last name, email, phone
   - **Address**: Complete address including city, state, ZIP
   - **Additional**: Emergency contact, date of birth (optional)
   - **VIP Status**: Check if priority customer
3. Click "Save Customer"

**Required Fields:**
- First Name
- Last Name
- Email
- Phone
- Address
- City
- State
- ZIP Code

### Adding Vehicle Information

1. Open customer details
2. Click "Add Vehicle"
3. Enter vehicle details:
   - Make and Model
   - Year
   - Color
   - License Plate
   - VIN (optional)
4. Mark as primary vehicle if applicable
5. Save vehicle

**Tips:**
- Customers can have multiple vehicles
- One vehicle should be marked as primary
- License plate helps drivers identify the vehicle

### Editing Customer Information

1. Find customer in list
2. Click "Edit" button
3. Update information
4. Click "Save Changes"

### Customer Status

- **Active**: Regular active customer
- **Inactive**: Not currently using service
- **Suspended**: Account suspended (cannot request services)

### VIP Customers

VIP customers receive:
- Priority service request handling
- Faster response times
- Special attention from drivers

**To make customer VIP:**
1. Edit customer
2. Check "VIP Customer" box
3. Save changes

### Viewing Service History

1. Open customer details
2. Scroll to "Service History" section
3. View all past service requests
4. Click on request for details

### Customer Notes

Add notes for:
- Special instructions
- Customer preferences
- Account-specific information
- Communication history

## Driver Management

### Viewing Drivers

**Navigate:** Main Menu → Drivers

Display shows:
- Driver name and contact
- Current status
- Rating
- Number of jobs (total/completed)
- Last location update

### Adding a Driver

1. Click "Add Driver" button
2. Fill in information:
   - **Personal**: Name, email, phone
   - **License**: License number, state, expiry date
   - **Vehicle**: Driver's vehicle information
   - **Status**: Set initial status
3. Click "Save Driver"

**Required Fields:**
- First Name
- Last Name
- Email
- Phone
- License Number
- License State
- License Expiry Date

### Driver Status

**Available**
- Ready to accept new assignments
- Location tracking active
- Can be assigned to requests

**Busy**
- Currently on a service call
- Cannot accept new assignments
- Location tracking active

**On Break**
- Temporarily unavailable
- Cannot accept assignments
- Will return soon

**Offline**
- Not working
- Cannot accept assignments
- Location tracking inactive

### Managing Driver Status

**Manual Status Change:**
1. Open driver details
2. Click "Change Status"
3. Select new status
4. Save

**Drivers can self-manage status from:**
- Mobile app
- Driver portal

### Driver Performance

**Metrics Tracked:**
- Total jobs assigned
- Completed jobs
- Completion rate
- Average customer rating
- Response time
- Service time

**Viewing Performance:**
1. Navigate to Reports → Driver Performance
2. Select driver
3. Choose date range
4. View metrics and charts

### Driver Ratings

- Customers rate drivers after service completion
- Ratings: 1-5 stars
- Average rating displayed on driver profile
- Use ratings for:
  - Performance reviews
  - Recognition programs
  - Improvement areas identification

### GPS Tracking

If GPS tracking is enabled:
- View current driver locations
- Track movement history
- Calculate distances
- Optimize routing

**To view driver location:**
1. Open driver details
2. View "Current Location" section
3. Click "View on Map" (if map integration enabled)

## Service Request Management

### Creating a Service Request

1. Click "Create Request" or navigate to Requests → Add Request
2. Fill in details:
   - **Customer**: Select from list or add new
   - **Service Type**: Choose service needed
   - **Location**: Enter service address
   - **Description**: Describe the problem
   - **Priority**: Set priority level
3. Click "Create Request"

### Service Request Lifecycle

**1. Pending**
- Newly created
- Awaiting driver assignment
- Action: Assign available driver

**2. Assigned**
- Driver assigned
- Driver en route to location
- Action: Monitor progress

**3. In Progress**
- Driver arrived at location
- Service being performed
- Action: Monitor status

**4. Completed**
- Service finished successfully
- Customer can provide rating
- Action: Review and close

**5. Cancelled**
- Request cancelled
- Reason should be documented
- Action: Review reason

### Assigning Drivers

**Manual Assignment:**
1. Open pending request
2. Click "Assign Driver"
3. View available drivers with:
   - Distance from location
   - Current status
   - Rating
   - Current workload
4. Select driver
5. Confirm assignment

**Automatic Assignment:**
(Future feature - currently manual)
- System finds nearest available driver
- Considers driver rating and workload
- Sends notification to driver

### Priority Levels

**Emergency**
- Immediate attention required
- Highest priority
- Examples: Stranded with children, dangerous location

**High**
- Urgent but not emergency
- Quick response needed
- Examples: Highway breakdown, late for important event

**Normal**
- Standard priority
- Regular response time
- Examples: Most routine services

**Low**
- Can wait if necessary
- Lower priority
- Examples: Non-urgent services during busy times

### Monitoring Active Requests

Dashboard shows:
- Current active requests
- Time since request created
- Time since driver assigned
- Current status
- Estimated completion time

### Updating Request Status

Drivers can update via mobile app, or administrators can update:
1. Open request details
2. Click "Update Status"
3. Select new status
4. Add notes if needed
5. Save

### Request Notes and Communication

**Customer Notes:**
- Special instructions
- Access codes
- Contact preferences

**Driver Notes:**
- Observations at scene
- Additional services needed
- Parts or equipment used

**Admin Notes:**
- Internal notes
- Follow-up required
- Billing notes

### Handling Cancellations

To cancel a request:
1. Open request
2. Click "Cancel Request"
3. Select cancellation reason:
   - Customer requested
   - Duplicate request
   - Customer no-show
   - Unable to service
   - Other (specify)
4. Add notes
5. Confirm cancellation

**If driver was assigned:**
- Driver is automatically notified
- Driver status returns to available
- Credit customer account if applicable

## Reports and Analytics

### Available Reports

**Daily Operations Report**
- Requests received
- Requests completed
- Average response time
- Revenue summary
- Driver performance

**Monthly Performance Report**
- Month-over-month comparison
- Trend analysis
- Customer growth
- Revenue trends
- Driver metrics

**Driver Performance Report**
- Individual driver statistics
- Completion rates
- Customer ratings
- Response times
- Revenue generated

**Customer Report**
- Customer service history
- Frequency analysis
- Spending patterns
- VIP customer activity

### Generating Reports

1. Navigate to Reports
2. Select report type
3. Choose date range
4. Select filters (if applicable)
5. Click "Generate Report"
6. View online or export

### Export Options

Reports can be exported as:
- **PDF**: For printing or sharing
- **CSV**: For Excel analysis
- **Excel**: Direct Excel format

### Report Scheduling

**Coming Soon:** Automatic report generation
- Daily email reports
- Weekly summaries
- Monthly analytics
- Custom schedules

## System Settings

### General Settings

**Navigate:** Settings → General

**Site Information:**
- Site Name
- Company Name
- Contact Email
- Contact Phone

**Business Hours:**
- Operating days
- Start time
- End time
- Break times

**Default Options:**
- Default service radius (km)
- Default priority level
- Automatic assignment (on/off)

### GPS Tracking Settings

Enable/disable GPS tracking for:
- Real-time driver locations
- Route optimization
- Distance calculations

**Requirements:**
- Drivers must use mobile app
- Location permissions granted
- Data connection available

### Notification Settings

**Coming Soon:**
- Email notifications
- SMS notifications
- Push notifications
- Notification triggers

### Service Types

**Managing Service Types:**
1. Navigate to Settings → Service Types
2. View current service types
3. Add new or edit existing

**Service Type Fields:**
- Name
- Description
- Base Price
- Estimated Duration
- Active Status

**Common Service Types:**
- Jump Start
- Tire Change
- Fuel Delivery
- Lockout Service
- Towing (various distances)
- Winch Out

### Pricing Configuration

Set pricing for each service type:
- Base price
- Distance charges
- Time-based charges
- After-hours surcharge

## User Management

### User Roles

**Admin**
- Full system access
- User management
- System settings
- All features

**Manager**
- View all data
- Generate reports
- Manage customers/drivers
- Cannot change system settings

**Dispatcher**
- Create/manage requests
- Assign drivers
- View customers/drivers
- Limited reporting

**Driver**
- View assigned requests
- Update request status
- Update own location
- View own performance

### Adding Users

1. Navigate to Settings → Users
2. Click "Add User"
3. Fill in information:
   - Username (unique)
   - Email
   - Password
   - First/Last Name
   - Role
   - Status
4. Save user

### Managing User Permissions

Permissions are role-based:
- Admins: All permissions
- Managers: Most features except settings
- Dispatchers: Request and customer management
- Drivers: Limited to own requests

### Deactivating Users

To deactivate a user:
1. Find user in list
2. Click "Edit"
3. Change status to "Inactive"
4. Save

**Note:** Inactive users cannot login but data is preserved.

### Resetting Passwords

**For Users:**
1. Navigate to Users
2. Find user
3. Click "Reset Password"
4. Provide temporary password
5. User must change on next login

**For Yourself:**
1. Click profile icon
2. Select "Change Password"
3. Enter current password
4. Enter new password (twice)
5. Save

## Best Practices

### Daily Operations

**Morning:**
- Review pending requests from overnight
- Check driver availability
- Review scheduled maintenance

**Throughout Day:**
- Monitor active requests
- Respond to urgent requests quickly
- Maintain communication with drivers
- Update customer notes as needed

**End of Day:**
- Review completed requests
- Check all requests closed properly
- Generate daily report
- Plan for next day

### Customer Service

**Communication:**
- Respond promptly to inquiries
- Keep customers informed of status
- Follow up on completed services
- Handle complaints professionally

**Quality Assurance:**
- Review customer ratings
- Follow up on low ratings
- Identify improvement areas
- Recognize exceptional service

### Driver Management

**Scheduling:**
- Ensure adequate driver coverage
- Balance workload among drivers
- Plan for peak times
- Account for breaks and lunch

**Performance:**
- Monitor completion rates
- Review customer feedback
- Provide coaching when needed
- Recognize top performers

**Communication:**
- Daily briefings
- Clear instructions
- Prompt responses to questions
- Open feedback channels

### Data Management

**Accuracy:**
- Verify customer information
- Maintain up-to-date driver details
- Update service types as needed
- Correct errors promptly

**Cleanup:**
- Archive old requests (annual)
- Remove duplicate entries
- Update inactive customers
- Maintain clean database

### Security

**Account Security:**
- Use strong passwords
- Change passwords regularly
- Never share login credentials
- Log out when finished

**Data Protection:**
- Protect customer privacy
- Limit access to sensitive data
- Follow data retention policies
- Report security concerns

## Troubleshooting

### Common Issues

**Cannot Login**
- Verify username and password
- Check Caps Lock
- Clear browser cache
- Try different browser
- Contact administrator

**Driver Not Showing as Available**
- Check driver status
- Verify GPS location updated
- Check mobile app connection
- Update driver status manually

**Request Assignment Failing**
- Verify driver is available
- Check for system conflicts
- Review error messages
- Contact technical support

**Reports Not Generating**
- Check date range validity
- Verify data exists for period
- Try different date range
- Contact technical support

### Getting Help

**Documentation:**
- Check this manual
- Review FAQ
- See troubleshooting guide
- API documentation (for developers)

**Support:**
- Contact system administrator
- Submit support ticket
- Check system logs
- Report bugs on GitHub

### System Maintenance

**Regular Tasks:**
- Review activity logs
- Monitor disk space
- Check backup status
- Update software (with IT)

**Performance Issues:**
- Clear browser cache
- Close unnecessary tabs
- Restart browser
- Check internet connection

## Tips and Tricks

### Keyboard Shortcuts

- **Ctrl/Cmd + K**: Quick search
- **Ctrl/Cmd + N**: New request
- **Esc**: Close modal dialogs

### Quick Search

Use the search bar to quickly find:
- Customers by name, phone, or email
- Drivers by name
- Requests by ID
- Service types

### Bulk Operations

For multiple items:
- Export customer list
- Export service history
- Batch update statuses
- Generate multiple reports

### Mobile Access

Access dashboard from mobile devices:
- Responsive design
- Touch-friendly interface
- View key metrics
- Respond to urgent items

---

## Support and Resources

**Documentation:**
- [Developer Setup](../developer/SETUP.md)
- [API Documentation](../api/README.md)
- [Database Schema](../database/SCHEMA.md)
- [Deployment Guide](../deployment/README.md)

**Additional Help:**
- FAQ: [docs/FAQ.md](../FAQ.md)
- Troubleshooting: [docs/TROUBLESHOOTING.md](../TROUBLESHOOTING.md)
- Contact: support@roadsideassistance.com

---

**Version:** 1.0  
**Last Updated:** October 2024  
**For:** Patone Roadside Assistance Admin Platform
