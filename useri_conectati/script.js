document.addEventListener('DOMContentLoaded', function() {
    fetch('api.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('user-sessions-container');
            data.forEach(user => {
                const card = document.createElement('div');
                card.classList.add('user-card');
                card.classList.add(user.online ? 'online' : 'offline');

                card.innerHTML = `
                    <h2>${user.name}</h2>
                    <p>Email: ${user.email || 'N/A'}</p>
                    <p>Telefon: ${user.phone}</p>
                    <p>Status: ${user.online ? 'Online' : 'Offline'}</p>
                `;
                container.appendChild(card);
            });
        })
        .catch(error => console.error('Error fetching data:', error));
});
