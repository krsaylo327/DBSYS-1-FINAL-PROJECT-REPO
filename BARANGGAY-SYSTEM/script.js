// =============================================
// BARANGAY SYSTEM - JAVASCRIPT VALIDATION
// =============================================

function validateResidentForm() {
    const firstName = document.querySelector('input[name="first_name"]');
    const lastName = document.querySelector('input[name="last_name"]');
    const birthDate = document.querySelector('input[name="birth_date"]');
    const gender = document.querySelector('select[name="gender"]');
    const household = document.querySelector('select[name="household_id"]');
    
    clearErrors();
    let isValid = true;
    let errors = [];
    
    if (firstName && firstName.value.trim() === '') {
        errors.push('First name is required.');
        showError(firstName);
        isValid = false;
    }
    
    if (lastName && lastName.value.trim() === '') {
        errors.push('Last name is required.');
        showError(lastName);
        isValid = false;
    }
    
    if (birthDate && birthDate.value === '') {
        errors.push('Birth date is required.');
        showError(birthDate);
        isValid = false;
    }
    
    if (gender && gender.value === '') {
        errors.push('Gender is required.');
        showError(gender);
        isValid = false;
    }
    
    if (household && household.value === '') {
        errors.push('Please select a household.');
        showError(household);
        isValid = false;
    }
    
    if (!isValid) {
        alert('Please fix the following errors:\n\n' + errors.join('\n'));
        return false;
    }
    
    return true;
}

function validateRequestForm() {
    const resident = document.querySelector('select[name="resident_id"]');
    const certificate = document.querySelector('select[name="certificate_id"]');
    const purpose = document.querySelector('textarea[name="purpose"]');
    
    clearErrors();
    let isValid = true;
    let errors = [];
    
    if (resident && resident.value === '') {
        errors.push('Please select a resident.');
        showError(resident);
        isValid = false;
    }
    
    if (certificate && certificate.value === '') {
        errors.push('Please select a certificate type.');
        showError(certificate);
        isValid = false;
    }
    
    if (purpose && purpose.value.trim() === '') {
        errors.push('Purpose is required.');
        showError(purpose);
        isValid = false;
    }
    
    if (!isValid) {
        alert('Please fix the following errors:\n\n' + errors.join('\n'));
        return false;
    }
    
    return true;
}

function showError(element) {
    if (element) {
        element.style.borderColor = '#dc3545';
        element.style.backgroundColor = '#fff0f0';
    }
}

function clearErrors() {
    document.querySelectorAll('.form-control, .form-select').forEach(el => {
        el.style.borderColor = '';
        el.style.backgroundColor = '';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.click();
            }
        }, 5000);
    });
    
    // Contact number auto-format
    const contactInput = document.querySelector('input[name="contact_number"]');
    if (contactInput) {
        contactInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
});