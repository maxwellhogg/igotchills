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

// If the user is an author, show only their posts; if admin, show all posts.
if ($currentUser['role'] === 'author') {
    $stmtPosts = $pdo->prepare("SELECT p.*, c.name AS category_name FROM posts p LEFT JOIN categories c ON p.post_category = c.id WHERE p.author = ? ORDER BY p.post_date DESC");
    $stmtPosts->execute([$currentUser['username']]);
} else { // admin
    $stmtPosts = $pdo->query("SELECT p.*, c.name AS category_name FROM posts p LEFT JOIN categories c ON p.post_category = c.id ORDER BY p.post_date DESC");
}
$posts = $stmtPosts->fetchAll();
?>

<main>
  <h1>Dashboard</h1>
  <p><a href="/igotchills/admin/new-post.php">Create New Post</a></p>
  <?php if ($currentUser['role'] === 'admin'): ?>
    <p><a href="/igotchills/admin/add-author.php">Add New Author</a></p>
  <?php endif; ?>
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
              <a href="/igotchills/admin/edit-post.php?id=<?php echo $post['id']; ?>">Edit</a> |
              <a href="../post.php?id=<?php echo $post['id']; ?>" target="_blank">View</a>
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
