<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controllers/ArticleController.php';
require_once __DIR__ . '/../../models/Article.php';

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

$controller = new ArticleController();
$article = null;

// Récupérer l'article à modifier
if (isset($_GET['auteur_id'])) {
    $article = $controller->getArticleByAuteurId($_GET['auteur_id']);
    if (!$article) {
        header('Location: show.php?error=article_not_found');
        exit();
    }
} else {
    header('Location: show.php?error=missing_id');
    exit();
}

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $article = new Article(
        $_POST['titre'],
        $_POST['contenu'],
        $_POST['auteur_id'],
        date('Y-m-d H:i:s'),
        $_POST['categorie'],
        $_POST['imageArticle'] ?? null,
        $_POST['shared_from'] ?? null
    );
    $article->setAuteurId($_POST['update_id']);

    if ($controller->updateArticle($article, isset($_FILES['imageArticle']) ? $_FILES['imageArticle'] : null)) {
        header('Location: show.php?success=1');
        exit();
    }
    header('Location: edit.php?auteur_id=' . $_POST['update_id'] . '&error=1');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="stylesback.css">
    <title>Modifier l'Article</title>
    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            display: none;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div id="updateNotification" class="notification">
        <i class="fas fa-check-circle"></i> L'article a été mis à jour avec succès!
    </div>
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
        <h1><i class="fas fa-edit"></i> Modifier l'Article</h1>
    </div>

    <section class="form-section">
        <div class="form-container">
            <h2>Modifier l'Article</h2>
            <form id="articleForm" method="POST" enctype="multipart/form-data" onsubmit="return validateForm(event);">
                <input type="hidden" name="update_id" value="<?= htmlspecialchars($article['auteur_id'] ?? '') ?>">
                <input type="hidden" name="auteur_id" value="<?= htmlspecialchars($article['auteur_id'] ?? '') ?>">
                
                <div class="form-group">
                    <label for="titre">Titre</label>
                    <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($article['titre'] ?? '') ?>">
                    <div class="error" id="titreError" style="color: red;"></div>
                </div>

                <div class="form-group">
                    <label for="contenu">Contenu</label>
                    <textarea id="contenu" name="contenu" rows="5"><?= htmlspecialchars($article['contenu'] ?? '') ?></textarea>
                    <div class="error" id="contenuError" style="color: red;"></div>
                </div>

                <div class="form-group">
                    <label for="categorie">Catégorie</label>
                    <select id="categorie" name="categorie">
                        <option value="">Sélectionnez une catégorie</option>
                        <?php
                        $categories = ['Services', 'Equipements'];
                        foreach ($categories as $cat) {
                            $selected = (isset($article['categorie']) && $article['categorie'] === $cat) ? 'selected' : '';
                            echo "<option value=\"".htmlspecialchars($cat)."\" $selected>".htmlspecialchars($cat)."</option>";
                        }
                        ?>
                    </select>
                    <div class="error" id="categorieError" style="color: red;"></div>
                </div>

                <div class="form-group">
                    <label for="imageArticle">Image de l'article</label>
                    <input type="file" id="imageArticle" name="imageArticle" accept="image/*">
                    <?php if (!empty($article['imageArticle'])): ?>
                        <p class="current-image">Image actuelle: <?= htmlspecialchars($article['imageArticle']) ?></p>
                        <input type="hidden" name="current_image" value="<?= htmlspecialchars($article['imageArticle']) ?>">
                    <?php endif; ?>
                    <div class="error" id="imageError" style="color: red;"></div>
                </div>

                <div class="form-group">
                    <label for="shared_from">Partagé depuis</label>
                    <input type="text" id="shared_from" name="shared_from" value="<?= htmlspecialchars($article['shared_from'] ?? '') ?>">
                </div>

                <div class="button-group">
                    <button type="submit" class="submit-button"><i class="fas fa-save"></i> Mettre à jour l'Article</button>
                    <button type="button" class="clear-button" onclick="clearForm()"><i class="fas fa-undo"></i> Réinitialiser</button>
                </div>
            </form>
        </div>
    </section>

    <script src="js/validation.js"></script>
    <script>
        // Afficher la notification si le paramètre success est présent dans l'URL
        if (window.location.href.includes('success=1')) {
            const notification = document.getElementById('updateNotification');
            notification.style.display = 'block';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>