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
    if (isset($_POST['action']) && $_POST['action'] == 'create') {
        // Handle new reclamation
        $reclamation_text = trim($_POST['reclamation_text'] ?? '');
        $priority = $_POST['priority'] ?? 'medium';
        
        if (!empty($reclamation_text)) {
            $stmt = $conn->prepare("INSERT INTO piscine_reclamations (piscine, priority) VALUES (?, ?)");
            $stmt->bind_param("ss", $reclamation_text, $priority);
            $stmt->execute();
            $stmt->close();
            
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'chatbot' && isset($_POST['message'])) {
        // Handle chatbot request
        $message = trim($_POST['message']);
        if (!empty($message)) {
            $response = callGeminiAPI($message);  // Changed from callDeepSeekAPI to callGeminiAPI
            header('Content-Type: application/json');
            echo json_encode(['response' => $response]);
            exit;
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id'])) {
        // Handle deletion
        $id = intval($_POST['id']);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM piscine_reclamations WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            
            // Redirect to prevent form resubmission
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'edit' && isset($_POST['id'])) {
        // Handle edit
        $id = intval($_POST['id']);
        $reclamation_text = trim($_POST['reclamation_text'] ?? '');
        $priority = $_POST['priority'] ?? 'medium';
        
        if ($id > 0 && !empty($reclamation_text)) {
            $stmt = $conn->prepare("UPDATE piscine_reclamations SET piscine = ?, priority = ? WHERE id = ?");
            $stmt->bind_param("ssi", $reclamation_text, $priority, $id);
            $stmt->execute();
            $stmt->close();
            
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        }
    }
}

// Get all reclamations for display
// Update the SQL query to include responses
$reclamations = [];
$sql = "SELECT r.*, 
        COALESCE(resp.response_text, '') as response_text,
        COALESCE(resp.admin_name, '') as admin_name,
        COALESCE(resp.response_date, '') as response_date
        FROM piscine_reclamations r
        LEFT JOIN reclamation_responses resp 
        ON r.id = resp.reclamation_id 
        AND resp.reclamation_type = 'piscine'
        ORDER BY CASE r.priority 
            WHEN 'high' THEN 1 
            WHEN 'medium' THEN 2 
            WHEN 'low' THEN 3 
        END, r.submission_date DESC";

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $reclamations[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Piscine - Hotelia Smart</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --gray-color: #6c757d;
            --border-radius: 8px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark-color);
            line-height: 1.6;
        }

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            text-align: center;
            margin-bottom: 2rem;
            box-shadow: var(--box-shadow);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHBhdHRlcm5Vbml0cz0idXNlclNwYWNlT25Vc2UiIHBhdHRlcm5UcmFuc2Zvcm09InJvdGF0ZSg0NSkiPjxyZWN0IHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjA1KSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3QgZmlsbD0idXJsKCNwYXR0ZXJuKSIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIvPjwvc3ZnPg==');
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            position: relative;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header p {
            font-weight: 300;
            opacity: 0.9;
            position: relative;
        }

        /* Main Container */
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
            padding-bottom: 3rem;
        }

        /* Sidebar Navigation */
        .sidebar {
            position: fixed;
            top: 0;
            left: -250px;
            width: 250px;
            height: 100vh;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: var(--transition);
            padding-top: 80px;
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li a {
            display: block;
            padding: 15px 25px;
            color: var(--dark-color);
            text-decoration: none;
            transition: var(--transition);
            border-left: 3px solid transparent;
        }

        .sidebar-menu li a:hover, 
        .sidebar-menu li a.active {
            background: rgba(52, 152, 219, 0.1);
            border-left-color: var(--primary-color);
            color: var(--primary-color);
        }

        .sidebar-menu li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Menu Toggle Button */
        .menu-toggle {
            position: fixed;
            top: 20px;
            left: 20px;
            background: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1001;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: var(--transition);
        }

        .menu-toggle:hover {
            transform: scale(1.05);
        }

        .menu-toggle i {
            font-size: 1.5rem;
            color: var(--primary-color);
            transition: var(--transition);
        }

        .menu-toggle.active i {
            transform: rotate(90deg);
        }

        /* Content Boxes */
        .content-box {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .content-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .content-box h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        .content-box h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--secondary-color);
            border-radius: 3px;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        textarea {
            width: 100%;
            min-height: 150px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            resize: vertical;
            font-family: inherit;
            font-size: 1rem;
            transition: var(--transition);
        }

        textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        /* Button Styles */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            font-family: inherit;
            font-size: 1rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: var(--gray-color);
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }

        .button-group {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        /* Message List */
        .message {
            background: white;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-radius: var(--border-radius);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: var(--transition);
            border-left: 4px solid var(--secondary-color);
        }

        .message:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .message-text {
            flex-grow: 1;
            color: var(--dark-color);
        }

        .message-date {
            font-size: 0.8rem;
            color: var(--gray-color);
            margin-top: 5px;
        }

        .message-actions {
            margin-left: 15px;
            display: flex;
            gap: 10px;
        }

        .message-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .message-btn-delete {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .message-btn-delete:hover {
            background-color: var(--danger-color);
            color: white;
        }

        /* Character Counter */
        .char-counter {
            text-align: right;
            font-size: 0.85rem;
            color: var(--gray-color);
            margin-top: -15px;
            margin-bottom: 15px;
        }

        .char-counter.warning {
            color: #f39c12;
        }

        .char-counter.error {
            color: var(--danger-color);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--gray-color);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Overlay for sidebar */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .container {
                padding: 0 15px;
            }

            .content-box {
                padding: 1.5rem;
            }

            .button-group {
                flex-direction: column;
                gap: 10px;
            }

            .btn {
                width: 100%;
            }

            .message {
                flex-direction: column;
                align-items: flex-start;
            }

            .message-actions {
                margin-left: 0;
                margin-top: 15px;
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="overlay" id="overlay"></div>

    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <li><a href="reclmation.php" class="active"><i class="fas fa-home"></i> Reclamation Menu</a></li>
            <li><a href="#"><i class="fas fa-user"></i> My Account</a></li>
            <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Menu Toggle Button -->
    <div class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Page Header -->
    <div class="header">
        <h1>Piscine Reclamation</h1>
        <p>Report any issues with the pool</p>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Reclamation Form -->
        <form id="reclamationForm" method="POST">
            <input type="hidden" name="action" value="create">
            <div class="content-box">
                <h2>New Reclamation</h2>
                <div class="form-group">
                    <textarea name="reclamation_text" id="reclamationText" 
                              placeholder="Describe your pool issue in detail..." 
                              required maxlength="500"></textarea>
                    <div class="char-counter" id="charCounter">0/500 characters</div>
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

        <!-- Previous Reclamations -->
        <div class="content-box">
            <h2>Previous Reclamations</h2>
            <?php if (empty($reclamations)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No previous reclamations found</p>
                </div>
            <?php else: ?>
                <?php foreach ($reclamations as $reclamation): ?>
                    <div class="message message-<?php echo htmlspecialchars($reclamation['priority']); ?>">
                        <div class="message-content">
                            <div class="message-text"><?php echo htmlspecialchars($reclamation['piscine']); ?></div>
                            <div class="message-meta">
                                <span class="message-date">
                                    <?php echo date('M j, Y g:i a', strtotime($reclamation['submission_date'])); ?>
                                </span>
                                <span class="priority-badge priority-<?php echo htmlspecialchars($reclamation['priority']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($reclamation['priority'])); ?> priority
                                </span>
                            </div>
                        </div>
                        <div class="message-actions">
                            <button class="edit-btn" onclick="editReclamation(<?php echo $reclamation['id']; ?>, '<?php echo addslashes($reclamation['piscine']); ?>', '<?php echo $reclamation['priority']; ?>')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this reclamation?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $reclamation['id']; ?>">
                                <button type="submit" class="delete-btn">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Menu toggle functionality
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        menuToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', function() {
            menuToggle.classList.remove('active');
            sidebar.classList.remove('active');
            this.classList.remove('active');
        });

        // Form handling
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.getElementById('reclamationText');
            const clearBtn = document.getElementById('clearBtn');
            const reclamationForm = document.getElementById('reclamationForm');
            const charCounter = document.getElementById('charCounter');

            // Character counter functionality
            textarea.addEventListener('input', function() {
                const currentLength = this.value.length;
                const maxLength = this.getAttribute('maxlength');
                charCounter.textContent = `${currentLength}/${maxLength} characters`;
                
                // Change color based on length
                if (currentLength > maxLength * 0.9) {
                    charCounter.className = 'char-counter error';
                } else if (currentLength > maxLength * 0.75) {
                    charCounter.className = 'char-counter warning';
                } else {
                    charCounter.className = 'char-counter';
                }
            });

            // Form validation
            reclamationForm.addEventListener('submit', function(e) {
                // Trim whitespace from textarea
                const text = textarea.value.trim();
                
                // Check if empty
                if (text === '') {
                    e.preventDefault();
                    alert('Please enter your reclamation before submitting');
                    textarea.focus();
                    return;
                }
                
                // Check minimum length
                if (text.length < 10) {
                    e.preventDefault();
                    alert('Reclamation must be at least 10 characters long');
                    textarea.focus();
                    return;
                }
            });

            // Clear textarea
            clearBtn.addEventListener('click', function() {
                textarea.value = '';
                charCounter.textContent = `0/${textarea.getAttribute('maxlength')} characters`;
                charCounter.className = 'char-counter';
                textarea.focus();
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>

<script>
    // Edit Modal functionality
    const modal = document.getElementById('editModal');
    const span = document.getElementsByClassName('close')[0];

    function editReclamation(id, text, priority) {
        document.getElementById('editId').value = id;
        document.getElementById('editText').value = text;
        document.getElementById('editPriority').value = priority;
        modal.style.display = 'block';
    }

    span.onclick = function() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>

<!-- Add these styles in your existing style section -->
