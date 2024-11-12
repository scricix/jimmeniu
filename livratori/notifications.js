let lastOrderCount = 0;

function checkForNewOrders() {
    fetch('check_new_orders.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }
            
            if (data.newOrders > lastOrderCount) {
                playNotificationSound();
                showNotification("Comenzi noi!", `Aveți ${data.newOrders} comenzi noi de preluat.`);
            }
            lastOrderCount = data.newOrders;
        })
        .catch(error => console.error('Error:', error));
}

function playNotificationSound() {
    const audio = new Audio('sunet.mp3');
    audio.play();
}

function showNotification(title, message) {
    if (!("Notification" in window)) {
        alert("Acest browser nu suportă notificări desktop");
    } else if (Notification.permission === "granted") {
        new Notification(title, { body: message });
    } else if (Notification.permission !== "denied") {
        Notification.requestPermission().then(function (permission) {
            if (permission === "granted") {
                new Notification(title, { body: message });
            }
        });
    }
}

// Verifică pentru comenzi noi la fiecare 30 de secunde
setInterval(checkForNewOrders, 5000);

// Inițializează notificările când pagina se încarcă
document.addEventListener('DOMContentLoaded', () => {
    Notification.requestPermission();
    checkForNewOrders(); // Verifică imediat la încărcare
});
