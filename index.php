<?php
session_start();
// Database connection
$db = new mysqli('localhost', 'root', '', 'hotel_forum');

if ($db->connect_error) {
    die('Connection failed: ' . $db->connect_error);
}

// Add the function definition here, before it's used
function getVoteCount($post_id, $vote_type) {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM post_votes WHERE post_id = ? AND vote_type = ?");
    $stmt->bind_param('is', $post_id, $vote_type);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'];
}

// Fetch all posts
$query = "SELECT forum_posts.*, 
          CASE 
              WHEN forum_posts.author_name = 'Admin' THEN 'Admin'
              ELSE user_profiles.nickname 
          END as author_name,
          forum_posts.image_path 
          FROM forum_posts 
          LEFT JOIN user_profiles ON forum_posts.user_profile_id = user_profiles.id
          WHERE forum_posts.status = 'active' 
          ORDER BY forum_posts.created_at DESC";
$result = $db->query($query);

// Add user check
$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="user-id" content="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>">
    <title>Hotel Supplies Forum - Home</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/post-handlers.js"></script>
    <script src="js/comment-handlers.js"></script>
    <script src="js/form-validation.js"></script>
    <!-- Add this in the head section -->
    <script src="js/vote-handlers.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Hotel Supplies Forum</h1>
            <div class="header-buttons">
                <a href="forum.html" class="create-post-btn">Create New Post</a>
                <a href="profile.php" class="profile-btn">My Profile</a>
            </div>
        </header>

        <main class="posts-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while($post = $result->fetch_assoc()): ?>
                    <article class="post-card" data-post-id="<?php echo $post['id']; ?>">
                        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                        <div class="post-meta">
                            <span>Posted by: <?php 
                                if ($post['author_name'] === 'Admin') {
                                    echo 'Admin';
                                } else {
                                    echo htmlspecialchars($post['author_name']); 
                                }
                            ?></span>
                            <span>Date: <?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                            <?php if ($is_logged_in && $_SESSION['user_id'] == $post['user_profile_id']): ?>
                                <button onclick="deletePost(<?php echo $post['id']; ?>)" class="delete-btn">Delete</button>
                            <?php endif; ?>
                        </div>
                        <div class="post-content">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                            <?php if (!empty($post['image_path'])): ?>
                                <div class="post-image">
                                    <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post image">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Move the vote buttons here, above the comment section -->
                        <?php if ($is_logged_in): ?>
                            <div class="vote-buttons" data-post-id="<?php echo $post['id']; ?>">
                                <button class="vote-btn upvote-btn" onclick="handleVote(<?php echo $post['id']; ?>, 'upvote')">
                                    <span class="vote-icon">👍</span>
                                    <span class="vote-count upvotes"><?php echo getVoteCount($post['id'], 'upvote'); ?></span>
                                </button>
                                <span class="vote-separator">•</span>
                                <button class="vote-btn downvote-btn" onclick="handleVote(<?php echo $post['id']; ?>, 'downvote')">
                                    <span class="vote-icon">👎</span>
                                    <span class="vote-count downvotes"><?php echo getVoteCount($post['id'], 'downvote'); ?></span>
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Comment section -->
                        <div class="comment-section">
                            <div class="comment-box">
                                <form class="comment-form" action="add_comment.php" method="POST" onsubmit="return validateCommentForm(this)">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <textarea name="comment_text" placeholder="Write a comment..." required></textarea>
                                    <button type="submit" class="submit-comment">Post</button>
                                </form>
                            </div>
                            
                            <!-- Display existing comments -->
                            <div class="comments-list">
                                <?php
                                $comment_stmt = $db->prepare("SELECT c.*, up.nickname FROM post_comments c 
                                                            LEFT JOIN user_profiles up ON c.user_profile_id = up.id 
                                                            WHERE c.post_id = ? ORDER BY c.created_at DESC");
                                $comment_stmt->bind_param('i', $post['id']);
                                $comment_stmt->execute();
                                $comments = $comment_stmt->get_result();
                                while($comment = $comments->fetch_assoc()): ?>
                                    <div class="comment" data-comment-id="<?php echo $comment['id']; ?>">
                                        <div class="comment-header">
                                            <span class="comment-author"><?php echo $comment['nickname'] ? htmlspecialchars($comment['nickname']) : 'Anonymous'; ?></span>
                                            <span class="comment-time"><?php echo date('F j, Y g:i a', strtotime($comment['created_at'])); ?></span>
                                            <?php if ($is_logged_in && $_SESSION['user_id'] == $comment['user_profile_id']): ?>
                                                <div class="comment-actions">
                                                    <button onclick="editComment(<?php echo $comment['id']; ?>)" class="edit-comment-btn">Edit</button>
                                                    <button onclick="deleteComment(<?php echo $comment['id']; ?>)" class="delete-comment-btn">Delete</button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="comment-text" id="comment-text-<?php echo $comment['id']; ?>">
                                            <?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?>
                                        </div>
                                        <div class="edit-comment-form" id="edit-form-<?php echo $comment['id']; ?>" style="display: none;">
                                            <textarea class="edit-comment-textarea"><?php echo htmlspecialchars($comment['comment_text']); ?></textarea>
                                            <button onclick="saveComment(<?php echo $comment['id']; ?>)" class="save-comment-btn">Save</button>
                                            <button onclick="cancelEdit(<?php echo $comment['id']; ?>)" class="cancel-edit-btn">Cancel</button>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-posts">No posts available yet.</p>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>