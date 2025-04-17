<?php

class Article {
    //private $id_article;
    private $titre;
    private $contenu;
    private $auteur_id;
    private $date_article;
    private $categorie;
    private $imageArticle;
    private $shared_from;

    public function __construct(
        $titre,
        $contenu,
        $auteur_id,
        $date_article,
        $categorie,
        $imageArticle = null,
        $shared_from = null,
        $id_article = null
    ) {
        $this->titre = $titre;
        $this->contenu = $contenu;
        $this->auteur_id = $auteur_id;
        $this->date_article = $date_article;
        $this->categorie = $categorie;
        $this->imageArticle = $imageArticle;
        $this->shared_from = $shared_from;
        //$this->id_article = $id_article;
    }

    // ID
    /*public function setIdArticle($val) {
        $this->id_article = $val;
    }

    public function getIdArticle() {
        return $this->id_article;
    }*/

    // Titre
    public function setTitre($val) {
        $this->titre = $val;
    }

    public function getTitre() {
        return $this->titre;
    }

    // Contenu
    public function setContenu($val) {
        $this->contenu = $val;
    }

    public function getContenu() {
        return $this->contenu;
    }

    // Auteur ID
    public function setAuteurId($val) {
        $this->auteur_id = $val;
    }

    public function getAuteurId() {
        return $this->auteur_id;
    }

    // Date Article
    public function setDateArticle($val) {
        $this->date_article = $val;
    }

    public function getDateArticle() {
        return $this->date_article;
    }

    // Catégorie
    public function setCategorie($val) {
        $this->categorie = $val;
    }

    public function getCategorie() {
        return $this->categorie;
    }

    // Image Article
    public function setImageArticle($val) {
        $this->imageArticle = $val;
    }

    public function getImageArticle() {
        return $this->imageArticle;
    }

    // Shared From
    public function setSharedFrom($val) {
        $this->shared_from = $val;
    }

    public function getSharedFrom() {
        return $this->shared_from;
    }

    // Helper method to get article data as array
    public function toArray() {
        return [
            //'id_article' => $this->id_article,
            'titre' => $this->titre,
            'contenu' => $this->contenu,
            'auteur_id' => $this->auteur_id,
            'date_article' => $this->date_article,
            'categorie' => $this->categorie,
            'imageArticle' => $this->imageArticle,
            'shared_from' => $this->shared_from
        ];
    }
}
?>