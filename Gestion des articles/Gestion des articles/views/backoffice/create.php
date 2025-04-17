<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controllers/ArticleController.php';

$controller = new ArticleController();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_article'])) {
    require_once __DIR__ . '/../../models/Article.php';
    
    $article = new Article(
        $_POST['titre'],
        $_POST['contenu'],
        null,
        date('Y-m-d H:i:s'),  // Current date and time
        $_POST['categorie'],
        $_POST['imageArticle'] ?? null,
        $_POST['shared_from'] ?? null
    );
    
    if ($controller->addArticle($article, $_FILES['imageArticle'])) {
        header('Location: show.php');
        exit();
    }
    //exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ajouter un Article</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="stylesback.css">
    <script src="js/validation.js" defer></script>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-nav-container">
            <div class="admin-logo">
                <img src="../FrontOffice/assets/HS.png" alt="Hotelia Smart Logo">
                <span class="hotelia">HOTELIA</span>
                <span class="smart">SMART</span>
            </div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="create.php" class="active"><i class="fas fa-plus-circle"></i> Ajouter</a></li>
                    <li><a href="show.php"><i class="fas fa-list"></i> Articles</a></li>
                    <li><a href="historique.php"><i class="fas fa-history"></i> Historique</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Paramètres</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Admin Title Section -->
    <div class="admin-title">
        <h1><i class="fas fa-plus-circle"></i> Ajouter un Article</h1>
    </div>

    <section class="form-section">
        <div class="form-container">
            <h2>Nouvel Article</h2>
            <form id="articleForm" method="POST" enctype="multipart/form-data" onsubmit="return validateCreateForm(event)">
                <input type="hidden" name="add_article" value="1">
                
                <div class="form-group">
                    <label for="titre">Titre</label>
                    <input type="text" id="titre" name="titre" placeholder="Entrez le titre de l'article">
                    <div class="error" id="titreError" style="color: red;"></div>
                </div>

                <div class="form-group">
                    <label for="contenu">Contenu</label>
                    <textarea id="contenu" name="contenu" rows="5" placeholder="Entrez le contenu de l'article"></textarea>
                    <div class="error" id="contenuError" style="color: red;"></div>
                </div>


                <div class="form-group">
                    <label for="categorie">Catégorie</label>
                    <select id="categorie" name="categorie">
                        <option value="">Sélectionnez une catégorie</option>
                        <option value="Services">Services</option>
                        <option value="Equipements">Equipements</option>
                        
                    </select>
                    <div class="error" id="categorieError" style="color: red;"></div>
                </div>

                <div class="form-group">
                    <label for="imageArticle">Image de l'article</label>
                    <input type="file" id="imageArticle" name="imageArticle" accept="image/*">
                </div>

                <div class="error" id="imageError" style="color: red;"></div>
                </div>

                <div class="form-group">
                    <label for="shared_from">Partagé depuis</label>
                    <input type="text" id="shared_from" name="shared_from" placeholder="Source de partage (optionnel)">
                </div>

                <div class="button-group">
                    <button type="submit" class="submit-button"><i class="fas fa-save"></i> Enregistrer</button>
                    <button type="button" class="clear-button" onclick="clearForm()"><i class="fas fa-undo"></i> Réinitialiser</button>
                </div>
            </form>
        </div>
    </section>

</body>
</html>