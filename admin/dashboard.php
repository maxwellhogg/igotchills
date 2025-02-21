<?php
require_once '../includes/functions.php';
include '../includes/header.php';
require '../includes/db-connect.php';

// Ensure only logged-in users can access.
if (!is_logged_in()) {
    redirect('../login.php');
}
$user_id = get_logged_in_user();

// Retrieve current user details.
$stmtUser = $pdo->prepare("SELECT username, role FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$currentUser = $stmtUser->fetch();

if (!$currentUser) {
    echo "<p>User not found.</p>";
    include '../includes/footer.php';
    exit;
}

// Set pagination parameters.
$postsPerPage = 30;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $postsPerPage;

// Prepare query based on role.
if ($currentUser['role'] === 'author') {
    // For authors, only show their own posts. Join with users to retrieve the username.
    $stmtCount = $pdo->prepare("SELECT COUNT(*) as total FROM posts WHERE author = ?");
    $stmtCount->execute([$user_id]);
    $totalPosts = $stmtCount->fetch()['total'];

    $stmtPosts = $pdo->prepare("SELECT p.*, c.name AS category_name, u.username AS author_name 
                                FROM posts p 
                                LEFT JOIN categories c ON p.post_category = c.id 
                                LEFT JOIN users u ON p.author = u.id 
                                WHERE p.author = ? 
                                ORDER BY p.post_date DESC 
                                LIMIT :limit OFFSET :offset");
    $stmtPosts->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmtPosts->bindValue(':limit', $postsPerPage, PDO::PARAM_INT);
    $stmtPosts->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmtPosts->execute();
    $posts = $stmtPosts->fetchAll();
} else { // admin
    // For admin, show all posts with joined author data.
    $stmtCount = $pdo->query("SELECT COUNT(*) as total FROM posts");
    $totalPosts = $stmtCount->fetch()['total'];

    $stmtPosts = $pdo->prepare("SELECT p.*, c.name AS category_name, u.username AS author_name 
                                FROM posts p 
                                LEFT JOIN categories c ON p.post_category = c.id 
                                LEFT JOIN users u ON p.author = u.id 
                                ORDER BY p.post_date DESC 
                                LIMIT :limit OFFSET :offset");
    $stmtPosts->bindValue(':limit', $postsPerPage, PDO::PARAM_INT);
    $stmtPosts->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmtPosts->execute();
    $posts = $stmtPosts->fetchAll();
}

$totalPages = ceil($totalPosts / $postsPerPage);
?>

<main>
  <div class="dashboard-container">
    <section class="dashboard-title">
      <h1>Dashboard</h1>
      <p><a href="new-post.php">Create New Post</a></p>
      <?php if ($currentUser['role'] === 'admin'): ?>
        <p><a href="add-author.php">Add New Author</a></p>
        <p><a href="../index.php" target="_blank">Main Site</a> (new tab)</p>
      <?php endif; ?>
    </section>
    
    <table border="1" cellpadding="8" cellspacing="0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Author</th>
          <th>Category</th>
          <th>Posted On</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($posts): ?>
          <?php foreach ($posts as $post): ?>
            <tr>
              <td><?php echo $post['id']; ?></td>
              <td><?php echo htmlspecialchars($post['title']); ?></td>
              <td><?php echo htmlspecialchars($post['author_name']); ?></td>
              <td><?php echo htmlspecialchars($post['category_name']); ?></td>
              <td><?php echo date('M d, Y', strtotime($post['post_date'])); ?></td>
              <td>
                <a href="edit-post.php?id=<?php echo $post['id']; ?>">Edit</a> |
                <a href="../post.php?id=<?php echo $post['id']; ?>" target="_blank">View</a> |
                <a href="delete-post.php?id=<?php echo $post['id']; ?>&token=<?php echo generate_csrf_token(); ?>" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6">No posts found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a class="pagination-arrow" href="?page=<?php echo ($page - 1); ?>">&larr;</a>
        <?php else: ?>
          <button class="pagination-arrow" disabled>&larr;</button>
        <?php endif; ?>

        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <?php if ($p == $page): ?>
            <button class="pagination-number active"><?php echo $p; ?></button>
          <?php else: ?>
            <a class="pagination-number" href="?page=<?php echo $p; ?>"><?php echo $p; ?></a>
          <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
          <a class="pagination-arrow" href="?page=<?php echo ($page + 1); ?>">&rarr;</a>
        <?php else: ?>
          <button class="pagination-arrow" disabled>&rarr;</button>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php include '../includes/footer.php'; ?>
