# DBHC - Dashboard Kepegawaian Regional 1 PT Angkasa Pura Indonesia

[![Laravel](https://img.shields.io/badge/Laravel-12-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-Proprietary-yellow.svg)]()

## Tentang Aplikasi

Dashboard Kepegawaian (DBHC) adalah sistem manajemen kepegawaian berbasis web yang dikembangkan untuk PT Angkasa Pura Indonesia Regional 1. Aplikasi ini menyediakan solusi lengkap untuk mengelola data karyawan, formasi jabatan, dan realisasi program kerja dengan fitur analytics yang mendalam.

## ✨ Fitur Utama

### 📊 Dashboard Interaktif
- KPI Cards dengan statistik real-time
- Interactive Charts menggunakan ECharts
- Visualisasi data organik vs outsourcing
- Analytics per lokasi dan unit

### 👥 Data Management
- **Data Karyawan**: CRUD lengkap untuk data karyawan organik dan outsourcing
- **Formasi Jabatan**: Management posisi dan kuota jabatan
- **Realisasi Program**: Tracking program kerja dan pencapaian
- Import/Export Excel dengan batch processing
- Template download untuk import

### 📈 Analytics System
- **Analitik Organik**: Piramida jabatan, distribusi gender, analisis usia, masa kerja
- **Analitik Outsourcing**: Analytics khusus untuk karyawan outsourcing
- Multi-tab analysis dengan visualisasi interaktif
- Drill-down per lokasi dan unit

### 🔐 User Management (Admin Only)
- CRUD user dengan role assignment (Admin/User)
- Password management dengan konfirmasi
- User profile dengan avatar support

### 📝 Audit Log System (Admin Only)
- **Automatic Tracking**: Semua perubahan data tercatat otomatis
- **Detailed Information**:
  - User, timestamp, IP address, user agent
  - Field-by-field comparison (nilai lama → baru)
  - Model identifier untuk identifikasi mudah
- **Advanced Filtering**:
  - Quick menu filter (Karyawan, Formasi, Realisasi, User)
  - Search, action type, user, date range
- **Security**: Sensitive fields (password) di-mask untuk keamanan

### 🔄 Version Control
- Snapshot data untuk backup
- Restore ke versi sebelumnya
- Download version export
- Version comparison

### 👤 Profile Management
- Update profil user
- Change password dengan validasi
- Session management

## 🛠️ Technical Stack

- **Backend**: Laravel 12.x
- **PHP Version**: 8.3+
- **Database**: MySQL/MariaDB
- **Frontend**: 
  - Blade Templates
  - Bootstrap 5
  - Alpine.js
  - Bootstrap Icons
- **Charts**: Apache ECharts
- **Excel Processing**: Maatwebsite Excel (PhpSpreadsheet)
- **Authentication**: Laravel built-in + Spatie Permissions
- **Audit Trail**: Custom trait-based observer pattern

## 📋 Requirements

- PHP >= 8.3
- Composer
- Node.js & NPM
- MySQL/MariaDB
- Apache/Nginx

## 🚀 Installation

```bash
# Clone repository
git clone [repository-url]
cd DBHC

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database di .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dbhc
DB_USERNAME=root
DB_PASSWORD=

# Run migrations dan seeders
php artisan migrate --seed

# Build frontend assets
npm run build

# Start development server
php artisan serve
```

## 👥 Default Login

**Admin Account:**
- Email: `admin@hc.com`
- Password: `password`

**User Account:**
- Email: `user@hc.com`  
- Password: `password`

## 📖 Documentation

- [System Flowchart](system_flowchart.md) - Diagram alur sistem lengkap
- [Audit Log Documentation](AUDIT_LOG_DOCUMENTATION.md) - Dokumentasi lengkap audit log
- [Audit Log Quick Guide](AUDIT_LOG_README.md) - Panduan cepat audit log

## 🔑 User Roles & Permissions

### Admin
- ✅ Full CRUD access semua data
- ✅ Import/Export data
- ✅ User management
- ✅ Audit log access
- ✅ Delete operations
- ✅ Version control

### User
- ✅ View data (read-only)
- ✅ Export data
- ✅ View analytics
- ✅ Profile management
- ❌ Create/Edit/Delete
- ❌ Import data
- ❌ User management
- ❌ Audit log access

## 🎯 Key Features Detail

### Audit Log System
Sistem audit log otomatis tracking semua perubahan:
- **Models Tracked**: DataKaryawan, Formasi, Realisasi, User
- **Information Captured**: User, timestamp, IP, user agent, old/new values
- **Filtering**: 7 filter types dengan quick menu buttons
- **Security**: Password fields di-mask, admin-only access

### Import System
- Support Excel (.xlsx, .xls) dan CSV
- Batch processing untuk file besar (1000 records/batch)
- Two modes: Add (append) dan Replace (truncate first)
- Validation dengan error reporting
- Template download available

### Analytics
- Interactive charts dengan drill-down capability
- Real-time data processing
- Multiple analysis tabs
- Export chart as image
- Responsive design

## 🏗️ Architecture

```
DBHC/
├── app/
│   ├── Http/Controllers/    # Controllers
│   ├── Models/              # Eloquent models
│   ├── Traits/              # HasAuditLog trait
│   ├── Observers/           # Model observers
│   ├── Imports/             # Excel import classes
│   └── Exports/             # Excel export classes
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/             # Data seeders
├── resources/
│   ├── views/               # Blade templates
│   ├── js/                  # JavaScript files
│   └── css/                 # Stylesheets
└── routes/
    └── web.php              # Application routes
```

## 🐛 Troubleshooting

### Audit log tidak muncul
- Pastikan user sudah login saat melakukan perubahan
- Check table `audit_logs` di database
- Verify model menggunakan trait `HasAuditLog`

### Import error
- Check file format (Excel/CSV)
- Verify column headers match template
- Check for data validation errors in error report

### Charts tidak muncul
- Run `npm run build` untuk compile assets
- Check browser console for JavaScript errors
- Verify database has data

## 📄 License

Proprietary - PT Angkasa Pura Indonesia

## 👨‍💻 Developer

Developed for PT Angkasa Pura Indonesia Regional 1

---

**Version**: 1.0.0  
**Last Updated**: October 2025

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
# DBHC1
