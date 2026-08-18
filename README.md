# 👑 THE ROMA PALACE — Luxury Hotel Management System & Web Portal

> **“A Legacy of Luxury, A Stay to Remember.”**  
> **Academic Level:** B.Tech Computer Science & Engineering (CSE)  
> **Subject / Course:** Database Management Systems (DBMS) Mini Project  
> **Architecture:** PHP 8.4 MVC-Style Architecture, Multi-Driver PDO (MySQL & Zero-Config SQLite), 18 Normalized Relational Tables (Strict 3NF).

---

## 🌟 Executive Overview

**The Roma Palace** is an enterprise-grade luxury hospitality web portal and database-driven Hotel Management System (HMS). Inspired by the timeless visual grandeur of heritage Indian palaces, this full-stack application unites:

1. **A Visually Magnificent Public Luxury Guest Portal:** Cinematic hero videography/imagery, dynamic contrast-adaptive header, royal properties catalog across Jaipur, Goa, Udaipur, and Lucknow, suite showcases with floor plans, interactive fine-dining restaurant menus, royal experience bookings, signature promotional packages, wellness retreat booking, and a 6-step ACID reservation wizard with printable official GST 18% tax invoice receipts.
2. **A Comprehensive Hotel Operations & Admin Control Center (`/admin/`):** 8 Top KPI counters, 5 interactive Chart.js analytics graphs, front-desk reception terminals for instant Check-In and Check-Out with automated room status transitions (`Available` $\leftrightarrow$ `Occupied`), Room & Hotel CRUD, Customer CRM with government ID verification, Master Reservation Ledger, Payment Transactions & Refunds, Staff HR & Payroll across 6 departments, In-Room Service Orders Tracker, Culinary Restaurant & Menu CRUD, Offers Management, Guest Reviews Moderation, and Financial Audit Reports with instant Print & CSV Export.
3. **An Academic Presentation & Viva Mode Center (`/admin/demo-presentation.php`):** An interactive examination tool featuring a live executable SQL query runner with 25+ pre-loaded demonstration queries, visual ER schema viewer for all 18 tables, 1NF/2NF/3NF normalization proofs, and a 20+ Viva Q&A cheat sheet with model answers.

---

## 🔑 Demo Access Credentials

| Role | Access URL | Email Address | Password | One-Click Auto-Fill |
| :--- | :--- | :--- | :--- | :--- |
| **System Admin / Manager** | `/admin/admin-login.php` | `admin@romapalace.com` | `Admin@123` | ✅ Available on Login UI |
| **Privileged Guest / Customer** | `/login.php` | `guest@romapalace.com` | `Guest@123` | ✅ Available on Login UI |
| **Viva Examination Mode** | `/admin/demo-presentation.php` | *Direct Access via Admin* | — | ✅ Public Demo Console |

---

## 🚀 Quick Start Guide (Windows / Linux / macOS)

### Option 1: One-Click Windows Launcher (Recommended)
1. Double-click the included `start-server.bat` file in the root folder.
2. The script will automatically verify PHP 8.4, start the built-in web server at `http://127.0.0.1:8000`, and launch your default browser.

### Option 2: Command Line (PHP Built-in Server)
```bash
# Navigate to project directory
cd "c:\Users\risha\OneDrive\Attachments\Desktop\hotel management 2"

# Start PHP built-in web server
php -S 127.0.0.1:8000
```
Open **`http://127.0.0.1:8000`** in your browser.

### Option 3: XAMPP / WAMP / Apache Deployment
1. Copy or move this project directory to your XAMPP `htdocs/` folder (e.g. `C:\xampp\htdocs\roma-palace`).
2. Start **Apache** and **MySQL** in XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`, create a database named `roma_palace`, and import `database/roma_palace.sql`.
4. Open `http://localhost/roma-palace` in your browser.

> **💡 Zero-Config Fallback Engine:** The system automatically checks if MySQL is running on `127.0.0.1:3306`. If MySQL is unavailable, it seamlessly switches to the SQLite engine (`database/roma_palace.sqlite`) with full relational schema and seed records automatically created on the fly!

---

## 🗄️ Database Architecture & Normalized Schema (3NF)

The database consists of **18 relational tables** designed in strict **Third Normal Form (3NF)**:

```
[users] (1) ──< (1) [customers] (1) ──< (M) [bookings] (1) ──< (M) [booking_services]
[users] (1) ──< (1) [admins]
[hotels] (1) ──< (M) [rooms] (1) ──< (M) [bookings] (1) ──< (1) [payments]
[hotels] (1) ──< (M) [restaurants] (1) ──< (M) [menu_items]
[hotels] (1) ──< (M) [staff]
[hotels] (1) ──< (M) [experiences]
[services] (1) ──< (M) [booking_services] & (M) [service_order_items]
[customers] (1) ──< (M) [service_orders] (1) ──< (M) [service_order_items]
[customers] (1) ──< (M) [reviews] >── (1) [hotels]
```

### Table Directory
1. `users` — Base authentication credentials and roles (`admin`, `customer`).
2. `customers` — Guest demographic profiles and Government ID verifications.
3. `admins` — Executive management profiles, role designations, and departments.
4. `hotels` — Luxury palace properties across 4 prime destinations.
5. `rooms` — Palace room inventory across 5 luxury tiers with live status flags.
6. `room_amenities` — Atomic room amenities breakdown (1NF compliance).
7. `bookings` — Master reservation transactions with check-in/out dates and GST.
8. `booking_services` — Associative bridge table linking optional add-ons to stays.
9. `payments` — Cryptographic payment ledger and transaction audit trail.
10. `services` — Luxury enhancement catalog (Spa, Airport transfers, Dining).
11. `service_orders` — In-room guest on-demand service orders.
12. `service_order_items` — Itemized order breakdown for room service requests.
13. `staff` — Employee records, department assignments, and payroll.
14. `restaurants` — Fine-dining culinary outlets across properties.
15. `menu_items` — Gourmet dishes, dietary classifications, and chef specials.
16. `offers` — Promotional campaigns, discount promo codes, and validity windows.
17. `experiences` — Royal activities (Heritage tours, private cruises, vintage car).
18. `reviews` — Guest feedback, star ratings, and moderation flags.

---

## 🛡️ Core DBMS Principles Implemented

### 1. Double-Booking Prevention & Date Overlap Query
Prevents conflicting reservations for the same room across overlapping date ranges:
```sql
SELECT COUNT(*) AS conflict_count FROM bookings 
WHERE room_id = :room_id 
AND booking_status IN ('Confirmed', 'Checked-In') 
AND NOT (check_out_date <= :check_in OR check_in_date >= :check_out);
```

### 2. ACID Transaction Isolation
Multi-table inserts (Booking + Services + Payments) execute inside an atomic transaction:
```php
$pdo->beginTransaction();
// 1. Verify availability
// 2. Insert booking record
// 3. Insert service add-ons
// 4. Record payment ledger entry
$pdo->commit(); // or $pdo->rollBack() on failure
```

### 3. Automatic Room Status State Machine
- **Booking Confirmed:** Room is flagged for upcoming arrival.
- **Reception Check-In:** Room status automatically updates from `Available` $\rightarrow$ `Occupied`.
- **Reception Check-Out:** Room status automatically updates from `Occupied` $\rightarrow$ `Available`.
- **Cancellation:** Instantly releases reserved room back to inventory.

---

## 📁 Project Directory Structure

```
hotel management 2/
├── admin/                           # Executive Hotel Management Portal
│   ├── admin-login.php              # Admin authentication with 1-click fill
│   ├── dashboard.php                # Master dashboard (8 KPIs + 5 Chart.js charts)
│   ├── rooms.php                    # Room inventory CRUD & status management
│   ├── hotels.php                   # Palace properties CRUD
│   ├── customers.php                # Customer CRM & Govt ID verification
│   ├── bookings.php                 # Master reservation ledger
│   ├── checkin.php                  # Reception Check-In terminal
│   ├── checkout.php                 # Reception Check-Out & Folio terminal
│   ├── payments.php                 # Payment transactions ledger & refunds
│   ├── staff.php                    # Staff directory & payroll CRUD
│   ├── services.php                 # Services catalog & in-room orders
│   ├── restaurants.php              # Restaurants CRUD
│   ├── menu.php                     # Menu items CRUD
│   ├── offers.php                   # Signature packages CRUD
│   ├── reviews.php                  # Guest reviews moderation
│   ├── reports.php                  # Financial & occupancy reports (Print/CSV)
│   ├── demo-presentation.php        # Viva & presentation interactive console
│   ├── settings.php                 # System configuration & 1-click DB reset
│   ├── logout.php                   # Admin session sign-out
│   └── includes/                    # Admin layout components
│       ├── admin-header.php
│       └── admin-footer.php
├── assets/
│   ├── css/
│   │   ├── style.css                # Master luxury frontend design system
│   │   └── admin.css                # Admin portal styling & viva console
│   └── js/
│       ├── main.js                  # Dynamic header scroll & price calculators
│       └── admin.js                 # Live clock & instant table search
├── database/
│   ├── roma_palace.sql              # Master SQL DDL + seed data + 25 queries
│   └── roma_palace.sqlite           # Auto-generated zero-config SQLite DB
├── includes/
│   ├── db.php                       # Resilient PDO connection engine
│   ├── auth.php                     # Auth helpers, password bcrypt & roles
│   ├── header.php                   # Public navbar with contrast switcher
│   └── footer.php                   # Luxury public footer
├── index.php                        # Homepage with cinematic hero & story
├── hotels.php                       # Palaces directory & amenities
├── rooms.php                        # Room catalog with date-overlap filters
├── room-details.php                 # Room specs, gallery & reservation drawer
├── dining.php                       # 4 fine-dining restaurants & live menu
├── experiences.php                  # Royal heritage experiences
├── wellness.php                     # Imperial spa, yoga & hydrothermal
├── offers.php                       # Signature packages & promo codes
├── booking.php                      # 6-step multi-tier reservation wizard
├── confirmation.php                 # Printable official GST 18% tax invoice
├── login.php                        # Guest sign-in with 1-click demo fill
├── register.php                     # Guest registration with Govt ID
├── customer-dashboard.php           # "MY ROMA PALACE" member portal
├── logout.php                       # Customer session logout
├── DBMS_PROJECT_REPORT.md           # 30+ page equivalent BTech project report
├── start-server.bat                 # 1-click Windows server launcher
└── README.md                        # Documentation & setup guide
```

---

## 👨‍🎓 Project Credits & Submission

- **Student Name:** B.Tech CSE Student
- **Course:** Database Management Systems (DBMS) Mini Project
- **Project Title:** The Roma Palace — Luxury Hotel Management System
- **Faculty / Examiner:** Department of Computer Science & Engineering
