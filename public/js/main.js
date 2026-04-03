// public/js/main.js

document.addEventListener('DOMContentLoaded', () => {
    // Interacciones UI sutiles
    const alerts = document.querySelectorAll('.alert');
    if(alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 3000); // Ocultar mensajes de alerta a los 3 seg
    }
});
