# Hotel Management System (HMS)

A complete, professional, production-ready Hotel Management System built with **PHP 8+**, **MySQL**, **Vanilla JavaScript**, **HTML5**, and **CSS3**. Designed for high performance, modern design aesthetics, security, and smooth execution in a standard **XAMPP (Apache + MySQL)** environment.

---

## 🌟 Key Features

### 🏢 Room Inventory Management
- **Add Room**: Register rooms with Room Number, Room Type (Single, Double, Deluxe, Suite), AC / Non-AC specifications, Floor Number, Price per Night, Status (Available, Booked, Occupied, Maintenance), and Photo uploads.
- **View Rooms**: Interactive catalog with real-time search, category filters (AC type, Room category, Status), and status badges.
- **Edit & Delete Rooms**: Update specifications, room rates, or remove obsolete records with automated media file cleanup.

### 👤 Guest & Customer Directory
- **Add Customer**: Register guests with full contact details, Date of Birth, Gender, Address, ID Proof type (Aadhar, PAN, Passport, Driving License), ID Number, and Guest Photo.
- **Customer Directory**: Fast search by Name, Phone Number, Email, or ID Proof Document.
- **Profile Updates & Removal**: Full CRUD functionality.

### 📅 Reservation & Booking Engine
- **New Booking**: Streamlined booking form with guest selection, room choice, check-in and check-out dates, adults/children count, special requests, and advance payment recording.
- **Dynamic Price & Duration Engine**: Real-time stay duration (nights) and total pricing calculation using Vanilla JavaScript.
- **Double Booking Prevention**: Automatic database check ensures rooms cannot be double booked during overlapping date ranges.
- **Status Updates**: Rooms automatically transition to `Booked` status upon reservation confirmation.

### 🛎️ Operations (Check-In & Check-Out)
- **Check-In Desk**: Process guest arrivals, log actual check-in timestamp, verify guest ID documents, and automatically update room status to `Occupied`.
- **Check-Out Counter**: Calculate total stay duration, auto-compute room charges, add extra services (Food & Beverages, Laundry, Mini Bar), apply 12% GST tax and special discounts.
- **Automated Billing**: Generates tax invoices and updates room status back to `Available`.

### 🧾 Professional Tax Invoicing
- **Printable Invoices**: Modern tax invoice template with hotel branding, GSTIN, detailed guest and stay breakdown, itemized extra charges, subtotal, GST, discounts, and payment status.
- **Print Ready**: Includes `@media print` CSS optimization for crisp paper/PDF printing.

### 📊 Modern Admin Dashboard
- **10 Real-Time KPIs**: Total Rooms, Available Rooms, Occupied Rooms, Booked Rooms, Today's Check-ins, Today's Check-outs, Total Customers, Total Bookings, Revenue Today (₹), and Revenue This Month (₹).
- **Quick Operations Grid**: Instant shortcuts to key tasks.
- **Recent Bookings Feed**: Quick status view and actions for recent reservations.

### 🔒 Security & Best Practices
- Prepared Statements (PDO) to protect against SQL Injection.
- Password Hashing using PHP's `password_hash()` and `password_verify()`.
- Session Management & Authorization controls.
- CSRF Token verification on all form submissions.
- Input sanitization & output escaping (`htmlspecialchars`).

---

## 🛠️ Tech Stack

| Layer | Technology |
| :--- | :--- |
| **Backend** | PHP 8+ (PDO Driver) |
| **Database** | MySQL / MariaDB |
| **Frontend** | HTML5, Vanilla CSS3 (Custom Design Tokens), Vanilla JavaScript |
| **Server** | XAMPP (Apache + MySQL) |
| **Database Tool** | phpMyAdmin |

---

## 🚀 Installation & Setup Guide

### 1. Requirements
- Installed XAMPP (or WAMP/LAMP) with PHP 8.0 or higher.
- MySQL Server.

### 2. Step-by-Step Installation

#### Step A: Copy Project Files
Place the project folder into your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\HMS
```

#### Step B: Start XAMPP Services
1. Open **XAMPP Control Panel**.
2. Start **Apache** service.
3. Start **MySQL** service.

#### Step C: Import Database (`hotel.sql`)
1. Open your browser and navigate to **phpMyAdmin**:
   `http://localhost/phpmyadmin/`
2. Click on the **Import** tab.
3. Choose the file located inside the project at:
   `database/hotel.sql`
4. Click **Go** to create database `hotel_management` and pre-fill schema & dummy data.

#### Step D: Run Application
Open your browser and visit:
```
http://localhost/HMS/
```
*(Or http://localhost/Hotel-Management-System/)*

---

## 🔑 Default Administrator Login

| Attribute | Value |
| :--- | :--- |
| **URL** | `http://localhost/HMS/login.php` |
| **Username** | `admin` |
| **Password** | `admin123` |

---

## 📁 Project Folder Structure

```
HMS/
├── 404.php                  # Custom Error 404 Page
├── add_customer.php         # Customer Registration Form
├── add_room.php             # Room Inventory Creation Form
├── bill.php                 # Tax Invoice & Printable Bill Module
├── booking.php              # New Reservation Engine
├── change_password.php      # Admin Password Update Page
├── checkin.php              # Guest Check-In Desk
├── checkout.php             # Guest Check-Out & Final Billing
├── dashboard.php            # Admin Dashboard with 10 KPIs
├── delete_customer.php      # Customer Removal Script
├── delete_room.php          # Room Removal Script
├── edit_customer.php        # Customer Profile Edit Form
├── edit_room.php            # Room Specification Edit Form
├── index.php                # System Landing / Auth Gate
├── login.php                # User Authentication Page
├── logout.php               # Session Termination Handler
├── profile.php              # Admin Profile Management
├── view_bookings.php        # All Reservations & Global Search API
├── view_customers.php       # Guest Directory Catalog
├── view_rooms.php           # Room Inventory Catalog
├── css/                     # Custom Modular Stylesheets
│   ├── bill.css
│   ├── dashboard.css
│   ├── forms.css
│   ├── login.css
│   ├── responsive.css
│   ├── style.css
│   └── tables.css
├── database/                # Database SQL File
│   └── hotel.sql
├── images/                  # Brand Logos & Graphical Assets
│   └── logo.png
├── includes/                # Shared Core PHP Components
│   ├── auth.php
│   ├── db.php
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   ├── navbar.php
│   └── sidebar.php
├── js/                      # Modular Vanilla JavaScript Engine
│   ├── bill.js
│   ├── booking.js
│   ├── main.js
│   └── validation.js
└── uploads/                 # Media File Storage
    ├── customer/
    └── room/
```

---

## 💰 Currency & Regional Customization
All prices, invoice subtotals, GST calculations, and dashboard financial counters are formatted in **INR (₹)** using standard currency formatting helpers (`formatCurrency()`).

---

## 📜 License
Developed for professional hotel management operations. All rights reserved.
