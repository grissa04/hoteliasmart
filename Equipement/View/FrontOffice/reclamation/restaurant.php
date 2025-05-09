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

// Add statistics calculations before displaying the content
$stats = [];

// Total reclamations
$sql = "SELECT COUNT(*) as total FROM restaurant_reclamations";
$result = $conn->query($sql);
$stats['total'] = $result->fetch_assoc()['total'];

// Reclamations by priority
$sql = "SELECT priority, COUNT(*) as count FROM restaurant_reclamations GROUP BY priority";
$result = $conn->query($sql);
$stats['by_priority'] = [];
while($row = $result->fetch_assoc()) {
    $stats['by_priority'][$row['priority']] = $row['count'];
}

// Response rate
$sql = "SELECT 
    COUNT(*) as total_responses,
    SUM(CASE WHEN response_text IS NOT NULL THEN 1 ELSE 0 END) as responded
    FROM restaurant_reclamations r
    LEFT JOIN reclamation_responses resp ON r.id = resp.reclamation_id";
$result = $conn->query($sql);
$response_stats = $result->fetch_assoc();
$stats['response_rate'] = $response_stats['total_responses'] > 0 
    ? round(($response_stats['responded'] / $response_stats['total_responses']) * 100, 1)
    : 0;

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                // Handle new reclamation
                $reclamation_text = trim($_POST['reclamation_text'] ?? '');
                $priority = $_POST['priority'] ?? 'medium';
                
                if (!empty($reclamation_text)) {
                    $stmt = $conn->prepare("INSERT INTO restaurant_reclamations (restaurant, priority) VALUES (?, ?)");
                    $stmt->bind_param("ss", $reclamation_text, $priority);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Redirect to prevent form resubmission
                    header("Location: ".$_SERVER['PHP_SELF']);
                    exit;
                }
                break;
                
            case 'delete':
                // Handle deletion
                $id = intval($_POST['id']);
                if ($id > 0) {
                    $stmt = $conn->prepare("DELETE FROM restaurant_reclamations WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Redirect to prevent form resubmission
                    header("Location: ".$_SERVER['PHP_SELF']);
                    exit;
                }
                break;
                
            case 'chatbot':
                // Handle chatbot request
                $message = trim($_POST['message']);
                if (!empty($message)) {
                    // Call Gemini API
                    $response = callGeminiAPI($message);
                    
                    // Return JSON response
                    header('Content-Type: application/json');
                    echo json_encode(['response' => $response]);
                    exit;
                }
                break;
                
            case 'respond':
                // Handle response to reclamation
                $reclamation_id = intval($_POST['reclamation_id'] ?? 0);
                $admin_name = trim($_POST['admin_name'] ?? '');
                $response_text = trim($_POST['response_text'] ?? '');
                
                if ($reclamation_id > 0 && !empty($admin_name) && !empty($response_text)) {
                    $stmt = $conn->prepare("INSERT INTO reclamation_responses 
                        (reclamation_id, reclamation_type, admin_name, response_text, response_date, status) 
                        VALUES (?, 'restaurant', ?, ?, NOW(), 'pending')");
                    $stmt->bind_param("iss", $reclamation_id, $admin_name, $response_text);
                    $stmt->execute();
                    $stmt->close();
                    header("Location: ".$_SERVER['PHP_SELF']);
                    exit;
                }
                break;
        }
    }
}

function callGeminiAPI($message) {
    $api_key = "AIzaSyDlBlYfEhRaCDNn44xpIfZ0NQKoLqEz8zw";
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $api_key;
    
    $data = [
        "contents" => [
            [
                "parts" => [
                    [
                        "text" => "You are a helpful restaurant assistant for Hotelia Smart. " .
                                "Help with: food quality, service, reservations, menu questions. " .
                                "User message: " . $message
                    ]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.7,
            "maxOutputTokens" => 500,
            "topP" => 0.8,
            "topK" => 40
        ],
        "safetySettings" => [
            [
                "category" => "HARM_CATEGORY_HARASSMENT",
                "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => false // Only for development
    ]);

    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        error_log('Curl error: ' . curl_error($ch));
        curl_close($ch);
        return "I apologize, but I'm experiencing technical difficulties. Please try again in a moment.";
    }

    $result = json_decode($response, true);
    curl_close($ch);

    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return $result['candidates'][0]['content']['parts'][0]['text'];
    }

    error_log('Gemini API unexpected response: ' . print_r($response, true));
    return "I apologize, but I'm having trouble understanding your request. Please try rephrasing it.";
}

// Get all reclamations with responses for display
$reclamations = [];
$sql = "SELECT r.*, 
        resp.response_text,
        resp.admin_name,
        resp.response_date,
        resp.status,
        resp.validation_date
        FROM restaurant_reclamations r
        LEFT JOIN reclamation_responses resp 
        ON r.id = resp.reclamation_id 
        AND resp.reclamation_type = 'restaurant'
        ORDER BY CASE r.priority 
            WHEN 'high' THEN 1 
            WHEN 'medium' THEN 2 
            WHEN 'low' THEN 3 
        END, r.submission_date DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
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
    <title>Restaurant - Hotelia Smart</title>
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

        /* Stats Container */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
            margin: 20px auto;
            max-width: 1200px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2em;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .stat-card h3 {
            color: var(--dark-color);
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        .stat-value {
            font-size: 1.8em;
            font-weight: bold;
            color: var(--secondary-color);
        }

        .priority-stat {
            display: block;
            font-size: 0.6em;
            margin: 5px 0;
        }

        .priority-stat.high { color: var(--danger-color); }
        .priority-stat.medium { color: var(--primary-color); }
        .priority-stat.low { color: var(--secondary-color); }

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

        /* Voice Input Button Styles */
        #voiceInputBtn {
            margin: 10px 0;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        #voiceInputBtn:hover {
            background-color: #2980b9;
        }

        #voiceInputBtn.recording {
            background-color: #e74c3c;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        #voiceInputBtn.recording i {
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        /* Priority Selector Styles */
        .priority-selector {
            margin: 15px 0;
        }

        .priority-selector label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
        }

        .priority-selector select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            font-family: inherit;
            font-size: 1rem;
            background-color: white;
            transition: var(--transition);
        }

        .priority-selector select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        /* Priority Badges */
        .priority-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .priority-high {
            background-color: #f8d7da;
            color: #721c24;
        }

        .priority-medium {
            background-color: #fff3cd;
            color: #856404;
        }

        .priority-low {
            background-color: #d1ecf1;
            color: #0c5460;
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
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: var(--transition);
        }

        .message-high {
            border-left: 4px solid var(--danger-color);
        }

        .message-medium {
            border-left: 4px solid #ffc107;
        }

        .message-low {
            border-left: 4px solid var(--secondary-color);
        }

        .message:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .message-content {
            flex-grow: 1;
        }

        .message-text {
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .message-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .message-date {
            font-size: 0.8rem;
            color: var(--gray-color);
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

        /* Response Styles */
        .response-content {
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 15px;
            margin-top: 15px;
            border-left: 3px solid var(--primary-color);
        }

        .response-content h4 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .response-content p {
            margin-bottom: 10px;
        }

        .response-content small {
            color: var(--gray-color);
            font-size: 0.8rem;
        }

        .response-content.pending {
            border-left-color: #ffc107;
        }

        .response-content.validated {
            border-left-color: var(--secondary-color);
        }

        .response-content.rejected {
            border-left-color: var(--danger-color);
        }

        .validation-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-top: 10px;
        }

        .validation-badge i {
            margin-right: 5px;
        }

        .validation-badge.validated {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--secondary-color);
        }

        .validation-badge.rejected {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        /* No Response Styles */
        .no-response {
            margin-top: 15px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            border-left: 3px solid #6c757d;
        }

        .no-response p {
            color: var(--gray-color);
            margin-bottom: 10px;
        }

        .view-admin {
            display: inline-block;
            padding: 8px 15px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .view-admin:hover {
            background-color: #2980b9;
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

        /* Chatbot Styles */
        .chatbot-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 350px;
            z-index: 1000;
        }
        
        .chatbot-toggle {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            position: absolute;
            bottom: 0;
            right: 0;
            z-index: 1001;
            transition: var(--transition);
        }
        
        .chatbot-toggle:hover {
            transform: scale(1.1);
        }
        
        .chatbot-toggle i {
            font-size: 24px;
        }
        
        .chatbot-window {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.3s ease-out, opacity 0.2s;
            opacity: 0;
            display: flex;
            flex-direction: column;
        }
        
        .chatbot-window.active {
            max-height: 500px;
            opacity: 1;
            margin-bottom: 70px;
        }
        
        .chatbot-header {
            background-color: var(--secondary-color);
            color: white;
            padding: 15px;
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chatbot-close {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .chatbot-close:hover {
            transform: scale(1.2);
        }
        
        .chatbot-messages {
            flex-grow: 1;
            padding: 15px;
            overflow-y: auto;
            max-height: 300px;
            background-color: #f9f9f9;
        }
        
        .chatbot-message {
            margin-bottom: 15px;
            display: flex;
        }
        
        .bot-message {
            justify-content: flex-start;
        }
        
        .user-message {
            justify-content: flex-end;
        }
        
        .message-bubble {
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.4;
            transition: var(--transition);
        }
        
        .bot-message .message-bubble {
            background-color: #e5e5ea;
            color: var(--dark-color);
            border-bottom-left-radius: 5px;
        }
        
        .user-message .message-bubble {
            background-color: var(--secondary-color);
            color: white;
            border-bottom-right-radius: 5px;
        }
        
        .chatbot-input {
            display: flex;
            padding: 10px;
            border-top: 1px solid #eee;
            background-color: white;
        }
        
        .chatbot-input input {
            flex-grow: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            outline: none;
            font-family: inherit;
            transition: var(--transition);
        }
        
        .chatbot-input input:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.2);
        }
        
        .chatbot-input button {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            margin-left: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        
        .chatbot-input button:hover {
            background-color: #27ae60;
            transform: scale(1.05);
        }
        
        .typing-indicator {
            display: flex;
            padding: 10px 15px;
            background-color: #e5e5ea;
            border-radius: 18px;
            margin-bottom: 15px;
            width: fit-content;
            border-bottom-left-radius: 5px;
        }
        
        .typing-dot {
            width: 8px;
            height: 8px;
            background-color: #999;
            border-radius: 50%;
            margin: 0 2px;
            animation: typingAnimation 1.4s infinite ease-in-out;
        }
        
        .typing-dot:nth-child(1) {
            animation-delay: 0s;
        }
        
        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typingAnimation {
            0%, 100% { transform: translateY(0); }
            30% { transform: translateY(-5px); }
        }
        
        .chatbot-intro {
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: var(--border-radius);
            margin-bottom: 15px;
        }
        
        .chatbot-intro p {
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        
        .chatbot-intro ul {
            margin-left: 20px;
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        
        .chatbot-intro li {
            margin-bottom: 5px;
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

            .stats-container {
                grid-template-columns: 1fr;
                padding: 10px;
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
            
            .chatbot-container {
                width: 300px;
                right: 10px;
            }
            
            .chatbot-window.active {
                max-height: 400px;
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
        <h1>Restaurant Reclamation</h1>
        <p>Report any issues with our restaurant</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <i class="fas fa-clipboard-list"></i>
            <h3>Total Reclamations</h3>
            <div class="stat-value"><?php echo $stats['total']; ?></div>
        </div>
        <div class="stat-card">
            <i class="fas fa-chart-pie"></i>
            <h3>Priority Distribution</h3>
            <div class="stat-value">
                <span class="priority-stat high">High: <?php echo $stats['by_priority']['high'] ?? 0; ?></span>
                <span class="priority-stat medium">Medium: <?php echo $stats['by_priority']['medium'] ?? 0; ?></span>
                <span class="priority-stat low">Low: <?php echo $stats['by_priority']['low'] ?? 0; ?></span>
            </div>
        </div>
        <div class="stat-card">
            <i class="fas fa-reply"></i>
            <h3>Response Rate</h3>
            <div class="stat-value"><?php echo $stats['response_rate']; ?>%</div>
        </div>
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
                              placeholder="Describe your restaurant issue in detail..." 
                              required maxlength="500"></textarea>
                    <div class="char-counter" id="charCounter">0/500 characters</div>
                    
                    <!-- Voice input button -->
                    <button type="button" id="voiceInputBtn" class="btn btn-secondary">
                        <i class="fas fa-microphone"></i> Start Voice Input
                    </button>
                    
                    <!-- Priority selection -->
                    <div class="priority-selector">
                        <label>Priority Level:</label>
                        <select name="priority" required>
                            <option value="low">Low Priority</option>
                            <option value="medium" selected>Medium Priority</option>
                            <option value="high">High Priority</option>
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
                            <div class="message-text"><?php echo htmlspecialchars($reclamation['restaurant']); ?></div>
                            <div class="message-meta">
                                <span class="message-date">
                                    <?php echo date('M j, Y g:i a', strtotime($reclamation['submission_date'])); ?>
                                </span>
                                <span class="priority-badge priority-<?php echo htmlspecialchars($reclamation['priority']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($reclamation['priority'])); ?> Priority
                                </span>
                            </div>
                            
                            <?php if (!empty($reclamation['response_text'])): ?>
                                <div class="response-content <?php echo $reclamation['status']; ?>">
                                    <h4>Response from <?php echo htmlspecialchars($reclamation['admin_name']); ?></h4>
                                    <p><?php echo htmlspecialchars($reclamation['response_text']); ?></p>
                                    <small>Responded: <?php echo date('M j, Y g:i a', strtotime($reclamation['response_date'])); ?></small>
                                    
                                    <?php if ($reclamation['status'] === 'validated'): ?>
                                        <div class="validation-badge validated">
                                            <i class="fas fa-check-circle"></i> Validated
                                            <?php if (!empty($reclamation['validation_date'])): ?>
                                                <small>(<?php echo date('M j, Y g:i a', strtotime($reclamation['validation_date'])); ?>)</small>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif ($reclamation['status'] === 'rejected'): ?>
                                        <div class="validation-badge rejected">
                                            <i class="fas fa-times-circle"></i> Rejected
                                            <?php if (!empty($reclamation['validation_date'])): ?>
                                                <small>(<?php echo date('M j, Y g:i a', strtotime($reclamation['validation_date'])); ?>)</small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="no-response">
                                    <p>Waiting for response from admin...</p>
                                    <a href="../../../BackOffice/responses.php" class="view-admin">View in Admin Panel</a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="message-actions">
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this reclamation?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $reclamation['id']; ?>">
                                <button type="submit" class="message-btn message-btn-delete">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- AI Chatbot Container -->
    <div class="chatbot-container" id="chatbotContainer">
        <button class="chatbot-toggle" id="chatbotToggle">
            <i class="fas fa-robot"></i>
        </button>
        <div class="chatbot-window" id="chatbotWindow">
            <div class="chatbot-header">
                <span>Restaurant Assistant</span>
                <button class="chatbot-close" id="chatbotClose">&times;</button>
            </div>
            <div class="chatbot-messages" id="chatbotMessages">
                <div class="chatbot-intro">
                    <p>Hello! I'm your Hotelia Smart restaurant assistant. I can help you with:</p>
                    <ul>
                        <li>Food quality issues</li>
                        <li>Service complaints</li>
                        <li>Reservation problems</li>
                        <li>Menu questions</li>
                    </ul>
                    <p>How can I help you today?</p>
                </div>
            </div>
            <div class="chatbot-input">
                <input type="text" id="chatbotInput" placeholder="Type your question about the restaurant...">
                <button id="chatbotSend"><i class="fas fa-paper-plane"></i></button>
            </div>
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

            // Voice input functionality
            const voiceInputBtn = document.getElementById('voiceInputBtn');
            let recognition;
            let isRecording = false;

            // Check if browser supports speech recognition
            if ('webkitSpeechRecognition' in window) {
                recognition = new webkitSpeechRecognition();
                recognition.continuous = true;
                recognition.interimResults = true;
                recognition.lang = 'fr-FR'; // Set to French

                recognition.onstart = function() {
                    voiceInputBtn.classList.add('recording');
                    voiceInputBtn.innerHTML = '<i class="fas fa-microphone"></i> Recording...';
                    isRecording = true;
                };

                recognition.onend = function() {
                    voiceInputBtn.classList.remove('recording');
                    voiceInputBtn.innerHTML = '<i class="fas fa-microphone"></i> Start Voice Input';
                    isRecording = false;
                };

                recognition.onresult = function(event) {
                    let interimTranscript = '';
                    let finalTranscript = '';

                    for (let i = event.resultIndex; i < event.results.length; i++) {
                        if (event.results[i].isFinal) {
                            finalTranscript += event.results[i][0].transcript;
                        } else {
                            interimTranscript += event.results[i][0].transcript;
                        }
                    }

                    // Update textarea with the final transcript
                    if (finalTranscript) {
                        textarea.value += finalTranscript + ' ';
                        // Trigger input event to update character counter
                        textarea.dispatchEvent(new Event('input'));
                    }
                };

                recognition.onerror = function(event) {
                    console.error('Speech recognition error', event.error);
                    voiceInputBtn.classList.remove('recording');
                    voiceInputBtn.innerHTML = '<i class="fas fa-microphone"></i> Start Voice Input';
                    isRecording = false;
                    
                    // Show error message to user
                    if (event.error === 'not-allowed') {
                        alert('Microphone access was denied. Please allow microphone access to use voice input.');
                    } else {
                        alert('Error occurred with voice recognition: ' + event.error);
                    }
                };

                voiceInputBtn.addEventListener('click', function() {
                    if (!isRecording) {
                        try {
                            recognition.start();
                        } catch (error) {
                            console.error('Error starting recognition:', error);
                            alert('Error starting voice recognition. Please try again.');
                        }
                    } else {
                        recognition.stop();
                    }
                });
            } else {
                // Browser doesn't support speech recognition
                voiceInputBtn.style.display = 'none';
                console.log('Speech recognition not supported in this browser');
            }

            // Chatbot functionality
            const chatbotToggle = document.getElementById('chatbotToggle');
    const chatbotWindow = document.getElementById('chatbotWindow');
    const chatbotClose = document.getElementById('chatbotClose');
    const chatbotMessages = document.getElementById('chatbotMessages');
    const chatbotInput = document.getElementById('chatbotInput');
    const chatbotSend = document.getElementById('chatbotSend');

    // Toggle chatbot window
    chatbotToggle.addEventListener('click', function() {
        chatbotWindow.classList.toggle('active');
    });

    chatbotClose.addEventListener('click', function() {
        chatbotWindow.classList.remove('active');
    });

    // Handle sending messages
    function sendMessage() {
        const message = chatbotInput.value.trim();
        if (!message) return;

        // Add user message to chat
        appendMessage('user', message);
        chatbotInput.value = '';

        // Show loading indicator
        appendMessage('bot', '<i class="fas fa-spinner fa-spin"></i> Thinking...');

        // Send to backend
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=chatbot&message=${encodeURIComponent(message)}`
        })
        .then(response => response.json())
        .then(data => {
            // Remove loading message
            chatbotMessages.lastElementChild.remove();
            // Add bot response
            appendMessage('bot', data.response);
        })
        .catch(error => {
            console.error('Error:', error);
            chatbotMessages.lastElementChild.remove();
            appendMessage('bot', 'Sorry, I encountered an error. Please try again.');
        });
    }

    function appendMessage(sender, message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}`;
        messageDiv.innerHTML = message;
        chatbotMessages.appendChild(messageDiv);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    chatbotSend.addEventListener('click', sendMessage);
    chatbotInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
            
            // Show typing indicator
            function showTypingIndicator() {
                const typingDiv = document.createElement('div');
                typingDiv.className = 'chatbot-message bot-message';
                typingDiv.id = 'typingIndicator';
                
                const dotsDiv = document.createElement('div');
                dotsDiv.className = 'typing-indicator';
                
                for (let i = 0; i < 3; i++) {
                    const dot = document.createElement('div');
                    dot.className = 'typing-dot';
                    dotsDiv.appendChild(dot);
                }
                
                typingDiv.appendChild(dotsDiv);
                chatbotMessages.appendChild(typingDiv);
                
                // Scroll to bottom
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }
            
            // Remove typing indicator
            function removeTypingIndicator() {
                const typingIndicator = document.getElementById('typingIndicator');
                if (typingIndicator) {
                    typingIndicator.remove();
                }
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>