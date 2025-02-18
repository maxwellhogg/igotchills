<?php 
include 'includes/header.php';
require 'includes/db-connect.php';
require 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Look up the user in the users table by email
        $stmtUser = $pdo->prepare("SELECT * FROM users WHERE email_address = ?");
        $stmtUser->execute([$email]);
        $user = $stmtUser->fetch();
        
        // Verify the password using password_verify()
        if ($user && verify_password($password, $user['password_hash'])) {
            login_user($user['id']);
            redirect('index.php');
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<div class="login-container" style="margin: 2rem auto; max-width: 500px;">
  <h2>Subscriber / User Login</h2>
  <?php if ($error): ?>
    <p style="color:red;"><?php echo $error; ?></p>
  <?php endif; ?>
  <form method="post" action="login.php">
    <label for="email">Email Address:</label>
    <input type="email" name="email" id="email" required>
    
    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required>
    
    <button type="submit">Log In</button>
  </form>
  <p>Don't have an account? <a href="signup.php">Sign up here</a>.</p>
</div>

<?php include 'includes/footer.php'; ?>
