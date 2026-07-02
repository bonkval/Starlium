<?php
require_once 'auth_helpers.php';

function adidas_category_catalog() {
    return array(
        'Running' => array(
            'page' => 'running.php',
            'label' => 'Running',
            'headline' => 'Running shoes for everyday pace and race-day push.',
            'copy' => 'Browse cushioned trainers, responsive daily shoes, and faster models built for long miles, tempo days, and personal best attempts.',
            'best_for' => 'Road runs, training days, and speed sessions',
            'sizes' => array('US 6.5', 'US 7', 'US 8', 'US 9', 'US 10', 'US 11', 'US 12')
        ),
        'Originals' => array(
            'page' => 'originals.php',
            'label' => 'Originals',
            'headline' => 'Archive icons with clean street-ready details.',
            'copy' => 'Explore classic Adidas silhouettes with low-profile builds, heritage colorways, and easy daily styling.',
            'best_for' => 'Lifestyle wear, school fits, and casual weekends',
            'sizes' => array('US 5', 'US 6', 'US 7', 'US 8', 'US 9', 'US 10', 'US 11')
        ),
        'Basketball' => array(
            'page' => 'basketball.php',
            'label' => 'Basketball',
            'headline' => 'Court shoes made for cuts, impact, and control.',
            'copy' => 'See hoop models with locked-in support, grippy traction, and cushioning for fast stops and explosive movement.',
            'best_for' => 'Indoor courts, pickup games, and performance training',
            'sizes' => array('US 7', 'US 8', 'US 9', 'US 10', 'US 11', 'US 12', 'US 13')
        )
    );
}

function adidas_get_category_config($category) {
    $catalog = adidas_category_catalog();

    if (isset($catalog[$category])) {
        return $catalog[$category];
    }

    return array(
        'page' => 'store.php#catalog',
        'label' => $category,
        'headline' => $category . ' collection',
        'copy' => 'Explore current Adidas products available in this category.',
        'best_for' => 'Everyday performance and casual wear',
        'sizes' => array('US 7', 'US 8', 'US 9', 'US 10', 'US 11')
    );
}

function adidas_all_category_links() {
    return adidas_category_catalog();
}

function adidas_category_url($category) {
    $config = adidas_get_category_config($category);
    return $config['page'];
}

function adidas_slug($value) {
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');

    return $slug !== '' ? $slug : 'item';
}

function adidas_product_image_path($product) {
    $name = isset($product['name']) ? $product['name'] : '';
    $image_dir = 'shoes images';
    
    $image_files = array(
        'Ultraboost Light' => 'ultraboost light.avif',
        'Superstar Classic' => 'superstar classic.avif',
        'Stan Smith' => 'stan smith.avif',
        'Dame 8' => 'Dame_8_Mr._Incredible_Shoes_Black_HR1562_01_standard.avif',
        'Don Issue 4' => 'don issue 4.avif',
        'Samba Classic Black' => 'samba classic black.avif',
        'Gazelle Vintage White' => '12998.jpg',
        'Campus 00s Grey' => 'Campus_00s_Shoes_Grey_HQ8707_01_standard.avif',
        'Adizero Adios Pro 3' => 'adizero adios pro 3.avif',
        'Pureboost 23' => 'pureboost 23.avif',
        'Solarboost 5' => 'Solarboost_5_Shoes_Black_HP5664_HM1.avif',
        'AE 1 (Anthony Edwards)' => 'ae1.webp',
        'Harden Volume 8' => 'Harden_Volume_8_Shoes_Blue_IE2697_01_standard.avif',
        'Trae Young 3' => 'Trae_Young_3_Shoes_Turquoise_IG0679_01_standard.avif',
        'Forum Low Classic' => 'Forum_Low_Classic_Shoes_White_ID6858_01_standard.avif'
    );
    
    if (isset($image_files[$name])) {
        return $image_dir . '/' . $image_files[$name];
    }
    
    return 'assets/starlium-logo.png';
}

function adidas_product_sizes($product) {
    $stock = isset($product['stock']) ? (int)$product['stock'] : 0;

    if ($stock <= 0) {
        return array();
    }

    $category = isset($product['category']) ? $product['category'] : '';
    $config = adidas_get_category_config($category);
    $sizes = $config['sizes'];

    if ($stock <= 5) {
        return array_slice($sizes, 0, 3);
    }

    if ($stock <= 15) {
        return array_slice($sizes, 0, 5);
    }

    return $sizes;
}

function adidas_format_price($price) {
    return '$' . number_format((float)$price, 2);
}

function adidas_stock_label($stock) {
    $stock = (int)$stock;
    return $stock > 0 ? 'In Stock (' . $stock . ')' : 'Out of Stock';
}

function adidas_stock_class($stock) {
    return (int)$stock > 0 ? 'pill' : 'pill muted-pill';
}

function adidas_product_url($product) {
    return 'product.php?id=' . (int)$product['id'];
}

function adidas_product_detail_meta($product) {
    $category = isset($product['category']) ? $product['category'] : '';
    $config = adidas_get_category_config($category);

    $category_meta = array(
        'Running' => array(
            'colorway' => 'Performance running colorway',
            'fit' => 'Secure running fit with cushioned step-in comfort',
            'upper' => 'Breathable engineered mesh with supportive overlays'
        ),
        'Originals' => array(
            'colorway' => 'Heritage lifestyle colorway',
            'fit' => 'Classic low-profile fit for everyday wear',
            'upper' => 'Durable textile and synthetic upper with archive-inspired panels'
        ),
        'Basketball' => array(
            'colorway' => 'Court-ready signature colorway',
            'fit' => 'Locked-in hoop fit for stops, cuts, and landings',
            'upper' => 'Structured performance upper with reinforced support zones'
        )
    );

    $details = isset($category_meta[$category]) ? $category_meta[$category] : array(
        'colorway' => 'Adidas seasonal colorway',
        'fit' => 'Comfort-focused fit for everyday movement',
        'upper' => 'Supportive Adidas upper construction'
    );

    $details['best_for'] = $config['best_for'];

    return $details;
}

function adidas_add_to_cart($product_id) {
    global $conn;

    auth_start_session();

    if (!isset($conn)) {
        return 'Unable to add this item right now.';
    }

    $user = auth_refresh_session_user($conn);

    if (!$user) {
        auth_redirect_to_login('cart_login', auth_current_local_url());
    }

    $product_id = (int)$product_id;

    if ($product_id <= 0) {
        return 'Unable to add that item to your cart.';
    }

    $stmt = mysqli_prepare($conn, "SELECT id, stock FROM products WHERE id = ? LIMIT 1");

    if (!$stmt) {
        return 'Unable to verify this item right now.';
    }

    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$product) {
        return 'Product not found.';
    }

    $stock = (int)$product['stock'];

    if ($stock <= 0) {
        return 'This product is currently out of stock.';
    }

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    $current_quantity = isset($_SESSION['cart'][$product_id]) ? (int)$_SESSION['cart'][$product_id] : 0;

    if ($current_quantity >= $stock) {
        return 'Your cart already contains all available stock for this item.';
    }

    $_SESSION['cart'][$product_id] = $current_quantity + 1;

    return 'Item successfully added to your cart!';
}
?>
