<?php
require_once 'db.php';
include_once 'header.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(strip_tags($_POST['name']));
    $email = trim(strip_tags($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = trim(strip_tags($_POST['address']));
    $phone = trim(strip_tags($_POST['phone']));

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

                $success = "Registration successful! A simulated confirmation email has been logged to " . $log_file;
            } else {
                $error = "Database Error: Could not complete registration.";
            }
        }
    }
}
?>

<section class="auth-layout register-layout">
    <div class="auth-intro">
        <p class="eyebrow">New member</p>
        <h1>Buyer Registration</h1>
        <p>Create a buyer account and receive a simulated confirmation email record in the local project folder.</p>
    </div>

    <form action="register.php" method="POST" class="auth-card register-card">
        <?php
        if (!empty($error)) echo "<p class='error-message'><strong>Error: " . htmlspecialchars($error) . "</strong></p>";
        if (!empty($success)) echo "<p class='success-message'><strong>Success: " . htmlspecialchars($success) . "</strong></p>";
        ?>

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
    </form>
</section>

<?php include_once 'footer.php'; ?>
