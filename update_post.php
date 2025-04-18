<?php
session_start();
$db = new mysqli('localhost', 'root', '', 'hotel_forum');

if ($db->connect_error) {
    die('Connection failed: ' . $db->connect_error);
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    
    // Validate title
    $title = trim($_POST['title']);
    if (strlen($title) < 3 || strlen($title) > 100) {
        $error_message = "Error: Title must be between 3 and 100 characters. Please try again.";
    } 
    // Validate content
    else {
        $content = trim($_POST['content']);
        if (strlen($content) < 10 || strlen($content) > 5000) {
            $error_message = "Error: Content must be between 10 and 5000 characters. Please try again.";
        } else {
            // Handle image validation
            $image_path = null;
            $current_image = '';

            // Get current image path
            $stmt = $db->prepare("SELECT image_path FROM forum_posts WHERE id = ?");
            $stmt->bind_param('i', $post_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $current_image = $row['image_path'];
            }

            // Handle image deletion checkbox
            if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
                if (!empty($current_image) && file_exists($current_image)) {
                    unlink($current_image);
                }
                $image_path = null; // Set to null to remove from database
            }
            // Handle new image upload
            elseif (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
                // Validate image type
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($_FILES['image']['type'], $allowed_types)) {
                    $error_message = "Error: Only JPG, PNG and GIF images are allowed. Please try again.";
                }
                // Validate image size (5MB max)
                elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                    $error_message = "Error: Image size must be less than 5MB. Please try again.";
                }
                else {
                    $target_dir = "uploads/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                    $new_filename = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $new_filename;

                    // Delete old image if exists
                    if (!empty($current_image) && file_exists($current_image)) {
                        unlink($current_image);
                    }

                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        $image_path = $target_file;
                    } else {
                        $error_message = "Error: Failed to upload image. Please try again.";
                    }
                }
            } else {
                // Keep current image
                $image_path = $current_image;
            }
            
            // If no errors, update the post
            if (empty($error_message)) {
                $update_stmt = $db->prepare("UPDATE forum_posts SET title = ?, content = ?, image_path = ? WHERE id = ? AND user_profile_id = ?");
                $update_stmt->bind_param('sssii', $title, $content, $image_path, $post_id, $user_id);
                
                if ($update_stmt->execute()) {
                    header('Location: profile.php');
                    exit;
                } else {
                    $error_message = "Error: Failed to update post: " . $db->error . ". Please try again.";
                }
            }
        }
    }
    
    // If there are errors, display them on the page
    if (!empty($error_message)) {
        // We'll display this error message on the page
        // Don't redirect, let the error show on the same page
    }
}

// If we have an error or this is a GET request, we need to display the form
// Get the post data for editing
if (isset($_POST['post_id'])) {
    $post_id = (int)$_POST['post_id'];
} else {
    $post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
}
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT * FROM forum_posts WHERE id = ? AND user_profile_id = ?");
$stmt->bind_param('ii', $post_id, $user_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    header('Location: profile.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Post</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .error-message {
            color: red;
            font-weight: bold;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #ffeeee;
            border: 1px solid #ffcccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="edit-form-container">
        <div class="edit-form-header">
            <h1>Edit Your Post</h1>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <form action="update_post.php" method="POST" enctype="multipart/form-data" class="post-form">
            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
            
            <div class="form-group">
                <label for="title">Title (3-100 characters)</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="content">Content (10-5000 characters)</label>
                <textarea id="content" name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="image">Update Image (JPG, PNG, GIF only, max 5MB)</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                
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
                <a href="profile.php" class="cancel-btn">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>