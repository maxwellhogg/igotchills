<?php
require 'includes/functions.php';

// Test input sanitization
$dirty_input = "<script>alert('xss');</script>";
echo "Sanitized input: " . sanitize($dirty_input) . "\n";

// Test CSRF token generation and validation
$token = generate_csrf_token();
if (validate_csrf_token($token)) {
    echo "CSRF token is valid.\n";
} else {
    echo "CSRF token is invalid.\n";
}

// Test password hashing and verification
$password = "MySecurePassword123!";
$hash = hash_password($password);
if (verify_password($password, $hash)) {
    echo "Password verified successfully.\n";
} else {
    echo "Password verification failed.\n";
}

// Test user session management (login, check, logout)
login_user(1); // Simulate logging in user with ID 1
if (is_logged_in()) {
    echo "User is logged in with ID: " . get_logged_in_user() . "\n";
}
logout_user();
if (!is_logged_in()) {
    echo "User is logged out.\n";
}
