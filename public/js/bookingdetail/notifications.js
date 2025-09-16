// notifications.js
window.showNotification = function(message, type='danger') {
    const container = document.getElementById('modalNotificationContainer');
    if(!container) return;

    const notif = document.createElement('div');
    notif.className = `alert alert-${type} alert-dismissible fade show mt-2`;
    notif.role = 'alert';
    notif.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
    container.appendChild(notif);
    setTimeout(() => notif.remove(), 4000);
}
