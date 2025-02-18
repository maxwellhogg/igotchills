<?php 
include 'includes/header.php';
require 'includes/db-connect.php';
require 'includes/functions.php';

// Get the post ID from the URL; redirect to index if missing.
if (isset($_GET['id'])) {
    $post_id = (int) $_GET['id'];
} else {
    redirect('index.php');
}

// Process comment submission.
$commentError = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    if (!is_logged_in()) {
        $commentError = "You must be logged in to post a comment.";
    } elseif (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $commentError = "Invalid CSRF token.";
    } else {
        $comment_text = sanitize($_POST['comment']);
        $user_id = get_logged_in_user();
        $stmtUser = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmtUser->execute([$user_id]);
        $user = $stmtUser->fetch();
        $username = $user ? $user['username'] : 'Anonymous';
        $stmtInsert = $pdo->prepare("INSERT INTO comments (username, comment, post, date_of_post) VALUES (?, ?, ?, NOW())");
        $stmtInsert->execute([$username, $comment_text, $post_id]);
        redirect("post.php?id=" . $post_id);
    }
}

// Fetch the full post.
$stmtPost = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmtPost->execute([$post_id]);
$post = $stmtPost->fetch();
if (!$post) {
    echo "<p>Post not found.</p>";
    include 'includes/footer.php';
    exit;
}

// Fetch comments for this post.
$stmtComments = $pdo->prepare("SELECT * FROM comments WHERE post = ? ORDER BY date_of_post ASC");
$stmtComments->execute([$post_id]);
$comments = $stmtComments->fetchAll();
?>

<main>
  <div class="flex-container">
    <div class="full-post-container">
      <article class="full-blog-post">
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <p class="post-content-poster">
          posted by <a href="#"><?php echo htmlspecialchars($post['author']); ?></a>
        </p>
        <img src="<?php echo $post['main_image_link']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" />
        <section class="full-blog-post-text">
          <?php 
            // Output the full post content (ensure that your post_content includes the necessary formatting)
            echo $post['post_content']; 
          ?>
        </section>
        <section class="like-share-container">
          <!-- LIKE BUTTON -->
          <div class="like-button" id="likeButton">
            <span class="heart">&#x2665;</span>
            <span class="like-count" id="likeCount">0</span>
            <span class="like">LIKE</span>
          </div>
          <!-- SHARE BUTTONS -->
          <div class="share-buttons-container">
            <button class="share-button" id="shareFacebook">
              SHARE <i class="fab fa-facebook"></i>
            </button>
            <button class="share-button" id="shareX">
              SHARE <i class="fab fa-twitter"></i>
            </button>
          </div>
        </section>
        <!-- COMMENTS SECTION WITH WYSIWYG EDITOR -->
        <section class="user-comments-input">
          <div class="comment-header">
            <span class="comment-count"><?php echo count($comments); ?> comments</span>
            <?php if (!is_logged_in()): ?>
              <span class="login-prompt">
                <a href="login.php">Log in</a> / <a href="signup.php">Sign up</a> to comment
              </span>
            <?php endif; ?>
          </div>
          <?php if ($commentError): ?>
            <p style="color:red;"><?php echo $commentError; ?></p>
          <?php endif; ?>
          <?php if (is_logged_in()): ?>
            <!-- WYSIWYG Toolbar -->
            <div class="wysiwyg-toolbar">
              <button class="toolbar-button" data-command="bold"><i class="fas fa-bold"></i></button>
              <button class="toolbar-button" data-command="italic"><i class="fas fa-italic"></i></button>
              <button class="toolbar-button" data-command="underline"><i class="fas fa-underline"></i></button>
              <button class="toolbar-button" data-command="h1"><i class="fas fa-heading"></i>1</button>
              <button class="toolbar-button" data-command="h2"><i class="fas fa-heading"></i>2</button>
              <button class="toolbar-button" data-command="h3"><i class="fas fa-heading"></i>3</button>
              <button class="toolbar-button" data-command="h4"><i class="fas fa-heading"></i>4</button>
              <button class="toolbar-button" data-command="quote"><i class="fas fa-quote-right"></i></button>
              <button class="toolbar-button" data-command="list-ul"><i class="fas fa-list-ul"></i></button>
              <button class="toolbar-button" data-command="list-ol"><i class="fas fa-list-ol"></i></button>
              <button class="toolbar-button" data-command="link"><i class="fas fa-link"></i></button>
              <button class="toolbar-button" data-command="image"><i class="fas fa-image"></i></button>
              <button class="toolbar-button" data-command="gif"><i class="fas fa-film"></i></button>
            </div>
            <!-- Comment Input Form -->
            <form method="post" action="post.php?id=<?php echo $post_id; ?>">
              <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
              <textarea class="comment-textarea" name="comment" placeholder="Write your comment here..."></textarea>
              <button class="post-comment-button" type="submit">Post Comment</button>
            </form>
          <?php endif; ?>
        </section>
        <!-- COMMENTS DISPLAY -->
        <section class="user-comments">
          <?php if ($comments): ?>
            <?php foreach ($comments as $comment): ?>
              <div class="comment">
                <div class="comment-profile-image">
                  <img src="images/profile-pic-placeholder.jpg" alt="Profile Image">
                </div>
                <div class="comment-content">
                  <div class="comment-header">
                    <span class="username"><?php echo htmlspecialchars($comment['username']); ?></span>
                    <span class="date-posted"><?php echo date('F j, Y', strtotime($comment['date_of_post'])); ?></span>
                  </div>
                  <p class="comment-text"><?php echo htmlspecialchars($comment['comment']); ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No comments yet.</p>
          <?php endif; ?>
        </section>
      </article>
    </div>
    <!-- Sidebar Container -->
    <div class="sidebar-container">
      <aside class="latest-sidebar">
        <?php include 'includes/sidebar.php'; ?>
      </aside>
    </div>
  </div>
</main>

<?php include 'includes/footer.php'; ?>
