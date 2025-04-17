// Messages d'erreur pour la validation du formulaire
const validationErrors = {
    titre: 'Le titre est requis (minimum 3 caractères)',
    contenu: 'Le contenu est requis (minimum 10 caractères)',
    categorie: 'Veuillez sélectionner une catégorie',
    imageArticle: 'Le fichier doit être une image valide (jpg, png, gif)',
   
};

// Fonction de validation commune
function validateCommonFields() {
    let isValid = true;

    // Réinitialiser les erreurs
    document.querySelectorAll('.error').forEach(el => el.style.display = 'none');

    // Valider le titre
    const titre = document.getElementById('titre').value.trim();
    if (titre.length < 3) {
        document.getElementById('titreError').textContent = validationErrors.titre;
        document.getElementById('titreError').style.display = 'block';
        isValid = false;
    }

    // Valider le contenu
    const contenu = document.getElementById('contenu').value.trim();
    if (contenu.length < 10) {
        document.getElementById('contenuError').textContent = validationErrors.contenu;
        document.getElementById('contenuError').style.display = 'block';
        isValid = false;
    }

    // Valider la catégorie
    const categorie = document.getElementById('categorie').value;
    if (!categorie) {
        document.getElementById('categorieError').textContent = validationErrors.categorie;
        document.getElementById('categorieError').style.display = 'block';
        isValid = false;
    }

    return isValid;
}

// Fonction de validation pour le formulaire de création
function validateForm(event, isCreate) {
    event.preventDefault();
    let isValid = validateCommonFields();

    // Valider le fichier image
    const imageFile = document.getElementById('imageArticle').files[0];
    if (isCreate && !imageFile) {
        document.getElementById('imageError').textContent = 'Une image est requise pour la création';
        document.getElementById('imageError').style.display = 'block';
        isValid = false;
    } else if (imageFile) {
        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        const maxSize = 2 * 1024 * 1024; // 2MB
        if (!validTypes.includes(imageFile.type)) {
            document.getElementById('imageError').textContent = validationErrors.imageArticle;
            document.getElementById('imageError').style.display = 'block';
            isValid = false;
        } else if (imageFile.size > maxSize) {
            document.getElementById('imageError').textContent = 'La taille de l\'image ne doit pas dépasser 2MB';
            document.getElementById('imageError').style.display = 'block';
            isValid = false;
        }
    }

    if (isValid) {
        document.getElementById('articleForm').submit();
        return true;
    }

    return false;
}

// Remplacer les appels aux fonctions de validation spécifiques par validateForm
// Exemple pour la création :
// document.getElementById('createForm').addEventListener('submit', (event) => validateForm(event, true));
// Exemple pour l'édition :
// document.getElementById('editForm').addEventListener('submit', (event) => validateForm(event, false));

// Fonction pour réinitialiser le formulaire
function clearForm() {
    const form = document.getElementById('articleForm');
    form.reset();
    document.querySelectorAll('.error').forEach(el => el.style.display = 'none');
}