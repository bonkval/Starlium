<?php
require_once 'db.php';
require_once 'admin_auth.php';
include_once 'header.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim(strip_tags($_POST['name']));
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $safe_name = mysqli_real_escape_string($conn, $name);

    $ins = "INSERT INTO products (name, category, price, stock) VALUES ('$safe_name', '$category', $price, $stock)";
    if (mysqli_query($conn, $ins)) {
        $act = mysqli_real_escape_string($conn, "Added Product Model: " . $name . " [Initial Volume: $stock]");
        $admin_name = mysqli_real_escape_string($conn, $_SESSION['user_name']);
        mysqli_query($conn, "INSERT INTO audit_log (user_id, user_name, action) VALUES (" . (int)$_SESSION['user_id'] . ", '$admin_name', '$act')");
        $msg = "Product entry archived correctly.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $pid = (int)$_POST['product_id'];
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];

    $upd = "UPDATE products SET price = $price, stock = $stock WHERE id = $pid";
    if (mysqli_query($conn, $upd)) {
        $act = mysqli_real_escape_string($conn, "Modified Item ID: $pid [Updated Price: $$price, Units Stocked: $stock]");
        $admin_name = mysqli_real_escape_string($conn, $_SESSION['user_name']);
        mysqli_query($conn, "INSERT INTO audit_log (user_id, user_name, action) VALUES (" . (int)$_SESSION['user_id'] . ", '$admin_name', '$act')");
        $msg = "Item entry alterations updated.";
    }
}
?>

<section class="admin-hero">
    <div>
        <p class="eyebrow">Admin console</p>
        <h1>Inventory Stock Management</h1>
    </div>
    <?php if (!empty($msg)): ?>
        <p class="status-message"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>
</section>

<section class="panel">
    <div class="section-heading">
        <p class="eyebrow">Catalog</p>
        <h2>Matrix Insertion Form</h2>
    </div>

    <form action="admin_inventory.php" method="POST" class="form-grid">
        <label>
            <span>Shoe Model Name</span>
            <input type="text" name="name" required>
        </label>

        <label>
            <span>Category Group</span>
            <select name="category">
                <option value="Running">Running</option>
                <option value="Originals">Originals</option>
                <option value="Basketball">Basketball</option>
            </select>
        </label>

        <label>
            <span>Retail Price ($)</span>
            <input type="number" step="0.01" name="price" min="0" required>
        </label>

        <label>
            <span>Initial Stock Count</span>
            <input type="number" name="stock" min="0" required>
        </label>

        <button type="submit" name="add_product">Insert Into Database Store</button>
    </form>
</section>

<section class="content-band">
    <div class="section-heading">
        <p class="eyebrow">Warehouse</p>
        <h2>Active Product Tracking Listings</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nomenclature Name</th>
                <th>Category Mapping</th>
                <th>Unit Valuation Price</th>
                <th>Physical Count Stock</th>
                <th>Operations Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $res = mysqli_query($conn, "SELECT * FROM products");
            while ($row = mysqli_fetch_assoc($res)) {
                $product_id = (int)$row['id'];
                $form_id = "product-update-" . $product_id;
                echo "<tr>";
                echo "<td>" . $product_id . "<form id='" . $form_id . "' action='admin_inventory.php' method='POST'><input type='hidden' name='product_id' value='" . $product_id . "'></form></td>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                echo "<td>";
                echo "<div class='currency-input'><span class='currency-field'>$</span><input form='" . $form_id . "' type='number' step='0.01' name='price' value='" . htmlspecialchars($row['price']) . "' min='0'></div>";
                echo "</td>";
                echo "<td><input form='" . $form_id . "' type='number' name='stock' value='" . (int)$row['stock'] . "' min='0'></td>";
                echo "<td><button form='" . $form_id . "' type='submit' name='update_product'>Commit Updates</button></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</section>

<?php include_once 'footer.php'; ?>
