// FILE: /public/assets/js/app.js

/**
 * SplashAudit - Frontend JavaScript
 */

(function() {
    'use strict';

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#e74c3c';
                } else {
                    field.style.borderColor = '#ddd';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });

    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure you want to delete this?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Auto-dismiss flash messages
    const flashMessages = document.querySelectorAll('.alert-success, .alert-error');
    flashMessages.forEach(msg => {
        setTimeout(() => {
            msg.style.transition = 'opacity 0.5s';
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }, 5000);
    });

    // Table row click to view details
    const tableRows = document.querySelectorAll('.data-table tbody tr[data-href]');
    tableRows.forEach(row => {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function() {
            window.location.href = this.getAttribute('data-href');
        });
    });

    // Mobile menu toggle (if needed)
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.querySelector('.sidebar');

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    // File upload preview
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const fileName = this.files[0]?.name;
            if (fileName) {
                const label = this.nextElementSibling;
                if (label && label.tagName === 'LABEL') {
                    label.textContent = fileName;
                }
            }
        });
    });

    // Date range validation
    const startDateInputs = document.querySelectorAll('input[name*="start"]');
    const endDateInputs = document.querySelectorAll('input[name*="end"]');

    if (startDateInputs.length && endDateInputs.length) {
        endDateInputs.forEach((endDate, index) => {
            const startDate = startDateInputs[index];
            if (startDate) {
                endDate.addEventListener('change', function() {
                    if (startDate.value && endDate.value) {
                        if (new Date(endDate.value) < new Date(startDate.value)) {
                            alert('End date cannot be before start date.');
                            endDate.value = '';
                        }
                    }
                });
            }
        });
    }

    // Dynamic form field additions (for team members, etc.)
    window.addFormField = function(containerId, fieldHtml) {
        const container = document.getElementById(containerId);
        if (container) {
            const div = document.createElement('div');
            div.innerHTML = fieldHtml;
            container.appendChild(div);
        }
    };

    // Format numbers
    window.formatNumber = function(num) {
        return new Intl.NumberFormat().format(num);
    };

    // Format dates
    window.formatDate = function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString();
    };

    console.log('SplashAudit initialized');
})();
