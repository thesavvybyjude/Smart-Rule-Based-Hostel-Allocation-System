/**
 * Smart Hostel Allocation System - Client-Side JavaScript
 * Form validation, UI helpers, and interactive elements
 */

// =============================================
// FORM VALIDATION
// =============================================

/**
 * Validate registration form
 * @param {HTMLFormElement} form
 * @returns {boolean}
 */
function validateRegistrationForm(form) {
    const fullname = form.querySelector('[name="fullname"]').value.trim();
    const email = form.querySelector('[name="email"]').value.trim();
    const password = form.querySelector('[name="password"]').value;
    const confirmPassword = form.querySelector('[name="confirm_password"]').value;
    const gender = form.querySelector('[name="gender"]').value;
    const level = form.querySelector('[name="level"]').value;
    
    if (fullname.length < 3) {
        showAlert('Full name must be at least 3 characters.', 'error');
        return false;
    }
    
    if (!isValidEmail(email)) {
        showAlert('Please enter a valid email address.', 'error');
        return false;
    }
    
    if (password.length < 6) {
        showAlert('Password must be at least 6 characters.', 'error');
        return false;
    }
    
    if (password !== confirmPassword) {
        showAlert('Passwords do not match.', 'error');
        return false;
    }
    
    if (!gender) {
        showAlert('Please select your gender.', 'error');
        return false;
    }
    
    if (!level) {
        showAlert('Please select your level.', 'error');
        return false;
    }
    
    return true;
}

/**
 * Validate login form
 * @param {HTMLFormElement} form
 * @returns {boolean}
 */
function validateLoginForm(form) {
    const email = form.querySelector('[name="email"]').value.trim();
    const password = form.querySelector('[name="password"]').value;
    
    if (!isValidEmail(email)) {
        showAlert('Please enter a valid email address.', 'error');
        return false;
    }
    
    if (password.length === 0) {
        showAlert('Please enter your password.', 'error');
        return false;
    }
    
    return true;
}

/**
 * Validate feedback form
 * @param {HTMLFormElement} form
 * @returns {boolean}
 */
function validateFeedbackForm(form) {
    const subject = form.querySelector('[name="subject"]').value.trim();
    const type = form.querySelector('[name="type"]').value;
    const message = form.querySelector('[name="message"]').value.trim();
    
    if (subject.length < 3) {
        showAlert('Subject must be at least 3 characters.', 'error');
        return false;
    }
    
    if (!type) {
        showAlert('Please select a feedback type.', 'error');
        return false;
    }
    
    if (message.length < 10) {
        showAlert('Message must be at least 10 characters.', 'error');
        return false;
    }
    
    return true;
}

// =============================================
// UTILITY FUNCTIONS
// =============================================

/**
 * Validate email format
 * @param {string} email
 * @returns {boolean}
 */
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Show a client-side alert message
 * @param {string} message
 * @param {string} type - 'success' or 'error'
 */
function showAlert(message, type) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert-js');
    existingAlerts.forEach(el => el.remove());
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-js`;
    alertDiv.textContent = message;
    
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.insertBefore(alertDiv, mainContent.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}

/**
 * Confirm delete action
 * @param {string} itemName
 * @returns {boolean}
 */
function confirmDelete(itemName) {
    return confirm(`Are you sure you want to delete this ${itemName}? This action cannot be undone.`);
}

// =============================================
// TAB FUNCTIONALITY
// =============================================

/**
 * Initialize tab switching
 */
function initTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-tab');
            
            // Deactivate all
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            // Activate clicked
            this.classList.add('active');
            const target = document.getElementById(targetId);
            if (target) {
                target.classList.add('active');
            }
        });
    });
}

// =============================================
// MOBILE MENU FUNCTIONALITY
// =============================================

/**
 * Initialize mobile side navigation
 */
function initMobileMenu() {
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const mainNav = document.getElementById('mainNav');
    const navOverlay = document.getElementById('navOverlay');

    if (mobileBtn && mainNav && navOverlay) {
        function toggleMenu() {
            mobileBtn.classList.toggle('active');
            mainNav.classList.toggle('active');
            navOverlay.classList.toggle('active');
            
            // Prevent body scrolling when menu is open
            if (mainNav.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        mobileBtn.addEventListener('click', toggleMenu);
        navOverlay.addEventListener('click', toggleMenu);
    }
}

// =============================================
// AUTO-DISMISS FLASH MESSAGES
// =============================================

document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss flash messages after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    
    // Initialize tabs if present
    initTabs();
    
    // Initialize mobile menu
    initMobileMenu();
});
