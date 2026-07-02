<?php
require_once 'db.php';
require_once 'product_meta.php';

$status_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $status_message = adidas_add_to_cart((int)$_POST['product_id']);
}

$categories = array();
$category_query = "
    SELECT DISTINCT category
    FROM products
    WHERE category IS NOT NULL AND category <> ''
    ORDER BY
        CASE category
            WHEN 'Running' THEN 1
            WHEN 'Originals' THEN 2
            WHEN 'Basketball' THEN 3
            ELSE 4
        END,
        category
";
$category_result = mysqli_query($conn, $category_query);

if ($category_result) {
    while ($category_row = mysqli_fetch_assoc($category_result)) {
        $categories[] = $category_row['category'];
    }
}

if (empty($categories)) {
    $categories = array('Running', 'Originals', 'Basketball');
}

include_once 'header.php';
?>

<?php if (!empty($status_message)): ?>
    <p class="status-message floating-status"><strong><?php echo htmlspecialchars($status_message); ?></strong></p>
<?php endif; ?>

<section class="hero store-hero">
    <div class="hero-copy">
        <p class="eyebrow">Adidas catalog</p>
        <h1>Performance gear with a sharp classroom-built storefront.</h1>
        <p class="hero-text">Shop running silhouettes, originals, and court-ready basketball models through a clean retail system built for the final project.</p>
        <a href="#catalog" class="button-link">Browse Catalog</a>
    </div>
    <div class="hero-specs" aria-label="Store highlights">
        <?php foreach ($categories as $cat): ?>
            <a href="<?php echo htmlspecialchars(adidas_category_url($cat)); ?>"><?php echo htmlspecialchars($cat); ?></a>
        <?php endforeach; ?>
    </div>
</section>

<nav class="category-link-grid" aria-label="Shop by category">
    <?php foreach (adidas_all_category_links() as $category => $config): ?>
        <a class="category-card-link category-card-<?php echo htmlspecialchars(adidas_slug($category)); ?>" href="<?php echo htmlspecialchars($config['page']); ?>">
            <span><?php echo htmlspecialchars($category); ?></span>
            <strong><?php echo htmlspecialchars($config['best_for']); ?></strong>
        </a>
    <?php endforeach; ?>
</nav>

<section class="catalog-layout" id="catalog">
    <?php foreach ($categories as $cat): ?>
        <section class="category-section">
            <div class="section-heading">
                <div>
                    <p class="eyebrow"><?php echo htmlspecialchars($cat); ?></p>
                    <h2><?php echo htmlspecialchars($cat); ?> Collection</h2>
                </div>
                <a class="button-link button-secondary" href="<?php echo htmlspecialchars(adidas_category_url($cat)); ?>">View <?php echo htmlspecialchars($cat); ?></a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Availability</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $safe_cat = mysqli_real_escape_string($conn, $cat);
                    $query = "SELECT * FROM products WHERE category = '$safe_cat' ORDER BY id ASC";
                    $result = mysqli_query($conn, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $product_url = adidas_product_url($row);
                            echo "<tr>";
                            echo "<td><a class='product-title-link' href='" . htmlspecialchars($product_url) . "'><strong>" . htmlspecialchars($row['name']) . "</strong></a></td>";
                            echo "<td>$" . number_format($row['price'], 2) . "</td>";

                            if ($row['stock'] > 0) {
                                echo "<td><span class='pill'>In Stock (" . (int)$row['stock'] . ")</span></td>";
                                echo "<td>";
                                echo "<div class='table-actions'>";
                                echo "<a class='button-link button-secondary' href='" . htmlspecialchars($product_url) . "'>Details</a>";
                                echo "<form action='store.php' method='POST' class='inline-form'>";
                                echo "<input type='hidden' name='product_id' value='" . (int)$row['id'] . "'>";
                                echo "<button type='submit' name='add_to_cart'>Add to Cart</button>";
                                echo "</form>";
                                echo "</div>";
                                echo "</td>";
                            } else {
                                echo "<td><span class='pill muted-pill'>Out of Stock</span></td>";
                                echo "<td><div class='table-actions'><a class='button-link button-secondary' href='" . htmlspecialchars($product_url) . "'>Details</a><button disabled>Unavailable</button></div></td>";
                            }
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No products available in this category.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    <?php endforeach; ?>
</section>

<?php include_once 'footer.php'; ?>
