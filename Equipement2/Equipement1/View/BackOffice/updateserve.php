<?php
include_once 'C:\xampp\htdocs\hootelia\Equipement\config.php';
include_once 'C:\xampp\htdocs\hootelia\Equipement\Controller\servivecontrolle.php';

$pdo = config::getConnexion();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Vérification et récupération des données envoyées
    $id = $_POST['service_id'];
    $newnumber = $_POST['edit-quantity'];
    $price = $_POST['edit-price'];

  
    echo($id);
     echo($newnumber);
     echo($price);

    // Appel de la fonction update
    $result = updateUser($id, $newnumber, $pdo, $price);

    if ($result) {
        // Redirection après succès
        header("Location: afficherservice.php");
        exit();
    } else {
        echo "Erreur lors de la mise à jour.";
    }
}

?>
