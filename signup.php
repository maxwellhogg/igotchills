<?php 
require_once 'includes/functions.php';
include 'includes/header.php';
require 'includes/db-connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate fields
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif (!is_valid_email($email)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check for existing user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email_address = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            $error = "Username or email already taken.";
        } else {
            // Handle profile image upload (optional)
            $profileImage = '';
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (in_array($_FILES['profile_image']['type'], $allowedTypes)) {
                    $uploadDir = 'user-uploads/';
                    $fileName = time() . "_" . basename($_FILES['profile_image']['name']);
                    $targetPath = $uploadDir . $fileName;
                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                        $profileImage = $targetPath;
                    } else {
                        $error = "Error uploading profile image.";
                    }
                } else {
                    $error = "Invalid file type. Only JPEG, PNG and GIF are allowed.";
                }
            }
            // Proceed if no error from file upload
            if (!$error) {
                $passwordHash = hash_password($password);
                // Insert new user with role 'subscriber'
                $stmtInsert = $pdo->prepare("INSERT INTO users (username, email_address, profile_image_link, role, password_hash) VALUES (?, ?, ?, 'subscriber', ?)");
                $stmtInsert->execute([$username, $email, $profileImage, $passwordHash]);
                
                // Optionally, generate a verification token, store it, and send a verification email.
                $success = "Signup successful! Please check your email to verify your account.";
            }
        }
    }
}
?>

<div class="signup-container">
  <h2>Subscriber Signup</h2>
  <?php if ($error): ?>
    <p style="color:red;"><?php echo $error; ?></p>
  <?php endif; ?>
  <?php if ($success): ?>
    <p style="color:green;"><?php echo $success; ?></p>
  <?php endif; ?>
  <form method="post" action="signup.php" enctype="multipart/form-data">
    <label for="username">Username:</label>
    <input type="text" name="username" id="username" required>
    
    <label for="email">Email Address:</label>
    <input type="email" name="email" id="email" required>
    
    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required>
    
    <label for="confirm_password">Confirm Password:</label>
    <input type="password" name="confirm_password" id="confirm_password" required>

    <label for="profile_image">Profile Image (optional):</label>
    <input type="file" name="profile_image" id="profile_image">
    
    <button type="submit">Sign Up</button>
  </form>
  <p>Already have an account? <a href="login.php">Log in here</a>.</p>
</div>

<?php include 'includes/footer.php'; ?>
