<?php
require_once 'includes/functions.php';
include 'includes/header.php';
require 'includes/db-connect.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    if (!is_valid_email($email)) {
       $message = "Please enter a valid email address.";
    } else {
       $token = bin2hex(random_bytes(16));
       $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
       // Update the user's record with reset token and expiration
       $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email_address = ?");
       $stmt->execute([$token, $expires, $email]);
       
       // Build the reset link
       $resetLink = "http://yourdomain.com/reset_password.php?token=" . $token;
       
       // Here you should send the email using mail() or a mailing library (like PHPMailer)
       // For demonstration, we display the link:
       $message = "A password reset link has been sent to your email. (For testing: <a href='{$resetLink}'>Reset Password</a>)";
    }
}
?>
<form method="post" action="forgot_password.php">
  <label for="email">Enter your email address:</label>
  <input type="email" name="email" id="email" required>
  <button type="submit">Reset Password</button>
</form>
<?php echo $message; ?>
<?php include 'includes/footer.php'; ?>
