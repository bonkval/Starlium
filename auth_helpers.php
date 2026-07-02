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

function auth_sanitize_redirect($redirect, $fallback = 'store.php') {
    $redirect = trim((string)$redirect);

    if ($redirect === '' || strpbrk($redirect, "\r\n") !== false) {
        return $fallback;
    }

    if (preg_match('/^[a-z][a-z0-9+\-.]*:/i', $redirect) || strpos($redirect, '//') === 0) {
        return $fallback;
    }

    $parts = parse_url($redirect);

    if ($parts === false || isset($parts['scheme']) || isset($parts['host'])) {
        return $fallback;
    }

    $path = isset($parts['path']) ? $parts['path'] : '';

    if ($path === '' || $path[0] === '/' || strpos($path, '\\') !== false || strpos($path, '..') !== false) {
        return $fallback;
    }

    if (!preg_match('/^[A-Za-z0-9_\-]+\.php$/', $path)) {
        return $fallback;
    }

    return $redirect;
}

function auth_current_local_url($fallback = 'store.php') {
    $page = isset($_SERVER['PHP_SELF']) ? basename($_SERVER['PHP_SELF']) : $fallback;
    $query = isset($_SERVER['QUERY_STRING']) ? trim($_SERVER['QUERY_STRING']) : '';
    $url = $page;

    if ($query !== '' && strpbrk($query, "\r\n") === false) {
        $url .= '?' . $query;
    }

    return auth_sanitize_redirect($url, $fallback);
}

function auth_notice_message($notice) {
    switch ($notice) {
        case 'cart_login':
            return 'Please log in before adding items to your cart.';
        case 'checkout_register':
            return 'Please register first before trying to checkout. If you already have an account, log in instead.';
        case 'checkout_login':
            return 'Please log in before trying to checkout.';
        default:
            return '';
    }
}

function auth_notice_url($page, $notice = '', $redirect = '') {
    $params = array();

    if ($notice !== '') {
        $params['notice'] = $notice;
    }

    if ($redirect !== '') {
        $params['redirect'] = auth_sanitize_redirect($redirect);
    }

    $query = http_build_query($params);

    return $query !== '' ? $page . '?' . $query : $page;
}

function auth_redirect_to_login($notice = 'cart_login', $redirect = '') {
    header("Location: " . auth_notice_url('login.php', $notice, $redirect));
    exit;
}

function auth_redirect_to_register($notice = 'checkout_register', $redirect = '') {
    header("Location: " . auth_notice_url('register.php', $notice, $redirect));
    exit;
}
?>
