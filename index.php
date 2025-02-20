<?php 
include 'includes/header.php'; 
require 'includes/db-connect.php';
?>

<main>
    <div class="flex-container">
        <div class="post-container">
            <!-- Recent Posts Section -->
            <div class="recent-posts-container">
                <?php
                // Fetch the 4 most recent posts along with the author's username.
                $stmt = $pdo->prepare("SELECT p.*, u.username AS author_name FROM posts p JOIN users u ON p.author = u.id ORDER BY p.post_date DESC LIMIT 4");
                $stmt->execute();
                $recentPosts = $stmt->fetchAll();
                ?>

                <?php if ($recentPosts): ?>
                    <?php foreach ($recentPosts as $index => $post): ?>
                        <?php 
                        // Assign a class based on index (title-post1, title-post2, etc.)
                        $class = "title-post" . ($index + 1); 
                        ?>
                        <article class="title-post <?php echo $class; ?> post">
                            <?php if ($index === 0): ?>
                                <h4>RECENT</h4>
                            <?php endif; ?>
                            <img src="<?php echo $post['thumbnail_link']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" />
                            <section class="post-content">
                                <p class="post-content-poster">
                                    posted by <a href="#"><?php echo htmlspecialchars($post['author_name']); ?></a>
                                </p>
                                <a href="post.php?id=<?php echo $post['id']; ?>">
                                    <?php 
                                    // Use <h1> for the most recent post and <h3> for others.
                                    if ($index === 0) {
                                        echo "<h1>" . htmlspecialchars($post['title']) . "</h1>";
                                    } else {
                                        echo "<h3>" . htmlspecialchars($post['title']) . "</h3>";
                                    }
                                    ?>
                                </a>
                            </section>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No recent posts available.</p>
                <?php endif; ?>
                <a href="category.php?category=latest" class="btn-link1">
                    <button class="btn-st1">SEE ALL LATEST...</button>
                </a>
            </div>
            
            <!-- Dynamic Sub-Posts Sections for Each Category -->
            <?php
            // Fetch all categories from the database.
            $stmt = $pdo->prepare("SELECT * FROM categories ORDER BY name ASC");
            $stmt->execute();
            $categories = $stmt->fetchAll();
            ?>

            <?php foreach ($categories as $category): ?>
                <?php
                // For each category, fetch up to 6 of the most recent posts along with the author's username.
                $stmtPosts = $pdo->prepare("SELECT p.*, u.username AS author_name FROM posts p JOIN users u ON p.author = u.id WHERE p.post_category = ? ORDER BY p.post_date DESC LIMIT 6");
                $stmtPosts->execute([$category['id']]);
                $posts = $stmtPosts->fetchAll();
                ?>
                <?php if ($posts): ?>
                    <div class="sub-posts-container">
                        <h4><?php echo strtoupper($category['name']); ?></h4>
                        <?php foreach ($posts as $post): ?>
                            <article class="sub-post">
                                <img src="<?php echo $post['thumbnail_link']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" />
                                <section class="sub-post-content">
                                    <a href="post.php?id=<?php echo $post['id']; ?>">
                                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                        <p>
                                            <?php 
                                            // Output an excerpt from the post content (first 100 characters)
                                            echo substr(strip_tags($post['post_content']), 0, 100) . '...'; 
                                            ?>
                                        </p>
                                    </a>
                                    <p class="post-content-poster">
                                        posted by <a href="#"><?php echo htmlspecialchars($post['author_name']); ?></a>
                                    </p>
                                </section>
                            </article>
                        <?php endforeach; ?>
                        <a href="category.php?category=<?php echo urlencode($category['slug']); ?>" class="btn-link1">
                            <button class="btn-st1">SEE ALL <?php echo strtoupper($category['name']); ?>...</button>
                        </a>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php include 'includes/sidebar.php'; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

