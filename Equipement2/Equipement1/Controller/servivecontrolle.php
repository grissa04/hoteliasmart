<?php 
include_once 'C:\xampp\htdocs\hootelia\Equipement\config.php'; // Adjusted path


function addService($pdo, $title, $solarNumber, $price, $service, $type) {
    // Prepare SQL to insert data into the database
    $sql = "INSERT INTO services (title, quantity, price, category_id, description) 
            VALUES (:name, :solarNumber, :price, :service, :type)";
    
    $stmt = $pdo->prepare($sql);

    // Bind the parameters to the query
    $stmt->bindParam(':name', $title);
    $stmt->bindParam(':solarNumber', $solarNumber, PDO::PARAM_INT);
    $stmt->bindParam(':price', $price, PDO::PARAM_STR);
    $stmt->bindParam(':service', $service, PDO::PARAM_INT);
    $stmt->bindParam(':type', $type);

    // Execute the query and return the result
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}


function afficherServices($pdo) {
    $sql = "SELECT * FROM services";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function supprimerService($pdo, $id) {
    try {
        $sql = "DELETE FROM services WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        echo "Erreur lors de la suppression du service : " . $e->getMessage();
        return false;
    }
}


function updateUser($id, $newnumber, $pdo, $price) {
    try {
        $sql = "UPDATE services SET quantity = :number, price = :price WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        
        // Bind des valeurs
        $stmt->bindParam(':number', $newnumber);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        // Exécution
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo "Utilisateur mis à jour avec succès.";
            header("Location: showserv.php");
        } else {
            echo "Aucune mise à jour effectuée.";
            header("Location: showserv.php");
        }
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}


?>