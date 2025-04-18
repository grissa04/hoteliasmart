<?php
session_start();
$db = new mysqli('localhost', 'root', '', 'hotel_forum');

if ($db->connect_error) {
    die('Connection failed: ' . $db->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    
    // Check if ID already exists
    $check_stmt = $db->prepare("SELECT id FROM user_profiles WHERE id = ?");
    $check_stmt->bind_param('i', $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        die("Error: Profile ID already exists. Please choose a different ID.");
    }
    
    $nickname = $_POST['nickname'];
    $lastname = $_POST['lastname'];
    
    // Handle profile picture upload
    $upload_dir = 'uploads/profiles/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $profile_pic = $_FILES['profile_pic'];
    $file_extension = strtolower(pathinfo($profile_pic['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $profile_pic_path = $upload_dir . $new_filename;
    
    if (move_uploaded_file($profile_pic['tmp_name'], $profile_pic_path)) {
        $stmt = $db->prepare("INSERT INTO user_profiles (id, nickname, lastname, profile_pic) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('isss', $user_id, $nickname, $lastname, $profile_pic_path);
        
        if ($stmt->execute()) {
            // Set session variables
            $_SESSION['user_id'] = $user_id;
            $_SESSION['nickname'] = $nickname;
            $_SESSION['lastname'] = $lastname;
            
            // Redirect to profile page with user ID
            header('Location: profile.php?id=' . $user_id);
            exit;
        } else {
            echo "Error creating profile: " . $db->error;
        }
    } else {
        echo "Error uploading profile picture";
    }
}
?>