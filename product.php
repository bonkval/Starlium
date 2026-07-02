<?php
require_once 'db.php';
require_once 'product_meta.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : $product_id;
    $status_message = adidas_add_to_cart($product_id);
}

$product = null;

if ($product_id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ? LIMIT 1");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }
}

$related_products = array();

if ($product) {
    $related_stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE category = ? AND id <> ? ORDER BY name ASC LIMIT 3");

    if ($related_stmt) {
        mysqli_stmt_bind_param($related_stmt, "si", $product['category'], $product_id);
        mysqli_stmt_execute($related_stmt);
        $related_result = mysqli_stmt_get_result($related_stmt);

        while ($related = mysqli_fetch_assoc($related_result)) {
            $related_products[] = $related;
        }

        mysqli_stmt_close($related_stmt);
    }
}

include_once 'header.php';
?>

<?php if (!empty($status_message)): ?>
    <p class="status-message floating-status"><strong><?php echo htmlspecialchars($status_message); ?></strong></p>
<?php endif; ?>

<?php if (!$product): ?>
    <section class="empty-state">
        <h2>Product not found.</h2>
        <p>The selected product could not be found in the database.</p>
        <a class="button-link" href="store.php">Back to Store</a>
    </section>
<?php else: ?>
    <?php
    $sizes = adidas_product_sizes($product);
    $meta = adidas_product_detail_meta($product);
    $category_url = adidas_category_url($product['category']);
    $category_slug = adidas_slug($product['category']);
    ?>

    <div class="breadcrumb-row">
        <a href="store.php#catalog">Store</a>
        <span>/</span>
        <a href="<?php echo htmlspecialchars($category_url); ?>"><?php echo htmlspecialchars($product['category']); ?></a>
    </div>

    <div class="product-theme-<?php echo htmlspecialchars($category_slug); ?>">
        <section class="product-detail-layout">
            <div class="product-showcase product-showcase-<?php echo htmlspecialchars($category_slug); ?>">
                <img class="product-image" src="<?php echo htmlspecialchars(adidas_product_image_path($product)); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>

            <article class="detail-panel">
                <div>
                    <p class="eyebrow"><?php echo htmlspecialchars($product['category']); ?> product details</p>
                    <h1 class="product-detail-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                </div>

                <div class="stock-strip">
                    <strong class="price-lockup"><?php echo adidas_format_price($product['price']); ?></strong>
                    <span class="<?php echo adidas_stock_class($product['stock']); ?>"><?php echo htmlspecialchars(adidas_stock_label($product['stock'])); ?></span>
                </div>

                <div>
                    <p class="size-label">Sizes Available</p>
                    <div class="size-list" aria-label="Display sizes for <?php echo htmlspecialchars($product['name']); ?>">
                        <?php if (empty($sizes)): ?>
                            <span class="muted-pill pill">No sizes available</span>
                        <?php else: ?>
                            <?php foreach ($sizes as $size): ?>
                                <button class="size-chip" type="button"><?php echo htmlspecialchars($size); ?></button>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <dl class="detail-list">
                    <div class="detail-row">
                        <dt>Colorway</dt>
                        <dd><?php echo htmlspecialchars($meta['colorway']); ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Best For</dt>
                        <dd><?php echo htmlspecialchars($meta['best_for']); ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Fit Feel</dt>
                        <dd><?php echo htmlspecialchars($meta['fit']); ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Upper</dt>
                        <dd><?php echo htmlspecialchars($meta['upper']); ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Stock Count</dt>
                        <dd><?php echo (int)$product['stock']; ?> unit<?php echo (int)$product['stock'] === 1 ? '' : 's'; ?></dd>
                    </div>
                </dl>

                <p class="product-note">Size buttons are display-only for this project page. They do not change the cart item or checkout quantity.</p>

                <div class="product-card-actions">
                    <a class="button-link button-secondary" href="<?php echo htmlspecialchars($category_url); ?>">Back to <?php echo htmlspecialchars($product['category']); ?></a>
                    <?php if ((int)$product['stock'] > 0): ?>
                        <form action="product.php?id=<?php echo (int)$product['id']; ?>" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                            <button type="submit" name="add_to_cart">Add to Cart</button>
                        </form>
                    <?php else: ?>
                        <button type="button" disabled>Unavailable</button>
                    <?php endif; ?>
                </div>
            </article>
        </section>
    </div>

    <?php if (!empty($related_products)): ?>
        <section class="content-band">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">More in <?php echo htmlspecialchars($product['category']); ?></p>
                    <h2>Related Products</h2>
                </div>
            </div>

            <div class="product-grid related-grid">
                <?php foreach ($related_products as $related): ?>
                    <article class="product-card product-card-<?php echo htmlspecialchars(adidas_slug($related['category'])); ?>" data-product-card>
                        <a class="product-media" href="<?php echo htmlspecialchars(adidas_product_url($related)); ?>" aria-label="View <?php echo htmlspecialchars($related['name']); ?>">
                            <img class="product-image" src="<?php echo htmlspecialchars(adidas_product_image_path($related)); ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
                        </a>
                        <div class="product-card-body">
                            <div>
                                <p class="eyebrow"><?php echo htmlspecialchars($related['category']); ?></p>
                                <h3><a class="product-title-link" href="<?php echo htmlspecialchars(adidas_product_url($related)); ?>"><?php echo htmlspecialchars($related['name']); ?></a></h3>
                            </div>
                            <div class="product-meta-row">
                                <strong class="price-lockup"><?php echo adidas_format_price($related['price']); ?></strong>
                                <span class="<?php echo adidas_stock_class($related['stock']); ?>"><?php echo htmlspecialchars(adidas_stock_label($related['stock'])); ?></span>
                            </div>
                            <a class="button-link button-secondary" href="<?php echo htmlspecialchars(adidas_product_url($related)); ?>">View Details</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
<?php endif; ?>

<?php include_once 'footer.php'; ?>