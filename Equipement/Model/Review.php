<?php
require_once(__DIR__ . '/../config.php');

class Review {
    private $db;

    public function __construct() {
        $this->db = config::getConnexion();
    }

    public function getAll() {
        try {
            $query = "SELECT * FROM reviews";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(Exception $e) {
            return [];
        }
    }

    public function add($data) {
        try {
            $query = "INSERT INTO reviews (user_name, comment, rating) VALUES (:name, :comment, :rating)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute($data);
        } catch(Exception $e) {
            return false;
        }
    }
}