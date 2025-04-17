<?php
require_once '../../config.php';
require_once '../../controllers/ArticleController.php';

$articleController = new ArticleController();
$articles = $articleController->getAllArticles();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="stylesback.css">
    <title>Liste des Articles</title>
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
        <h1><i class="fas fa-newspaper"></i> Liste des Articles</h1>
    </div>

    <?php if (isset($error_message)): ?>
    <div class="error-message">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
    </div>
    <?php endif; ?>

    <section class="articles-list">
        <?php if (empty($articles)): ?>
            <div class="no-articles">
                <i class="fas fa-info-circle"></i>
                <p>Aucun article n'a été trouvé.</p>
                <a href="create.php" class="create-button"><i class="fas fa-plus-circle"></i> Créer un nouvel article</a>
            </div>
        <?php else: ?>
            <div class="articles-grid">
                <?php foreach ($articles as $article): ?>
                    <div class="article-card">
                        <a href="show_article.php?auteur_id=<?php echo htmlspecialchars($article['auteur_id']); ?>" class="article-link">
                            <?php if (!empty($article['imageArticle'])): ?>
                                <div class="article-image">
                                <img src="../../uploads/<?php echo htmlspecialchars($article['imageArticle']); ?>" alt="Image de l'article">
                                </div>
                            <?php endif; ?>
                            <div class="article-content">
                                <h3><?php echo htmlspecialchars($article['titre']); ?></h3>
                                <p class="article-category">
                                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($article['categorie']); ?>
                                </p>
                                <p class="article-date">
                                    <i class="fas fa-calendar-alt"></i> 
                                    <?php echo date('d/m/Y', strtotime($article['date_article'])); ?>
                                </p>
                            </div>
                        </a>
                        <div class="article-actions">
                            <a href="edit.php?auteur_id=<?php echo htmlspecialchars($article['auteur_id']); ?>" class="clear-button" style="padding: 8px 15px; margin-right: 10px; text-decoration: none;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?auteur_id=<?php echo htmlspecialchars($article['auteur_id']); ?>" class="submit-button" style="padding: 8px 15px; text-decoration: none; background-color: #dc3545;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet équipement ?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</body>
</html>