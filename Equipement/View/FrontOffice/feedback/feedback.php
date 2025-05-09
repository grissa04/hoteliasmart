<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotlia_rec";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'edit') {
        $id = intval($_POST['id']);
        $name = trim($_POST['edit_name']);
        $email = trim($_POST['edit_email']);
        $message = trim($_POST['edit_message']);
        $rating = intval($_POST['edit_rating']);
        
        if (!empty($name) && !empty($email) && !empty($message)) {
            $stmt = $conn->prepare("UPDATE feedback SET name = ?, email = ?, message = ?, rating = ? WHERE id = ?");
            $stmt->bind_param("sssii", $name, $email, $message, $rating, $id);
            $stmt->execute();
            $stmt->close();
        }
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
    if (isset($_POST['action']) && $_POST['action'] == 'create') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $rating = intval($_POST['rating'] ?? 5);
        $reclamation_type = trim($_POST['reclamation_type'] ?? '');
        $reclamation_id = intval($_POST['reclamation_id'] ?? 0);

        if (!empty($name) && !empty($email) && !empty($message)) {
            // Initialize all reclamation IDs as NULL
            $chambre_id = NULL;
            $piscine_id = NULL;
            $restaurant_id = NULL;

            // Only set the reclamation ID if both type and ID are provided
            if (!empty($reclamation_type) && $reclamation_id > 0) {
                // Verify that the reclamation exists before setting the ID
                $check_sql = "";
                switch($reclamation_type) {
                    case 'chambre':
                        $check_sql = "SELECT id FROM chambre_reclamations WHERE id = ?";
                        break;
                    case 'piscine':
                        $check_sql = "SELECT id FROM piscine_reclamations WHERE id = ?";
                        break;
                    case 'restaurant':
                        $check_sql = "SELECT id FROM restaurant_reclamations WHERE id = ?";
                        break;
                }

                if (!empty($check_sql)) {
                    $check_stmt = $conn->prepare($check_sql);
                    $check_stmt->bind_param("i", $reclamation_id);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
                    
                    if ($check_result->num_rows > 0) {
                        switch($reclamation_type) {
                            case 'chambre':
                                $chambre_id = $reclamation_id;
                                break;
                            case 'piscine':
                                $piscine_id = $reclamation_id;
                                break;
                            case 'restaurant':
                                $restaurant_id = $reclamation_id;
                                break;
                        }
                    }
                    $check_stmt->close();
                }
            }

            // Insert feedback with verified reclamation IDs
            $sql = "INSERT INTO feedback (name, email, message, rating, 
                    chambre_reclamation_id, piscine_reclamation_id, restaurant_reclamation_id, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssiiii", $name, $email, $message, $rating, 
                            $chambre_id, $piscine_id, $restaurant_id);
            
            $stmt->execute();
            $stmt->close();
            
            header("Location: ".$_SERVER['PHP_SELF']."?success=1");
            exit;
        }
    }
}

// Get all feedbacks for display
$feedbacks = [];
$sql = "SELECT f.*, 
        COALESCE(cr.id, pr.id, rr.id) as reclamation_id,
        CASE 
            WHEN cr.id IS NOT NULL THEN 'chambre'
            WHEN pr.id IS NOT NULL THEN 'piscine'
            WHEN rr.id IS NOT NULL THEN 'restaurant'
        END as reclamation_type
        FROM feedback f
        LEFT JOIN chambre_reclamations cr ON f.chambre_reclamation_id = cr.id
        LEFT JOIN piscine_reclamations pr ON f.piscine_reclamation_id = pr.id
        LEFT JOIN restaurant_reclamations rr ON f.restaurant_reclamation_id = rr.id
        ORDER BY f.created_at DESC";

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $feedbacks[] = $row;
    }
}

// Get reclamations for dropdown
$reclamations = [
    'chambre' => [],
    'piscine' => [],
    'restaurant' => []
];

$tables = ['chambre_reclamations', 'piscine_reclamations', 'restaurant_reclamations'];
foreach ($tables as $table) {
    $type = str_replace('_reclamations', '', $table);
    $sql = "SELECT id, $type as text, submission_date FROM $table ORDER BY submission_date DESC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $reclamations[$type][] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - Hotelia Smart</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
        }
    
        .top-banner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 30px;
            background-color: #FFFFFF;
            width: 100%;
            box-sizing: border-box;
            position: relative;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    
        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
        }
    
        .logo-img {
            height: 50px;
            transition: transform 0.3s;
        }
    
        .logo-text {
            font-family: 'Arial Black', sans-serif;
            font-size: 22px;
        }
    
        .logo-text span:first-child { color: #3498db; }
        .logo-text span:last-child { color: #333; }
    
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
    
        .content-box {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
    
        .content-box:hover {
            transform: translateY(-5px);
        }
    
        .form-group {
            margin-bottom: 25px;
        }
    
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
    
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
    
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
    
        .rating-stars {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
        }
    
        .rating-stars label {
            font-size: 30px;
            padding: 5px;
            cursor: pointer;
            transition: all 0.2s;
        }
    
        .rating-stars label:hover {
            transform: scale(1.2);
        }
    
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
    
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    
        .btn-primary {
            background: #3498db;
            color: white;
        }
    
        .btn-secondary {
            background: #e1e1e1;
            color: #333;
        }
    
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    
        .message {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
            transition: all 0.3s;
        }
    
        .message:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    
        .message-content {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
    
        .message-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 10px;
            font-size: 0.9em;
            color: #666;
        }
    
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }
    
        .empty-state i {
            font-size: 48px;
            color: #3498db;
            margin-bottom: 20px;
        }
    
        .success-message {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #d4edda;
            color: #155724;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: slideIn 0.5s ease;
        }
    
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    
        .header {
            text-align: center;
            padding: 40px 0;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            margin-bottom: 40px;
            border-radius: 0 0 50% 50% / 20px;
        }
    
        .header h1 {
            font-size: 36px;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
    
        .header p {
            font-size: 18px;
            margin: 10px 0 0;
            opacity: 0.9;
        }
    
        .reclamation-selector select {
            background: white;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            padding: 12px;
            width: 100%;
            margin-top: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
    
        .reclamation-selector select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
    
        .char-counter {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
            transition: all 0.3s;
        }
    
        .char-counter.warning { color: #f39c12; }
        .char-counter.error { color: #e74c3c; }
    
        /* Chatbox styles */
        .chat-widget {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 10000;
        }

        .chat-toggle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #3498db;
            color: white;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            z-index: 10001;
        }

        .chat-container {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
            z-index: 10002;
            display: none;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            padding: 15px;
            background: #3498db;
            color: white;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background: #f8f9fa;
        }

        .chat-input {
            padding: 15px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
            background: white;
            border-radius: 0 0 15px 15px;
        }

        .chat-input textarea {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            resize: none;
        }

        .chat-input button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3498db;
            color: white;
            border: none;
            cursor: pointer;
        }

        .message-bubble {
            margin-bottom: 10px;
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 80%;
            word-wrap: break-word;
        }

        .message-bubble.user {
            background: #3498db;
            color: white;
            margin-left: auto;
        }

        .message-bubble.bot {
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-right: auto;
        }

        .minimize-chat {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 20px;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            position: relative;
        }
        
        .close {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .edit-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .edit-btn:hover {
            background-color: #45a049;
        }
        
        /* Menu styles */
        .menu-container {
            position: relative;
        }
    
        .menu-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
        }
    
        .menu-button img {
            height: 30px;
            transition: transform 0.3s;
        }
    
        .menu-button.active img {
            transform: rotate(90deg);
        }
    
        .menu-dropdown {
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            width: 200px;
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.3s ease-out;
            z-index: 1000;
        }
    
        .menu-button.active + .menu-dropdown {
            max-height: 300px;
        }
    
        .menu-item {
            padding: 12px 20px;
            display: block;
            color: #333;
            text-decoration: none;
            font-family: 'Montserrat', sans-serif;
            border-bottom: 1px solid #eee;
            transition: all 0.2s;
        }
    
        .menu-item:hover {
            background: #f5f5f5;
            color: #3498db;
        }
    </style>
</head>
<body>
    <!-- Top Banner -->
    <div class="top-banner">
        <div class="logo-container" onclick="window.location.href='../home.php'">
            <img src="../reclamation/hotelia.png" alt="Hotelia Logo" class="logo-img">
            <div class="logo-text">
                <span>Hotelia</span><span>Smart</span>
            </div>
        </div>
        
        <div class="menu-container">
            <button class="menu-button" id="menuButton">
                <img src="../reclamation/menu.png" alt="Menu">
            </button>
            <div class="menu-dropdown">
                <a href="../reclamation/reclmation.php" class="menu-item">Home</a>
                <a href="#" class="menu-item">My Account</a>
                <a href="#" class="menu-item">Settings</a>
                <a href="#" class="menu-item">Logout</a>
            </div>
        </div>
    </div>

    <!-- Page Header -->
    <div class="header">
        <h1>Feedback</h1>
        <p>Share your experience with us</p>
    </div>

    <!-- Main Content -->
    <div class="container">
        <?php if (isset($_GET['success'])): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i> Thank you for your feedback!
        </div>
        <?php endif; ?>

        <!-- Feedback Form -->
        <form id="feedbackForm" method="POST">
            <input type="hidden" name="action" value="create">
            <div class="content-box">
                <h2>New Feedback</h2>
                <div class="form-group">
                    <input type="text" name="name" placeholder="Your Name" required
                           class="form-control" style="width: 100%; padding: 10px; margin-bottom: 15px;">
                    
                    <input type="email" name="email" placeholder="Your Email" required
                           class="form-control" style="width: 100%; padding: 10px; margin-bottom: 15px;">
                    
                    <textarea name="message" id="feedbackText" 
                              placeholder="Share your experience..." 
                              required maxlength="500" class="form-control"></textarea>
                    <div class="char-counter" id="charCounter">0/500 characters</div>
                    
                    <div class="rating-stars">
                        <input type="radio" name="rating" value="5" id="star5" checked>
                        <label for="star5" class="fas fa-star"></label>
                        <input type="radio" name="rating" value="4" id="star4">
                        <label for="star4" class="fas fa-star"></label>
                        <input type="radio" name="rating" value="3" id="star3">
                        <label for="star3" class="fas fa-star"></label>
                        <input type="radio" name="rating" value="2" id="star2">
                        <label for="star2" class="fas fa-star"></label>
                        <input type="radio" name="rating" value="1" id="star1">
                        <label for="star1" class="fas fa-star"></label>
                    </div>

                    <div class="reclamation-selector">
                        <label>Related Reclamation (Optional):</label>
                        <select name="reclamation_type" id="reclamationType">
                            <option value="">Select Type</option>
                            <option value="chambre">Chambre</option>
                            <option value="piscine">Piscine</option>
                            <option value="restaurant">Restaurant</option>
                        </select>
                        
                        <select name="reclamation_id" id="reclamationId" style="display: none;">
                            <option value="">Select Reclamation</option>
                        </select>
                    </div>
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-secondary" id="clearBtn">
                        <i class="fas fa-trash"></i> Clear
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit
                    </button>
                </div>
            </div>
        </form>

        <!-- Previous Feedbacks -->
        <div class="content-box">
            <h2>Previous Feedbacks</h2>
            <?php if (empty($feedbacks)): ?>
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <p>No feedbacks yet</p>
                </div>
            <?php else: ?>
                <?php foreach ($feedbacks as $feedback): ?>
                    <div class="message">
                        <div class="message-content">
                            <div class="message-text">
                                <strong><?php echo htmlspecialchars($feedback['name']); ?></strong>
                                <div style="margin: 5px 0;">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star" style="color: <?php echo $i <= $feedback['rating'] ? '#ffd700' : '#ddd'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <?php echo htmlspecialchars($feedback['message']); ?>
                            </div>
                            <div class="message-meta">
                                <span class="message-date">
                                    <?php echo date('M j, Y g:i a', strtotime($feedback['created_at'])); ?>
                                </span>
                                <div class="message-actions">
                                    <button onclick="editFeedback(
                                        <?php echo $feedback['id']; ?>, 
                                        '<?php echo addslashes($feedback['name']); ?>', 
                                        '<?php echo addslashes($feedback['email']); ?>', 
                                        '<?php echo addslashes($feedback['message']); ?>', 
                                        <?php echo $feedback['rating']; ?>
                                    )" class="edit-btn">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Chatbox -->
    <div class="chat-widget">
        <button class="chat-toggle" id="chatToggle">
            <i class="fas fa-comments"></i>
        </button>
        <div class="chat-container" id="chatContainer">
            <div class="chat-header">
                <h3>Reclamation Assistant</h3>
                <button class="minimize-chat" id="minimizeChat">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
            <div class="chat-messages" id="chatMessages">
                <div class="message-bubble bot">
                    Hello! I'm your reclamation assistant. How can I help you today?
                </div>
            </div>
            <div class="chat-input">
                <textarea id="userInput" placeholder="Type your message..." rows="1"></textarea>
                <button id="sendMessage">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Feedback</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="form-group">
                    <input type="text" name="edit_name" id="editName" placeholder="Your Name" required class="form-control">
                    <input type="email" name="edit_email" id="editEmail" placeholder="Your Email" required class="form-control">
                    <textarea name="edit_message" id="editText" required maxlength="500" class="form-control"></textarea>
                    <div class="char-counter" id="editCharCounter">0/500 characters</div>
                    
                    <div class="rating-stars">
                        <?php for($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="edit_rating" value="<?php echo $i; ?>" id="editStar<?php echo $i; ?>">
                            <label for="editStar<?php echo $i; ?>" class="fas fa-star"></label>
                        <?php endfor; ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Menu toggle functionality
            const menuButton = document.getElementById('menuButton');
            menuButton.addEventListener('click', function() {
                this.classList.toggle('active');
            });

            // Character counter for feedback text
            const feedbackText = document.getElementById('feedbackText');
            const charCounter = document.getElementById('charCounter');
            
            feedbackText.addEventListener('input', function() {
                const currentLength = this.value.length;
                charCounter.textContent = `${currentLength}/500 characters`;
                
                if (currentLength > 450) {
                    charCounter.classList.add('warning');
                    charCounter.classList.remove('error');
                } else if (currentLength > 490) {
                    charCounter.classList.remove('warning');
                    charCounter.classList.add('error');
                } else {
                    charCounter.classList.remove('warning', 'error');
                }
            });

            // Clear form button
            document.getElementById('clearBtn').addEventListener('click', function() {
                document.getElementById('feedbackForm').reset();
                charCounter.textContent = '0/500 characters';
                charCounter.classList.remove('warning', 'error');
            });

            // Reclamation type selector
            const reclamationType = document.getElementById('reclamationType');
            const reclamationId = document.getElementById('reclamationId');
            
            reclamationType.addEventListener('change', function() {
                const type = this.value;
                reclamationId.style.display = type ? 'block' : 'none';
                
                if (type) {
                    // Clear existing options
                    reclamationId.innerHTML = '<option value="">Select Reclamation</option>';
                    
                    // Add options based on selected type
                    const reclamations = <?php echo json_encode($reclamations); ?>;
                    reclamations[type].forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.text + ' (' + item.submission_date + ')';
                        reclamationId.appendChild(option);
                    });
                }
            });

            // Edit modal functionality
            const modal = document.getElementById('editModal');
            const span = document.getElementsByClassName('close')[0];
            const editText = document.getElementById('editText');
            const editCharCounter = document.getElementById('editCharCounter');
            
            window.editFeedback = function(id, name, email, message, rating) {
                document.getElementById('editId').value = id;
                document.getElementById('editName').value = name;
                document.getElementById('editEmail').value = email;
                document.getElementById('editText').value = message;
                document.getElementById(`editStar${rating}`).checked = true;
                updateCharCount(editText, editCharCounter);
                modal.style.display = 'block';
            }
            
            function updateCharCount(textarea, counter) {
                const currentLength = textarea.value.length;
                const maxLength = textarea.getAttribute('maxlength');
                counter.textContent = `${currentLength}/${maxLength} characters`;
            }
            
            editText.addEventListener('input', function() {
                updateCharCount(this, editCharCounter);
            });
            
            span.onclick = function() {
                modal.style.display = 'none';
            }
            
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }

            // Chatbox functionality
            const chatToggle = document.getElementById('chatToggle');
            const chatContainer = document.getElementById('chatContainer');
            const minimizeChat = document.getElementById('minimizeChat');
            const sendMessage = document.getElementById('sendMessage');
            const userInput = document.getElementById('userInput');
            const chatMessages = document.getElementById('chatMessages');

            // Initialize chat visibility
            chatContainer.style.display = 'none';
            chatToggle.style.display = 'block';

            // Toggle chat visibility
            chatToggle.addEventListener('click', () => {
                chatContainer.style.display = 'flex'; // Changed to flex
                chatToggle.style.display = 'none';
            });

            minimizeChat.addEventListener('click', () => {
                chatContainer.style.display = 'none';
                chatToggle.style.display = 'block';
            });

            // Handle sending messages
            async function handleSendMessage() {
                const message = userInput.value.trim();
                if (!message) return;

                // Add user message
                addMessage(message, true);
                userInput.value = '';
                userInput.style.height = 'auto';

                try {
                    const response = await fetch('chat_handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ message: message })
                    });

                    if (!response.ok) throw new Error('Network response was not ok');
                    
                    const data = await response.json();
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    addMessage(data.response);
                } catch (error) {
                    console.error('Error:', error);
                    addMessage('Sorry, I encountered an error. Please try again later.', false);
                }
            }

            function addMessage(message, isUser = false) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message-bubble ${isUser ? 'user' : 'bot'}`;
                messageDiv.textContent = message;
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            sendMessage.addEventListener('click', handleSendMessage);

            userInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    handleSendMessage();
                }
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>