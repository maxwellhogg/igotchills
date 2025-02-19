<?php
require_once '../includes/functions.php';
include '../includes/header.php';
require '../includes/db-connect.php';

// Only allow admin or author to create new posts.
if (!is_logged_in()) {
    redirect('login.php');
}
$user_id = get_logged_in_user();
$stmtUser = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch();
if (!in_array($user['role'], ['admin', 'author'])) {
    echo "<p>Access denied. You do not have permission to create a post.</p>";
    include 'includes/footer.php';
    exit;
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $post_content = $_POST['post_content'] ?? ''; // assuming content can have HTML
    $category_id = (int) ($_POST['category'] ?? 0);

    // File upload handling for thumbnail and main image
    $thumbnail_link = '';
    $main_image_link = '';
    
    // Handle thumbnail upload (if any)
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['thumbnail']['type'], $allowedTypes)) {
            $uploadDir = 'blog-uploads/';
            $fileName = time() . "_" . basename($_FILES['thumbnail']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetPath)) {
                $thumbnail_link = $targetPath;
            } else {
                $error .= "Thumbnail upload error. ";
            }
        } else {
            $error .= "Invalid thumbnail file type. ";
        }
    }
    
    // Handle main image upload (if any)
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['main_image']['type'], $allowedTypes)) {
            $uploadDir = 'blog-uploads/';
            $fileName = time() . "_" . basename($_FILES['main_image']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['main_image']['tmp_name'], $targetPath)) {
                $main_image_link = $targetPath;
            } else {
                $error .= "Main image upload error. ";
            }
        } else {
            $error .= "Invalid main image file type. ";
        }
    }

    if (empty($title) || empty($post_content) || $category_id <= 0) {
        $error .= "Please fill in all required fields.";
    }

    if (!$error) {
        // Insert new post; assuming author field stores username (or you can store user id)
        // For demonstration, we'll fetch the username.
        $stmtUser = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmtUser->execute([$user_id]);
        $userData = $stmtUser->fetch();
        $author = $userData ? $userData['username'] : 'Anonymous';

        $stmtInsert = $pdo->prepare("INSERT INTO posts (title, author, post_category, post_content, thumbnail_link, main_image_link, post_date, like_count) VALUES (?, ?, ?, ?, ?, ?, NOW(), 0)");
        $stmtInsert->execute([$title, $author, $category_id, $post_content, $thumbnail_link, $main_image_link]);
        $success = "New post created successfully.";
    }
}

// Fetch categories for the selection dropdown.
$stmtCategories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmtCategories->fetchAll();
?>

<main>
  <h1>Create New Post</h1>
  <?php if ($error): ?>
    <p style="color:red;"><?php echo $error; ?></p>
  <?php endif; ?>
  <?php if ($success): ?>
    <p style="color:green;"><?php echo $success; ?></p>
  <?php endif; ?>
  <form method="post" action="new-post.php" enctype="multipart/form-data">
    <label for="title">Post Title:</label>
    <input type="text" name="title" id="title" required>
    
    <label for="category">Category:</label>
    <select name="category" id="category" required>
      <option value="">-- Select Category --</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
      <?php endforeach; ?>
    </select>
    
    <label for="post_content">Content:</label>
    <textarea name="post_content" id="post_content" rows="10" required></textarea>
    <!-- You can later integrate a rich text editor here -->
    
    <label for="thumbnail">Thumbnail Image (optional):</label>
    <input type="file" name="thumbnail" id="thumbnail">
    
    <label for="main_image">Main Image (optional):</label>
    <input type="file" name="main_image" id="main_image">
    
    <button type="submit">Create Post</button>
  </form>
</main>

<?php include '../includes/footer.php'; ?>
