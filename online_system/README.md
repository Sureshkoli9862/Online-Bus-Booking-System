# Online System - Login & Registration

A complete PHP-based authentication system with modern UI.

## Setup Instructions

1. **Database Setup:**
   - Import `database.sql` into your MySQL database
   - Update database credentials in `config.php` if needed

2. **File Structure:**
   ```
   online_system/
   ├── index.php          # Main login/register page
   ├── dashboard.php      # Protected user dashboard
   ├── login_process.php  # Login form handler
   ├── register_process.php # Registration form handler
   ├── logout.php         # Logout handler
   ├── session_check.php  # Session validation
   ├── config.php         # Database & app configuration
   ├── style.css          # Modern UI styling
   └── database.sql       # Database schema
   ```

3. **Features:**
   - ✅ User registration with validation
   - ✅ Secure login with password hashing
   - ✅ Session management with database storage
   - ✅ Protected dashboard area
   - ✅ Responsive modern UI
   - ✅ Input validation and error handling
   - ✅ Automatic session expiry

## Usage

1. Navigate to `http://localhost/online_system/`
2. Register a new account or login with existing credentials
3. Access the protected dashboard after successful authentication

## Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection protection with prepared statements
- Session token-based authentication
- Automatic session cleanup
- Input validation and sanitization
