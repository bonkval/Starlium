<?php
require_once 'db.php';
require_once 'admin_auth.php';

$msg = "";
$search_query = isset($_GET['q']) ? trim(strip_tags($_GET['q'])) : "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $delete_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $current_admin_id = (int)$_SESSION['user_id'];

    if ($delete_id <= 0) {
        $msg = "Unable to delete user: invalid user record.";
    } elseif ($delete_id === $current_admin_id) {
        $msg = "You cannot delete the admin account you are currently using.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT name, email FROM users WHERE id = ? LIMIT 1");

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $delete_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $delete_user = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($delete_user) {
                $del_stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ? LIMIT 1");

                if ($del_stmt) {
                    mysqli_stmt_bind_param($del_stmt, "i", $delete_id);

                    if (mysqli_stmt_execute($del_stmt)) {
                        $adm_id = (int)$_SESSION['user_id'];
                        $adm_name = mysqli_real_escape_string($conn, $_SESSION['user_name']);
                        $act = mysqli_real_escape_string($conn, "Deleted User Account: " . $delete_user['email']);
                        mysqli_query($conn, "INSERT INTO audit_log (user_id, user_name, action) VALUES ($adm_id, '$adm_name', '$act')");

                        $msg = "User account deleted.";
                    } else {
                        $msg = "Unable to delete user account.";
                    }

                    mysqli_stmt_close($del_stmt);
                } else {
                    $msg = "Unable to prepare delete request.";
                }
            } else {
                $msg = "User account was not found.";
            }
        } else {
            $msg = "Unable to find user account.";
        }
    }
}

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

include_once 'header.php';
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
        <h2>Current User Profiles</h2>
    </div>

    <form action="admin_users.php" method="GET" class="user-search-form">
        <label class="user-search-field">
            <span>Search by Username or ID</span>
            <input type="search" name="q" value="<?php echo htmlspecialchars($search_query, ENT_QUOTES); ?>" placeholder="Enter name or ID">
        </label>
        <div class="user-search-actions">
            <button type="submit">Search</button>
            <?php if ($search_query !== ""): ?>
                <a class="button-link button-secondary" href="admin_users.php">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status Config</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = null;

            if ($search_query !== "") {
                $search_like = "%" . $search_query . "%";
                $search_id = ctype_digit($search_query) ? (int)$search_query : 0;
                $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ? OR name LIKE ? ORDER BY role ASC, id ASC");

                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "is", $search_id, $search_like);
                    mysqli_stmt_execute($stmt);
                    $res = mysqli_stmt_get_result($stmt);
                } else {
                    $res = false;
                }
            } else {
                $res = mysqli_query($conn, "SELECT * FROM users ORDER BY role ASC, id ASC");
            }

            if ($res && mysqli_num_rows($res) > 0) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $user_id = (int)$row['id'];
                    $is_current_user = $user_id === (int)$_SESSION['user_id'];
                    $user_name = htmlspecialchars($row['name'], ENT_QUOTES);
                    $user_email = htmlspecialchars($row['email'], ENT_QUOTES);
                    $user_role = htmlspecialchars($row['role'], ENT_QUOTES);
                    $user_status = htmlspecialchars($row['status'], ENT_QUOTES);

                    echo "<tr>";
                    echo "<td data-label='ID'>" . $user_id . "</td>";
                    echo "<td data-label='Name'>" . $user_name . "</td>";
                    echo "<td data-label='Email'>" . $user_email . "</td>";
                    echo "<td data-label='Role'><span class='pill muted-pill'>" . $user_role . "</span></td>";
                    echo "<td data-label='Status Config'><span class='pill'>" . $user_status . "</span></td>";
                    echo "<td data-label='Actions'>";

                    if ($is_current_user) {
                        echo "<span class='muted-action'>Current admin</span>";
                    } else {
                        $delete_action = "admin_users.php" . ($search_query !== "" ? "?q=" . urlencode($search_query) : "");
                        echo "<form action='" . htmlspecialchars($delete_action, ENT_QUOTES) . "' method='POST' class='delete-user-form'>";
                        echo "<input type='hidden' name='user_id' value='" . $user_id . "'>";
                        echo "<button type='submit' name='delete_user' class='delete-user-button' aria-label='Delete " . $user_name . "' title='Delete user'>X</button>";
                        echo "</form>";
                    }

                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td data-label='Search' colspan='6'>No users matched your search.</td></tr>";
            }

            if ($stmt) {
                mysqli_stmt_close($stmt);
            }
            ?>
        </tbody>
    </table>
</section>

<?php include_once 'footer.php'; ?>
