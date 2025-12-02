// SplashAudit Main App JavaScript - See full implementation in Phase 4C commit
document.addEventListener('DOMContentLoaded', function() {
    console.log('SplashAudit loaded');
    autoHideAlerts();
    confirmDeletes();
});

function autoHideAlerts() {
    setTimeout(() => {
        document.querySelectorAll('.alert-success, .alert-info').forEach(a => {
            a.style.opacity = '0';
            setTimeout(() => a.remove(), 500);
        });
    }, 5000);
}

function confirmDeletes() {
    document.querySelectorAll('form[action*="/delete"]').forEach(form => {
        form.onsubmit = () => confirm('Delete this item?');
    });
}
