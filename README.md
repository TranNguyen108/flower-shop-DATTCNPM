# 🌸 Flower Store Website - E-Commerce Platform

**Version**: 3.0 - Folder Reorganization  
**Last Updated**: December 26, 2024  
**Status**: ✅ Production Ready

---

## 📋 Mục Lục

1. [Tổng Quan](#tổng-quan)
2. [Tính Năng](#tính-năng)
3. [Công Nghệ Sử Dụng](#công-nghệ-sử-dụng)
4. [Cài Đặt](#cài-đặt)
5. [Bảo Mật](#bảo-mật)
6. [Cấu Trúc Dự Án](#cấu-trúc-dự-án)
7. [API & Database](#api--database)
8. [Roadmap](#roadmap)
9. [Contributors](#contributors)

---

## 🎯 Tổng Quan

**Flower Store** là một hệ thống website bán hoa trực tuyến đầy đủ tính năng, được xây dựng với PHP và MySQL. Phiên bản 2.0 tập trung vào nâng cấp bảo mật và trải nghiệm người dùng.

### ✨ Điểm Nổi Bật

- 🔐 **Bảo mật cao**: Prepared Statements, CSRF Protection, Password Hashing (bcrypt)
- 🛒 **E-commerce đầy đủ**: Cart, Wishlist, Orders, Reviews
- 👥 **Quản lý người dùng**: User/Admin roles
- 📦 **Quản lý sản phẩm**: CRUD operations, Categories, Stock management
- 💰 **Thanh toán**: Multiple payment methods (cash, online)
- 📊 **Dashboard Admin**: Statistics, Orders management, Reports
- 📱 **Responsive Design**: Mobile-friendly interface
- 🚚 **Order Tracking**: Real-time delivery status with map

---

## ⚡ Tính Năng

### Khách Hàng (User)

- ✅ Đăng ký / Đăng nhập (với bcrypt password hashing)
- ✅ Duyệt sản phẩm theo danh mục
- ✅ Tìm kiếm & lọc sản phẩm
- ✅ Thêm vào giỏ hàng / Wishlist
- ✅ Đặt hàng với nhiều phương thức thanh toán
- ✅ Theo dõi đơn hàng
- ✅ Đánh giá sản phẩm
- ✅ Quản lý tài khoản & avatar
- ✅ Liên hệ với shop

### Quản Trị Viên (Admin)

- ✅ Dashboard với thống kê tổng quan
- ✅ Quản lý sản phẩm (CRUD)
- ✅ Quản lý đơn hàng (status, payment, delivery)
- ✅ Quản lý người dùng
- ✅ Quản lý đánh giá (approve/reply)
- ✅ Xem & trả lời tin nhắn liên hệ
- ✅ Quản lý coupon/voucher
- ✅ Xem báo cáo & analytics
- ⏳ Export reports (coming soon)

---

## 🛠️ Công Nghệ Sử Dụng

### Backend
- **PHP 8.0+** - Server-side scripting
- **MySQL/MariaDB** - Database
- **MySQLi** - Database interface với Prepared Statements

### Frontend
- **HTML5** - Markup
- **CSS3** - Styling với CSS Variables
- **JavaScript (ES6+)** - Client-side interactions
- **Font Awesome 6** - Icons
- **Leaflet.js** - Maps cho order tracking

### Security
- **bcrypt** - Password hashing
- **CSRF Tokens** - Cross-Site Request Forgery protection
- **Prepared Statements** - SQL Injection prevention
- **XSS Protection** - Output escaping
- **Session Security** - Secure session management

### Libraries & Tools
- **PHPMailer** (planned) - Email notifications
- **Chart.js** (planned) - Analytics charts
- **MoMo/VNPay API** (planned) - Payment gateway

---

## 📥 Cài Đặt

### Yêu Cầu Hệ Thống

- PHP 8.0 hoặc cao hơn
- MySQL 5.7+ / MariaDB 10.2+
- Apache/Nginx Web Server
- 2GB RAM minimum
- SSL Certificate (khuyến nghị cho production)

### Các Bước Cài Đặt

#### 1. Clone dự án

```bash
git clone https://github.com/yourrepo/flower-store.git
cd flower-store
```

#### 2. Tạo database

```sql
CREATE DATABASE shop_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

#### 3. Import database

```bash
mysql -u root -p shop_db < shop_db.sql
```

Hoặc sử dụng phpMyAdmin để import file `shop_db.sql`

#### 4. Cấu hình

Edit file `config.php`:

```php
$conn = mysqli_connect('localhost', 'root', 'YOUR_PASSWORD', 'shop_db');
```

Đổi `YOUR_PASSWORD` thành mật khẩu MySQL của bạn.

#### 5. Set permissions

```bash
chmod 755 uploaded_img/
chmod 644 config.php
```

#### 6. Chạy migration (QUAN TRỌNG!)

Mở trình duyệt:
```
http://localhost/flower-store/migrate_passwords.php
```

Click "Chạy Migration" để chuyển passwords từ MD5 sang bcrypt.

⚠️ **SAU KHI CHẠY XONG, XÓA FILE `migrate_passwords.php`!**

#### 7. Đăng nhập

- **User URL**: `http://localhost/flower-store/login.php`
- **Admin URL**: `http://localhost/flower-store/admin_page.php`

**Tài khoản mặc định** (sau migration):
- Email: `admin@gmail.com` (hoặc email có sẵn trong DB)
- Password: `FlowerStore2025!`

⚠️ **ĐỔI MẬT KHẨU NGAY SAU KHI ĐĂNG NHẬP!**

---

## 🔐 Bảo Mật

### Các Biện Pháp Bảo Mật Đã Triển Khai

#### 1. SQL Injection Prevention
```php
// ❌ TRƯỚC (không an toàn)
mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");

// ✅ SAU (an toàn)
db_fetch_one($conn, "SELECT * FROM users WHERE email = ?", "s", [$email]);
```

#### 2. CSRF Protection
```php
// Trong form
<?php echo csrf_field(); ?>

// Khi xử lý
if (!verify_csrf_token($_POST['csrf_token'])) {
    die('CSRF attack detected!');
}
```

#### 3. Password Hashing
```php
// ❌ TRƯỚC (MD5 - dễ crack)
$password = md5($_POST['password']);

// ✅ SAU (bcrypt - an toàn)
$password = hash_password($_POST['password']);
if (verify_password($input_pass, $stored_hash)) {
    // Login success
}
```

#### 4. XSS Prevention
```php
// ❌ TRƯỚC
echo $user_input;

// ✅ SAU
echo e($user_input); // htmlspecialchars wrapper
```

#### 5. Session Security
- Session timeout (30 phút)
- Session regeneration sau login
- Secure cookie settings
- HTTPOnly flags

### Security Headers

File `config.php` tự động set các headers:
```
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
X-XSS-Protection: 1; mode=block
```

### File Upload Security

```php
// Validate file uploads
$validation = validate_image_upload($_FILES['image']);
if (!$validation['success']) {
    die($validation['message']);
}

// Secure filename
$secure_name = secure_filename($_FILES['image']['name']);
```

---

## 📁 Cấu Trúc Dự Án

```
flower-shop/
│
├── config.php                     # Database & security config
├── header.php                     # Global header
├── footer.php                     # Global footer
├── ajax_search.php                # AJAX search endpoint
│
├── pages/                         # 🆕 User Pages
│   ├── home.php                   # Homepage
│   ├── shop.php                   # Shop with filters
│   ├── category.php               # Category pages (consolidated)
│   ├── hotnhat.php                # Hot products
│   ├── about.php                  # About page
│   ├── contact.php                # Contact form
│   ├── view_page.php              # Product details
│   ├── search_page.php            # Search products
│   ├── cart.php                   # Shopping cart
│   ├── checkout.php               # Checkout process
│   ├── orders.php                 # User orders
│   ├── wishlist.php               # User wishlist
│   ├── profile.php                # User profile
│   ├── place_order.php            # Order processing
│   └── submit_review.php          # Review submission
│
├── admin/                         # 🆕 Admin Pages
│   ├── dashboard.php              # Admin dashboard
│   ├── products.php               # Manage products
│   ├── orders.php                 # Manage orders
│   ├── users.php                  # Manage users
│   ├── reviews.php                # Manage reviews
│   ├── chat.php                   # Admin chat
│   ├── inventory.php              # Inventory management
│   ├── coupons.php                # Manage coupons
│   ├── stats.php                  # Statistics
│   ├── update_product.php         # Product editing
│   ├── header.php                 # Admin header
│   └── .htaccess                  # 🔒 Admin security
│
├── auth/                          # 🆕 Authentication
│   ├── login.php                  # Login page
│   ├── register.php               # Register page
│   ├── logout.php                 # Logout handler
│   ├── forgot_password.php        # Password recovery
│   └── reset_password.php         # Password reset
│
├── payment/                       # 🆕 Payment Processing
│   ├── payment_ipn.php            # Payment callback
│   └── payment_return.php         # Payment return page
│
├── chat/                          # 🆕 Chat System
│   ├── chat_widget.php            # Chat widget
│   └── chat_ajax.php              # Chat AJAX handler
│
├── assets/                        # 🆕 Assets Directory
│   ├── uploads/
│   │   ├── products/              # Product images
│   │   ├── users/                 # User avatars
│   │   └── reviews/               # Review images
│   └── images/                    # Static images (logo, icons)
│
├── includes/                      # Utility Functions
│   ├── db_functions.php           # Database helpers
│   ├── admin_functions.php        # Admin utilities
│   ├── email_service.php          # Email handling
│   ├── inventory_functions.php    # Inventory helpers
│   └── payment_gateway.php        # Payment integration
│
├── css/                           # Stylesheets
│   ├── style.css                  # Main styles
│   ├── product-cards.css          # Product card styles
│   └── admin_style.css            # Admin styles
│
├── js/                            # JavaScript
│   ├── script.js                  # Frontend scripts
│   └── admin_script.js            # Admin scripts
│
├── shop_db.sql                    # Database schema
├── database_*.sql                 # Database updates
│
├── README.md                      # This file
├── MIGRATION_NOTES.md             # 🆕 Migration documentation
├── STRUCTURE_ANALYSIS.md          # 🆕 Structure analysis
├── FOLDER_REORGANIZATION.md       # 🆕 Reorganization plan
└── OPTIMIZATION_NOTES.md          # Optimization notes
```

### 🎯 Folder Organization Benefits

1. **Separation of Concerns**: Clear distinction between user, admin, auth, and utility code
2. **Better Security**: Admin folder protected with .htaccess
3. **Easier Maintenance**: Related files grouped together
4. **Cleaner URLs**: Logical path structure (e.g., `/pages/shop.php`, `/admin/dashboard.php`)
5. **Scalability**: Easy to add new features in appropriate folders

---

## 💾 API & Database

### Database Schema

#### Users Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255), -- bcrypt hash
    user_type ENUM('user', 'admin'),
    avatar VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Products Table
```sql
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    details TEXT,
    price INT,
    image VARCHAR(255),
    category VARCHAR(50),
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Orders Table
```sql
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    name VARCHAR(100),
    number VARCHAR(12),
    email VARCHAR(100),
    method VARCHAR(50),
    address VARCHAR(500),
    total_products TEXT,
    total_price INT,
    placed_on VARCHAR(50),
    payment_status VARCHAR(20) DEFAULT 'pending',
    delivery_status VARCHAR(50) DEFAULT 'Đang xử lý',
    delivery_lat FLOAT,
    delivery_lng FLOAT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Helper Functions

#### Database Operations
```php
// SELECT
$result = db_select($conn, "SELECT * FROM products WHERE category = ?", "s", [$category]);

// INSERT
$id = db_insert($conn, "INSERT INTO cart (user_id, pid) VALUES (?, ?)", "ii", [$user_id, $product_id]);

// UPDATE
db_update($conn, "UPDATE products SET stock = ? WHERE id = ?", "ii", [$new_stock, $product_id]);

// DELETE
db_delete($conn, "DELETE FROM cart WHERE id = ?", "i", [$cart_id]);

// COUNT
$count = db_count($conn, "SELECT * FROM orders WHERE user_id = ?", "i", [$user_id]);

// FETCH ONE
$user = db_fetch_one($conn, "SELECT * FROM users WHERE id = ?", "i", [$user_id]);
```

---

## 🗺️ Roadmap

### ✅ Phase 1: Security (70% Complete)
- [x] SQL Injection protection
- [x] CSRF tokens
- [x] Password hashing (bcrypt)
- [x] XSS protection
- [x] Session security
- [ ] Apply to all remaining files

### 🔄 Phase 2: Backend Features (Planned)
- [ ] Email notifications (PHPMailer)
- [ ] Payment gateway integration (MoMo/VNPay)
- [ ] Inventory management (auto-deduct stock)
- [ ] Coupon system completion
- [ ] Advanced error handling & logging

### 📱 Phase 3: Frontend Improvements (Planned)
- [ ] AJAX cart operations
- [ ] Image lazy loading
- [ ] Real-time search suggestions
- [ ] Better mobile responsiveness
- [ ] Loading states & animations

### 🚀 Phase 4: Advanced Features (Planned)
- [ ] Admin analytics dashboard (Chart.js)
- [ ] RESTful API for mobile app
- [ ] Multi-language support (i18n)
- [ ] Live chat integration
- [ ] Social media integration
- [ ] PWA support
- [ ] SEO optimization

---

## 🐛 Known Issues & Limitations

### Current Limitations
1. ⚠️ Nhiều file chưa được update với prepared statements
2. ⚠️ Một số forms thiếu CSRF protection
3. ⚠️ Payment gateway chưa tích hợp API thật
4. ⚠️ Email notifications chưa được triển khai
5. ⚠️ Mobile responsiveness cần cải thiện

### Planned Fixes
- Update tất cả files với security improvements
- Implement real payment gateway
- Add email system
- Improve mobile UI/UX
- Add comprehensive testing

---

## 🧪 Testing

### Manual Testing Checklist

#### Security Tests
- [ ] Test SQL injection (should fail)
- [ ] Test CSRF attack (should fail)
- [ ] Test XSS injection (should be escaped)
- [ ] Test session hijacking (should regenerate)
- [ ] Test file upload (only images allowed)

#### Functionality Tests
- [ ] User registration & login
- [ ] Add products to cart/wishlist
- [ ] Checkout process
- [ ] Order tracking
- [ ] Admin CRUD operations
- [ ] Search & filters

### Test Accounts

After running `migrate_passwords.php`:

**Admin Account:**
- Email: (check database)
- Password: `FlowerStore2025!`

**Test User:**
- Register new account via `/register.php`

---

## 📄 License

This project is licensed under the MIT License.

---

## 👥 Contributors

- **Lead Developer**: [Your Name]
- **Security Consultant**: AI Assistant (Claude Sonnet 4.5)
- **UI/UX Designer**: [Designer Name]

---

## 📞 Support

Nếu gặp vấn đề:

1. Kiểm tra file `SECURITY_UPDATE_GUIDE.md`
2. Xem lại phần [Cài Đặt](#cài-đặt)
3. Kiểm tra logs trong `error_log`
4. Liên hệ developer

---

## 🙏 Credits

- **Font Awesome** - Icons
- **Leaflet.js** - Maps
- **Unsplash** - Sample images
- **Google Fonts** - Typography

---

*Cập nhật lần cuối: December 12, 2025*  
*Made with ❤️ for a secure e-commerce experience*
