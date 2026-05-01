<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Admin') ?> | Hypernex Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <a class="navbar-brand d-flex align-items-center gap-1" href="<?= base_url('/') ?>" style="font-family: 'Outfit', sans-serif; text-decoration: none;">
                    <span class="fw-bold text-white fs-4">Hypernex</span>
                    <span class="text-primary fs-6">®</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <a href="<?= site_url('/admin/products') ?>" class="sidebar-link <?= strpos(current_url(), 'admin/products') !== false ? 'active' : '' ?>">
                    <i class="bi bi-box-seam"></i>
                    Inventory
                </a>
                <a href="<?= site_url('/admin/orders') ?>" class="sidebar-link <?= strpos(current_url(), 'admin/orders') !== false ? 'active' : '' ?>">
                    <i class="bi bi-cart"></i>
                    Orders
                </a>
                <a href="#" class="sidebar-link">
                    <i class="bi bi-people"></i>
                    Customers
                </a>
                <a href="#" class="sidebar-link">
                    <i class="bi bi-graph-up"></i>
                    Analytics
                </a>
                <a href="#" class="sidebar-link">
                    <i class="bi bi-gear"></i>
                    Settings
                </a>
            </nav>
            
            <div class="sidebar-footer p-4 border-top border-white border-opacity-5">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-primary rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color: #000; font-weight: 800; font-size: 0.75rem;">
                        A
                    </div>
                    <div>
                        <div class="text-white small fw-bold"><?= esc(session('user_name')) ?></div>
                        <div class="text-muted" style="font-size: 0.7rem;">Administrator</div>
                    </div>
                </div>
                <form action="<?= base_url('/logout') ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="btn btn-soft btn-sm w-100" type="submit">
                        <i class="bi bi-box-arrow-left me-2"></i>Logout
                    </button>
                </form>
            </div>
        </aside>
        
        <main class="admin-main">
            <header class="admin-header d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h1 class="h3 text-white fw-bold mb-1"><?= esc($title ?? 'Dashboard') ?></h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#" class="text-muted text-decoration-none small">Admin</a></li>
                            <li class="breadcrumb-item active text-white small" aria-current="page"><?= esc($title ?? 'Dashboard') ?></li>
                        </ol>
                    </nav>
                </div>
                <div class="admin-header-actions d-flex gap-3">
                    <button class="btn btn-soft btn-sm px-3"><i class="bi bi-bell"></i></button>
                    <a href="<?= base_url('/') ?>" class="btn btn-soft btn-sm px-3">View Site <i class="bi bi-arrow-up-right ms-1"></i></a>
                </div>
            </header>
            
            <?= $this->include('partials/flash') ?>
            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
