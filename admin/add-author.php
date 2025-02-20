<?php
require_once '../includes/functions.php';
include '../includes/header.php';
require '../includes/db-connect.php';

// Only allow admin users.
if (!is_logged_in()) {
    redirect('../login.php');
}
$user_id = get_logged_in_user();
$stmtUser = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$currentUser = $stmtUser->fetch();
if ($currentUser['role'] !== 'admin') {
    echo "<p>Access denied. You do not have permission to add a new author.</p>";
    include '../includes/footer.php';
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif (!is_valid_email($email)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if the username or email already exists.
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email_address = ?");
        $stmtCheck->execute([$username, $email]);
        if ($stmtCheck->rowCount() > 0) {
            $error = "Username or email already taken.";
        } else {
            $passwordHash = hash_password($password);
            $stmtInsert = $pdo->prepare("INSERT INTO users (username, email_address, profile_image_link, role, password_hash) VALUES (?, ?, ?, 'author', ?)");
            $stmtInsert->execute([$username, $email, '', $passwordHash]);
            $success = "New author added successfully.";
        }
    }
}
?>

<main>
  <h1>Add New Author</h1>
  <?php if ($error): ?>
    <p style="color:red;"><?php echo $error; ?></p>
  <?php endif; ?>
  <?php if ($success): ?>
    <p style="color:green;"><?php echo $success; ?></p>
  <?php endif; ?>
  <form method="post" action="add-author.php" enctype="multipart/form-data">
    <label for="username">Username:</label>
    <input type="text" name="username" id="username" required>
    
    <label for="email">Email Address:</label>
    <input type="email" name="email" id="email" required>
    
    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required>
    
    <label for="confirm_password">Confirm Password:</label>
    <input type="password" name="confirm_password" id="confirm_password" required>
    
    <button type="submit">Add Author</button>
  </form>
</main>

<?php include '../includes/footer.php'; ?>
