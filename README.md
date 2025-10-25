# 🚗 Drivelink - Global Driver Management Platform

[![Laravel](https://img.shields.io/badge/Laravel-8.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

A comprehensive, enterprise-grade driver management platform built with Laravel 8, designed to connect drivers with transportation companies globally. Features multi-category driver support, advanced KYC verification, intelligent matching algorithms, and complete role-based access control.

## 🌟 Key Features

### 👥 Multi-Role User Management
- **Drivers**: Professional, Commercial, Public, and Executive driver categories
- **Admins**: Company request management, driver verification, and analytics
- **Super Admins**: System administration, audit trails, and global settings
- **Companies**: Transportation companies (coming soon)

### 🌍 Global Multi-Category Driver Support
- **Commercial Truck Drivers**: Tanker, Tipper, Trailer, Container, Flatbed, Refrigerated
- **Professional Drivers**: Executive cars, Luxury vehicles, Corporate fleets
- **Public Drivers**: Taxi, Ride-share, Mini-bus services
- **Executive Drivers**: Luxury sedans, Armored vehicles, Diplomatic transport

### 🔐 Advanced Security & Authentication
- Multi-guard authentication system (Admin, Driver, Company)
- Role-Based Access Control (RBAC) with granular permissions
- Progressive KYC verification with OCR document processing
- Rate limiting and security headers middleware
- Audit trails and activity monitoring

### 📋 Progressive KYC Workflow
- **Step 1**: Basic profile & category selection (30% completion)
- **Step 2**: Category-specific requirements (65% completion)
- **Step 3**: Verification & onboarding (100% completion)
- OCR-powered document verification
- Multi-language support with RTL capabilities

### 🤝 Intelligent Matching System
- Automated driver-company matching algorithms
- Real-time availability tracking
- Priority queue management
- Bulk matching operations
- Performance analytics and reporting

### 💰 Commission & Payment Management
- Multi-currency support (NGN, USD, EUR, GBP, AED, etc.)
- Automated commission calculations
- Payment tracking and dispute management
- Financial reporting and analytics

### 📊 Analytics & Reporting
- Real-time dashboards for all user roles
- Driver performance metrics
- Company activity reports
- Financial analytics
- Export capabilities (CSV, PDF)

### 🌐 Globalization Features
- **195+ Countries** with localized support
- **Multi-language UI**: English, French, Arabic, Spanish, Hausa, Yoruba, Igbo
- **Regional Compliance**: Country-specific requirements and validation
- **Timezone Management**: Automatic detection and conversion
- **Cultural Adaptation**: RTL support, local formatting

### 📱 Mobile & API Ready
- RESTful API endpoints
- Mobile app documentation
- API rate limiting and versioning
- Webhook integrations for payments and notifications

## 🏗️ Project Milestone

### Current Status: **PRODUCTION READY** 🚀

**Version**: 2.0.0 (Global Enhancement Release)

**Development Phase**: Enterprise Production Deployment

### ✅ Completed Features (100% Implemented)
- ✅ Multi-guard authentication system
- ✅ Global driver categories with specialized workflows
- ✅ Progressive 3-step KYC system
- ✅ OCR document verification
- ✅ Intelligent matching algorithms
- ✅ Commission management system
- ✅ Role-based access control (RBAC)
- ✅ Audit trails and security monitoring
- ✅ Multi-language and multi-currency support
- ✅ Advanced analytics and reporting
- ✅ API documentation and mobile readiness
- ✅ Performance optimization and caching
- ✅ Security enhancements and rate limiting

### 🚧 In Development / Coming Soon
- 🔄 **Company Portal**: Full company dashboard for transportation companies
- 🔄 **Scheduled Reporting**: Automated report generation and delivery
- 🔄 **Advanced Matching AI**: Machine learning-based driver-company matching
- 🔄 **Mobile App**: Native iOS/Android applications
- 🔄 **Real-time Notifications**: Push notifications and WebSocket support

## 📋 Pages per User Role

### 👨‍💼 Admin Dashboard
- **Dashboard**: Overview stats, recent activity, quick actions
- **Driver Management**: List, create, edit, verify drivers; bulk operations; KYC review
- **Company Requests**: Manage transportation requests, approve/reject, queue management
- **Matching System**: Auto/manual matching, view matches, confirm/cancel
- **Notifications**: Compose, send bulk, manage templates, delivery stats
- **Reports**: Commission, driver performance, company activity, financial reports
- **Commissions**: Track payments, mark as paid, handle disputes
- **Verification**: Driver verification dashboard, bulk approval/rejection
- **Companies**: Manage company accounts, verification status
- **Users**: Admin user management, roles, permissions, activity logs

### 👨‍🚗 Driver Portal
- **Dashboard**: Earnings overview, job history, availability status, notifications
- **Profile**: Personal information, settings, password management
- **KYC Process**: 3-step verification workflow with document uploads
- **Jobs**: Available jobs, job history, accept/decline/complete jobs
- **Documents**: Upload and manage verification documents
- **Earnings**: Commission tracking, payment history, financial reports
- **Support**: Help center, contact support, FAQs

### 👔 Super Admin Portal
- **All Admin Features** plus:
- **System Health**: Server monitoring, performance metrics
- **Audit Trails**: Complete activity logs, security monitoring
- **Global Settings**: System configuration, API settings, security policies
- **Role Management**: Create/edit roles, assign permissions
- **Permission Management**: Granular permission control, bulk operations
- **User Analytics**: Registration trends, activity analysis, geographic insights

### 🏢 Company Portal (Coming Soon)
- **Dashboard**: Company overview, active drivers, request management
- **Driver Hiring**: Post requests, review applications, manage matches
- **Fleet Management**: Track vehicles, maintenance schedules
- **Financial Reports**: Commission payments, expense tracking
- **Performance Analytics**: Driver ratings, completion rates

## 🛠️ Technology Stack

### Backend
- **Framework**: Laravel 8.x
- **PHP**: 8.1+
- **Database**: MySQL 8.0+
- **Cache**: Redis/Memcached (configurable)
- **Queue**: Database/Redis queues

### Frontend
- **CSS Framework**: Bootstrap 5
- **JavaScript**: Vanilla JS with modern ES6+
- **Build Tool**: Vite
- **Icons**: Font Awesome

### Security & Performance
- **Authentication**: Laravel Sanctum
- **Authorization**: Custom RBAC system
- **Security**: Security headers, rate limiting, XSS protection
- **Performance**: Query optimization, caching, eager loading

### Integrations
- **Payment**: Flutterwave, Paystack, Stripe
- **SMS**: Twilio, Sendchamp, Termii
- **Email**: Mailgun, Sendchamp
- **OCR**: Google Cloud Vision API
- **Maps**: Google Maps API
- **KYC**: Veriff, Jumio, Smile Identity

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js & NPM
- MySQL 8.0+
- Git

### Quick Start

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/drivelink.git
   cd drivelink
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   # Configure your database in .env file
   php artisan migrate
   php artisan db:seed
   ```

6. **Build assets**
   ```bash
   npm run build
   # OR for development
   npm run dev
   ```

7. **Storage setup**
   ```bash
   php artisan storage:link
   ```

8. **Start the application**
   ```bash
   php artisan serve
   ```

### Production Deployment

1. **Optimize for production**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   composer install --optimize-autoloader --no-dev
   npm run build
   ```

2. **Set up queues and cron jobs**
   ```bash
   # Add to crontab
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

3. **Configure web server** (Apache/Nginx)
   ```apache
   <VirtualHost *:80>
       ServerName your-domain.com
       DocumentRoot /path/to/drivelink/public

       <Directory /path/to/drivelink/public>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

## 📖 Usage Guide

### For Drivers
1. **Register**: Choose driver category and complete basic information
2. **Complete KYC**: Follow 3-step verification process
3. **Upload Documents**: Submit required documents for verification
4. **Get Verified**: Wait for admin approval
5. **Start Working**: Accept jobs and earn commissions

### For Admins
1. **Login**: Access admin dashboard
2. **Manage Drivers**: Review and verify driver applications
3. **Handle Requests**: Process company transportation requests
4. **Monitor Matching**: Oversee driver-company connections
5. **Generate Reports**: Analyze platform performance

### For Companies (Coming Soon)
1. **Register Company**: Create company profile
2. **Post Requests**: Submit transportation requirements
3. **Review Matches**: Evaluate matched drivers
4. **Manage Fleet**: Track drivers and vehicles

## 🏛️ Project Structure

```
drivelink/
├── app/
│   ├── Console/          # Artisan commands
│   ├── Events/           # Event classes
│   ├── Exceptions/       # Exception handlers
│   ├── Http/
│   │   ├── Controllers/  # HTTP controllers
│   │   ├── Middleware/   # Custom middleware
│   │   └── Requests/     # Form request classes
│   ├── Models/           # Eloquent models
│   ├── Notifications/    # Notification classes
│   ├── Observers/        # Model observers
│   ├── Providers/        # Service providers
│   ├── Services/         # Business logic services
│   └── Traits/           # Reusable traits
├── bootstrap/            # Laravel bootstrap
├── config/               # Configuration files
├── database/
│   ├── factories/        # Model factories
│   ├── migrations/       # Database migrations
│   └── seeders/          # Database seeders
├── public/               # Public assets
├── resources/
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript files
│   ├── lang/            # Language files
│   └── views/           # Blade templates
├── routes/               # Route definitions
├── storage/              # File storage
├── tests/                # Test files
└── vendor/               # Composer dependencies
```

## 📚 API Documentation

### Authentication Endpoints
```http
POST /api/login
POST /api/register
POST /api/logout
POST /api/refresh
```

### Driver Endpoints
```http
GET    /api/drivers
POST   /api/drivers
GET    /api/drivers/{id}
PUT    /api/drivers/{id}
DELETE /api/drivers/{id}
POST   /api/drivers/{id}/verify
```

### Matching Endpoints
```http
GET    /api/matches
POST   /api/matches
PUT    /api/matches/{id}/confirm
PUT    /api/matches/{id}/cancel
```

### Commission Endpoints
```http
GET    /api/commissions
POST   /api/commissions/{id}/mark-paid
POST   /api/commissions/{id}/dispute
```

### Webhook Endpoints
```http
POST /api/webhooks/payment
POST /api/webhooks/verification
POST /api/webhooks/matching
```

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Ensure all tests pass before submitting PR

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Laravel Framework - The PHP framework for web artisans
- Bootstrap - The most popular HTML, CSS, and JS library
- Font Awesome - The iconic SVG, font, and CSS toolkit
- All our contributors and the open-source community

## 📞 Support

For support, email support@drivelink.com or join our Slack community.

---

**Drivelink** - Connecting drivers with opportunities worldwide 🌍

*Built with ❤️ using Laravel 8*
