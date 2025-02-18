<?php
require 'includes/db-connect.php';
?>
<div class="sidebar-container">
    <aside class="latest-sidebar">
        <!-- Most Recent Posts Section (Overall) -->
        <h4>MOST RECENT POSTS</h4>
        <?php
        // Fetch the next 6 most recent posts overall, skipping the first 4 (displayed in the main recent posts section)
        $stmt = $pdo->prepare("SELECT * FROM posts ORDER BY post_date DESC LIMIT 6 OFFSET 4");
        $stmt->execute();
        $recentSidebarPosts = $stmt->fetchAll();
        if ($recentSidebarPosts):
            foreach ($recentSidebarPosts as $post):
        ?>
                <article class="sidebar-post">
                    <a href="post.php?id=<?php echo $post['id']; ?>">
                        <img src="<?php echo $post['thumbnail_link']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" />
                    </a>
                    <section class="sidebar-post-content">
                        <a href="post.php?id=<?php echo $post['id']; ?>">
                            <h4><?php echo htmlspecialchars($post['title']); ?></h4>
                        </a>
                    </section>
                </article>
        <?php
            endforeach;
        else:
            echo "<p>No recent posts available.</p>";
        endif;
        ?>

        <?php
        // For each category, display additional posts (offset 6 from main area)
        $stmtCat = $pdo->prepare("SELECT * FROM categories ORDER BY name ASC");
        $stmtCat->execute();
        $categories = $stmtCat->fetchAll();
        foreach ($categories as $cat):
            // Fetch posts for this category, skipping the first 6 (already shown in the main content)
            $stmtCatPosts = $pdo->prepare("SELECT * FROM posts WHERE post_category = ? ORDER BY post_date DESC LIMIT 6 OFFSET 6");
            $stmtCatPosts->execute([$cat['id']]);
            $catPosts = $stmtCatPosts->fetchAll();
            if ($catPosts):
        ?>
                <h4>MORE <?php echo strtoupper($cat['name']); ?> POSTS</h4>
                <?php foreach ($catPosts as $post): ?>
                    <article class="sidebar-post">
                        <a href="post.php?id=<?php echo $post['id']; ?>">
                            <img src="<?php echo $post['thumbnail_link']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" />
                        </a>
                        <section class="sidebar-post-content">
                            <a href="post.php?id=<?php echo $post['id']; ?>">
                                <h4><?php echo htmlspecialchars($post['title']); ?></h4>
                            </a>
                        </section>
                    </article>
                <?php endforeach; ?>
        <?php
            endif;
        endforeach;
        ?>
    </aside>
</div>
