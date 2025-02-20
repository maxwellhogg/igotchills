<?php
require_once '../includes/functions.php';
include '../includes/header.php';
require '../includes/db-connect.php';

// Only allow admin or author
if (!is_logged_in()) {
    redirect('login.php');
}
$user_id = get_logged_in_user();
$stmtUser = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch();
if (!in_array($user['role'], ['admin', 'author'])) {
    echo "<p>Access denied. You do not have permission to edit posts.</p>";
    include 'includes/footer.php';
    exit;
}

// Get the post ID from the URL; redirect if missing.
if (isset($_GET['id'])) {
    $post_id = (int) $_GET['id'];
} else {
    redirect('dashboard.php');
}

// Fetch the post data.
$stmtPost = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmtPost->execute([$post_id]);
$post = $stmtPost->fetch();
if (!$post) {
    echo "<p>Post not found.</p>";
    include 'includes/footer.php';
    exit;
}

$error = '';
$success = '';

// Process form submission to update the post.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $post_content = $_POST['post_content'] ?? '';
    $category_id = (int) ($_POST['category'] ?? 0);

    // File upload handling similar to new_post.php
    $thumbnail_link = $post['thumbnail_link'];
    $main_image_link = $post['main_image_link'];

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
        $stmtUpdate = $pdo->prepare("UPDATE posts SET title = ?, post_category = ?, post_content = ?, thumbnail_link = ?, main_image_link = ? WHERE id = ?");
        $stmtUpdate->execute([$title, $category_id, $post_content, $thumbnail_link, $main_image_link, $post_id]);
        $success = "Post updated successfully.";
        // Refresh the post data.
        $stmtPost = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
        $stmtPost->execute([$post_id]);
        $post = $stmtPost->fetch();
    }
}

// Fetch categories for the dropdown.
$stmtCategories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmtCategories->fetchAll();
?>

<main>
  <h1>Edit Post</h1>
  <?php if ($error): ?>
    <p style="color:red;"><?php echo $error; ?></p>
  <?php endif; ?>
  <?php if ($success): ?>
    <p style="color:green;"><?php echo $success; ?></p>
  <?php endif; ?>
  <form method="post" action="edit-post.php?id=<?php echo $post_id; ?>" enctype="multipart/form-data">
    <label for="title">Post Title:</label>
    <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
    
    <label for="category">Category:</label>
    <select name="category" id="category" required>
      <option value="">-- Select Category --</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?php echo $cat['id']; ?>" <?php if ($cat['id'] == $post['post_category']) echo 'selected'; ?>>
          <?php echo htmlspecialchars($cat['name']); ?>
        </option>
      <?php endforeach; ?>
    </select>
    
    <!-- Post Content Editor for Editing -->
    <label for="post_content">Content:</label>

    <!-- Toolbar -->
    <div id="editor-toolbar">
      <button type="button" class="toolbar-button" data-command="bold" title="Bold"><i class="fa fa-bold"></i></button>
      <button type="button" class="toolbar-button" data-command="italic" title="Italic"><i class="fa fa-italic"></i></button>
      <button type="button" class="toolbar-button" data-command="underline" title="Underline"><i class="fa fa-underline"></i></button>
      
      <!-- Headings -->
      <button type="button" class="toolbar-button" data-command="formatBlock" data-value="H1" title="Heading 1"><i class="fa fa-header"></i>1</button>
      <button type="button" class="toolbar-button" data-command="formatBlock" data-value="H2" title="Heading 2"><i class="fa fa-header"></i>2</button>
      <button type="button" class="toolbar-button" data-command="formatBlock" data-value="H3" title="Heading 3"><i class="fa fa-header"></i>3</button>
      <button type="button" class="toolbar-button" data-command="formatBlock" data-value="H4" title="Heading 4"><i class="fa fa-header"></i>4</button>
      
      <!-- Quote -->
      <button type="button" class="toolbar-button" data-command="formatBlock" data-value="blockquote" title="Quote"><i class="fa fa-quote-left"></i></button>
      
      <!-- Link -->
      <button type="button" class="toolbar-button" data-command="createLink" title="Insert Link"><i class="fa fa-link"></i></button>
      
      <!-- Image Upload -->
      <button type="button" class="toolbar-button" data-command="insertImage" title="Insert Image"><i class="fa fa-image"></i></button>
    </div>

    <!-- Hidden file input for image upload (hidden by default) -->
    <input type="file" id="imageUploadInput" style="display:none;">

    <!-- Hidden input to capture editor content -->
    <input type="hidden" name="post_content" id="post_content">

    <!-- The WYSIWYG editor -->
    <div id="editor" contenteditable="true" class="editor" style="min-height:300px; border:2px solid var(--col-sec); padding:1rem;">
      <!-- For new posts, this will start empty. For edit, pre-populate with existing content. -->
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Set default paragraph separator to <br> if desired.
      document.execCommand('defaultParagraphSeparator', false, 'br');
      
      const toolbarButtons = document.querySelectorAll('#editor-toolbar .toolbar-button');
      const editor = document.getElementById('editor');
      const imageUploadInput = document.getElementById('imageUploadInput');

      toolbarButtons.forEach(button => {
        button.addEventListener('click', function() {
          const command = button.getAttribute('data-command');
          if (command === 'createLink') {
            let url = prompt("Enter the link URL:");
            if(url) {
              document.execCommand(command, false, url);
            }
          } else if (command === 'insertImage') {
            // Trigger the file input instead of prompting for a URL.
            imageUploadInput.click();
          } else {
            const value = button.getAttribute('data-value') || null;
            document.execCommand(command, false, value);
          }
          editor.focus();
        });
      });

      // Handle file input change for image uploads.
      imageUploadInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
          const formData = new FormData();
          formData.append('image', file);
          
          // Adjust the endpoint path as needed.
          fetch('/igotchills/admin/upload-image.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Insert the uploaded image into the editor.
              document.execCommand('insertImage', false, data.url);
            } else {
              alert("Image upload failed: " + data.error);
            }
          })
          .catch(error => console.error('Error uploading image:', error));
        }
      });

      // On form submission, copy the editor's content to the hidden input.
      const form = document.querySelector('form');
      if (form) {
        form.addEventListener('submit', function(e) {
          const hiddenInput = document.getElementById('post_content');
          hiddenInput.value = editor.innerHTML;
        });
      }
    });
    </script>



    
    <label for="thumbnail">Thumbnail Image (optional):</label>
    <input type="file" name="thumbnail" id="thumbnail">
    <?php if (!empty($post['thumbnail_link'])): ?>
      <p>Current Thumbnail: <img src="<?php echo $post['thumbnail_link']; ?>" alt="Thumbnail" style="max-height:100px;"></p>
    <?php endif; ?>
    
    <label for="main_image">Main Image (optional):</label>
    <input type="file" name="main_image" id="main_image">
    <?php if (!empty($post['main_image_link'])): ?>
      <p>Current Main Image: <img src="<?php echo $post['main_image_link']; ?>" alt="Main Image" style="max-height:100px;"></p>
    <?php endif; ?>
    
    <button type="submit">Update Post</button>
  </form>
</main>

<?php include '../includes/footer.php'; ?>
