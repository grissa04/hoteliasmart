// Main Service Card Handler
class ServiceCardHandler {
    constructor(config) {
      this.serviceCard = document.getElementById(config.cardId);
      this.contactForm = document.getElementById(config.formId);
      this.blurOverlay = document.getElementById(config.overlayId);
      this.closeFormBtn = document.getElementById(config.closeBtnId);
      this.priceCalculator = config.priceCalculator || null;
      this.validator = config.validator || null;
  
      this.init();
    }
  
    init() {
      // Open form when card is clicked
      this.serviceCard?.addEventListener("click", () => this.openForm());
  
      // Close form when close button is clicked
      this.closeFormBtn?.addEventListener("click", () => this.closeForm());
  
      // Close form when clicking outside
      document.addEventListener("click", (event) => {
        if (!this.contactForm?.contains(event.target) && 
            !this.blurOverlay?.contains(event.target) && 
            !this.serviceCard?.contains(event.target)) {
          this.closeForm();
        }
      });
  
      // Initialize price calculator if exists
      if (this.priceCalculator) {
        this.priceCalculator.init();
      }
  
      // Initialize form validation if exists
      if (this.validator) {
        this.validator.init();
      }
    }
  
    openForm() {
      this.contactForm.style.display = "block";
      this.blurOverlay.style.display = "block";
    }
  
    closeForm() {
      this.contactForm.style.display = "none";
      this.blurOverlay.style.display = "none";
    }
  }
  
  // Price Calculator for Solar Service
  class SolarPriceCalculator {
    constructor() {
      this.solarNumberInput = document.getElementById("solarNumber");
      this.priceInput = document.getElementById("price");
      this.unitPrice = 500;
    }
  
    init() {
      this.solarNumberInput?.addEventListener("input", () => this.calculatePrice());
    }
  
    calculatePrice() {
      this.priceInput.value = this.solarNumberInput.value * this.unitPrice;
    }
  }
  
  // Price Calculator for Water Service
  class WaterPriceCalculator {
    constructor() {
      this.tapsInput = document.getElementById('taps');
      this.showerheadsInput = document.getElementById('showerheads');
      this.toiletsInput = document.getElementById('toilets');
      this.priceInput = document.getElementById('price_water');
      this.pricePerTap = 10;
      this.pricePerShowerhead = 15;
      this.pricePerToilet = 20;
    }
  
    init() {
      this.tapsInput?.addEventListener('input', () => this.calculatePrice());
      this.showerheadsInput?.addEventListener('input', () => this.calculatePrice());
      this.toiletsInput?.addEventListener('input', () => this.calculatePrice());
    }
  
    calculatePrice() {
      const taps = parseInt(this.tapsInput.value) || 0;
      const showerheads = parseInt(this.showerheadsInput.value) || 0;
      const toilets = parseInt(this.toiletsInput.value) || 0;
  
      const totalPrice = (taps * this.pricePerTap) + 
                        (showerheads * this.pricePerShowerhead) + 
                        (toilets * this.pricePerToilet);
      this.priceInput.value = totalPrice.toFixed(2);
    }
  }
  
  // Price Calculator for Cleaning Service
  class CleaningPriceCalculator {
    constructor() {
      this.ecoSoapInput = document.getElementById('eco_soap');
      this.disinfectantInput = document.getElementById('disinfectant');
      this.priceInput = document.getElementById('total_price');
      this.errorElement = document.getElementById('errorCleaning');
      this.ecoSoapPrice = 3;
      this.disinfectantPrice = 7.5;
    }
  
    init() {
      this.ecoSoapInput?.addEventListener('input', () => this.calculatePrice());
      this.disinfectantInput?.addEventListener('input', () => this.calculatePrice());
    }
  
    calculatePrice() {
      this.errorElement.style.display = "none";
      this.errorElement.textContent = "";
  
      const ecoSoap = parseFloat(this.ecoSoapInput.value);
      const disinfectant = parseFloat(this.disinfectantInput.value);
  
      if (isNaN(ecoSoap) || isNaN(disinfectant)) {
        this.errorElement.style.display = "block";
        this.errorElement.textContent = "Please enter valid numbers for Eco Soap and Disinfectant.";
        this.priceInput.value = "";
        return;
      }
  
      const totalPrice = (ecoSoap * this.ecoSoapPrice) + (disinfectant * this.disinfectantPrice);
      this.priceInput.value = totalPrice.toFixed(2);
    }
  }
  
  // Form Validators
  // Updated Form Validators to properly prevent submission

class SolarFormValidator {
    constructor() {
      this.form = document.querySelector("#contactForm form");
      this.solarNumberInput = document.getElementById("solarNumber");
      this.priceInput = document.getElementById("price");
      this.errorDiv = document.getElementById("erreur");
    }
  
    init() {
      this.form?.addEventListener("submit", (e) => this.validate(e));
    }
  
    validate(e) {
      let isValid = true;
      const solarNumber = this.solarNumberInput.value.trim();
      const price = this.priceInput.value.trim();
  
      // Reset error message
      this.errorDiv.textContent = "";
  
      if (solarNumber === "") {
        this.errorDiv.textContent = "Please enter the number of solar panels.";
        this.solarNumberInput.focus();
        isValid = false;
      } else if (!/^[0-9]+$/.test(solarNumber)) {
        this.errorDiv.textContent = "Only numbers are allowed.";
        this.solarNumberInput.focus();
        isValid = false;
      }
  
      if (price === "" || isNaN(price)) {
        this.errorDiv.textContent = this.errorDiv.textContent 
          ? this.errorDiv.textContent + " Also, please enter a valid price." 
          : "Please enter a valid price.";
        if (isValid) this.priceInput.focus();
        isValid = false;
      }
  
      if (!isValid) {
        e.preventDefault();
      }
      // If valid, form will submit normally
    }
  }
  
  class WaterFormValidator {
    constructor() {
      this.form = document.querySelector("#contactForm_water form");
      this.tapsInput = document.getElementById('taps');
      this.showerheadsInput = document.getElementById('showerheads');
      this.toiletsInput = document.getElementById('toilets');
      this.errorElement = document.getElementById('erreur_water');
    }
  
    init() {
      this.form?.addEventListener("submit", (e) => this.validate(e));
    }
  
    validate(e) {
      let isValid = true;
      this.errorElement.textContent = "";
  
      const validateField = (input, fieldName) => {
        if (!input.value.trim() || isNaN(input.value)) {
          this.errorElement.textContent = `Please enter a valid number for ${fieldName}.`;
          if (isValid) input.focus();
          isValid = false;
        }
      };
  
      validateField(this.tapsInput, "taps");
      validateField(this.showerheadsInput, "showerheads");
      validateField(this.toiletsInput, "toilets");
  
      if (!isValid) {
        e.preventDefault();
      }
      // If valid, form will submit normally
    }
  }
  
  class CleaningFormValidator {
    constructor() {
      this.form = document.querySelector("#contactForm_cleaning form");
      this.ecoSoapInput = document.getElementById('eco_soap');
      this.disinfectantInput = document.getElementById('disinfectant');
      this.errorElement = document.getElementById('errorCleaning');
    }
  
    init() {
      this.form?.addEventListener("submit", (e) => this.validate(e));
    }
  
    validate(e) {
      let isValid = true;
      this.errorElement.style.display = "none";
      this.errorElement.textContent = "";
  
      const ecoSoap = parseFloat(this.ecoSoapInput.value);
      const disinfectant = parseFloat(this.disinfectantInput.value);
  
      if (isNaN(ecoSoap) || ecoSoap <= 0) {
        this.errorElement.style.display = "block";
        this.errorElement.textContent = "Please enter a valid quantity for Eco Soap (must be greater than 0).";
        this.ecoSoapInput.focus();
        isValid = false;
      }
  
      if (isNaN(disinfectant) || disinfectant <= 0) {
        this.errorElement.style.display = "block";
        this.errorElement.textContent = this.errorElement.textContent 
          ? this.errorElement.textContent + " Also, please enter a valid quantity for Disinfectant." 
          : "Please enter a valid quantity for Disinfectant (must be greater than 0).";
        if (isValid) this.disinfectantInput.focus();
        isValid = false;
      }
  
      if (!isValid) {
        e.preventDefault();
      }
      // If valid, form will submit normally
    }
  }
  
  // Update the initialization to include all validators
  document.addEventListener('DOMContentLoaded', () => {
    // Solar Service
    new ServiceCardHandler({
      cardId: "serviceCard",
      formId: "contactForm",
      overlayId: "blurOverlay",
      closeBtnId: "closeFormBtn",
      priceCalculator: new SolarPriceCalculator(),
      validator: new SolarFormValidator()
    });
  
    // Water Service
    new ServiceCardHandler({
      cardId: "serviceCard_water",
      formId: "contactForm_water",
      overlayId: "blurOverlay_water",
      closeBtnId: "closeFormBtn_water",
      priceCalculator: new WaterPriceCalculator(),
      validator: new WaterFormValidator()
    });
  
    // Cleaning Service
    new ServiceCardHandler({
      cardId: "serviceCard_cleaning",
      formId: "contactForm_cleaning",
      overlayId: "blurOverlay_cleaning",
      closeBtnId: "closeFormBtn_cleaning",
      priceCalculator: new CleaningPriceCalculator(),
      validator: new CleaningFormValidator()
    });
  });


// Add to your existing ServiceCardHandler class
// No changes needed if you already have it

class WastePriceCalculator {
    constructor() {
        this.personneInput = document.getElementById('personne');
        this.priceInput = document.getElementById('price_waste');
        this.pricePerPerson = 20;
        
        // Debugging
        console.log('Price Calculator Elements:', {
            personneInput: this.personneInput,
            priceInput: this.priceInput
        });
    }

    init() {
        if (!this.personneInput || !this.priceInput) {
            console.error('Missing required elements for price calculator');
            return;
        }

        this.personneInput.addEventListener('input', () => {
            console.log('Input event triggered on personneInput');
            this.calculatePrice();
            // Clear any existing errors
            const errorDiv = document.getElementById('erreurwaste');
            if (errorDiv) {
                errorDiv.textContent = '';
                errorDiv.style.display = 'none';
            }
        });

        // Trigger initial calculation if value exists
        if (this.personneInput.value) {
            this.calculatePrice();
        }
    }

    calculatePrice() {
        const rawValue = this.personneInput.value;
        const personnes = parseInt(rawValue) || 0;
        const totalPrice = (personnes * this.pricePerPerson).toFixed(2);
        
        console.log(`Calculating: ${personnes} * ${this.pricePerPerson} = ${totalPrice}`);
        
        this.priceInput.value = totalPrice;
        console.log('Updated price input value:', this.priceInput.value);
    }
}

class WasteFormValidator {
    constructor() {
        this.form = document.getElementById('form_waste');
        this.personneInput = document.getElementById('personne');
        this.errorDiv = document.getElementById('erreurwaste');
        
        // Debugging
        console.log('Validator Elements:', {
            form: this.form,
            personneInput: this.personneInput,
            errorDiv: this.errorDiv
        });
    }

    init() {
        if (!this.form) {
            console.error('Form element not found');
            return;
        }

        this.form.addEventListener('submit', (e) => {
            console.log('Form submission intercepted');
            this.validate(e);
        });
    }

    validate(e) {
        const personnes = this.personneInput.value.trim();
        console.log('Validating value:', personnes);
        
        // Reset error state
        if (this.errorDiv) {
            this.errorDiv.textContent = '';
            this.errorDiv.style.display = 'none';
        }

        let isValid = true;

        if (!personnes) {
            this.showError('Veuillez entrer le nombre de personnes');
            isValid = false;
        } else if (!/^\d+$/.test(personnes)) {
            this.showError('Le nombre de personnes doit être un nombre entier');
            isValid = false;
        } else if (parseInt(personnes) <= 0) {
            this.showError('Le nombre de personnes doit être supérieur à 0');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            console.log('Validation failed - preventing submission');
        } else {
            console.log('Validation passed - allowing submission');
        }
    }

    showError(message) {
        console.log('Displaying error:', message);
        if (this.errorDiv) {
            this.errorDiv.textContent = message;
            this.errorDiv.style.display = 'block';
            this.errorDiv.style.color = 'red';
            this.errorDiv.style.margin = '10px 0';
            this.errorDiv.style.padding = '5px';
            this.errorDiv.style.borderRadius = '4px';
            this.errorDiv.style.backgroundColor = '#ffeeee';
        }
        if (this.personneInput) {
            this.personneInput.focus();
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM fully loaded - initializing waste service');
    
    try {
        const wasteService = new ServiceCardHandler({
            cardId: "serviceCard_waste",
            formId: "contactForm_waste", 
            overlayId: "blurOverlay_waste",
            closeBtnId: "closewasteFormBtn",
            priceCalculator: new WastePriceCalculator(),
            validator: new WasteFormValidator()
        });

        // Test the calculation manually
        const testInput = document.getElementById('personne');
        if (testInput) {
            const event = new Event('input');
            testInput.dispatchEvent(event);
            console.log('Test calculation triggered');
        }
    } catch (error) {
        console.error('Error initializing waste service:', error);
    }
});


class SmartTechPriceCalculator {
    constructor() {
        this.chambresInput = document.getElementById('chambres');
        this.priceInput = document.getElementById('price_tech');
        this.pricePerRoom = 50; // You can adjust this value as needed

        console.log('SmartTech Price Calculator Elements:', {
            chambresInput: this.chambresInput,
            priceInput: this.priceInput
        });
    }

    init() {
        if (!this.chambresInput || !this.priceInput) {
            console.error('Missing required elements for SmartTech price calculator');
            return;
        }

        this.chambresInput.addEventListener('input', () => {
            console.log('Input event triggered on chambresInput');
            this.calculatePrice();
            const errorDiv = document.getElementById('erreurtech');
            if (errorDiv) {
                errorDiv.textContent = '';
                errorDiv.style.display = 'none';
            }
        });

        if (this.chambresInput.value) {
            this.calculatePrice();
        }
    }

    calculatePrice() {
        const rawValue = this.chambresInput.value;
        const chambres = parseInt(rawValue) || 0;
        const totalPrice = (chambres * this.pricePerRoom).toFixed(2);

        console.log(`Calculating: ${chambres} * ${this.pricePerRoom} = ${totalPrice}`);

        this.priceInput.value = totalPrice;
        console.log('Updated price input value:', this.priceInput.value);
    }
}

class SmartTechFormValidator {
    constructor() {
        this.form = document.getElementById('form_tech');
        this.chambresInput = document.getElementById('chambres');
        this.errorDiv = document.getElementById('erreurtech');

        console.log('SmartTech Validator Elements:', {
            form: this.form,
            chambresInput: this.chambresInput,
            errorDiv: this.errorDiv
        });
    }

    init() {
        if (!this.form) {
            console.error('SmartTech form element not found');
            return;
        }

        this.form.addEventListener('submit', (e) => {
            console.log('SmartTech form submission intercepted');
            this.validate(e);
        });
    }

    validate(e) {
        const chambres = this.chambresInput.value.trim();
        console.log('Validating value:', chambres);

        if (this.errorDiv) {
            this.errorDiv.textContent = '';
            this.errorDiv.style.display = 'none';
        }

        let isValid = true;

        if (!chambres) {
            this.showError('Veuillez entrer le nombre de chambres');
            isValid = false;
        } else if (!/^\d+$/.test(chambres)) {
            this.showError('Le nombre de chambres doit être un nombre entier');
            isValid = false;
        } else if (parseInt(chambres) <= 0) {
            this.showError('Le nombre de chambres doit être supérieur à 0');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            console.log('Validation failed - preventing submission');
        } else {
            console.log('Validation passed - allowing submission');
        }
    }

    showError(message) {
        console.log('Displaying error:', message);
        if (this.errorDiv) {
            this.errorDiv.textContent = message;
            this.errorDiv.style.display = 'block';
            this.errorDiv.style.color = 'red';
            this.errorDiv.style.margin = '10px 0';
            this.errorDiv.style.padding = '5px';
            this.errorDiv.style.borderRadius = '4px';
            this.errorDiv.style.backgroundColor = '#ffeeee';
        }
        if (this.chambresInput) {
            this.chambresInput.focus();
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM fully loaded - initializing SmartTech service');

    try {
        const smartTechService = new ServiceCardHandler({
            cardId: "serviceCard_tech",
            formId: "contactForm_tech",
            overlayId: "blurOverlay_tech",
            closeBtnId: "closetechFormBtn",
            priceCalculator: new SmartTechPriceCalculator(),
            validator: new SmartTechFormValidator()
        });

        // Test initial calculation
        const testInput = document.getElementById('chambres');
        if (testInput) {
            const event = new Event('input');
            testInput.dispatchEvent(event);
            console.log('SmartTech test calculation triggered');
        }
    } catch (error) {
        console.error('Error initializing SmartTech service:', error);
    }
});




class SmartJardinFormValidator {
    constructor() {
        this.form = document.getElementById('form_jard');
        this.jardinInput = document.getElementById('jardin');
        this.errorDiv = document.getElementById('erreurjard');

        console.log('SmartJardin Validator Elements:', {
            form: this.form,
            jardinInput: this.jardinInput,
            errorDiv: this.errorDiv
        });
    }

    init() {
        if (!this.form) {
            console.error('SmartJardin form element not found');
            return;
        }

        this.form.addEventListener('submit', (e) => {
            console.log('SmartJardin form submission intercepted');
            this.validate(e);
        });
    }

    validate(e) {
        const jardins = this.jardinInput.value.trim();
        console.log('Validating value:', jardins);

        if (this.errorDiv) {
            this.errorDiv.textContent = '';
            this.errorDiv.style.display = 'none';
        }

        let isValid = true;

        if (!jardins) {
            this.showError('Veuillez entrer le nombre de jardins');
            isValid = false;
        } else if (!/^\d+$/.test(jardins)) {
            this.showError('Le nombre de jardins doit être un nombre entier');
            isValid = false;
        } else if (parseInt(jardins) <= 0) {
            this.showError('Le nombre de jardins doit être supérieur à 0');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            console.log('Validation failed - preventing submission');
        } else {
            console.log('Validation passed - allowing submission');
        }
    }

    showError(message) {
        console.log('Displaying error:', message);
        if (this.errorDiv) {
            this.errorDiv.textContent = message;
            this.errorDiv.style.display = 'block';
            this.errorDiv.style.color = 'red';
            this.errorDiv.style.margin = '10px 0';
            this.errorDiv.style.padding = '5px';
            this.errorDiv.style.borderRadius = '4px';
            this.errorDiv.style.backgroundColor = '#ffeeee';
        }
        if (this.jardinInput) {
            this.jardinInput.focus();
        }
    }
}
class SmartJardinPriceCalculator {
    constructor() {
        this.jardinInput = document.getElementById('jardin');
        this.priceInput = document.getElementById('price_jardin');

        this.PRICE_PER_JARDIN = 50; 

        console.log('SmartJardin PriceCalculator Elements:', {
            jardinInput: this.jardinInput,
            priceInput: this.priceInput
        });
    }

    init() {
        if (!this.jardinInput) {
            console.error('Jardin input element not found');
            return;
        }

        this.jardinInput.addEventListener('input', () => {
            this.calculatePrice();
        });
    }

    calculatePrice() {
        const jardins = this.jardinInput.value.trim();
        console.log('Calculating price for:', jardins);

        if (!jardins || isNaN(jardins) || jardins <= 0) {
            this.priceInput.value = '';
            return;
        }

        const totalPrice = jardins * this.PRICE_PER_JARDIN;
        this.priceInput.value = totalPrice.toFixed(2);
    }
}


document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM fully loaded - initializing SmartJardin service');

    try {
        const smartJardinService = new ServiceCardHandler({
            cardId: "serviceCard_jard",
            formId: "contactForm_jard",
            overlayId: "blurOverlay_jard",
            closeBtnId: "closejardFormBtn",
            priceCalculator: new SmartJardinPriceCalculator(),
            validator: new SmartJardinFormValidator()
        });

        // Test initial calculation
        const testInput = document.getElementById('jardin');
        if (testInput) {
            const event = new Event('input');
            testInput.dispatchEvent(event);
            console.log('SmartJardin test calculation triggered');
        }
    } catch (error) {
        console.error('Error initializing SmartJardin service:', error);
    }
});


