<?php
session_start();
header('Content-Type: application/json');

// Database connection
$db = new mysqli('localhost', 'root', '', 'hotel_forum');

if ($db->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $db->connect_error]));
}

// Get post ID and validate user
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

if (!$post_id || !$user_id) {
    die(json_encode(['success' => false, 'message' => 'Invalid request']));
}

// First delete comments associated with the post
$delete_comments = $db->prepare("DELETE FROM post_comments WHERE post_id = ?");
$delete_comments->bind_param('i', $post_id);
$delete_comments->execute();

// Then delete the post
$delete_post = $db->prepare("DELETE FROM forum_posts WHERE id = ? AND user_profile_id = ?");
$delete_post->bind_param('ii', $post_id, $user_id);

if ($delete_post->execute() && $delete_post->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete post']);
}

$db->close();
?>