<?php
session_start();
$db = new mysqli('localhost', 'root', '', 'hotel_forum');

if ($db->connect_error) {
    die('Connection failed: ' . $db->connect_error);
}

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Get post data
$stmt = $db->prepare("SELECT * FROM forum_posts WHERE id = ? AND user_profile_id = ?");
$stmt->bind_param('ii', $post_id, $user_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    header('Location: profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    $update_stmt = $db->prepare("UPDATE forum_posts SET title = ?, content = ? WHERE id = ? AND user_profile_id = ?");
    $update_stmt->bind_param('ssii', $title, $content, $post_id, $user_id);
    
    if ($update_stmt->execute()) {
        header('Location: profile.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Post</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/form-validation.js"></script>
</head>
<body>
    <div class="edit-form-container">
        <div class="edit-form-header">
            <h1>Edit Your Post</h1>
        </div>
        
        <form action="update_post.php" method="POST" enctype="multipart/form-data" class="post-form" onsubmit="return validatePostForm(this)">
            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
            
            <div class="form-group">
                <label for="title">Title (3-100 characters)</label>
                <input type="text" id="postTitle" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="content">Content (10-5000 characters)</label>
                <textarea id="postContent" name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="image">Update Image (JPG, PNG, GIF only, max 5MB)</label>
                <input type="file" id="postImage" name="image" accept="image/jpeg,image/png,image/gif">
                
                <?php if (!empty($post['image_path'])): ?>
                    <div class="current-image-container">
                        <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Current post image">
                        <div class="image-controls">
                            <div class="delete-image-option">
                                <input type="checkbox" id="delete_image" name="delete_image" value="1">
                                <label for="delete_image">Delete current image</label>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-buttons">
                <button type="submit" class="submit-btn">Update Post</button>
                <a href="index.php" class="cancel-btn">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>