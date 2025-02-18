<?php 
include 'includes/header.php';
require 'includes/db-connect.php';
require 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif (!is_valid_email($email)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username or email already exists in the users table.
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email_address = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            $error = "Username or email already taken.";
        } else {
            // Hash the password using our utility function (uses PASSWORD_DEFAULT, which is bcrypt)
            $passwordHash = hash_password($password);
            // Insert into the users table; set role as 'subscriber'
            $stmtInsert = $pdo->prepare("INSERT INTO users (username, email_address, profile_image_link, role, password_hash) VALUES (?, ?, ?, 'subscriber', ?)");
            $stmtInsert->execute([$username, $email, '', $passwordHash]);
            $success = "Signup successful! You can now log in.";
        }
    }
}
?>

<div class="signup-container" style="margin: 2rem auto; max-width: 500px;">
  <h2>Subscriber Signup</h2>
  <?php if ($error): ?>
    <p style="color:red;"><?php echo $error; ?></p>
  <?php endif; ?>
  <?php if ($success): ?>
    <p style="color:green;"><?php echo $success; ?></p>
  <?php endif; ?>
  <form method="post" action="signup.php">
    <label for="username">Username:</label>
    <input type="text" name="username" id="username" required>
    
    <label for="email">Email Address:</label>
    <input type="email" name="email" id="email" required>
    
    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required>
    
    <label for="confirm_password">Confirm Password:</label>
    <input type="password" name="confirm_password" id="confirm_password" required>
    
    <button type="submit">Sign Up</button>
  </form>
  <p>Already have an account? <a href="login.php">Log in here</a>.</p>
</div>

<?php include 'includes/footer.php'; ?>
