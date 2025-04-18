<?php

class equipement
{
    private $reference;
    private $nom;
    private $prix;
    private $quantite;
    private $type;

    public function __construct($reference, $nom, $prix, $quantite, $type)
    {
        $this->reference = $reference;
        $this->nom = $nom;
        $this->prix = $prix;
        $this->quantite = $quantite;
        $this->type = $type;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function getPrix()
    {
        return $this->prix;
    }

    public function getQuantite()
    {
        return $this->quantite;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    public function setPrix($prix)
    {
        $this->prix = $prix;
    }

    public function setQuantite($quantite)
    {
        $this->quantite = $quantite;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

}
?>