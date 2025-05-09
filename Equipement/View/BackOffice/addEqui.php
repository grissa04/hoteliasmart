<?php
require_once '../../Model/equipement.php';
require_once '../../Controller/equipementController.php';
require_once '../../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reference = $_POST['reference'];
    $nom = $_POST['nom'];
    $prix = $_POST['prix'];
    $quantite = $_POST['quantite'];
    $type = $_POST['type'];

    $equipement = new equipement($reference, $nom, $prix, $quantite, $type);
    $equipementController = new equipementController();
    $equipementController->addEquipement($equipement);
    
    header('Location: showEqui.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ajouter un Équipement</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="stylesback.css">
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
                    <li><a href="addEqui.php" ><i class="fas fa-plus-circle"></i> Add</a></li>
                    <li><a href="showEqui.php"><i class="fas fa-list"></i> Equipements</a></li>
                    <li><a href="statistics.php"><i class="fas fa-chart-bar"></i> Statistics</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Admin Title Section -->
    <div class="admin-title">
        <h1><i class="fas fa-plus-circle"></i> Ajouter un Équipement</h1>
    </div>
    <section class="form-section">
        <div class="form-container">
            <h2>Nouvel Équipement</h2>
            <form id="equipmentForm" action="#" method="POST">
                <div class="form-group">
                    <label for="reference">Référence</label>
                    <input type="text" id="reference" name="reference" placeholder="Entrez la référence de l'équipement" required>
                </div>
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" placeholder="Entrez le nom de l'équipement" required>
                </div>
                <div class="form-group">
                    <label for="prix">Prix (€)</label>
                    <input type="number" id="prix" name="prix" placeholder="Entrez le prix" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="quantite">Quantité</label>
                    <input type="number" id="quantite" name="quantite" placeholder="Entrez la quantité" min="1" required>
                </div>
                <div class="form-group">
                    <label for="type">Type</label>
                    <input type="text" id="type" name="type" placeholder="Entrez le type d'équipement" required>
                </div>
                <div class="button-group">
                    <button type="submit" class="submit-button"><i class="fas fa-save"></i> Enregistrer</button>
                    <button type="button" class="clear-button" onclick="clearForm()"><i class="fas fa-undo"></i> Réinitialiser</button>
                </div>
            </form>
        </div>
    </section>

    <!-- JavaScript to clear the form -->
    <script>
        function clearForm() {
            const form = document.getElementById('equipmentForm');
            form.reset();
        }
    </script>
</body>
</html>