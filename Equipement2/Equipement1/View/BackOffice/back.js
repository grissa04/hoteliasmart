class ServiceFormValidator {
    constructor() {
        this.formErrors = {};
        this.modal = document.getElementById('edit-service-modal');
        this.form = document.getElementById('edit-form');
        this.fields = ['title', 'quantity', 'price', 'description'];
        this.init();
    }

    init() {
        this.initEditButtons();
        this.initModalControls();
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
        this.fields.forEach(field => {
            const input = document.getElementById(`edit-${field}`);
            if (input) {
                input.addEventListener('input', () => {
                    const error = this[`validate${field.charAt(0).toUpperCase() + field.slice(1)}`](input.value);
                    this.formErrors[field] = error;
                    this.showError(`edit-${field}`, error);
                });
            }
        });

        this.form?.addEventListener('submit', (event) => this.handleSubmit(event));
    }

    handleSubmit(event) {
        this.fields.forEach(field => {
            const input = document.getElementById(`edit-${field}`);
            if (input) {
                const error = this[`validate${field.charAt(0).toUpperCase() + field.slice(1)}`](input.value);
                this.formErrors[field] = error;
                this.showError(`edit-${field}`, error);
            }
        });

        if (Object.values(this.formErrors).some(error => error !== "")) {
            event.preventDefault();
        }
    }

    initEditButtons() {
        const editButtons = document.querySelectorAll('.edit-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', () => this.handleEditClick(button));
        });
    }

    handleEditClick(button) {
        const serviceData = {
            id: button.getAttribute('data-id'),
            title: button.getAttribute('data-title'),
            quantity: button.getAttribute('data-quantity'),
            price: button.getAttribute('data-price'),
            description: button.getAttribute('data-description')
        };

        this.populateForm(serviceData);
        this.clearErrors();
        this.showModal();
    }

    populateForm(data) {
        document.getElementById('service_id').value = data.id;
        this.fields.forEach(field => {
            const input = document.getElementById(`edit-${field}`);
            if (input) {
                input.value = data[field] || '';
            }
        });
    }

    clearErrors() {
        this.formErrors = {};
        this.fields.forEach(field => {
            this.showError(`edit-${field}`, '');
        });
    }

    initModalControls() {
        const closeButton = document.querySelector('.close-modal');
        const cancelButton = document.getElementById('cancel-edit');

        closeButton?.addEventListener('click', (e) => this.handleModalClose(e));
        cancelButton?.addEventListener('click', (e) => this.handleModalClose(e));

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.handleModalClose(e);
            }
        });
    }

    handleModalClose(event) {
        event.preventDefault();
        this.hideModal();
        this.clearErrors();
    }

    showModal() {
        if (this.modal) {
            this.modal.style.display = 'block';
        }
    }

    hideModal() {
        if (this.modal) {
            this.modal.style.display = 'none';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new ServiceFormValidator();
});
