<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/Article.php';

require_once __DIR__ . '/../../controllers/ArticleController.php';

$controller = new ArticleController();

// Vérifier si un auteur_id est fourni
if (!isset($_GET['auteur_id']) || empty($_GET['auteur_id'])) {
    header('Location: show.php');
    exit();
}

$auteur_id = $_GET['auteur_id'];

// Vérifier si l'article existe avant de le supprimer
$article = $controller->getArticleByAuteurId($auteur_id);
if ($article) {
    $controller->deleteArticle($article['auteur_id']);
} else {
    header('Location: show.php?error=article_not_found');
    exit();
}

// Rediriger vers la liste des articles
header('Location: show.php');
exit();