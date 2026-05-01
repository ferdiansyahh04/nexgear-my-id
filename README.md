# Hypernex® Elite Storefront

A premium, high-conversion e-commerce platform for elite gaming hardware. Built with **CodeIgniter 4**, **MySQL**, and a custom **NuPhy-inspired** technical design system.

![Hypernex Banner](https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?q=80&w=1200&auto=format&fit=crop)

## 💎 Design Philosophy

The Hypernex Store follows a **Tech-Editorial** aesthetic, prioritizing visual excellence and conversion-focused UX:
- **Elite Palette**: Deep Charcoal (`#0D0D0D`) and Vibrant Lime (`#D4FF37`) accents.
- **Typography**: Geometric precision using **Outfit** for headers and **Inter** for body text.
- **Micro-interactions**: Smooth Animate-On-Scroll (AOS) transitions and glassmorphism.
- **Performance**: Lightweight Bootstrap 5 base with custom CSS optimizations.

## 🚀 Key Features

### 🛒 Storefront Experience
- **NuPhy Hero**: High-impact product showcase with floating animations.
- **High-Conversion Detail Pages**: Technical product breakdown and split-view layouts.
- **Smart Cart**: Persistent session-based cart with real-time stock verification.
- **Robust Checkout**: Full shipping data capture (Address, City, Postal Code) and persistent order storage.

### 🛠️ Administrative Suite
- **Elite Dashboard**: Dedicated management layout with sidebar navigation.
- **Inventory Analytics**: Real-time stats cards (Total Products, Inventory Value, Stock Health).
- **Stock Management**: Color-coded stock status badges (In Stock, Low Stock, Out of Stock).
- **Order Tracking**: Detailed customer transaction records and fulfillment management.

## 📁 Technical Architecture

```text
hypernex-store/
├── app/
│   ├── Config/              # Core application & security configuration
│   ├── Controllers/         # MVC Controllers (Storefront, Cart, Checkout, Admin)
│   ├── Filters/             # Role-Based Access Control (Admin/User)
│   ├── Models/              # Persistent Data Models (ActiveRecord pattern)
│   └── Views/               # Premium UI Layouts & Components
├── database/
│   └── hypernex_store.sql   # Database Schema & Seed Data
├── public/
│   ├── assets/              # Custom Elite CSS/JS
│   └── uploads/             # Product Media Storage
└── .env                     # Environment Configuration
```

## 🛠️ Installation

### Prerequisites
- PHP 8.1+ (with intl, mbstring, gd extensions)
- MySQL 5.7+ / MariaDB 10.4+
- Composer

### Setup Steps
1. **Clone & Install Dependencies**:
   ```bash
   composer install
   ```
2. **Configure Environment**:
   ```bash
   cp .env.example .env
   # Set your database credentials and app.baseURL
   ```
3. **Database Migration**:
   Import `database/hypernex_store.sql` via phpMyAdmin or MySQL CLI.
4. **Launch Server**:
   ```bash
   php spark serve
   ```
   Open `http://localhost:8080` in your browser.

### Seed Accounts
- **Admin**: `admin@hypernex.test` / `password`
- **User**: `user@hypernex.test` / `password`

## 🔒 Security Measures
- **CSRF Protection**: Enabled globally for all state-changing requests.
- **SQLi Prevention**: Leveraging CodeIgniter's Query Builder for automated parameter binding.
- **RBAC**: Strict role-based filtering for administrative routes.
- **Image Sanitization**: Strict MIME-type and size validation for product media uploads.

---
*Created by Antigravity for the Hypernex Team. Elevating gaming commerce through precision engineering.*
