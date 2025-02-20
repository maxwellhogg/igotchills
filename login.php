<?php
require_once 'includes/functions.php';
include 'includes/header.php';
require 'includes/db-connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Look up the user in the users table by email.
        $stmtUser = $pdo->prepare("SELECT * FROM users WHERE email_address = ?");
        $stmtUser->execute([$email]);
        $user = $stmtUser->fetch();
        
        // Verify the password.
        if ($user && verify_password($password, $user['password_hash'])) {
            login_user($user['id']);
            // If the user is an admin or author, open the dashboard in a new tab.
            if (in_array($user['role'], ['admin', 'author'])) {
              redirect('/igotchills/admin/dashboard.php');
          } else {
              redirect('/igotchills/index.php');
          }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<div class="login-container">
  <h2>Subscriber / User Login</h2>
  <?php if ($error): ?>
    <p style="color:red;"><?php echo $error; ?></p>
  <?php endif; ?>
  <form method="post" action="/igotchills/login.php">
    <label for="email">Email Address:</label>
    <input type="email" name="email" id="email" required>
    
    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required>
    
    <button type="submit">Log In</button>
  </form>
  <p>Don't have an account? <a href="/igotchills/signup.php">Sign up here</a>.</p>
</div>

<?php include 'includes/footer.php'; ?>
