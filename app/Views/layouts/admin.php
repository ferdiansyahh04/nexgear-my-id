<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Admin') ?> | NexGear Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@400;500;700&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
</head>
<body style="background-color: #f2f2f2; color: #000;">
    <div class="admin-layout d-flex" style="min-height: 100vh;">
        <!-- Sidebar -->
        <aside class="admin-sidebar border-end border-dark" style="width: 280px; background: #fff; position: sticky; top: 0; height: 100vh; display: flex; flex-direction: column;">
            <div class="p-4 border-bottom border-dark">
                <a class="navbar-brand m-0 d-block" href="<?= base_url('/') ?>" style="font-family: 'Space Grotesk', sans-serif;">
                    <span class="fw-bold text-dark fs-4 tracking-tight">NEXGEAR</span>
                    <span class="font-serif text-dark opacity-50 ms-1" style="font-size: 0.8rem; font-style: italic;">Vault</span>
                </a>
            </div>
            
            <nav class="sidebar-nav flex-grow-1 p-3">
                <div class="text-uppercase text-muted mb-3 ps-3" style="font-size: 0.65rem; letter-spacing: 0.2em; font-weight: 700;">Inventory Management</div>
                <a href="<?= site_url('/admin/products') ?>" class="admin-nav-link <?= strpos(current_url(), 'admin/products') !== false ? 'active' : '' ?>">
                    <span class="nav-num font-serif italic me-3" style="font-size: 0.7rem; opacity: 0.5;">01 /</span>
                    Products
                </a>
                <a href="<?= site_url('/admin/orders') ?>" class="admin-nav-link <?= strpos(current_url(), 'admin/orders') !== false ? 'active' : '' ?>">
                    <span class="nav-num font-serif italic me-3" style="font-size: 0.7rem; opacity: 0.5;">02 /</span>
                    Orders
                </a>
                <div class="text-uppercase text-muted mt-5 mb-3 ps-3" style="font-size: 0.65rem; letter-spacing: 0.2em; font-weight: 700;">Platform</div>
                <a href="#" class="admin-nav-link">
                    <span class="nav-num font-serif italic me-3" style="font-size: 0.7rem; opacity: 0.5;">03 /</span>
                    Customers
                </a>
                <a href="#" class="admin-nav-link">
                    <span class="nav-num font-serif italic me-3" style="font-size: 0.7rem; opacity: 0.5;">04 /</span>
                    Analytics
                </a>
            </nav>
            
            <div class="p-4 border-top border-dark mt-auto bg-light">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="bg-dark text-white font-serif rounded-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 1.1rem; font-style: italic;">
                        <?= substr(session('email'), 0, 1) ?>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-dark small fw-bold text-truncate" style="font-family: 'Space Grotesk', sans-serif;"><?= esc(session('email')) ?></div>
                        <div class="text-muted font-serif italic" style="font-size: 0.7rem;">Curator</div>
                    </div>
                </div>
                <form action="<?= base_url('/logout') ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="btn btn-dark btn-sm w-100 text-uppercase fw-bold rounded-0 py-2" style="font-size: 0.7rem; letter-spacing: 0.1em;" type="submit">
                        Sign Out <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </form>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-grow-1" style="background-color: #f2f2f2; color: #000;">
            <header class="p-4 border-bottom border-dark d-flex justify-content-between align-items-center bg-white" style="position: sticky; top: 0; z-index: 100;">
                <div>
                    <h1 class="h5 m-0 text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;"><?= esc($title ?? 'Dashboard') ?></h1>
                </div>
                <div class="d-flex gap-3">
                    <a href="<?= base_url('/') ?>" class="btn btn-outline-dark btn-sm px-4 text-uppercase fw-bold rounded-0" style="font-size: 0.7rem; letter-spacing: 0.1em; border-width: 1px;">
                        Public View <i class="bi bi-arrow-up-right ms-1"></i>
                    </a>
                </div>
            </header>
            
            <div class="p-4 p-lg-5">
                <?= $this->include('partials/flash') ?>
                <?= $this->renderSection('content') ?>
            </div>
        </main>
    </div>

    <style>
        .admin-nav-link {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            color: #000 !important;
            text-decoration: none;
            font-family: 'Space Grotesk', sans-serif;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            font-weight: 700;
            transition: all 0.2s ease;
            margin-bottom: 2px;
            border: 1px solid transparent;
        }
        .admin-nav-link:hover {
            background: #e8e8e8;
            color: #000 !important;
        }
        .admin-nav-link.active {
            background: #000 !important;
            color: #fff !important;
        }
        .admin-nav-link.active .nav-num {
            color: #fff !important;
            opacity: 0.8 !important;
        }
        
        /* Admin Overrides */
        .admin-table-wrap {
            background: #fff !important;
            border: 1px solid #000 !important;
            border-radius: 0 !important;
        }
        .admin-table th {
            color: #000 !important;
            border-bottom: 1px solid #000 !important;
            background: #f8f8f8 !important;
            text-transform: uppercase;
            font-size: 0.65rem;
            letter-spacing: 0.1em;
        }
        .admin-table td {
            border-bottom: 1px solid #f0f0f0 !important;
            color: #000 !important;
            padding: 1.5rem 2rem;
        }
        .stat-card {
            background: #fff !important;
            border: 1px solid #000 !important;
            padding: 2rem;
            border-radius: 0 !important;
        }
        .stat-card-label {
            color: #666 !important;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 700;
        }
        .stat-card-value {
            color: #000 !important;
            font-size: 2.5rem;
            font-weight: 700;
        }
        .admin-input {
            background: #fff !important;
            border: 1px solid #000 !important;
            color: #000 !important;
            padding: 12px 16px;
            border-radius: 0 !important;
        }
        .admin-input:focus {
            outline: 2px solid #000;
            box-shadow: none;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

