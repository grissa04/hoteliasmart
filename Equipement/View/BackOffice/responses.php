<?php
require_once(__DIR__ . '/../../config.php');

// Move getSuggestions function here, before any HTML output
function getSuggestions($type, $reclamation_text) {
    $suggestions = [];
    
    // Common response templates
    $common_templates = [
        "Thank you for bringing this to our attention. We will address this issue immediately.",
        "We apologize for any inconvenience caused. Your feedback is important to us.",
        "We appreciate your feedback and will take necessary actions to improve our service."
    ];
    
    // Type-specific templates
    $type_templates = [
        'chambre' => [
            "Our housekeeping team has been notified and will resolve this shortly.",
            "We will have our maintenance team check and fix this issue right away.",
            "Your room comfort is our priority. We'll address this immediately."
        ],
        'restaurant' => [
            "Our chef and restaurant team have been informed of your feedback.",
            "We take food quality very seriously and will investigate this matter.",
            "Thank you for your feedback about our restaurant service."
        ],
        'piscine' => [
            "Our pool maintenance team will address this issue promptly.",
            "We will ensure the pool area meets our high standards of safety and cleanliness.",
            "Thank you for helping us maintain the quality of our pool facilities."
        ]
    ];
    
    // Add common templates
    $suggestions = array_merge($suggestions, $common_templates);
    
    // Add type-specific templates
    if (isset($type_templates[$type])) {
        $suggestions = array_merge($suggestions, $type_templates[$type]);
    }
    
    // Add AI-generated suggestion based on reclamation text
    if (!empty($reclamation_text)) {
        $keywords = [
            'dirty' => 'We will ensure thorough cleaning is performed immediately.',
            'broken' => 'Our maintenance team will repair this as soon as possible.',
            'cold' => 'We will check the temperature control system right away.',
            'service' => 'We will review our service standards with our staff.',
            'noise' => 'We will take measures to ensure a quieter environment.'
        ];
        
        foreach ($keywords as $keyword => $response) {
            if (stripos($reclamation_text, $keyword) !== false) {
                $suggestions[] = $response;
            }
        }
    }
    
    return array_unique($suggestions);
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotlia_rec";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'validate' || $_POST['action'] === 'reject') {
            $reclamation_id = intval($_POST['reclamation_id']);
            $reclamation_type = $_POST['reclamation_type'];
            $status = ($_POST['action'] === 'validate') ? 'validated' : 'rejected';
            $current_time = date('Y-m-d H:i:s');
            
            // Update the response status and validation date
            $stmt = $conn->prepare("UPDATE reclamation_responses 
                SET status = ?, validation_date = ? 
                WHERE reclamation_id = ? AND reclamation_type = ?");
            $stmt->bind_param("ssis", $status, $current_time, $reclamation_id, $reclamation_type);
            $stmt->execute();
            
            // Also update the main reclamation table
            $table_name = $reclamation_type . "_reclamations";
            $status_column = $reclamation_type . "_status";
            
            $update_sql = "UPDATE " . $table_name . " 
                SET status = ?, validation_date = ? 
                WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ssi", $status, $current_time, $reclamation_id);
            $stmt->execute();
            $stmt->close();
            
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } elseif ($_POST['action'] === 'respond') {
            // Handle new response submission
            $reclamation_id = intval($_POST['reclamation_id']);
            $reclamation_type = $_POST['reclamation_type'];
            $admin_name = trim($_POST['admin_name']);
            $response_text = trim($_POST['response_text']);
            
            if (!empty($admin_name) && !empty($response_text)) {
                // Insert the response
                $stmt = $conn->prepare("INSERT INTO reclamation_responses 
                    (reclamation_id, reclamation_type, admin_name, response_text, response_date, status) 
                    VALUES (?, ?, ?, ?, NOW(), 'pending')");
                $stmt->bind_param("isss", $reclamation_id, $reclamation_type, $admin_name, $response_text);
                
                if ($stmt->execute()) {
                    // Update the main reclamation table status
                    $table_name = $reclamation_type . "_reclamations";
                    $update_sql = "UPDATE " . $table_name . " SET status = 'responded' WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("i", $reclamation_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
                $stmt->close();
                
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }
        }
    }
}

// Fetch reclamations with their responses
// Update the getReclamationsWithResponses function
function getReclamationsWithResponses($conn, $type) {
    $sql = "SELECT r.*, 
            COALESCE(resp.id, 0) as response_id,
            COALESCE(resp.response_text, '') as response_text,
            COALESCE(resp.admin_name, '') as admin_name,
            COALESCE(resp.response_date, '') as response_date,
            COALESCE(resp.status, '') as status,
            COALESCE(resp.validation_date, '') as validation_date
            FROM {$type}_reclamations r
            LEFT JOIN reclamation_responses resp 
            ON r.id = resp.reclamation_id 
            AND resp.reclamation_type = ?
            ORDER BY r.submission_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $type);
    $stmt->execute();
    return $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reclamation Responses - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="stylesback.css">
    <style>
        .validate-btn {
            background: #27ae60;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s ease;
        }

        .reject-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s ease;
            margin-left: 10px;
        }

        .validate-btn:hover {
            background: #219a52;
        }

        .reject-btn:hover {
            background: #c0392b;
        }

        .validation-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 4px;
            margin-top: 10px;
            font-size: 0.9em;
        }

        .validated-badge {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .rejected-badge {
            background: #ffebee;
            color: #c62828;
        }

        .validated {
            border-left: 4px solid #27ae60;
        }

        .rejected {
            border-left: 4px solid #e74c3c;
        }

        .pending {
            border-left: 4px solid #f39c12;
        }

        .response-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .reclamation-card {
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .reclamation-content {
            padding: 15px;
            background: #fff;
        }

        .response-content {
            padding: 15px;
            background: #f9f9f9;
        }

        .response-form {
            padding: 15px;
            background: #f5f5f5;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .submit-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-nav-container">
            <div class="admin-logo">
                <img src="../FrontOffice/assets/HS.png" alt="Hotelia Smart Logo">
                <span class="hotelia">HOTELIA</span>
                <span class="smart">SMART</span>
            </div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="addEqui.php"><i class="fas fa-plus-circle"></i> Add</a></li>
                    <li><a href="showEqui.php"><i class="fas fa-list"></i> Equipements</a></li>
                    <li><a href="statistics.php"><i class="fas fa-chart-bar"></i> Statistics</a></li>
                    <li><a href="responses.php" class="active"><i class="fas fa-reply"></i> Responses</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="content-container">
        <h1><i class="fas fa-reply"></i> Manage Reclamation Responses</h1>

        <!-- Tabs for different types of reclamations -->
        <div class="tab">
            <button class="tablinks active" onclick="openTab(event, 'chambre')">Chambre</button>
            <button class="tablinks" onclick="openTab(event, 'restaurant')">Restaurant</button>
            <button class="tablinks" onclick="openTab(event, 'piscine')">Piscine</button>
        </div>

        <?php
        $types = ['chambre', 'restaurant', 'piscine'];
        foreach ($types as $type): ?>
            <div id="<?php echo $type; ?>" class="tabcontent" <?php echo $type === 'chambre' ? 'style="display:block;"' : ''; ?>>
                <div class="reclamations-list">
                    <?php
                    $result = getReclamationsWithResponses($conn, $type);
                    while ($row = $result->fetch_assoc()):
                    ?>
                        <div class="reclamation-card">
                            <div class="reclamation-content">
                                <h3>Reclamation #<?php echo $row['id']; ?></h3>
                                <p><?php echo htmlspecialchars($row[$type]); ?></p>
                                <small>Submitted: <?php echo $row['submission_date']; ?></small>
                            </div>

                            <?php if (!empty($row['response_text'])): ?>
                                <div class="response-content <?php echo $row['status']; ?>">
                                    <h4>Response from <?php echo htmlspecialchars($row['admin_name']); ?></h4>
                                    <p><?php echo htmlspecialchars($row['response_text']); ?></p>
                                    <small>Responded: <?php echo $row['response_date']; ?></small>
                                    
                                    <div class="response-status">
                                        <?php if ($row['status'] === 'validated'): ?>
                                            <div class="validation-badge validated-badge">
                                                <i class="fas fa-check-circle"></i> Validated
                                            </div>
                                        <?php elseif ($row['status'] === 'rejected'): ?>
                                            <div class="validation-badge rejected-badge">
                                                <i class="fas fa-times-circle"></i> Rejected
                                            </div>
                                        <?php else: ?>
                                            <div class="response-actions">
                                                <form method="POST" class="validate-form">
                                                    <input type="hidden" name="action" value="validate">
                                                    <input type="hidden" name="reclamation_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="reclamation_type" value="<?php echo $type; ?>">
                                                    <button type="submit" class="validate-btn">
                                                        <i class="fas fa-check"></i> Validate
                                                    </button>
                                                </form>
                                                <form method="POST" class="reject-form">
                                                    <input type="hidden" name="action" value="reject">
                                                    <input type="hidden" name="reclamation_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="reclamation_type" value="<?php echo $type; ?>">
                                                    <button type="submit" class="reject-btn">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <!-- Add this after the existing styles -->
                            <style>
                                .suggestions-container {
                                    margin-top: 10px;
                                    display: grid;
                                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                                    gap: 10px;
                                }

                                .suggestion-btn {
                                    background: #f8f9fa;
                                    border: 1px solid #e9ecef;
                                    padding: 8px 12px;
                                    border-radius: 4px;
                                    cursor: pointer;
                                    transition: all 0.3s ease;
                                    font-size: 0.9em;
                                    text-align: left;
                                }

                                .suggestion-btn:hover {
                                    background: #e9ecef;
                                    transform: translateY(-2px);
                                }

                                .suggestion-btn.selected {
                                    background: #e3f2fd;
                                    border-color: #2196f3;
                                    color: #1976d2;
                                }
                            </style>

                            <!-- Modify the response form section -->
                            <?php else: ?>
                                <form class="response-form" method="POST">
                                    <input type="hidden" name="action" value="respond">
                                    <input type="hidden" name="reclamation_type" value="<?php echo $type; ?>">
                                    <input type="hidden" name="reclamation_id" value="<?php echo $row['id']; ?>">
                                    <div class="form-group">
                                        <input type="text" name="admin_name" placeholder="Your Name" required>
                                    </div>
                                    <div class="form-group">
                                        <textarea name="response_text" id="response_text_<?php echo $row['id']; ?>" 
                                                  placeholder="Write your response..." required></textarea>
                                        
                                        <!-- Add suggestions container -->
                                        <div class="suggestions-container">
                                            <?php
                                            // Get suggestions based on reclamation type and content
                                            $suggestions = getSuggestions($type, $row[$type]);
                                            foreach ($suggestions as $suggestion): ?>
                                                <button type="button" class="suggestion-btn" 
                                                        onclick="useTemplate(this, '<?php echo $row['id']; ?>')">
                                                            <?php echo htmlspecialchars($suggestion); ?>
                                                        </button>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="submit-btn">Send Response</button>
                                </form>
                            <?php endif; ?>

                            <!-- Remove the duplicate getSuggestions function definition that was here -->

                            <!-- Keep the JavaScript -->
                            <script>
                                function useTemplate(button, reclamationId) {
                                    const textarea = document.getElementById(`response_text_${reclamationId}`);
                                    const allButtons = button.parentElement.getElementsByClassName('suggestion-btn');
                                    
                                    // Remove selected class from all buttons
                                    Array.from(allButtons).forEach(btn => btn.classList.remove('selected'));
                                    
                                    // Add selected class to clicked button
                                    button.classList.add('selected');
                                    
                                    // Set textarea value
                                    textarea.value = button.textContent.trim();
                                    
                                    // Focus on textarea for additional editing
                                    textarea.focus();
                                }
                            </script>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>