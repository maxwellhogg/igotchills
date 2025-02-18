<?php
// Start the session for user session management and CSRF tokens
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect to a specified URL and exit.
 *
 * @param string $url The URL to redirect to.
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Sanitize input data.
 *
 * @param string $data Input data.
 * @return string Sanitized data.
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate an email address.
 *
 * @param string $email
 * @return bool True if valid, false otherwise.
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash a password using a secure algorithm.
 *
 * @param string $password
 * @return string The hashed password.
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify a password against a given hash.
 *
 * @param string $password The plain-text password.
 * @param string $hash The hash to verify against.
 * @return bool True if the password matches the hash.
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate a CSRF token and store it in the session.
 *
 * @return string The generated CSRF token.
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a submitted CSRF token.
 *
 * @param string $token The token to validate.
 * @return bool True if valid, false otherwise.
 */
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Log in a user by storing their user ID in the session.
 *
 * @param int $user_id
 */
function login_user($user_id) {
    // Prevent session fixation attacks by regenerating session ID
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user_id;
}

/**
 * Check if a user is logged in.
 *
 * @return bool True if logged in, false otherwise.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Log out the current user.
 */
function logout_user() {
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session cookie if it exists
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $params["path"], 
            $params["domain"],
            $params["secure"], 
            $params["httponly"]
        );
    }
    
    // Destroy the session itself
    session_destroy();
}

/**
 * Get the currently logged-in user's ID.
 *
 * @return int|null The user ID or null if not logged in.
 */
function get_logged_in_user() {
    return $_SESSION['user_id'] ?? null;
}
