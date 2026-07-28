/* ============================================================
   FORM VALIDATION MODULE
   ============================================================ */

const Validation = {
    isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(String(email).toLowerCase());
    },

    isValidPhone(phone) {
        const re = /^[0-9]{10,12}$/;
        return re.test(String(phone).replace(/[\s\-\+]/g, ''));
    },

    showError(inputElement, message) {
        this.clearError(inputElement);
        inputElement.classList.add('is-invalid');
        inputElement.style.borderColor = 'var(--danger)';

        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error-msg';
        errorDiv.style.color = 'var(--danger)';
        errorDiv.style.fontSize = '0.785rem';
        errorDiv.style.marginTop = '0.25rem';
        errorDiv.textContent = message;

        inputElement.parentElement.appendChild(errorDiv);
    },

    clearError(inputElement) {
        inputElement.classList.remove('is-invalid');
        inputElement.style.borderColor = '';
        const existingError = inputElement.parentElement.querySelector('.form-error-msg');
        if (existingError) {
            existingError.remove();
        }
    },

    validateForm(formElement) {
        let isValid = true;
        const requiredInputs = formElement.querySelectorAll('[required]');

        requiredInputs.forEach(input => {
            this.clearError(input);
            if (!input.value.trim()) {
                this.showError(input, 'This field is required');
                isValid = false;
            } else if (input.type === 'email' && !this.isValidEmail(input.value)) {
                this.showError(input, 'Please enter a valid email address');
                isValid = false;
            } else if (input.type === 'tel' && !this.isValidPhone(input.value)) {
                this.showError(input, 'Please enter a valid phone number');
                isValid = false;
            }
        });

        // Date check validation if present
        const checkInInput = formElement.querySelector('[name="check_in_date"]');
        const checkOutInput = formElement.querySelector('[name="check_out_date"]');

        if (checkInInput && checkOutInput && checkInInput.value && checkOutInput.value) {
            const checkIn = new Date(checkInInput.value);
            const checkOut = new Date(checkOutInput.value);

            if (checkOut <= checkIn) {
                this.showError(checkOutInput, 'Check-out date must be after check-in date');
                isValid = false;
            }
        }

        return isValid;
    }
};

document.addEventListener('DOMContentLoaded', function () {
    const validatedForms = document.querySelectorAll('form.needs-validation');
    validatedForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!Validation.validateForm(this)) {
                e.preventDefault();
            }
        });
    });
});
