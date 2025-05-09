<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../Controller/equipementController.php');

$equipementC = new equipementController();
$listeEquipements = $equipementC->listEquipement();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Gestion des Équipements</title>
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
            </nav>
        </div>
    </header>

    <!-- Admin Title Section -->
    <div class="admin-title">
        <h1><i class="fas fa-clipboard-list"></i> Gestion des Équipements</h1>
    </div>

    <section class="form-section">
        <div class="form-container" style="max-width: 1200px;">
            <h2>Liste des Équipements</h2>
            <div style="text-align: right; margin-bottom: 20px;">
                <a href="addEqui.php" class="submit-button" style="display: inline-block; text-decoration: none;">
                    <i class="fas fa-plus"></i> Ajouter un équipement
                </a>
            </div>

        <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <thead>
                        <tr style="background-color: #003087; color: white;">
                            <th style="padding: 15px; text-align: left;">Référence</th>
                            <th style="padding: 15px; text-align: left;">Nom</th>
                            <th style="padding: 15px; text-align: left;">Prix</th>
                            <th style="padding: 15px; text-align: left;">Quantité</th>
                            <th style="padding: 15px; text-align: left;">Type</th>
                            <th style="padding: 15px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listeEquipements as $equipement) { ?>
                            <tr style="border-bottom: 1px solid #e0e0e0;">
                                <td style="padding: 15px;"><?php echo $equipement['reference']; ?></td>
                                <td style="padding: 15px;"><?php echo $equipement['nom']; ?></td>
                                <td style="padding: 15px;"><?php echo $equipement['prix']; ?> tnd</td>
                                <td style="padding: 15px;"><?php echo $equipement['quantite']; ?></td>
                                <td style="padding: 15px;"><?php echo $equipement['type']; ?></td>
                                <td style="padding: 15px; text-align: center;">
                                    <a href="updateEqui.php?reference=<?php echo $equipement['reference']; ?>" 
                                       class="clear-button" style="padding: 8px 15px; margin-right: 10px; text-decoration: none;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="deleteEqui.php?reference=<?php echo $equipement['reference']; ?>" 
                                       class="submit-button" style="padding: 8px 15px; text-decoration: none; background-color: #dc3545;"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet équipement ?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</body>
</html>