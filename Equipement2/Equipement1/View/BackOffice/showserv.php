<?php
include_once 'C:\xampp\htdocs\hootelia\Equipement\config.php';
include_once 'C:\xampp\htdocs\hootelia\Equipement\Controller\servivecontrolle.php';

$pdo = config::getConnexion();
$services = afficherServices($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Services</title>
    <link rel="stylesheet" href="stylesback.css">
</head>
<style>
    .modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.9); /* White semi-transparent backdrop */
    overflow: auto;
}

.modal-content {
    background-color: white;
    margin: 10% auto;
    padding: 2.5rem;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    animation: modalOpen 0.3s ease-out;
    border: 1px solid #f0f0f0;
}

@keyframes modalOpen {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.8rem;
}

.modal-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #222;
    margin: 0;
}

.close-modal {
    background: transparent;
    border: none;
    color: #999;
    font-size: 1.8rem;
    cursor: pointer;
    transition: color 0.2s;
    line-height: 1;
    padding: 0.25rem;
}

.close-modal:hover {
    color: #222;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #555;
    font-size: 0.95rem;
}

.form-control {
    width: 100%;
    padding: 0.8rem;
    background-color: white;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    color: #333;
    font-size: 1rem;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #aaa;
    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
}

.form-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #f5f5f5;
}

/* Button styles for the footer */
.form-footer button {
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid #e0e0e0;
    background-color: white;
    color: #333;
}

.form-footer button:last-child {
    background-color: #222;
    border-color: #222;
    color: white;
}

.form-footer button:hover {
    background-color: #f9f9f9;
}

.form-footer button:last-child:hover {
    background-color: #111;
}
</style>
<body>

<h2>Liste des Services</h2>

<?php if ($services): ?>
    <table border='1' cellpadding='10' cellspacing='0'>
        <tr>
            <th>#</th>
            <th>Title</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>

        <?php $count = 1; foreach ($services as $service): ?>
            <tr>
                <td><?= $count ?></td>
                <td><?= htmlspecialchars($service['title']) ?></td>
                <td><?= htmlspecialchars($service['quantity']) ?></td>
                <td><?= htmlspecialchars($service['price']) ?></td>
                <td><?= htmlspecialchars($service['description']) ?></td>
                <td>
                    <a href="#" 
                       class="action-btn edit-btn"
                       data-id="<?= $service['id'] ?>" 
                       data-title="<?= htmlspecialchars($service['title']) ?>"
                       data-quantity="<?= htmlspecialchars($service['quantity']) ?>"
                       data-price="<?= htmlspecialchars($service['price']) ?>"
                       data-description="<?= htmlspecialchars($service['description']) ?>">
                        Modifier
                    </a>
                    <a class="action-btn delete-btn" 
                       href="deleteserv.php?id=<?= $service['id'] ?>" 
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce service ?');">
                        Supprimer
                    </a>
                </td>
            </tr>
        <?php $count++; endforeach; ?>
    </table>
<?php else: ?>
    <p>Aucun service trouvé.</p>
<?php endif; ?>

<!-- EDIT FORM MODAL -->
<form action="updateserve.php" method="POST" enctype="multipart/form-data" id="edit-form" style="background-color:white;">
    <div id="edit-service-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Modifier Service</div>
                <button type="button" class="close-modal">&times;</button>
            </div>

            <input type="hidden" name="service_id" id="service_id"> 

            <div class="form-group">
                <label for="edit-title">Titre</label>
                <input type="text" id="edit-title" name="edit-title" class="form-control" required readonly>
            </div>

            <div class="form-group">
                <label for="edit-quantity">Quantité</label>
                <input type="number" id="edit-quantity" name="edit-quantity" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="edit-price">Prix</label>
                <input type="number" id="edit-price" name="edit-price" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="edit-description">Description</label>
                <textarea id="edit-description" name="edit-description" class="form-control" require readonly></textarea>
            </div>

            <div class="form-footer">
                <button type="button" class="btn btn-secondary" id="cancel-edit">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </div>
    </div>
</form>

<script src="back.js"></script>
</body>
</html>
