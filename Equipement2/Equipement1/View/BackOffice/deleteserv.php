<?php
include_once 'C:\xampp\htdocs\hootelia\Equipement\config.php';
include_once 'C:\xampp\htdocs\hootelia\Equipement\Controller\servivecontrolle.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $pdo = config::getConnexion();
    
    if (supprimerService($pdo, $id)) {
        // Redirection après suppression
        header("Location: showserv.php");
        exit();
    } else {
        echo "Erreur : impossible de supprimer ce service.";
    }
} else {
    echo "ID non spécifié.";
}
?>
