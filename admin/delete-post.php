<?php
require_once '../includes/functions.php';
require '../includes/db-connect.php';

if (!is_logged_in()) {
    redirect('../login.php');
}

$user_id = get_logged_in_user();
$stmtUser = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$currentUser = $stmtUser->fetch();

// Only allow admin or an author deleting their own post.
if (!in_array($currentUser['role'], ['admin', 'author'])) {
    echo "Access denied.";
    exit;
}

if (!isset($_GET['id']) || !isset($_GET['token']) || !validate_csrf_token($_GET['token'])) {
    echo "Invalid request.";
    exit;
}

$post_id = (int)$_GET['id'];

// If the user is an author, verify that the post belongs to them.
if ($currentUser['role'] === 'author') {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND author = ?");
    $stmt->execute([$post_id, $user_id]);
    if (!$stmt->fetch()) {
        echo "You don't have permission to delete this post.";
        exit;
    }
}

// Delete the post.
$stmtDelete = $pdo->prepare("DELETE FROM posts WHERE id = ?");
$stmtDelete->execute([$post_id]);

redirect('dashboard.php');
?>
