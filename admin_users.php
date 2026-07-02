<?php
require_once 'db.php';
require_once 'admin_auth.php';
include_once 'header.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $name = trim(strip_tags($_POST['name']));
    $email = trim(strip_tags($_POST['email']));
    $password = $_POST['password'];
    $address = trim(strip_tags($_POST['address']));
    $phone = trim(strip_tags($_POST['phone']));

    if (!empty($name) && !empty($email) && !empty($password)) {
        $safe_name = mysqli_real_escape_string($conn, $name);
        $safe_email = mysqli_real_escape_string($conn, $email);
        $safe_password = mysqli_real_escape_string($conn, $password);
        $safe_address = mysqli_real_escape_string($conn, $address);
        $safe_phone = mysqli_real_escape_string($conn, $phone);

        $ins = "INSERT INTO users (name, email, password, address, phone, role, status)
                VALUES ('$safe_name', '$safe_email', '$safe_password', '$safe_address', '$safe_phone', 'admin', 'active')";
        if (mysqli_query($conn, $ins)) {
            $adm_id = (int)$_SESSION['user_id'];
            $adm_name = mysqli_real_escape_string($conn, $_SESSION['user_name']);
            $act = mysqli_real_escape_string($conn, "Created Admin Account: " . $email);
            mysqli_query($conn, "INSERT INTO audit_log (user_id, user_name, action) VALUES ($adm_id, '$adm_name', '$act')");

            $msg = "Admin User successfully deployed.";
        }
    }
}
?>

<section class="admin-hero">
    <div>
        <p class="eyebrow">Access control</p>
        <h1>Admin User Management</h1>
    </div>
    <?php if (!empty($msg)): ?>
        <p class="status-message"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>
</section>

<section class="panel">
    <div class="section-heading">
        <p class="eyebrow">Authorize</p>
        <h2>New Admin Operator</h2>
    </div>

    <form action="admin_users.php" method="POST" class="form-grid">
        <label>
            <span>Admin User Full Name</span>
            <input type="text" name="name" required>
        </label>

        <label>
            <span>Corporate E-mail Target</span>
            <input type="email" name="email" required>
        </label>

        <label>
            <span>Access Control Password</span>
            <input type="password" name="password" required>
        </label>

        <label>
            <span>Office / Work Address Reference</span>
            <input type="text" name="address" value="Corporate HQ">
        </label>

        <label>
            <span>Direct Contact Number Extension</span>
            <input type="text" name="phone" value="N/A">
        </label>

        <button type="submit" name="add_admin">Register Administrative Node</button>
    </form>
</section>

<section class="content-band">
    <div class="section-heading">
        <p class="eyebrow">Directory</p>
        <h2>Current Administrative Profiles</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status Config</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $res = mysqli_query($conn, "SELECT * FROM users WHERE role = 'admin'");
            while ($row = mysqli_fetch_assoc($res)) {
                echo "<tr>";
                echo "<td>" . (int)$row['id'] . "</td>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td><span class='pill'>" . htmlspecialchars($row['status']) . "</span></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</section>

<?php include_once 'footer.php'; ?>
