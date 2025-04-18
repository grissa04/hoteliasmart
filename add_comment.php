<?php
session_start();
$db = new mysqli('localhost', 'root', '', 'hotel_forum');

if ($db->connect_error) {
    die('Connection failed: ' . $db->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $comment_text = isset($_POST['comment_text']) ? trim($_POST['comment_text']) : '';
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    if ($post_id <= 0 || empty($comment_text)) {
        echo "Invalid post ID or empty comment.";
        exit;
    }

    // Verify the post exists
    $check_stmt = $db->prepare("SELECT id FROM forum_posts WHERE id = ? AND status = 'active'");
    $check_stmt->bind_param('i', $post_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Post not found.";
        exit;
    }

    // Insert the comment
    $stmt = $db->prepare("INSERT INTO post_comments (post_id, user_profile_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param('iis', $post_id, $user_id, $comment_text);

    if ($stmt->execute()) {
        // Redirect back to the page where the comment was added
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
        header("Location: $referer");
        exit;
    } else {
        echo "Error adding comment: " . $db->error;
    }
} else {
    echo "Invalid request method.";
}

$db->close();
?>