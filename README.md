<!-- @format -->

# 🪷 Dhoti Mahal — Traditional Indian Attire E-Commerce

A complete PHP + MySQL e-commerce platform for selling traditional Indian dhotis, built with clean procedural PHP, PDO, and a luxurious traditional aesthetic.

---

## ✨ Features

### Customer-Facing

- 🏠 **Homepage** — Hero slider, featured products, categories, trust badges
- 🛍️ **Product Catalog** — Filter by category, sort, paginate, search
- 🔍 **Product Detail** — Image gallery, size selector, quantity picker, related products
- 🛒 **Shopping Cart** — AJAX add/remove/update, shipping calculator
- 💳 **Checkout** — Address form with validation, order notes
- 📱 **UPI Payment** — QR code, deep link, transaction ID confirmation
- ✅ **Order Confirmation** — Summary with item details
- 🚚 **Order Tracking** — Timeline view, status steps
- 👤 **User Accounts** — Register, login, profile, order history, password change

### Admin Panel (`/admin/`)

- 📊 **Dashboard** — Stats, recent orders, low-stock alerts
- 📋 **Orders** — List, filter by status, update status + add tracking entries
- 📦 **Products** — Add, edit, deactivate, stock management
- 🏷️ **Categories** — Add/remove product categories
- 🖼️ **Banners** — Upload homepage slider banners
- ⚙️ **Settings** — UPI ID, shipping rates, contact info, social links

---

## 🚀 Installation

### Requirements

- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` enabled

### Steps

1. **Clone / copy** the project folder to your web server:

   ```
   /var/www/html/dhoti-mahal/
   ```

   or XAMPP/WAMP:

   ```
   C:/xampp/htdocs/dhoti-mahal/
   ```

2. **Create the database**:

   ```sql
   CREATE DATABASE dhoti_mahal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Import the schema**:

   ```bash
   mysql -u root -p dhoti_mahal < config/schema.sql
   ```

4. **Configure database credentials** in `config/database.php`:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'dhoti_mahal');
   define('DB_USER', 'root');
   define('DB_PASS', 'your_password');
   ```

5. **Set your BASE_URL** in `config/config.php`:

   ```php
   define('BASE_URL', 'http://localhost/dhoti-mahal/');
   ```

6. **Make upload directories writable**:

   ```bash
   chmod 755 uploads/products/ uploads/banners/ storage/
   ```

7. **Enable Apache `mod_rewrite`** (for clean URLs):

   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

8. Visit `http://localhost/dhoti-mahal/`

---

## 🔐 Admin Login

URL: `http://localhost/dhoti-mahal/admin/login.php`

| Field    | Value                  |
| -------- | ---------------------- |
| Email    | `admin@dhotimahal.com` |
| Password | `admin123`             |

> ⚠️ **Change the admin password immediately after first login via Settings or directly in the database.**

---

## 📁 Project Structure

```
dhoti-mahal/
├── config/
│   ├── database.php       # PDO DB connection
│   ├── config.php         # BASE_URL, constants
│   └── schema.sql         # DB structure + sample data
│
├── includes/
│   ├── functions.php      # All helper functions
│   ├── auth.php           # Session & auth handling
│   ├── header.php         # Site header
│   ├── footer.php         # Site footer
│   └── product-card.php   # Reusable product card
│
├──notifications/
|   ├──check-whatsapp-tables.php
|   ├──verify-whatsapp.php
|   ├──whatsapp-test.php
|
├── pages/                 # Customer-facing pages
│   ├── home.php
│   ├── category.php
│   ├── product.php
│   ├── cart.php
│   ├── checkout.php
│   ├── payment.php
│   ├── confirmation.php
│   ├── track-order.php
│   ├── user-profile.php
│   ├── login.php
│   ├── register.php
│   └── logout.php
│
├── admin/                 # Admin panel
│   ├── partials/
│   │   ├── header.php
│   │   └── footer.php
│   ├── login.php
│   ├── dashboard.php
│   ├── orders.php
│   ├── order-detail.php
│   ├── products.php
│   ├── product-add.php
│   ├── product-edit.php
│   ├── categories.php
│   ├── banners.php
│   └── settings.php
│
├── api/
│   ├── cart.php           # AJAX cart operations
│   └── order.php          # Order status API
│
├── assets/
│   ├── css/main.css       # All styles
│   ├── js/main.js         # All JavaScript
│   └── images/            # Static assets
│
├── uploads/
│   ├── products/          # Product images
│   └── banners/           # Banner images
│
├── storage/
│   └── logs.txt           # Error logs
│
├── .htaccess              # Apache URL rewrite + security
├── index.php              # Main router
└── README.md
```

---

## 🛒 Shopping Flow

```
Homepage → Category/Search → Product Detail
    → Add to Cart (AJAX)
    → Cart Page
    → Checkout (address + details)
    → Payment Page (UPI QR + transaction ID)
    → Order Confirmation
    → Track Order
```

---

## 💳 Payment Flow

This uses **manual UPI verification**:

1. Customer places order → redirected to Payment page
2. QR code generated from `upi://` deep link
3. Customer pays via GPay / PhonePe / Paytm / any UPI app
4. Customer enters UTR / Transaction ID
5. Order marked as `confirmed` + `paid`
6. Admin can verify and update tracking

---

## 🎨 Design System

| Token         | Value            | Use                 |
| ------------- | ---------------- | ------------------- |
| `--crimson`   | `#9B1C1C`        | Primary color, CTAs |
| `--gold`      | `#C9922A`        | Accents, highlights |
| `--ivory`     | `#FAF7F0`        | Background          |
| `--cream`     | `#F5EDD8`        | Sections            |
| `--font-head` | Playfair Display | Headings            |
| `--font-body` | Lato             | Body text           |

---

## 🔒 Security Features

- PDO prepared statements (SQL injection prevention)
- CSRF token validation on all forms
- Password hashing with `bcrypt` (cost 12)
- `htmlspecialchars` output sanitization
- Session security with `HttpOnly` + `SameSite` cookies
- Protected config/includes directories via `.htaccess`
- Admin authentication separate from user auth

---

## 📝 Customization

### Change UPI ID

Go to Admin → Settings → Payment Settings → UPI ID

### Add Products

Go to Admin → Products → Add Product

### Change Shipping Cost

Go to Admin → Settings → Shipping Settings

### Change Colors / Fonts

Edit `assets/css/main.css` — all design tokens are CSS variables at the top of the file.

---

## � Tech Stack

| Layer    | Technology                             |
| -------- | -------------------------------------- |
| Backend  | PHP 8.0+ (procedural)                  |
| Database | MySQL 5.7+ via PDO                     |
| Frontend | HTML5, CSS3, Vanilla JS                |
| Icons    | Font Awesome 6                         |
| Fonts    | Google Fonts (Playfair Display + Lato) |
| Server   | Apache + mod_rewrite                   |

---

_Built with ❤️ in West Bengal — Dhoti Mahal 2024_
