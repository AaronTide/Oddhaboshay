// ============================================================
//  Oddhaboshay LMS - Main JavaScript File
// ============================================================

// Confirm before dangerous actions (delete, decline)
function confirmAction(message) {
    return confirm(message || 'Are you sure?');
}

// Auto-hide alerts after 4 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() { alert.remove(); }, 500);
        }, 4000);
    });
});

// Simple tab switching (used in messages page)
function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(function(el) {
        el.style.display = 'none';
    });
    document.querySelectorAll('.tab-btn').forEach(function(el) {
        el.classList.remove('active');
    });
    document.getElementById(tabId).style.display = 'block';
    event.target.classList.add('active');
}
