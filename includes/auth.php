<?php
/**
 * THE ROMA PALACE — Authentication & Session Management
 * BTech CSE DBMS Mini Project
 */

require_once __DIR__ . '/db.php';

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function is_admin() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function is_customer() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
}

function current_user() {
    if (!is_logged_in()) return null;
    return [
        'user_id' => $_SESSION['user_id'],
        'email'   => $_SESSION['email'],
        'role'    => $_SESSION['role'],
        'name'    => $_SESSION['name'] ?? 'Valued Guest'
    ];
}

function current_customer() {
    if (!is_customer()) return null;
    $user_id = $_SESSION['user_id'];
    return db_fetch_one("SELECT c.*, u.email FROM customers c INNER JOIN users u ON c.user_id = u.user_id WHERE c.user_id = ?", [$user_id]);
}

function current_admin() {
    if (!is_admin()) return null;
    $user_id = $_SESSION['user_id'];
    return db_fetch_one("SELECT a.*, u.email FROM admins a INNER JOIN users u ON a.user_id = u.user_id WHERE a.user_id = ?", [$user_id]);
}

function require_login($redirect = 'login.php') {
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = 'Please log in to access this page.';
        header("Location: $redirect");
        exit;
    }
}

function require_admin($redirect = '../admin/admin-login.php') {
    if (!is_admin()) {
        $_SESSION['flash_error'] = 'Administrator authentication required.';
        header("Location: $redirect");
        exit;
    }
}

function require_customer($redirect = 'login.php') {
    if (!is_customer()) {
        $_SESSION['flash_error'] = 'Customer account required for this section.';
        header("Location: $redirect");
        exit;
    }
}

/**
 * Universal User Login
 */
function login_user($email, $password, $requiredRole = null) {
    $email = trim(strtolower($email));
    $user = db_fetch_one("SELECT * FROM users WHERE email = ? AND status = 'active'", [$email]);

    if (!$user) {
        return ['success' => false, 'message' => 'No active account found with this email address.'];
    }

    // Demo password bypass or verify hash
    $isDemoPass = ($user['role'] === 'admin' && $password === 'Admin@123') ||
                  ($user['role'] === 'customer' && $password === 'Guest@123') ||
                  ($password === 'password') ||
                  password_verify($password, $user['password_hash']);

    if (!$isDemoPass) {
        return ['success' => false, 'message' => 'Invalid email or password entered.'];
    }

    if ($requiredRole && $user['role'] !== $requiredRole) {
        return ['success' => false, 'message' => "Access denied. Account is registered as {$user['role']}."];
    }

    // Fetch display name based on role
    $displayName = 'Valued Guest';
    if ($user['role'] === 'customer') {
        $cust = db_fetch_one("SELECT full_name FROM customers WHERE user_id = ?", [$user['user_id']]);
        if ($cust) $displayName = $cust['full_name'];
    } elseif ($user['role'] === 'admin') {
        $adm = db_fetch_one("SELECT full_name FROM admins WHERE user_id = ?", [$user['user_id']]);
        if ($adm) $displayName = $adm['full_name'];
    }

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['email']   = $user['email'];
    $_SESSION['role']    = $user['role'];
    $_SESSION['name']    = $displayName;

    // Update last login
    db_execute("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?", [$user['user_id']]);

    return ['success' => true, 'user' => $user];
}

/**
 * Register New Customer
 */
function register_customer($data) {
    global $pdo;
    $email = trim(strtolower($data['email']));
    $password = $data['password'];
    $fullName = trim($data['full_name']);
    $phone = trim($data['phone']);
    $address = trim($data['address'] ?? '');
    $city = trim($data['city'] ?? '');
    $state = trim($data['state'] ?? '');
    $idType = $data['id_type'] ?? 'Aadhaar Card';
    $idNumber = trim($data['id_number'] ?? '');

    // Validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please provide a valid email address.'];
    }
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must contain at least 6 characters.'];
    }
    if (empty($fullName) || empty($phone) || empty($idNumber)) {
        return ['success' => false, 'message' => 'Please fill in all mandatory profile and ID fields.'];
    }

    // Check duplicate email
    $existing = db_fetch_one("SELECT user_id FROM users WHERE email = ?", [$email]);
    if ($existing) {
        return ['success' => false, 'message' => 'An account with this email address already exists.'];
    }

    try {
        $pdo->beginTransaction();
        $hash = password_hash($password, PASSWORD_BCRYPT);
        db_execute("INSERT INTO users (email, password_hash, role, status) VALUES (?, ?, 'customer', 'active')", [
            $email, $hash
        ]);
        $userId = db_insert_id();

        db_execute("INSERT INTO customers (user_id, full_name, phone, address, city, state, id_type, id_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
            $userId, $fullName, $phone, $address, $city, $state, $idType, $idNumber
        ]);

        $pdo->commit();

        // Auto login
        $_SESSION['user_id'] = $userId;
        $_SESSION['email']   = $email;
        $_SESSION['role']    = 'customer';
        $_SESSION['name']    = $fullName;

        return ['success' => true, 'message' => 'Account created successfully. Welcome to The Roma Palace!'];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
    }
}

/**
 * Logout
 */
function logout_user() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
