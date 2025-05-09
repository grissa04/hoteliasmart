<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotlia_rec";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getReclamationWithResponse($conn, $type, $id) {
    $sql = "SELECT r.*, 
            resp.response_text,
            resp.admin_name,
            resp.response_date,
            resp.status,
            resp.validation_date
            FROM {$type}_reclamations r
            LEFT JOIN reclamation_responses resp 
            ON r.id = resp.reclamation_id 
            AND resp.reclamation_type = ?
            WHERE r.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $type, $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Update the response display section
?>
<div class="response-details">
    <h3>Response from <?php echo htmlspecialchars($reclamation['admin_name']); ?></h3>
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

$type = $_GET['type'] ?? '';
$id = intval($_GET['id'] ?? 0);

if (!empty($type) && $id > 0) {
    $reclamation = getReclamationWithResponse($conn, $type, $id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reclamation Response</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/stylefront3.css">
    <style>
        .response-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .reclamation-details {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .response-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .no-response {
            color: #666;
            font-style: italic;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="response-container">
        <a href="javascript:history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <?php if (isset($reclamation)): ?>
            <div class="reclamation-details">
                <h2>Your Reclamation</h2>
                <p><?php echo htmlspecialchars($reclamation[$type]); ?></p>
                <small>Submitted: <?php echo $reclamation['submission_date']; ?></small>
            </div>

            <?php if (!empty($reclamation['response_text'])): ?>
                <div class="response-details">
                    <h3>Response from <?php echo htmlspecialchars($reclamation['admin_name']); ?></h3>
                    <p><?php echo htmlspecialchars($reclamation['response_text']); ?></p>
                    <small>Responded: <?php echo $reclamation['response_date']; ?></small>
                </div>
            <?php else: ?>
                <p class="no-response">No response yet. We'll get back to you soon.</p>
            <?php endif; ?>
        <?php else: ?>
            <p>Reclamation not found.</p>
        <?php endif; ?>
    </div>
</body>
</html>