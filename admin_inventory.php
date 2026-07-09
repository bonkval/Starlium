<?php
require_once 'db.php';
require_once 'admin_auth.php';
include_once 'header.php';

$msg = "";
$search_query = isset($_GET['q']) ? trim(strip_tags($_GET['q'])) : "";
$inventory_action = "admin_inventory.php" . ($search_query !== "" ? "?q=" . urlencode($search_query) : "");

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $pid = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if ($pid <= 0) {
        $msg = "Unable to delete product: invalid inventory record.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT name FROM products WHERE id = ? LIMIT 1");

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $pid);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $delete_product = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($delete_product) {
                $del_stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ? LIMIT 1");

                if ($del_stmt) {
                    mysqli_stmt_bind_param($del_stmt, "i", $pid);

                    if (mysqli_stmt_execute($del_stmt)) {
                        $act = mysqli_real_escape_string($conn, "Deleted Product Model: " . $delete_product['name']);
                        $admin_name = mysqli_real_escape_string($conn, $_SESSION['user_name']);
                        mysqli_query($conn, "INSERT INTO audit_log (user_id, user_name, action) VALUES (" . (int)$_SESSION['user_id'] . ", '$admin_name', '$act')");
                        $msg = "Product entry deleted.";
                    } else {
                        $msg = "Unable to delete product entry.";
                    }

                    mysqli_stmt_close($del_stmt);
                } else {
                    $msg = "Unable to prepare delete request.";
                }
            } else {
                $msg = "Product entry was not found.";
            }
        } else {
            $msg = "Unable to find product entry.";
        }
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

    <form action="admin_inventory.php" method="GET" class="user-search-form">
        <label class="user-search-field">
            <span>Search by Shoe Name or ID</span>
            <input type="search" name="q" value="<?php echo htmlspecialchars($search_query, ENT_QUOTES); ?>" placeholder="Enter shoe name or ID">
        </label>
        <div class="user-search-actions">
            <button type="submit">Search</button>
            <?php if ($search_query !== ""): ?>
                <a class="button-link button-secondary" href="admin_inventory.php">Clear</a>
            <?php endif; ?>
        </div>
    </form>

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
            $stmt = null;

            if ($search_query !== "") {
                $search_like = "%" . $search_query . "%";
                $search_id = ctype_digit($search_query) ? (int)$search_query : 0;
                $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ? OR name LIKE ? ORDER BY id ASC");

                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "is", $search_id, $search_like);
                    mysqli_stmt_execute($stmt);
                    $res = mysqli_stmt_get_result($stmt);
                } else {
                    $res = false;
                }
            } else {
                $res = mysqli_query($conn, "SELECT * FROM products ORDER BY id ASC");
            }

            if ($res && mysqli_num_rows($res) > 0) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $product_id = (int)$row['id'];
                    $product_name = htmlspecialchars($row['name'], ENT_QUOTES);
                    $form_id = "product-update-" . $product_id;
                    echo "<tr>";
                    echo "<td>" . $product_id . "<form id='" . $form_id . "' action='" . htmlspecialchars($inventory_action, ENT_QUOTES) . "' method='POST'><input type='hidden' name='product_id' value='" . $product_id . "'></form></td>";
                    echo "<td>" . $product_name . "</td>";
                    echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                    echo "<td>";
                    echo "<div class='currency-input'><span class='currency-field'>$</span><input form='" . $form_id . "' type='number' step='0.01' name='price' value='" . htmlspecialchars($row['price']) . "' min='0'></div>";
                    echo "</td>";
                    echo "<td><input form='" . $form_id . "' type='number' name='stock' value='" . (int)$row['stock'] . "' min='0'></td>";
                    echo "<td>";
                    echo "<div class='table-actions inventory-actions'>";
                    echo "<button form='" . $form_id . "' type='submit' name='update_product'>Commit Updates</button>";
                    echo "<form action='" . htmlspecialchars($inventory_action, ENT_QUOTES) . "' method='POST' class='delete-user-form'>";
                    echo "<input type='hidden' name='product_id' value='" . $product_id . "'>";
                    echo "<button type='submit' name='delete_product' class='delete-user-button' aria-label='Delete " . $product_name . "' title='Delete product'>X</button>";
                    echo "</form>";
                    echo "</div>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td data-label='Search' colspan='6'>No shoes matched your search.</td></tr>";
            }

            if ($stmt) {
                mysqli_stmt_close($stmt);
            }
            ?>
        </tbody>
    </table>
</section>

<?php include_once 'footer.php'; ?>
