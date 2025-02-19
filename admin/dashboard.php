<?php
require_once '../includes/functions.php';
include '../includes/header.php';
require '../includes/db-connect.php';

// Role-based access control: Only allow admin or author
if (!is_logged_in()) {
    redirect('login.php');
}
$user_id = get_logged_in_user();
$stmtUser = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch();
if (!in_array($user['role'], ['admin', 'author'])) {
    echo "<p>Access denied. You do not have permission to view this page.</p>";
    include 'includes/footer.php';
    exit;
}

// Fetch all blog posts
$stmtPosts = $pdo->query("SELECT p.*, c.name AS category_name FROM posts p LEFT JOIN categories c ON p.post_category = c.id ORDER BY post_date DESC");
$posts = $stmtPosts->fetchAll();
?>

<main>
  <h1>Dashboard</h1>
  <p><a href="new-post.php">Create New Post</a></p>
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
            <td><?php echo htmlspecialchars($post['author']); ?></td>
            <td><?php echo htmlspecialchars($post['category_name']); ?></td>
            <td><?php echo date('M d, Y', strtotime($post['post_date'])); ?></td>
            <td>
              <a href="edit-post.php?id=<?php echo $post['id']; ?>">Edit</a> |
              <a href="/igotchills/post.php?id=<?php echo $post['id']; ?>" target="_blank">View</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
          <tr><td colspan="6">No posts found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</main>

<?php include '../includes/footer.php'; ?>
