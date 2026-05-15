# NexGear® Elite Storefront

A premium, high-conversion e-commerce platform for elite gaming hardware. Built with **CodeIgniter 4**, **MySQL**, and a **Tech-Editorial** design system inspired by brutalist aesthetics and precision engineering.

![NexGear Banner](https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?q=80&w=1200&auto=format&fit=crop)

## 💎 Design Philosophy

The NexGear Store follows a **Brutalist Editorial** aesthetic, prioritizing visual impact and high-performance UX:
- **Elite Palette**: Deep Charcoal (`#0D0D0D`) with high-contrast borders and subtle glassmorphism.
- **Typography**: Geometric precision using **Space Grotesk** for high-impact headers and **Inter** for clean, legible body text.
- **Dynamic Interactions**: Leverages **Animate-On-Scroll (AOS)** for smooth component entry and custom CSS marquees for brand movement.
- **Layout**: A disciplined 1px grid system that organizes content with mathematical clarity.

## 🔄 Project Workflows

### 🛍️ Customer Journey
1. **Discovery**: Landing on a high-impact 100vh Hero section that sets the brand tone.
2. **Engagement**: Browsing through interactive marquees and brand story splits that build trust.
3. **Selection**: Exploring the "Curated Store" product grid with clean, informative cards.
4. **Conversion**: Seamless "Add to Bag" interaction leading to a transparent cart and a secure, streamlined checkout process.

### 🛠️ Development Workflow
- **Frontend**: Custom styles located in `public/assets/css/app.css`, utilizing Bootstrap 5 for layout stability.
- **Backend**: CodeIgniter 4 MVC architecture. New features should follow the pattern:
    - Define Model in `app/Models/`
    - Implement Logic in `app/Controllers/`
    - Create View/Component in `app/Views/`
- **Animations**: Use data-aos attributes on HTML elements to trigger entrance animations.

### 💼 Administrative Workflow
- **Inventory Control**: Add or update products via the `/admin` dashboard.
- **Stock Monitoring**: Real-time status badges (In Stock, Low Stock) help maintain supply chain health.
- **Order Management**: Track customer transactions and fulfillment from the dedicated admin interface.

## 🚀 Key Features

### 🛒 Storefront Experience
- **NuPhy-Inspired Hero**: High-impact product showcase with editorial typography.
- **Interactive Marquees**: Dynamic tickers for brand messaging and promotions.
- **Smart Cart**: Persistent session-based cart with real-time updates.
- **Streamlined Checkout**: Integrated delivery data capture and order summary.

### 🛠️ Administrative Suite
- **Elite Dashboard**: Real-time inventory analytics and stock health monitoring.
- **Full CRUD Management**: Comprehensive tools for product media and metadata.
- **Order Tracking**: Detailed records for transaction management.

## 📁 Technical Architecture

```text
nexgear-store/
├── app/
│   ├── Config/              # System & Security configuration
│   ├── Controllers/         # MVC Logic (Storefront, Cart, Admin)
│   ├── Filters/             # Access Control (Admin/User Roles)
│   ├── Models/              # Data persistence (ActiveRecord)
│   └── Views/               # Premium Layouts & AOS-enabled components
├── public/
│   ├── assets/              # Elite CSS, JS, and AOS libraries
│   └── uploads/             # Product Media Storage
├── database/
│   └── nexgear_store.sql   # Schema & Seed Data
└── .env                     # Environment settings
```

## 🛠️ Installation

### Prerequisites
- PHP 8.1+ (intl, mbstring, gd)
- MySQL 5.7+ / MariaDB 10.4+
- Composer

### Setup Steps
1. **Install Dependencies**: `composer install`
2. **Configure Environment**: `cp .env.example .env` (Update DB & baseURL)
3. **Database**: Import `database/nexgear_store.sql`
4. **Launch**: `php spark serve` -> `http://localhost:8080`

### Seed Accounts
- **Admin**: `admin@nexgear.test` / `password`
- **User**: `user@nexgear.test` / `password`

## 🔒 Security
- **CSRF Protection**: Enabled for all state-changing requests.
- **RBAC**: Strict role-based filtering for `/admin` routes.
- **SQLi Prevention**: Query Builder automated parameter binding.

---
*Created by Antigravity for the NexGear Team. Elevating gaming commerce through precision engineering.*
