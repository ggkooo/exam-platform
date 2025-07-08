# 🎓 Exam Platform - Moodle Integration

> A Laravel-based examination platform with seamless Moodle integration for user authentication and role management.

## 🚀 **Features**

### **🔐 Authentication**
- **Moodle Integration**: Direct authentication against Moodle database
- **Role-Based Access**: Comprehensive role hierarchy and permissions
- **Secure Sessions**: Cookie-based session management with 30-day expiration
- **Auto Role Detection**: Dynamic role fetching and primary role determination

### **👥 User Management**
- **Admin Dashboard**: Complete user management interface
- **Role Assignment**: Assign/remove roles with real-time updates
- **User Search**: Advanced search with pagination
- **Role Hierarchy**: admin > manager > editingteacher > teacher > student

### **🎨 Modern Interface**
- **Responsive Design**: Bootstrap 5 with mobile-first approach
- **Modular Architecture**: Reusable partials and components
- **Professional UI**: Clean, intuitive admin interfaces
- **Real-time Updates**: AJAX-powered role refresh without logout

## 📋 **Requirements**

- **PHP**: 8.1+
- **Laravel**: 11.x
- **Database**: MySQL/PostgreSQL (Moodle integration)
- **Composer**: Latest version
- **Node.js**: 16+ (for asset compilation)

## ⚡ **Quick Start**

### **1. Clone & Install**
```bash
git clone https://github.com/your-username/exam-platform.git
cd exam-platform
composer install
npm install && npm run build
```

### **2. Environment Setup**
```bash
cp .env.example .env
php artisan key:generate
```

### **3. Database Configuration**
Configure your `.env` file with Moodle database connection:

```env
# Default Laravel Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=exam_platform
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Moodle Database Connection
MOODLE_DB_CONNECTION=mysql
MOODLE_DB_HOST=127.0.0.1
MOODLE_DB_PORT=3306
MOODLE_DB_DATABASE=moodle
MOODLE_DB_USERNAME=moodle_user
MOODLE_DB_PASSWORD=moodle_password
```

### **4. Run Migrations**
```bash
php artisan migrate
```

### **5. Start Development Server**
```bash
php artisan serve
```

Visit `http://localhost:8000` and login with your Moodle credentials!

## 🏗️ **Architecture**

### **Backend Structure**
```
app/
├── Http/Controllers/
│   ├── Auth/LoginController.php       # Moodle authentication
│   ├── RoleManagementController.php   # Admin role management
│   └── SessionController.php          # Session & role refresh
├── Helpers/
│   ├── RoleHelper.php                 # Role utilities & UI helpers
│   └── MoodleRoleManager.php          # Database role operations
└── Providers/AppServiceProvider.php   # Service registration
```

### **Frontend Structure**
```
resources/views/
├── layouts/app.blade.php              # Base layout template
├── partials/                          # Reusable components
├── dashboard.blade.php                # Main dashboard
├── auth/login.blade.php               # Login interface
└── admin/                             # Admin interfaces
    ├── roles.blade.php                # Role management
    ├── users.blade.php                # User listing
    └── user-roles.blade.php           # User role editor
```

## 🎯 **Usage**

### **For Students/Teachers**
1. Login with Moodle credentials
2. View dashboard with role information
3. Access role-appropriate features

### **For Administrators**
1. Login and access admin panel
2. Manage users and roles
3. Assign/remove permissions
4. Monitor system activity

### **Role Management**
- **Self Management**: Users can manage their own roles (admin only)
- **User Management**: Admins can assign roles to other users
- **Dynamic Updates**: Roles update in real-time without logout
- **Site Admin**: Special admin role for full system access

## 🔧 **Configuration**

### **Role Hierarchy**
The system uses Moodle's role hierarchy:
- `admin/manager`: Full system administration
- `editingteacher`: Course creation and management
- `teacher`: Course teaching capabilities
- `student`: Course participation
- `guest`: Limited read-only access

### **Security Features**
- **Permission Checks**: All admin functions require proper authorization
- **Secure Cookies**: httpOnly cookies with secure transmission
- **SQL Protection**: Prepared statements prevent injection
- **Error Logging**: Comprehensive logging for debugging

## 📚 **API Endpoints**

### **Authentication**
- `POST /login` - Moodle authentication
- `POST /logout` - Session cleanup

### **Admin Routes** (Requires admin role)
- `GET /admin/users` - User listing with search
- `GET /admin/users/{id}/roles` - User role management
- `POST /admin/users/roles/assign` - Assign role to user
- `POST /admin/users/roles/remove` - Remove role from user

### **Session Management**
- `POST /session/refresh-roles` - Refresh current user roles

## 🧪 **Testing**

```bash
# Run PHP tests
php artisan test

# Run feature tests
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

## 🤝 **Contributing**

1. Fork the repository
2. Create feature branch: `git checkout -b feature/amazing-feature`
3. Commit changes: `git commit -m 'feat: add amazing feature'`
4. Push to branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

### **Commit Convention**
We use semantic commits:
- `feat:` New features
- `fix:` Bug fixes
- `refactor:` Code refactoring
- `docs:` Documentation updates
- `test:` Testing improvements

## 📄 **License**

This project is licensed under the [MIT License](LICENSE).

## 🆘 **Support**

For support and questions:
- 📧 Email: [your-email@domain.com]
- 🐛 Issues: [GitHub Issues](https://github.com/your-username/exam-platform/issues)
- 📖 Docs: [Project Wiki](https://github.com/your-username/exam-platform/wiki)

---

**Built with ❤️ using Laravel & Bootstrap**

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
