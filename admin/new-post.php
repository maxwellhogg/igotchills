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
    include '../includes/footer.php';
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
            $uploadDir = '../blog-uploads/';
            $fileName = time() . "_" . basename($_FILES['thumbnail']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetPath)) {
                // Store the path relative to the root (without ../)
                $thumbnail_link = "blog-uploads/" . $fileName;
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
            $uploadDir = '../blog-uploads/';
            $fileName = time() . "_" . basename($_FILES['main_image']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['main_image']['tmp_name'], $targetPath)) {
                $main_image_link = "blog-uploads/" . $fileName;
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
        $stmtInsert->execute([$title, $user_id, $category_id, $post_content, $thumbnail_link, $main_image_link]);        
        $success = "New post created successfully.";
    }
}

// Fetch categories for the selection dropdown.
$stmtCategories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmtCategories->fetchAll();
?>

<main>
    <div class="create-post-container">
        <h1>Create New Post</h1>
        <p><a href="dashboard.php">Return to Dashboard</a></p>
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
            
            <!-- Post Content Editor -->
            <label for="post_content">Content:</label>
            <!-- Toolbar -->
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
            
            <label for="main_image">Main Image (optional):</label>
            <input type="file" name="main_image" id="main_image">
            
            <button type="submit">Create Post</button>
        </form>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
