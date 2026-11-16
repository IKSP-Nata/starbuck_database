<?php
// Menu navigasi utama (hanya 4 tautan sesuai PRD 4.1)
$sb_current_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$sb_nav_items = [
    ['label' => 'Dashboard', 'icon' => 'bi-grid',    'href' => '/starbuck/pages/dashboard.php',         'match' => '/pages/dashboard.php'],
    ['label' => 'Kasir',     'icon' => 'bi-people',  'href' => '/starbuck/pages/cashiers/list.php',     'match' => '/pages/cashiers/'],
    ['label' => 'Produk',    'icon' => 'bi-cup-hot', 'href' => '/starbuck/pages/products/list.php',     'match' => '/pages/products/'],
    ['label' => 'Transaksi', 'icon' => 'bi-receipt', 'href' => '/starbuck/pages/transactions/list.php', 'match' => '/pages/transactions/'],
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/starbuck/assets/css/style.css">
</head>

<body>
    <a class="skip-link" href="#main-content">Lewati ke konten</a>

    <!-- Sidebar kiri (desktop, >= 992px) -->
    <aside class="sb-sidebar" aria-label="Navigasi utama">
        <a class="sb-brand" href="/starbuck/pages/dashboard.php">Starbuck</a>
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
    </aside>

    <!-- Navbar atas (layar < 992px) -->
    <header class="sb-topbar">
        <div class="sb-topbar-row">
            <a class="sb-brand" href="/starbuck/pages/dashboard.php">Starbuck</a>
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
