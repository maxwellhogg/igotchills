<?php 
include 'includes/header.php'; 
require 'includes/db-connect.php';

// Get the selected category from URL (default to 'latest')
$categorySlug = isset($_GET['category']) ? $_GET['category'] : 'latest';

// Set pagination parameters for main content
$postsPerPage = 14;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $postsPerPage;

if ($categorySlug === 'latest') {
    $heading = "ALL LATEST POSTS";
    // Count all posts
    $stmtCount = $pdo->prepare("SELECT COUNT(*) as total FROM posts");
    $stmtCount->execute();
    $totalPosts = $stmtCount->fetch()['total'];
    
    // Fetch posts without filtering by category
    $stmtPosts = $pdo->prepare("SELECT * FROM posts ORDER BY post_date DESC LIMIT :limit OFFSET :offset");
    $stmtPosts->bindValue(':limit', $postsPerPage, PDO::PARAM_INT);
    $stmtPosts->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmtPosts->execute();
    $posts = $stmtPosts->fetchAll();
} else {
    // Look up category details using the slug
    $stmtCat = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmtCat->execute([$categorySlug]);
    $category = $stmtCat->fetch();
    
    if ($category) {
        $heading = "ALL " . strtoupper($category['name']) . " POSTS";
        // Count posts in this category
        $stmtCount = $pdo->prepare("SELECT COUNT(*) as total FROM posts WHERE post_category = ?");
        $stmtCount->execute([$category['id']]);
        $totalPosts = $stmtCount->fetch()['total'];

        // For a specific category (not "latest"):
        $stmtPosts = $pdo->prepare("SELECT * FROM posts WHERE post_category = :cat ORDER BY post_date DESC LIMIT :limit OFFSET :offset");
        $stmtPosts->bindValue(':cat', $category['id'], PDO::PARAM_INT);
        $stmtPosts->bindValue(':limit', $postsPerPage, PDO::PARAM_INT);
        $stmtPosts->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmtPosts->execute();
        $posts = $stmtPosts->fetchAll();
        
    } else {
        $heading = "Category Not Found";
        $posts = [];
        $totalPosts = 0;
    }
}

$totalPages = ceil($totalPosts / $postsPerPage);
?>

<main>
    <div class="flex-container">
        <!-- Main Posts Container -->
        <div class="post-container">
            <div class="sub-posts-container sub-post-container-categories">
                <h4><?php echo $heading; ?></h4>
                <?php if(!empty($posts)): ?>
                    <?php foreach($posts as $post): ?>
                        <article class="sub-post">
                            <img src="<?php echo $post['thumbnail_link']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" />
                            <section class="sub-post-content">
                                <a href="post.php?id=<?php echo $post['id']; ?>">
                                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                    <p><?php echo substr(strip_tags($post['post_content']), 0, 100) . '...'; ?></p>
                                </a>
                                <p class="post-content-poster">
                                    posted by <a href="#"><?php echo htmlspecialchars($post['author']); ?></a>
                                </p>
                            </section>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No posts available in this category.</p>
                <?php endif; ?>
                
                <!-- Pagination (dynamically rendered) -->
                <?php if($totalPages > 1): ?>
                <div class="pagination">
                    <!-- Left Arrow -->
                    <?php if($page > 1): ?>
                        <a class="pagination-arrow" href="?category=<?php echo urlencode($categorySlug); ?>&page=<?php echo ($page - 1); ?>">&larr;</a>
                    <?php else: ?>
                        <button class="pagination-arrow" disabled>&larr;</button>
                    <?php endif; ?>
                    
                    <?php for($p = 1; $p <= $totalPages; $p++): ?>
                        <?php if($p == $page): ?>
                            <button class="pagination-number active"><?php echo $p; ?></button>
                        <?php else: ?>
                            <a class="pagination-number" href="?category=<?php echo urlencode($categorySlug); ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <!-- Right Arrow -->
                    <?php if($page < $totalPages): ?>
                        <a class="pagination-arrow" href="?category=<?php echo urlencode($categorySlug); ?>&page=<?php echo ($page + 1); ?>">&rarr;</a>
                    <?php else: ?>
                        <button class="pagination-arrow" disabled>&rarr;</button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar Container (exactly as in your original category.html) -->
        <div class="sidebar-container">
            <aside class="latest-sidebar">
                <!-- MOST RECENT POSTS -->
                <h4>MOST RECENT POSTS</h4>
                <?php
                // Fetch 6 most recent posts overall for the sidebar
                $stmtSidebar = $pdo->prepare("SELECT * FROM posts ORDER BY post_date DESC LIMIT 6");
                $stmtSidebar->execute();
                $sidebarPosts = $stmtSidebar->fetchAll();
                if($sidebarPosts):
                    foreach($sidebarPosts as $post):
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
                // Dynamically render additional sidebar sections for each category
                // (Ordered by id: assumes NEWS, RUMOURS, OPINION, PREVIEWS, REVIEWS, etc.)
                $stmtCategories = $pdo->prepare("SELECT * FROM categories ORDER BY id ASC");
                $stmtCategories->execute();
                $allCategories = $stmtCategories->fetchAll();
                foreach($allCategories as $cat):
                    // Fetch 6 posts from this category for the sidebar
                    $stmtCatSidebar = $pdo->prepare("SELECT * FROM posts WHERE post_category = ? ORDER BY post_date DESC LIMIT 6");
                    $stmtCatSidebar->execute([$cat['id']]);
                    $catSidebarPosts = $stmtCatSidebar->fetchAll();
                    if($catSidebarPosts):
                ?>
                <h4>MORE <?php echo strtoupper($cat['name']); ?> POSTS</h4>
                <?php foreach($catSidebarPosts as $post): ?>
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
    </div>
</main>

<?php include 'includes/footer.php'; ?>
