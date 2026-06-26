// Login validation
function validateLogin() {
    const username = document.getElementById('username');
    const password = document.getElementById('password');
    
    if (username.value.trim() === '') {
        alert('Please enter your username or email.');
        username.focus();
        return false;
    }
    if (password.value.trim() === '') {
        alert('Please enter your password.');
        password.focus();
        return false;
    }
    return true;
}

// Registration validation
function validateRegister() {
    const fullName = document.getElementById('full_name');
    const username = document.getElementById('username');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    
    if (fullName.value.trim() === '') {
        alert('Please enter your full name.');
        fullName.focus();
        return false;
    }
    if (username.value.trim() === '' || username.value.length < 3) {
        alert('Username must be at least 3 characters.');
        username.focus();
        return false;
    }
    if (email.value.trim() === '' || !email.value.includes('@')) {
        alert('Please enter a valid email address.');
        email.focus();
        return false;
    }
    if (password.value.trim() === '' || password.value.length < 6) {
        alert('Password must be at least 6 characters.');
        password.focus();
        return false;
    }
    return true;
}

// Vitals validation
function validateVitals() {
    const systolic = document.getElementById('systolic');
    const diastolic = document.getElementById('diastolic');
    
    if (systolic.value === '' || parseInt(systolic.value) < 70 || parseInt(systolic.value) > 250) {
        alert('Please enter a valid systolic BP (70-250).');
        systolic.focus();
        return false;
    }
    if (diastolic.value === '' || parseInt(diastolic.value) < 40 || parseInt(diastolic.value) > 160) {
        alert('Please enter a valid diastolic BP (40-160).');
        diastolic.focus();
        return false;
    }
    if (document.getElementById('heart_rate').value !== '') {
        const hr = parseInt(document.getElementById('heart_rate').value);
        if (hr < 30 || hr > 220) {
            alert('Heart rate must be between 30 and 220.');
            document.getElementById('heart_rate').focus();
            return false;
        }
    }
    if (document.getElementById('weight').value !== '') {
        const weight = parseFloat(document.getElementById('weight').value);
        if (weight < 20 || weight > 300) {
            alert('Weight must be between 20 and 300 kg.');
            document.getElementById('weight').focus();
            return false;
        }
    }
    return true;
}

// Medication validation
function validateMedication() {
    const name = document.getElementById('name');
    const dosage = document.getElementById('dosage');
    const frequency = document.getElementById('frequency');
    const startDate = document.getElementById('start_date');
    
    if (name.value.trim() === '') {
        alert('Please enter the medication name.');
        name.focus();
        return false;
    }
    if (dosage.value.trim() === '') {
        alert('Please enter the dosage.');
        dosage.focus();
        return false;
    }
    if (frequency.value.trim() === '') {
        alert('Please enter the frequency.');
        frequency.focus();
        return false;
    }
    if (startDate.value === '') {
        alert('Please select the start date.');
        startDate.focus();
        return false;
    }
    return true;
}

// Goal validation
function validateGoal() {
    const goalType = document.getElementById('goal_type');
    const targetValue = document.getElementById('target_value');
    const startDate = document.getElementById('start_date');
    
    if (targetValue.value.trim() === '') {
        alert('Please enter the target value.');
        targetValue.focus();
        return false;
    }
    if (startDate.value === '') {
        alert('Please select the start date.');
        startDate.focus();
        return false;
    }
    return true;
}
