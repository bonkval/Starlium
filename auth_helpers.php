<?php
function auth_start_session() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function auth_clear_user_session() {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['role']);
}

function auth_refresh_session_user($conn) {
    auth_start_session();

    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $user_id = (int)$_SESSION['user_id'];

    if ($user_id <= 0) {
        auth_clear_user_session();
        return null;
    }

    $stmt = mysqli_prepare($conn, "SELECT id, name, role, status FROM users WHERE id = ? LIMIT 1");

    if (!$stmt) {
        auth_clear_user_session();
        return null;
    }

    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$user || strtolower(trim($user['status'])) !== 'active') {
        auth_clear_user_session();
        return null;
    }

    $user['role'] = strtolower(trim($user['role']));
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['role'] = $user['role'];

    return $user;
}

function auth_current_user_is_admin($conn) {
    $user = auth_refresh_session_user($conn);
    return $user && $user['role'] === 'admin';
}

function auth_require_admin($conn) {
    if (!auth_current_user_is_admin($conn)) {
        http_response_code(403);
        die("Access Denied: Administrative access credentials required.");
    }
}
?>
