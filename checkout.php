<?php
require_once 'db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: store.php");
    exit;
}

$error = "";
$success_order = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_name = trim(strip_tags($_POST['shipping_name']));
    $shipping_address = trim(strip_tags($_POST['shipping_address']));
    $card_number = trim($_POST['card_number']);
    $expiry = trim($_POST['expiry']);

    if (!empty($shipping_name) && !empty($shipping_address) && !empty($card_number)) {
        $stock_ok = true;

        foreach ($_SESSION['cart'] as $id => $qty) {
            $id = (int)$id;
            $qty = (int)$qty;
            $check_res = mysqli_query($conn, "SELECT name, stock FROM products WHERE id = $id");
            $prod = mysqli_fetch_assoc($check_res);

            if ($prod['stock'] < $qty) {
                $stock_ok = false;
                $error = "Insufficient stock available for model: " . $prod['name'];
                break;
            }
        }

        if ($stock_ok) {
            foreach ($_SESSION['cart'] as $id => $qty) {
                $id = (int)$id;
                $qty = (int)$qty;
                mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id = $id");
            }

            unset($_SESSION['cart']);
            $success_order = true;
        }
    } else {
        $error = "Please complete all fields for standard delivery processing.";
    }
}

include_once 'header.php';
?>

<section class="admin-hero">
    <div>
        <p class="eyebrow">Secure payment</p>
        <h1>Checkout</h1>
    </div>
</section>

<?php if ($success_order): ?>
    <section class="empty-state success-state">
        <h2>Payment Approved Successfully!</h2>
        <p>Thank you for shopping with us. Your order has been scheduled for local transit dispatch.</p>
        <a class="button-link" href="store.php">Return to Shoe Catalog</a>
    </section>
<?php else: ?>
    <section class="panel checkout-panel">
        <?php if (!empty($error)) echo "<p class='error-message'><strong>Error: " . htmlspecialchars($error) . "</strong></p>"; ?>

        <form action="checkout.php" method="POST" class="form-grid">
            <div class="section-heading full-span">
                <p class="eyebrow">Delivery</p>
                <h2>Shipping Context Details</h2>
            </div>

            <label>
                <span>Recipient Full Name</span>
                <input type="text" name="shipping_name" required>
            </label>

            <label class="full-span">
                <span>Detailed Shipping Destination Address</span>
                <textarea name="shipping_address" required></textarea>
            </label>

            <div class="section-heading full-span">
                <p class="eyebrow">Payment</p>
                <h2>Mock Payment Gate Info</h2>
            </div>

            <label>
                <span>Test Card Number Identification</span>
                <input type="text" name="card_number" placeholder="0000-1111-2222-3333" required>
            </label>

            <label>
                <span>Expiration Reference Data (MM/YY)</span>
                <input type="text" name="expiry" placeholder="12/29" required>
            </label>

            <button type="submit">Submit Order & Process Payment</button>
        </form>
    </section>
<?php endif; ?>

<?php include_once 'footer.php'; ?>
