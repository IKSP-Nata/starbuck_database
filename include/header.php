<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Menu navigasi utama (4 tautan operasional POS)
$sb_current_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$sb_base_url = (strpos($sb_current_path, '/starbuck/') === 0) ? '/starbuck' : '';
$sb_nav_items = [
    ['label' => 'Dashboard', 'icon' => 'bi-grid',    'href' => $sb_base_url . '/pages/dashboard.php',         'match' => '/pages/dashboard.php'],
    ['label' => 'Kasir',     'icon' => 'bi-people',  'href' => $sb_base_url . '/pages/cashiers/list.php',     'match' => '/pages/cashiers/'],
    ['label' => 'Produk',    'icon' => 'bi-cup-hot', 'href' => $sb_base_url . '/pages/products/list.php',     'match' => '/pages/products/'],
    ['label' => 'Transaksi', 'icon' => 'bi-receipt', 'href' => $sb_base_url . '/pages/transactions/list.php', 'match' => '/pages/transactions/'],
];
?>
<!DOCTYPE html>
<html lang="id" class="color-scheme-light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starbuck</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $sb_base_url; ?>/assets/css/style.css">
</head>

<body>
    <a class="skip-link" href="#main-content">Lewati ke konten</a>

    <!-- Sidebar kiri (desktop, >= 992px) -->
    <aside class="sb-sidebar sidebar" aria-label="Navigasi utama">
        <a class="sb-brand" href="<?php echo $sb_base_url; ?>/pages/dashboard.php" title="Starbuck Dashboard">
            <img src="<?php echo $sb_base_url; ?>/assets/img/logo-starbuck.png" alt="Starbuck" class="sidebar-brand-logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
            <span class="sidebar-brand-fallback" style="display:none;">Starbuck</span>
        </a>
        <ul class="sb-nav sidebar-nav">
            <div class="nav-curve-indicator" aria-hidden="true"></div>
            <?php foreach ($sb_nav_items as $sb_index => $sb_item): ?>
                <?php $sb_is_active = strpos($sb_current_path, $sb_item['match']) !== false; ?>
                <li>
                    <a class="sb-link nav-link<?php echo $sb_is_active ? ' is-active active' : ''; ?>"
                        href="<?php echo $sb_item['href']; ?>"
                        data-nav-index="<?php echo $sb_index; ?>"
                        <?php echo $sb_is_active ? 'aria-current="page"' : ''; ?>>
                        <i class="bi <?php echo $sb_item['icon']; ?>" aria-hidden="true"></i>
                        <span><?php echo $sb_item['label']; ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <!-- Navbar atas (layar < 992px) -->
    <header class="sb-topbar">
        <div class="sb-topbar-row">
            <a class="sb-brand" href="<?php echo $sb_base_url; ?>/pages/dashboard.php" title="Starbuck Dashboard">
                <img src="<?php echo $sb_base_url; ?>/assets/img/logo-starbuck.png" alt="Starbuck" class="sidebar-brand-logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                <span class="sidebar-brand-fallback" style="display:none;">Starbuck</span>
            </a>
            <button class="sb-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sbMobileNav"
                aria-controls="sbMobileNav" aria-expanded="false" aria-label="Buka menu navigasi">
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>
        </div>
        <nav class="collapse" id="sbMobileNav" aria-label="Navigasi utama">
            <ul class="sb-nav">
                <?php foreach ($sb_nav_items as $sb_item): ?>
                    <?php $sb_is_active = strpos($sb_current_path, $sb_item['match']) !== false; ?>
                    <li>
                        <a class="sb-link<?php echo $sb_is_active ? ' is-active' : ''; ?>"
                            href="<?php echo $sb_item['href']; ?>"
                            <?php echo $sb_is_active ? 'aria-current="page"' : ''; ?>>
                            <i class="bi <?php echo $sb_item['icon']; ?>" aria-hidden="true"></i>
                            <span><?php echo $sb_item['label']; ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </header>

    <main class="sb-content" id="main-content">
