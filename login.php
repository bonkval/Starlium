<?php
require_once 'db.php';
require_once 'auth_helpers.php';

auth_start_session();

$error = "";
$redirect = isset($_GET['redirect']) ? auth_sanitize_redirect($_GET['redirect'], 'store.php') : 'store.php';
$notice = isset($_GET['notice']) ? $_GET['notice'] : '';
$notice_message = auth_notice_message($notice);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strip_tags($_POST['email']));
    $password = trim($_POST['password']);
    $redirect = isset($_POST['redirect']) ? auth_sanitize_redirect($_POST['redirect'], 'store.php') : 'store.php';

    if (!empty($email) && !empty($password)) {
        $safe_email = mysqli_real_escape_string($conn, $email);
        $safe_password = mysqli_real_escape_string($conn, $password);
        $query = "SELECT * FROM users WHERE email = '$safe_email' AND password = '$safe_password' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $role = strtolower(trim($user['role']));

            if (strtolower(trim($user['status'])) === 'active') {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $role;

                if ($role === 'admin') {
                    $u_id = (int)$user['id'];
                    $u_name = mysqli_real_escape_string($conn, $user['name']);
                    $act = "Admin Logged In";
                    mysqli_query($conn, "INSERT INTO audit_log (user_id, user_name, action) VALUES ($u_id, '$u_name', '$act')");
                    header("Location: admin_inventory.php");
                } else {
                    header("Location: " . $redirect);
                }
                exit;
            } else {
                $error = "Your account is inactive.";
            }
        } else {
            $error = "Invalid Email or Password.";
        }
    } else {
        $error = "Please fill up all fields.";
    }
}

include_once 'header.php';
?>

<section class="auth-layout">
    <div class="auth-intro">
        <p class="eyebrow">Member access</p>
        <h1>Account Login</h1>
        <p>Enter the store dashboard, continue shopping, or manage inventory with an admin account.</p>
    </div>

    <form action="login.php" method="POST" class="auth-card">
        <?php if (!empty($notice_message)) echo "<p class='status-message'><strong>" . htmlspecialchars($notice_message) . "</strong></p>"; ?>
        <?php if (!empty($error)) echo "<p class='error-message'><strong>Error: " . htmlspecialchars($error) . "</strong></p>"; ?>
        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

        <label>
            <span>Email</span>
            <input type="email" name="email" required>
        </label>

        <label>
            <span>Password</span>
            <input type="password" name="password" required>
        </label>

        <button type="submit">Login</button>
        <a class="button-link button-secondary" href="<?php echo htmlspecialchars(auth_notice_url('register.php', $notice === 'checkout_login' ? 'checkout_register' : '', $redirect)); ?>">Register Account</a>
    </form>
</section>

<?php include_once 'footer.php'; ?>
