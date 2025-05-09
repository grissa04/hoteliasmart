<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../Model/equipement.php');

class equipementController
{ 
    public function listEquipement()
    {
        $sql = "SELECT * FROM equipement";
        $db = config::getConnexion();
        try
        {
            $liste = $db->query($sql);
            return $liste;
        }
        catch (Exception $e)
        {
            die('Erreur: ' . $e->getMessage());
        }
    }
    public function deleteEquipement($reference)
    {
        var_dump($reference);
        $sql = "DELETE FROM equipement WHERE reference=:reference";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':reference', $reference);
        try
        {
            $req->execute();
        }
        catch (Exception $e)
        {
            die('Erreur:'.$e->getMessage());
        }
    }
    public function addEquipement($equipement)
    {
        var_dump($equipement);
        $sql = "INSERT INTO equipement(reference, nom, prix, quantite, type) VALUES (:reference, :nom, :prix, :quantite, :type)";
        $db = config::getConnexion();
        try
        {
            $query = $db->prepare($sql);
            $query->execute
            ([
                'reference' => $equipement->getReference(),
                'nom' => $equipement->getNom(),
                'prix' => $equipement->getPrix(),
                'quantite' => $equipement->getQuantite(),
                'type' => $equipement->getType()
            ]);
        }
        catch (Exception $e)
        {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function updateEquipement($equipement)
    {
        var_dump($equipement);
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE equipement SET 
                reference = :reference, 
                nom = :nom, 
                prix = :prix, 
                quantite = :quantite, 
                type = :type
                WHERE reference = :reference'
            );
            
            $query->execute([
                'reference' => $equipement->getReference(),
                'nom' => $equipement->getNom(),
                'prix' => $equipement->getPrix(),
                'quantite' => $equipement->getQuantite(),
                'type' => $equipement->getType()
            ]);
            echo $query->rowCount() . " records UPDATED successfully <br>";
        }
        catch (PDOException $e)
        {
            echo "Error: " . $e->getMessage();
        }
    }
    public function showEquipement($reference)
    {
        $sql = "SELECT * FROM equipement WHERE reference=:reference";
        $db = config::getConnexion();
        try
        {
            $query = $db->prepare($sql);
            $query->execute(array('reference' => $reference));
            $equipement = $query->fetch();
            return $equipement;
        }
        catch (Exception $e)
        {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function getEquipementWithReviews($reference)
    {
        $sql = "SELECT e.*, r.comment, r.rating, r.user_name 
                FROM equipement e 
                LEFT JOIN reviews r ON e.reference = r.equipment_id 
                WHERE e.reference = :reference";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['reference' => $reference]);
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function listEquipementWithReviews()
    {
        $sql = "SELECT e.*, r.comment, r.rating, r.user_name 
                FROM equipement e 
                LEFT JOIN reviews r ON e.reference = r.equipment_id";
        $db = config::getConnexion();
        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
?>