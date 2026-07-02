<?php
require_once 'db.php';
require_once 'auth_helpers.php';

auth_start_session();

$error = "";
$success = "";
$redirect = isset($_GET['redirect']) ? auth_sanitize_redirect($_GET['redirect'], '') : '';
$notice = isset($_GET['notice']) ? $_GET['notice'] : '';
$notice_message = auth_notice_message($notice);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(strip_tags($_POST['name']));
    $email = trim(strip_tags($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = trim(strip_tags($_POST['address']));
    $phone = trim(strip_tags($_POST['phone']));
    $redirect = isset($_POST['redirect']) ? auth_sanitize_redirect($_POST['redirect'], '') : '';

    if (empty($name) || empty($email) || empty($password) || empty($address) || empty($phone)) {
        $error = "All fields are required.";
    } elseif (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email)) {
        $error = "Invalid E-mail format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $safe_email = mysqli_real_escape_string($conn, $email);
        $check_query = "SELECT id FROM users WHERE email = '$safe_email' LIMIT 1";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $error = "Email address is already registered.";
        } else {
            $safe_name = mysqli_real_escape_string($conn, $name);
            $safe_password = mysqli_real_escape_string($conn, $password);
            $safe_address = mysqli_real_escape_string($conn, $address);
            $safe_phone = mysqli_real_escape_string($conn, $phone);

            $query = "INSERT INTO users (name, email, password, address, phone, role, status)
                      VALUES ('$safe_name', '$safe_email', '$safe_password', '$safe_address', '$safe_phone', 'buyer', 'active')";

            if (mysqli_query($conn, $query)) {
                $new_user_id = mysqli_insert_id($conn);
                $dir_path = "register_email";

                if (!file_exists($dir_path)) {
                    $dir_path = ".";
                }

                $safe_email_file = str_replace(array('@', '.'), '_', $email);
                $log_file = $dir_path . "/email_" . $safe_email_file . ".txt";

                $timestamp = date("Y-m-d H:i:s");
                $email_content = "---------------------------------------\n";
                $email_content .= "Timestamp: " . $timestamp . "\n";
                $email_content .= "To: " . $email . "\n";
                $email_content .= "Subject: Welcome to Starlium Adidas Store!\n";
                $email_content .= "Hi " . $name . ",\nThank you for registering at Starlium Adidas Store! Your account is now active.\n";
                $email_content .= "---------------------------------------\n";

                $file_handle = fopen($log_file, "w");
                if ($file_handle) {
                    fwrite($file_handle, $email_content);
                    fclose($file_handle);
                }

                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$new_user_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['role'] = 'buyer';

                if ($redirect !== '') {
                    header("Location: " . $redirect);
                    exit;
                }

                $success = "Registration successful! A simulated confirmation email has been logged to " . $log_file;
            } else {
                $error = "Database Error: Could not complete registration.";
            }
        }
    }
}

include_once 'header.php';
?>

<section class="auth-layout register-layout">
    <div class="auth-intro">
        <p class="eyebrow">New member</p>
        <h1>Buyer Register</h1>
        <p>Create a buyer account and receive a simulated confirmation email record in the local project folder.</p>
    </div>

    <form action="register.php" method="POST" class="auth-card register-card">
        <?php
        if (!empty($notice_message)) echo "<p class='status-message'><strong>" . htmlspecialchars($notice_message) . "</strong></p>";
        if (!empty($error)) echo "<p class='error-message'><strong>Error: " . htmlspecialchars($error) . "</strong></p>";
        if (!empty($success)) echo "<p class='success-message'><strong>Success: " . htmlspecialchars($success) . "</strong></p>";
        ?>
        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

        <label>
            <span>Complete Name</span>
            <input type="text" name="name" required>
        </label>

        <label>
            <span>E-mail Address</span>
            <input type="email" name="email" required>
        </label>

        <label>
            <span>Password</span>
            <input type="password" name="password" required>
        </label>

        <label>
            <span>Confirm Password</span>
            <input type="password" name="confirm_password" required>
        </label>

        <label>
            <span>Complete Address</span>
            <textarea name="address" required></textarea>
        </label>

        <label>
            <span>Contact Numbers</span>
            <input type="text" name="phone" required>
        </label>

        <button type="submit">Register Account</button>
        <a class="button-link button-secondary" href="<?php echo htmlspecialchars(auth_notice_url('login.php', $notice === 'checkout_register' ? 'checkout_login' : '', $redirect !== '' ? $redirect : 'store.php')); ?>">Login Instead</a>
    </form>
</section>

<?php include_once 'footer.php'; ?>
