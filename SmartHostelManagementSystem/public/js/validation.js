/**
 * Form validation utilities
 */

function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function validatePhone(phone) {
    return /^[0-9]{10,15}$/.test(phone);
}

function validatePassword(password) {
    return password.length >= 6;
}

function validateRequired(value) {
    return value.trim().length > 0;
}

function validateMinLength(value, min) {
    return value.length >= min;
}

function validateMaxLength(value, max) {
    return value.length <= max;
}

function validateNumeric(value) {
    return /^\d+$/.test(value);
}

function validateConfirm(password, confirm) {
    return password === confirm;
}

function showFieldError(field, message) {
    const formGroup = field.closest('.form-group');
    if (!formGroup) return;

    // Remove existing error
    const existingError = formGroup.querySelector('.form-error');
    if (existingError) existingError.remove();

    formGroup.classList.add('has-error');

    const error = document.createElement('div');
    error.className = 'form-error';
    error.textContent = message;
    formGroup.appendChild(error);
}

function clearFieldErrors() {
    document.querySelectorAll('.form-group.has-error').forEach(el => {
        el.classList.remove('has-error');
        const error = el.querySelector('.form-error');
        if (error) error.remove();
    });
}

function validateForm(form, rules) {
    clearFieldErrors();
    let isValid = true;
    const errors = [];

    for (const [fieldName, ruleList] of Object.entries(rules)) {
        const field = form.querySelector(`[name="${fieldName}"]`);
        if (!field) continue;

        const value = field.value;
        const rulesArray = ruleList.split('|');

        for (const rule of rulesArray) {
            let [ruleName, ruleParam] = rule.split(':');

            switch (ruleName) {
                case 'required':
                    if (!validateRequired(value)) {
                        showFieldError(field, 'This field is required');
                        isValid = false;
                        errors.push(`${fieldName} is required`);
                    }
                    break;
                case 'email':
                    if (value && !validateEmail(value)) {
                        showFieldError(field, 'Please enter a valid email address');
                        isValid = false;
                        errors.push(`${fieldName} is invalid`);
                    }
                    break;
                case 'min':
                    if (value && !validateMinLength(value, parseInt(ruleParam))) {
                        showFieldError(field, `Must be at least ${ruleParam} characters`);
                        isValid = false;
                        errors.push(`${fieldName} must be at least ${ruleParam} characters`);
                    }
                    break;
                case 'max':
                    if (value && !validateMaxLength(value, parseInt(ruleParam))) {
                        showFieldError(field, `Must not exceed ${ruleParam} characters`);
                        isValid = false;
                        errors.push(`${fieldName} must not exceed ${ruleParam} characters`);
                    }
                    break;
                case 'numeric':
                    if (value && !validateNumeric(value)) {
                        showFieldError(field, 'Must be a number');
                        isValid = false;
                        errors.push(`${fieldName} must be a number`);
                    }
                    break;
                case 'confirmed':
                    const confirmField = form.querySelector(`[name="${fieldName}_confirmation"]`);
                    if (confirmField && !validateConfirm(value, confirmField.value)) {
                        showFieldError(field, 'Does not match confirmation');
                        showFieldError(confirmField, 'Does not match');
                        isValid = false;
                        errors.push(`${fieldName} confirmation does not match`);
                    }
                    break;
            }
        }
    }

    return { isValid, errors };
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-clear errors on input
    document.querySelectorAll('.form-group input, .form-group select, .form-group textarea').forEach(el => {
        el.addEventListener('input', function() {
            const formGroup = this.closest('.form-group');
            if (formGroup) {
                formGroup.classList.remove('has-error');
                const error = formGroup.querySelector('.form-error');
                if (error) error.remove();
            }
        });
    });
});