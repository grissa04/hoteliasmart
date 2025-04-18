<?php
session_start();
// Database connection
$db = new mysqli('localhost', 'root', '', 'hotel_forum');

// Add this line after session_start
$is_logged_in = isset($_SESSION['user_id']);

if ($db->connect_error) {
    die('Connection failed: ' . $db->connect_error);
}

// First, check if user has a profile
$query = "SELECT * FROM user_profiles WHERE id = ?";
$stmt = $db->prepare($query);
$user_id = isset($_GET['id']) ? $_GET['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

// If profile exists, get their posts
if ($profile) {
    $query = "SELECT * FROM forum_posts 
              WHERE user_profile_id = ? AND status = 'active' 
              ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $posts = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<!-- Add this in the head section -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Hotel Supplies Forum</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/post-handlers.js"></script>
    <script src="js/comment-handlers.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <h1>My Profile</h1>
            <div class="header-buttons">
                <a href="index.php" class="back-btn">Back to Forum</a>
                <a href="forum.html" class="create-post-btn">Create New Post</a>
            </div>
        </header>

        <main>
            <?php if (!$profile): ?>
                <!-- Show create profile form -->
                <div class="create-profile-form">
                    <h2>Create Your Profile</h2>
                    <form action="create_profile.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="user_id">ID:</label>
                            <input type="text" id="user_id" name="user_id" required>
                        </div>
                        <div class="form-group">
                            <label for="nickname">Nickname:</label>
                            <input type="text" id="nickname" name="nickname" required>
                        </div>
                        <div class="form-group">
                            <label for="lastname">Last Name:</label>
                            <input type="text" id="lastname" name="lastname" required>
                        </div>
                        <div class="form-group">
                            <label for="profile_pic">Profile Picture:</label>
                            <input type="file" id="profile_pic" name="profile_pic" accept="image/*" required>
                        </div>
                        <button type="submit">Create Profile</button>
                    </form>
                </div>
            <?php else: ?>
                <!-- Show profile info and posts -->
                <div class="profile-info">
                    <img src="<?php echo htmlspecialchars($profile['profile_pic']); ?>" alt="Profile Picture" class="profile-pic">
                    <h2><?php echo htmlspecialchars($profile['nickname']); ?></h2>
                    <p><?php echo htmlspecialchars($profile['lastname']); ?></p>
                </div>

                <div class="posts-container">
                    <h3>My Posts</h3>
                    <?php if ($posts && $posts->num_rows > 0): ?>
                        <?php while($post = $posts->fetch_assoc()): ?>
                            <article class="post-card">
                                <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                <div class="post-meta">
                                    <span>Date: <?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                                    <?php if ($post['user_profile_id'] == $_SESSION['user_id']): ?>
                                        <button onclick="editPost(<?php echo $post['id']; ?>)" class="edit-btn">Edit Post</button>
                                        <button onclick="deletePost(<?php echo $post['id']; ?>)" class="delete-btn">Delete Post</button>
                                    <?php endif; ?>
                                </div>
                                <!-- Add this inside your post-card div, after the post content -->
                                <div class="post-content">
                                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                                    <?php if (!empty($post['image_path'])): ?>
                                        <div class="post-image">
                                            <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post image">
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Add the comments section -->
                                <div class="comment-section">
                                    <div class="comment-box">
                                        <form class="comment-form" action="add_comment.php" method="POST">
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
                        <p class="no-posts">You haven't created any posts yet.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>