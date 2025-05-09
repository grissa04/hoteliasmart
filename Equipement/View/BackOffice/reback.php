<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backoffice - Reclamations</title>
    <style>
        :root {
            --primary: #4CAF50;
            --secondary: #3498db;
            --danger: #ff4444;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 30px;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
            color: var(--dark);
        }

        h1 {
            font-size: 32px;
            margin-bottom: 30px;
            color: var(--dark);
            border-bottom: 3px solid var(--primary);
            padding-bottom: 10px;
            display: inline-block;
        }

        .tab {
            background: var(--light);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .tab button {
            background: transparent;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .tab button:hover {
            background: rgba(76, 175, 80, 0.1);
        }

        .tab button.active {
            background: var(--primary);
            color: white;
        }

        .tabcontent {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: var(--light);
            font-weight: 600;
            color: var(--dark);
        }

        tr:hover {
            background: rgba(76, 175, 80, 0.05);
        }

        .delete-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .delete-btn:hover {
            background: #cc0000;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .tab button {
                padding: 10px 15px;
                font-size: 14px;
            }
            
            .tabcontent {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <h1>Reclamations Backoffice</h1>
    <div class="tab">
        <button class="tablinks active" onclick="openTab(event, 'chambre')">Chambre</button>
        <button class="tablinks" onclick="openTab(event, 'restaurant')">Restaurant</button>
        <button class="tablinks" onclick="openTab(event, 'piscine')">Piscine</button>
    </div>

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

    // Function to get reclamations
    function getReclamations($conn, $table) {
        $sql = "SELECT * FROM {$table}_reclamations ORDER BY submission_date DESC";
        $result = $conn->query($sql);
        return $result;
    }

    // Handle deletion if requested
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $table = $_POST['table'];
        $sql = "DELETE FROM {$table}_reclamations WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        // Redirect to prevent form resubmission
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
    ?>

    <!-- Chambre Reclamations -->
    <div id="chambre" class="tabcontent" style="display: block;">
        <h2>Chambre Reclamations</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Reclamation</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = getReclamations($conn, 'chambre');
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".$row['id']."</td>";
                        echo "<td>".$row['chambre']."</td>";
                        echo "<td>".$row['submission_date']."</td>";
                        echo "<td>
                                <form method='POST' style='display:inline;'>
                                    <input type='hidden' name='id' value='".$row['id']."'>
                                    <input type='hidden' name='table' value='chambre'>
                                    <button type='submit' name='delete' class='delete-btn'>Delete</button>
                                </form>
                            </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No reclamations found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Restaurant Reclamations -->
    <div id="restaurant" class="tabcontent">
        <h2>Restaurant Reclamations</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Reclamation</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = getReclamations($conn, 'restaurant');
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".$row['id']."</td>";
                        echo "<td>".$row['restaurant']."</td>";
                        echo "<td>".$row['submission_date']."</td>";
                        echo "<td>
                                <form method='POST' style='display:inline;'>
                                    <input type='hidden' name='id' value='".$row['id']."'>
                                    <input type='hidden' name='table' value='restaurant'>
                                    <button type='submit' name='delete' class='delete-btn'>Delete</button>
                                </form>
                            </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No reclamations found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Piscine Reclamations -->
    <div id="piscine" class="tabcontent">
        <h2>Piscine Reclamations</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Reclamation</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = getReclamations($conn, 'piscine');
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".$row['id']."</td>";
                        echo "<td>".$row[$table]."</td>";
                        echo "<td>".$row['submission_date']."</td>";
                        echo "<td>
                            <form method='POST' style='display:inline;'>
                                <input type='hidden' name='id' value='".$row['id']."'>
                                <input type='hidden' name='table' value='".$table."'>
                                <button type='submit' name='delete' class='delete-btn'>Delete</button>
                            </form>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No reclamations found</td></tr>";
                }
                ?>
            </tbody>
        </table>
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

    <!-- Add before closing body tag -->
    <div class="header-actions">
        <a href="responses.php" class="action-button">
            <i class="fas fa-reply"></i> Manage Responses
        </a>
    </div>

    <style>
        .header-actions {
            margin-bottom: 20px;
            text-align: right;
        }
        
        .action-button {
            background: var(--secondary);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .action-button:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
    </style>
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Reclamation</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                <input type="hidden" name="table" id="editTable">
                <div class="form-group">
                    <label>Reclamation Text:</label>
                    <textarea name="reclamation_text" id="editText" required></textarea>
                    <div class="priority-selector">
                        <label>Priority:</label>
                        <select name="priority" id="editPriority">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="submit-btn">Save Changes</button>
            </form>
        </div>
    </div>
    <?php $conn->close(); ?>
</body>
</html>

<script>
const modal = document.getElementById('editModal');
const span = document.getElementsByClassName('close')[0];

function editReclamation(id, text, table) {
    document.getElementById('editId').value = id;
    document.getElementById('editText').value = text;
    document.getElementById('editTable').value = table;
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

// Add in the POST handling section
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'edit') {
        $id = intval($_POST['id']);
        $table = $_POST['table'] . '_reclamations';
        $text = trim($_POST['reclamation_text']);
        $priority = $_POST['priority'];
        
        $column = $_POST['table']; // column name matches table name (e.g., 'piscine' for piscine_reclamations)
        
        $stmt = $conn->prepare("UPDATE $table SET $column = ?, priority = ? WHERE id = ?");
        $stmt->bind_param("ssi", $text, $priority, $id);
        $stmt->execute();
        $stmt->close();
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}