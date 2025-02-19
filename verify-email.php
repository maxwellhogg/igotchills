<?php
include 'includes/header.php';
require 'includes/db-connect.php';
require 'includes/functions.php';

if (!isset($_GET['token'])) {
    echo "Invalid request.";
    exit;
}

$token = $_GET['token'];

// Find the user with this token
$stmt = $pdo->prepare("SELECT id FROM users WHERE verification_token = ? LIMIT 1");
$stmt->execute([$token]);
$user = $stmt->fetch();

if ($user) {
    // Mark as verified and clear the token
    $stmt = $pdo->prepare("UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = ?");
    $stmt->execute([$user['id']]);
    echo "Your email has been verified. You can now log in.";
} else {
    echo "Invalid or expired verification token.";
}

include 'includes/footer.php';
?>
