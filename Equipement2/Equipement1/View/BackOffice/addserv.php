<?php
include_once 'C:\xampp\htdocs\hootelia\Equipement\config.php';
include_once 'C:\xampp\htdocs\hootelia\Equipement\Controller\servivecontrolle.php';

$pdo = config::getConnexion();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Service - Backoffice</title>
    <link rel="stylesheet" href="stylesback.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container">
        <h1>Ajouter un Nouveau Service</h1>
        <form action="" method="POST" class="service-form">
            <div class="form-group">
                <label for="title">Titre du Service:</label>
                <input type="text" id="title" name="name">
            </div>

            <div class="form-group">
                <label for="category">Catégorie de Service:</label>
                <select id="category" name="service">
                    <option value="1">Installation de Panneaux Solaires</option>
                    <option value="2">Conservation de l'Eau</option>
                    <option value="3">Nettoyage Écologique</option>
                    <option value="4">Gestion Intelligente des Déchets</option>
                    <option value="5">Intégration de Technologies Intelligentes</option>
                    <option value="6">Espaces Verts & Jardinage</option>
                </select>
            </div>

            <div class="form-group">
                <label for="quantity">Quantité:</label>
                <input type="number" id="quantity" name="solarNumber">
            </div>

            <div class="form-group">
                <label for="price">Prix:</label>
                <input type="number" id="price" name="price" step="0.01">
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="type"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Ajouter le Service</button>
                <a href="showserv.php" class="btn-cancel">Annuler</a>
            </div>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = $_POST['name'];
            $quantity = $_POST['solarNumber'];
            $price = $_POST['price'];
            $service = $_POST['service'];
            $type = $_POST['type'];

            $result = addService($pdo, $title, $quantity, $price, $service, $type);
            
            if ($result) {
                header("Location: showserv.php");
                exit();
            } else {
                echo "<div class='error-message'>Erreur lors de l'ajout du service. Veuillez réessayer.</div>";
            }
        }
        ?>
    </div>
    <script src="addService.js"></script>
</body>
</html>