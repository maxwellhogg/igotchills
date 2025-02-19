<?php
require 'includes/db-connect.php';

if (isset($_GET['post'])) {
    $post_id = (int) $_GET['post'];
    // Update the like_count column: increment by 1.
    $stmt = $pdo->prepare("UPDATE posts SET like_count = like_count + 1 WHERE id = ?");
    $stmt->execute([$post_id]);
    
    // Retrieve the updated like count.
    $stmt = $pdo->prepare("SELECT like_count FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $result = $stmt->fetch();
    echo $result['like_count'];
}
?>
