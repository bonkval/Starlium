<?php
require_once 'db.php';
include_once 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart']) && isset($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $p_id => $qty) {
            $p_id = (int)$p_id;
            $qty = (int)$qty;
            if ($qty <= 0) {
                unset($_SESSION['cart'][$p_id]);
            } else {
                $_SESSION['cart'][$p_id] = $qty;
            }
        }
    }
    if (isset($_POST['clear_cart'])) {
        unset($_SESSION['cart']);
    }
}

$total_balance = 0.00;
?>

<section class="admin-hero">
    <div>
        <p class="eyebrow">Checkout path</p>
        <h1>Your Shopping Cart</h1>
    </div>
</section>

<?php if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])): ?>
    <section class="empty-state">
        <h2>Your cart is empty.</h2>
        <p>Explore the catalog to pick your choice.</p>
        <a class="button-link" href="store.php">Go to Store</a>
    </section>
<?php else: ?>
    <section class="content-band">
        <form action="cart.php" method="POST">
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($_SESSION['cart'] as $id => $quantity) {
                        $id = (int)$id;
                        $quantity = (int)$quantity;
                        $query = "SELECT * FROM products WHERE id = $id LIMIT 1";
                        $res = mysqli_query($conn, $query);
                        if ($res && mysqli_num_rows($res) > 0) {
                            $product = mysqli_fetch_assoc($res);
                            $subtotal = $product['price'] * $quantity;
                            $total_balance += $subtotal;

                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($product['name']) . "</td>";
                            echo "<td>$" . number_format($product['price'], 2) . "</td>";
                            echo "<td><input type='number' name='quantities[$id]' value='$quantity' min='0'></td>";
                            echo "<td>$" . number_format($subtotal, 2) . "</td>";
                            echo "</tr>";
                        }
                    }
                    ?>
                    <tr class="table-total">
                        <td colspan="3" align="right"><strong>Total Balance:</strong></td>
                        <td><strong>$<?php echo number_format($total_balance, 2); ?></strong></td>
                    </tr>
                </tbody>
            </table>

            <div class="action-row">
                <button type="submit" name="update_cart">Update Quantities</button>
                <button type="submit" name="clear_cart" class="button-secondary">Clear Cart</button>
                <a href="checkout.php" class="button-link">Proceed to Checkout &rarr;</a>
            </div>
        </form>
    </section>
<?php endif; ?>

<?php include_once 'footer.php'; ?>
