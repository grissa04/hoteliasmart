<?php
require_once '../../config.php';
require_once '../../controllers/ArticleController.php';

// Récupérer l'ID de l'article depuis l'URL
$auteur_id = isset($_GET['auteur_id']) ? $_GET['auteur_id'] : null;

if (!$auteur_id) {
    header('Location: show.php');
    exit;
}

// Instancier le contrôleur et récupérer l'article
$controller = new ArticleController();
$article = $controller->getArticleByAuteurId($auteur_id);

if (!$article) {
    header('Location: show.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="stylesback.css">
    <title><?php echo htmlspecialchars($article['titre']); ?></title>
</head>
<body>
    <header class="admin-header">
        <div class="admin-nav-container">
            <div class="admin-logo">
                <img src="../FrontOffice/assets/HS.png" alt="Hotelia Smart Logo">
                <span class="hotelia">HOTELIA</span>
                <span class="smart">SMART</span>
            </div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="create.php"><i class="fas fa-plus-circle"></i> Ajouter</a></li>
                    <li><a href="show.php" class="active"><i class="fas fa-list"></i> Articles</a></li>
                    <li><a href="historique.php"><i class="fas fa-history"></i> Historique</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Paramètres</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="admin-title">
        <h1><i class="fas fa-newspaper"></i> Détails de l'Article</h1>
    </div>

    <div class="article-detail-container">
        <div class="article-detail-content">
            <?php if (!empty($article['imageArticle'])): ?>
                <div class="article-detail-image">
                    <img src="../../uploads/<?php echo htmlspecialchars($article['imageArticle']); ?>" alt="Image de l'article">
                </div>
            <?php endif; ?>
            
            <h2 class="article-detail-title"><?php echo htmlspecialchars($article['titre']); ?></h2>
            
            <div class="article-detail-meta">
                <span class="article-detail-category">
                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($article['categorie']); ?>
                </span>
                <span class="article-detail-date">
                    <i class="fas fa-calendar-alt"></i> 
                    <?php echo date('d/m/Y', strtotime($article['date_article'])); ?>
                </span>
            </div>

            <div class="article-detail-text">
                <?php echo nl2br(htmlspecialchars($article['contenu'])); ?>
            </div>

            <div class="article-detail-actions">
                <a href="show.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
                <div class="article-actions">
                                <a href="edit.php?auteur_id=<?php echo htmlspecialchars($article['auteur_id']); ?>" class="clear-button" style="padding: 8px 15px; margin-right: 10px; text-decoration: none;">
                                <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete.php?auteur_id=<?php echo htmlspecialchars($article['auteur_id']); ?>"  class="submit-button" style="padding: 8px 15px; text-decoration: none; background-color: #dc3545;"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet équipement ?');">
                                        <i class="fas fa-trash"></i>
                                </a>
                            </div>
            </div>
        </div>
    </div>
</body>
</html>