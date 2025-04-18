class AddServiceFormValidator {
    constructor() {
        this.formErrors = {};
        this.form = document.querySelector('.service-form');
        this.fields = ['title', 'quantity', 'price', 'description'];
        this.init();
    }

    init() {
        this.initFormValidation();
    }

    validateTitle(value) {
        const trimmedValue = value.trim();
        if (trimmedValue.length < 3) {
            return "Le titre doit contenir au moins 3 caractères";
        }
        if (!/^[a-zA-ZÀ-ÿ0-9\s\-_]+$/.test(trimmedValue)) {
            return "Le titre ne peut contenir que des lettres, chiffres, espaces, tirets et underscores";
        }
        return "";
    }

    validateQuantity(value) {
        const numValue = parseInt(value);
        if (!value || isNaN(numValue) || numValue <= 0) {
            return "La quantité doit être un nombre entier positif";
        }
        return "";
    }

    validatePrice(value) {
        const numValue = parseFloat(value);
        if (!value || isNaN(numValue) || numValue <= 0) {
            return "Le prix doit être un nombre positif";
        }
        if (!/^\d+(\.\d{0,2})?$/.test(value)) {
            return "Le prix doit avoir au maximum 2 décimales";
        }
        return "";
    }

    validateDescription(value) {
        const trimmedValue = value.trim();
        if (trimmedValue.length < 10) {
            return "La description doit contenir au moins 10 caractères";
        }
        if (trimmedValue.length > 500) {
            return "La description ne doit pas dépasser 500 caractères";
        }
        return "";
    }

    showError(inputId, message) {
        const input = document.getElementById(inputId);
        if (!input) return;

        let errorDiv = input.nextElementSibling;
        if (!errorDiv || !errorDiv.classList.contains('error-message')) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.style.color = 'red';
            errorDiv.style.fontSize = '12px';
            errorDiv.style.marginTop = '5px';
            input.parentNode.insertBefore(errorDiv, input.nextSibling);
        }

        errorDiv.textContent = message;
        input.style.borderColor = message ? 'red' : '';
        input.style.transition = 'border-color 0.3s ease';
    }

    initFormValidation() {
        if (!this.form) return;

        this.fields.forEach(field => {
            const input = document.getElementById(field);
            if (input) {
                input.addEventListener('input', () => {
                    const error = this[`validate${field.charAt(0).toUpperCase() + field.slice(1)}`](input.value);
                    this.formErrors[field] = error;
                    this.showError(field, error);
                });
            }
        });

        this.form.addEventListener('submit', (event) => this.handleSubmit(event));
    }

    handleSubmit(event) {
        let hasErrors = false;

        this.fields.forEach(field => {
            const input = document.getElementById(field);
            if (input) {
                const error = this[`validate${field.charAt(0).toUpperCase() + field.slice(1)}`](input.value);
                this.formErrors[field] = error;
                this.showError(field, error);
                if (error) hasErrors = true;
            }
        });

        if (hasErrors) {
            event.preventDefault();
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new AddServiceFormValidator();
});