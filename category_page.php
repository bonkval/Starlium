<?php
require_once 'db.php';
require_once 'product_meta.php';

if (!isset($category_name)) {
    $category_name = 'Running';
}

$category_config = adidas_get_category_config($category_name);
$status_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $status_message = adidas_add_to_cart((int)$_POST['product_id']);
}

$products = array();
$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE category = ? ORDER BY name ASC");

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $category_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    mysqli_stmt_close($stmt);
}

include_once 'header.php';
?>

<?php if (!empty($status_message)): ?>
    <p class="status-message floating-status"><strong><?php echo htmlspecialchars($status_message); ?></strong></p>
<?php endif; ?>

<section class="hero category-hero category-hero-<?php echo htmlspecialchars(adidas_slug($category_name)); ?>">
    <div class="hero-copy">
        <p class="eyebrow"><?php echo htmlspecialchars($category_config['label']); ?> collection</p>
        <h1><?php echo htmlspecialchars($category_config['headline']); ?></h1>
        <p class="hero-text"><?php echo htmlspecialchars($category_config['copy']); ?></p>
        <a href="store.php#catalog" class="button-link">Back to Full Catalog</a>
    </div>
    <div class="hero-specs" aria-label="Category summary">
        <span><?php echo count($products); ?> Models</span>
        <span><?php echo htmlspecialchars($category_config['best_for']); ?></span>
        <span>Display Sizes</span>
    </div>
</section>

<nav class="category-link-grid" aria-label="Shop by category">
    <?php foreach (adidas_all_category_links() as $category => $config): ?>
        <a class="category-card-link category-card-<?php echo htmlspecialchars(adidas_slug($category)); ?>" href="<?php echo htmlspecialchars($config['page']); ?>"<?php echo $category === $category_name ? ' aria-current="page"' : ''; ?>>
            <span><?php echo htmlspecialchars($category); ?></span>
            <strong><?php echo htmlspecialchars($config['best_for']); ?></strong>
        </a>
    <?php endforeach; ?>
</nav>

<section class="content-band">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Available products</p>
            <h2><?php echo htmlspecialchars($category_name); ?> Product Details</h2>
        </div>
    </div>

    <?php if (empty($products)): ?>
        <div class="empty-state">
            <h2>No products available.</h2>
            <p>This category does not have products in the database yet.</p>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <?php
                $sizes = adidas_product_sizes($product);
                $product_url = adidas_product_url($product);
                ?>
                <article class="product-card product-card-<?php echo htmlspecialchars(adidas_slug($product['category'])); ?>" data-product-card>
                    <a class="product-media" href="<?php echo htmlspecialchars($product_url); ?>" aria-label="View <?php echo htmlspecialchars($product['name']); ?>">
                        <img class="product-image" src="<?php echo htmlspecialchars(adidas_product_image_path($product)); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </a>
                    <div class="product-card-body">
                        <div>
                            <p class="eyebrow"><?php echo htmlspecialchars($product['category']); ?></p>
                            <h3><a class="product-title-link" href="<?php echo htmlspecialchars($product_url); ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                        </div>

                        <div class="product-meta-row">
                            <strong class="price-lockup"><?php echo adidas_format_price($product['price']); ?></strong>
                            <span class="<?php echo adidas_stock_class($product['stock']); ?>"><?php echo htmlspecialchars(adidas_stock_label($product['stock'])); ?></span>
                        </div>

                        <div>
                            <p class="size-label">Sizes Available</p>
                            <div class="size-list" aria-label="Available sizes for <?php echo htmlspecialchars($product['name']); ?>">
                                <?php if (empty($sizes)): ?>
                                    <span class="muted-pill pill">No sizes available</span>
                                <?php else: ?>
                                    <?php foreach ($sizes as $size): ?>
                                        <button class="size-chip" type="button"><?php echo htmlspecialchars($size); ?></button>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="product-card-actions">
                            <a class="button-link button-secondary" href="<?php echo htmlspecialchars($product_url); ?>">View Details</a>
                            <?php if ((int)$product['stock'] > 0): ?>
                                <form action="<?php echo htmlspecialchars($category_config['page']); ?>" method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                                    <button type="submit" name="add_to_cart">Add to Cart</button>
                                </form>
                            <?php else: ?>
                                <button type="button" disabled>Unavailable</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php include_once 'footer.php'; ?>
