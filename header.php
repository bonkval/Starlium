<?php
require_once 'db.php';
require_once 'auth_helpers.php';

auth_start_session();
$current_user = auth_refresh_session_user($conn);

$page_parts = explode('/', $_SERVER['PHP_SELF']);
$current_page = end($page_parts);
$page_slug = str_replace('.php', '', $current_page);
$catalog_search_pages = array('store.php', 'running.php', 'originals.php', 'basketball.php');
$show_catalog_search = in_array($current_page, $catalog_search_pages, true);
$catalog_search_query = isset($_GET['q']) ? trim(strip_tags($_GET['q'])) : "";
$catalog_search_action = $current_page === 'store.php' ? 'store.php#catalog' : $current_page;

function nav_active($page, $current_page) {
    return $page === $current_page ? ' aria-current="page"' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starlium - Adidas Store</title>
    <link rel="icon" type="image/png" href="assets/starlium-logo.png">
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>">
</head>
<body class="page-<?php echo htmlspecialchars($page_slug); ?>">
    <div class="site-shell">
        <header class="site-header<?php echo $show_catalog_search ? ' has-catalog-search' : ''; ?>" data-site-header>
            <a class="brand-lockup" href="store.php" aria-label="Starlium home">
                <span class="brand-mark" aria-hidden="true">
                    <img src="assets/starlium-logo.png" alt="">
                </span>
                <span class="brand-copy">
                    <strong>Starlium</strong>
                    <small>Adidas Store</small>
                </span>
            </a>

            <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="primary-nav" aria-label="Open navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <?php if ($show_catalog_search): ?>
                <form class="catalog-header-search" action="<?php echo htmlspecialchars($catalog_search_action, ENT_QUOTES); ?>" method="GET" role="search">
                    <label>
                        <span class="screen-reader-text">Search shoe records</span>
                        <input type="search" name="q" value="<?php echo htmlspecialchars($catalog_search_query, ENT_QUOTES); ?>" placeholder="Search shoe name">
                    </label>
                    <button type="submit">Search</button>
                    <?php if ($catalog_search_query !== ""): ?>
                        <a class="catalog-search-clear" href="<?php echo htmlspecialchars($catalog_search_action, ENT_QUOTES); ?>" aria-label="Clear shoe search" title="Clear search">X</a>
                    <?php endif; ?>
                </form>
            <?php endif; ?>

            <nav class="primary-nav" id="primary-nav" data-primary-nav>
                <a href="store.php"<?php echo nav_active('store.php', $current_page); ?>>Store</a>
                <a href="running.php"<?php echo nav_active('running.php', $current_page); ?>>Running</a>
                <a href="originals.php"<?php echo nav_active('originals.php', $current_page); ?>>Originals</a>
                <a href="basketball.php"<?php echo nav_active('basketball.php', $current_page); ?>>Basketball</a>
                <a href="cart.php"<?php echo nav_active('cart.php', $current_page); ?>>Cart</a>
                <a href="about.php"<?php echo nav_active('about.php', $current_page); ?>>About</a>

                <?php if ($current_user): ?>
                    <?php if ($current_user['role'] === 'admin'): ?>
                        <a href="admin_users.php"<?php echo nav_active('admin_users.php', $current_page); ?>>Users</a>
                        <a href="admin_inventory.php"<?php echo nav_active('admin_inventory.php', $current_page); ?>>Inventory</a>
                        <a href="admin_reports.php"<?php echo nav_active('admin_reports.php', $current_page); ?>>Reports</a>
                    <?php endif; ?>
                    <span class="nav-user">Hi, <?php echo htmlspecialchars($current_user['name']); ?></span>
                    <a class="nav-cta" href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php"<?php echo nav_active('login.php', $current_page); ?>>Login</a>
                    <a class="nav-cta" href="register.php"<?php echo nav_active('register.php', $current_page); ?>>Register</a>
                <?php endif; ?>
            </nav>
        </header>

        <main class="page-frame">
