<?php
require_once 'includes/functions.php';
include 'includes/header.php';
require 'includes/db-connect.php';

$error = '';
$message = '';

if (!isset($_GET['token'])) {
    echo "Invalid request.";
    exit;
}

$token = $_GET['token'];
// Verify token and expiration
$stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    echo "Invalid or expired token.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if (empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $passwordHash = hash_password($password);
        // Update the user's password and clear the reset token
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->execute([$passwordHash, $user['id']]);
        $message = "Your password has been reset. You can now log in.";
    }
}
?>
<?php if ($error): ?>
  <p style="color:red;"><?php echo $error; ?></p>
<?php endif; ?>
<?php if ($message): ?>
  <p style="color:green;"><?php echo $message; ?></p>
<?php else: ?>
<form method="post" action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>">
  <label for="password">New Password:</label>
  <input type="password" name="password" id="password" required>
  
  <label for="confirm_password">Confirm New Password:</label>
  <input type="password" name="confirm_password" id="confirm_password" required>
  
  <button type="submit">Reset Password</button>
</form>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>
