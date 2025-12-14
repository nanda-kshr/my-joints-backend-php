# MyJoints Backend (PHP/MySQL)

A simple PHP/MySQL backend for the MyJoints application with folder-based routing.

## Setup Instructions

### 1. Database Setup
```bash
# Create database and import schema
mysql -u root -p < database.sql
```

### 2. Environment Configuration
```bash
# Copy .env.example to .env
cp .env.example .env

# Edit .env with your database credentials
nano .env
```

### 3. Configure Apache/PHP
Make sure `mod_rewrite` is enabled in Apache:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 4. Set Permissions
```bash
chmod 755 api
chmod 755 lib
chmod 777 public  # For file uploads
```

### 5. Access the API
The API will be available at: `http://localhost/api/`

## API Endpoints

### Authentication
- `POST /api/auth/signin` - Sign in (patient/doctor)
- `POST /api/auth/signup` - Not implemented (use patient/doctor specific)
- `POST /api/auth/verify-otp` - Verify OTP
- `POST /api/auth/forgot-password` - Request password reset
- `POST /api/auth/reset-password` - Reset password with OTP
- `POST /api/auth/delete-account` - Delete user account

### Patient Endpoints
- `POST /api/patient/signup` - Register new patient
- `GET /api/patient/profile` - Get patient profile (auth required)
- `POST /api/patient/upload` - Upload file
- `GET /api/patient/files` - Get patient files
- `GET /api/patient/download` - Download file
- `GET /api/patient/doctors` - Get assigned doctors
- `POST/GET/DELETE /api/patient/investigation` - Manage investigations
- `POST/GET/DELETE /api/patient/disease_score` - Manage disease scores
- `POST/GET/DELETE /api/patient/referrals` - Manage referrals
- `POST/GET/DELETE /api/patient/treatments` - Manage treatments
- `POST/GET/DELETE /api/patient/comorbidities` - Manage comorbidities
- `POST/GET/DELETE /api/patient/medications` - Manage medications
- `POST/GET /api/patient/pain-assessment` - Record/view pain scores
- `POST/GET/DELETE /api/patient/complaints` - Manage complaints

### Doctor Endpoints
- `POST /api/doctor/signup` - Register new doctor
- `GET /api/doctor/profile` - Get doctor profile (auth required)
- `GET/POST /api/doctor/patient` - Get/Link patients
- `GET /api/doctor/patients` - Get all linked patients
- `POST /api/doctor/consult-request` - Send consultation request
- `GET /api/doctor/notifications` - Get pending notifications
- `PUT /api/doctor/notifications/update` - Update notification status

## Authentication
All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer <your_jwt_token>
```

## Testing
Use the provided `postman.json` file to import all API endpoints into Postman.

## Notes
- File uploads are stored in the `public/` directory
- JWT tokens expire after 24 hours
- OTPs expire after 10 minutes
- Email sending uses PHP's `mail()` function (configure SMTP in .env)
