<?php
require_once(__DIR__ . '/../Model/Review.php');

class ReviewController {
    private $reviewModel;

    public function __construct() {
        $this->reviewModel = new Review();
    }

    public function getAllReviews() {
        return $this->reviewModel->getAll();
    }

    public function addReview($data) {
        return $this->reviewModel->add($data);
    }
}