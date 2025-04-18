<?php
session_start();
$db = new mysqli('localhost', 'root', '', 'hotel_forum');

if ($db->connect_error) {
    die('Connection failed: ' . $db->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate title
    $title = trim($_POST['title']);
    if (strlen($title) < 5) {
        $errors[] = "Title must be at least 5 characters long";
    }
    if (strlen($title) > 100) {
        $errors[] = "Title cannot exceed 100 characters";
    }
    
    // Validate content
    $content = trim($_POST['content']);
    if (strlen($content) < 20) {
        $errors[] = "Post content must be at least 20 characters long";
    }
    if (strlen($content) > 1000) {
        $errors[] = "Post content cannot exceed 1000 characters";
    }
    
    // Validate image if uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = "Only JPG, PNG and GIF images are allowed";
        }
        
        if ($_FILES['image']['size'] > $max_size) {
            $errors[] = "Image size must be less than 5MB";
        }
    }
    
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
        exit;
    }

    // Continue with post creation if validation passes
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = 'uploads/posts/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    $user_id = $_SESSION['user_id'];
    $stmt = $db->prepare("INSERT INTO forum_posts (title, content, user_profile_id, image_path, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param('ssis', $title, $content, $user_id, $image_path);
    
    if ($stmt->execute()) {
        header('Location: index.php');
        exit;
    } else {
        echo "Error creating post: " . $db->error;
    }
}
?>