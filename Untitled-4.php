<!-- ... existing code ... -->
<div class="posts-container">
    <h3>My Posts</h3>
    <?php if ($posts && $posts->num_rows > 0): ?>
        <?php while($post = $posts->fetch_assoc()): ?>
            <article class="post-card">
                <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                <div class="post-meta">
                    <span>Date: <?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                </div>
                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>
                <?php if ($post['image_path']): ?>
                    <div class="post-image">
                        <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post image">
                    </div>
                <?php endif; ?>
            </article>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="no-posts">You haven't created any posts yet.</p>
    <?php endif; ?>
</div>
<!-- ... existing code ... -->