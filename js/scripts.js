// /js/scripts.js

document.addEventListener('DOMContentLoaded', () => {
    const toggleButton = document.getElementById('toggle-dark-mode');
    const body = document.body;

    // Funcție pentru activarea/dezactivarea modului Dark Mode
    toggleButton.addEventListener('click', () => {
        body.classList.toggle('dark-mode');
        toggleButton.textContent = body.classList.contains('dark-mode') ? 'Dezactivați Dark Mode' : 'Activați Dark Mode';
    });

    // Funcție pentru afișarea notificării
    function showNotification(message) {
        const container = document.getElementById('notification-container');
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;

        container.appendChild(notification);

        // Afișează notificarea
        requestAnimationFrame(() => {
            notification.classList.add('show');
        });

        // Ascunde notificarea după 3 secunde
        setTimeout(() => {
            notification.classList.remove('show');
            notification.classList.add('hide');
            
            // Elimină notificarea din DOM după ce tranziția se încheie
            notification.addEventListener('transitionend', () => {
                notification.remove();
            });
        }, 3000);
    }

    // Funcții dummy pentru gestionarea utilizatorilor, restaurantelor, meniurilor, livratorilor și recenziilor
    window.addUser = function() {
        showNotification("Utilizator adăugat!");
    };

    window.addRestaurant = function() {
        showNotification("Restaurant adăugat!");
    };

    window.addMenu = function() {
        showNotification("Meniu adăugat!");
    };

    window.addDeliverer = function() {
        showNotification("Livrator adăugat!");
    };

    window.addReview = function() {
        showNotification("Recenzie adăugată!");
    };
});
