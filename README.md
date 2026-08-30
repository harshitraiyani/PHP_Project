# Vehicle Service Portal

Full-stack vehicle service booking system — **PHP + MySQL + Bootstrap 5**.
Admin panel + Customer panel, dynamic pricing by brand/model/service type,
service status tracking, PDF invoices, and light/dark mode.

---

## 📁 Project Structure

```
vehicle-service-portal/
├── admin/
│   ├── dashboard.php       (stats: customers, vehicles, bookings, income)
│   ├── bookings.php        (manage bookings, update status, generate invoice)
│   ├── customers.php       (all customers list)
│   └── vehicles.php        (all registered vehicles)
├── customer/
│   ├── dashboard.php
│   ├── my_vehicles.php     (add / list vehicles)
│   ├── book_service.php    (dynamic price calculation)
│   ├── history.php         (service status tracker)
│   └── download_invoice.php (PDF invoice via DomPDF)
├── includes/
│   ├── config.php          (DB connection — EDIT THIS)
│   ├── functions.php       (auth helpers)
│   ├── header.php
│   └── footer.php
├── assets/
│   ├── css/style.css       (light/dark theme)
│   └── js/script.js        (theme toggle + price calc)
├── database.sql            (import this into MySQL)
├── composer.json           (DomPDF dependency)
├── index.php, login.php, register.php, logout.php
```

---

## ⚙️ Setup Instructions (Laragon)

### 1. Copy project
Extract the `vehicle-service-portal` folder directly inside Laragon's root
www directory — usually:
```
C:\laragon\www\vehicle-service-portal
```
(Laragon → right-click tray icon → **www directory** if unsure of the path.)

### 2. Start services
Open Laragon → click **Start All** (this starts Apache + MySQL).

### 3. Import database
- In Laragon, click **Database** (bottom menu) → opens HeidiSQL, or use the
  phpMyAdmin shortcut from Laragon's menu — either works.
- Create nothing manually, just import.
- phpMyAdmin: click **Import** → select `database.sql` → Go.
- HeidiSQL: right-click a session → **Load SQL file** → select `database.sql` → run (F9).
- This creates the `vehicle_service_portal` database with tables, sample
  brands/models/pricing, service types, and a default admin account.

### 4. Configure DB connection
Edit `includes/config.php` — Laragon's MySQL defaults are usually:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // Laragon default MySQL has no password
define('DB_NAME', 'vehicle_service_portal');
define('BASE_URL', '/vehicle-service-portal/'); // match your folder name
```

### 5. Install DomPDF (required for PDF invoices)
Laragon comes with **Composer pre-installed** — just open Laragon's
built-in **Terminal** (Laragon → Terminal button, or right-click tray →
Terminal), `cd` into the project folder if needed, then run:
```bash
composer require dompdf/dompdf
```
This creates a `vendor/` folder — required for the invoice PDF download
feature to work. If `composer` isn't recognized, use Laragon's **Terminal**
button specifically (not a plain cmd/PowerShell window) — it auto-loads
Laragon's bundled PHP + Composer into that session's PATH.

### 6. Run the project
Laragon auto-detects folders in `www` as pretty URLs. Visit either:
```
http://vehicle-service-portal.test/
```
or the plain path:
```
http://localhost/vehicle-service-portal/
```
If you use the `.test` domain, also update `BASE_URL` in `includes/config.php`
to `'/'` instead of `'/vehicle-service-portal/'`.

---

## 🔑 Default Login

| Role     | Email                | Password  |
|----------|-----------------------|-----------|
| Admin    | admin@example.com     | admin123  |
| Customer | Register your own      | —        |

---

## 🚀 How It Works

### Customer Flow
1. Register / Login
2. **My Vehicles** → Add your car (select brand + model + reg no)
3. **Book Service** → Select vehicle + service type → price auto-calculates
   (base price of the model × service type multiplier)
4. **Service History** → Track status: Pending → In Progress → Completed
5. Once Admin marks a booking **Completed**, an invoice auto-generates
   and a **Download Invoice (PDF)** button appears

### Admin Flow
1. Login with admin account
2. **Dashboard** → Total Customers, Total Vehicles, Total Bookings, Total Income
3. **Bookings** → Filter by status, update status, auto-generates invoice
   with 18% GST when marked Completed
4. **Customers** → List with vehicle count, booking count, total spent
5. **Vehicles** → All registered vehicles across all customers

### Pricing Logic
Each car **model** has a `base_price` (in `model_pricing` table).
Each **service type** has a `base_multiplier` (e.g. Basic = 1.0x,
Premium = 2.2x). Final price = `base_price × multiplier`.
You can edit these directly in the database to adjust pricing.

### Theme (Light/Dark Mode)
Toggle button in navbar. Preference saved in `localStorage`, persists
across page loads. All colors are CSS variables in `assets/css/style.css`.

---

## 🧩 Suggested Enhancements (not included, but easy to add)
- Email/SMS notifications on status change (PHPMailer)
- Online payment gateway (Razorpay/Stripe test mode)
- Ratings & reviews after service completion
- Mechanic/staff assignment per booking
- Chart.js revenue graphs on admin dashboard
- Service due-date reminders (cron job)
- Discount coupon codes

---

## 🛠️ Tech Stack
- **Frontend:** HTML5, CSS3, Bootstrap 5.3, Vanilla JS, Bootstrap Icons
- **Backend:** PHP 8 (mysqli, prepared statements)
- **Database:** MySQL
- **PDF:** DomPDF (via Composer)
