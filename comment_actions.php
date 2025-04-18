<?php
session_start();
header('Content-Type: application/json');

$db = new mysqli('localhost', 'root', '', 'hotel_forum');

if ($db->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed']));
}

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not logged in']));
}

$action = $_POST['action'] ?? '';
$comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'delete':
        $stmt = $db->prepare("DELETE FROM post_comments WHERE id = ? AND user_profile_id = ?");
        $stmt->bind_param('ii', $comment_id, $user_id);
        $success = $stmt->execute();
        echo json_encode(['success' => $success]);
        break;

    case 'update':
        $comment_text = $_POST['comment_text'] ?? '';
        $stmt = $db->prepare("UPDATE post_comments SET comment_text = ? WHERE id = ? AND user_profile_id = ?");
        $stmt->bind_param('sii', $comment_text, $comment_id, $user_id);
        $success = $stmt->execute();
        echo json_encode(['success' => $success]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$db->close();
?>