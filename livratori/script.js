let isLoggedIn = false;
let currentDelivererId = null;
let currentDelivererName = null;

async function autentificare() {
    const name = document.getElementById('name').value;
    const password = document.getElementById('password').value;
    
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'login',
                name: name,
                password: password
            }),
        });

        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            const data = await response.json();
            if (data.status) {
                isLoggedIn = true;
                currentDelivererId = data.data.delivererId;
                currentDelivererName = data.data.delivererName;
                
                const autentificareElement = document.getElementById('autentificare');
                if (autentificareElement) {
                    autentificareElement.style.display = 'none';
                } else {
                    console.error('Elementul de autentificare nu a fost găsit');
                }
                
                const comenziContainer = document.getElementById('comenzi-container');
                if (comenziContainer) {
                    comenziContainer.style.display = 'block';
                } else {
                    console.error('Containerul de comenzi nu a fost găsit');
                }
                
                const welcomeMessage = document.getElementById('welcomeMessage');
                if (welcomeMessage) {
                    welcomeMessage.textContent = `Bine ai venit, ${currentDelivererName}!`;
                } else {
                    console.error('Elementul welcomeMessage nu a fost găsit');
                }
                
                getOrders();
                startRealTimeUpdates();
            } else {
                alert(data.message || 'Autentificare eșuată');
            }
        } else {
            const text = await response.text();
            throw new Error('Răspuns neașteptat de la server: ' + text);
        }
    } catch (error) {
        console.error('Eroare la autentificare:', error);
        alert('A apărut o eroare la autentificare. Vă rugăm să încercați din nou.');
    }
}

async function deconectare() {
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'logout'
            }),
        });

        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            const data = await response.json();
            if (data.status) {
                isLoggedIn = false;
                currentDelivererId = null;
                currentDelivererName = null;
                document.getElementById('autentificare').style.display = 'block';
                document.getElementById('comenzi-container').style.display = 'none';
                stopRealTimeUpdates();
            } else {
                alert(data.message || 'Deconectare eșuată');
            }
        } else {
            const text = await response.text();
            throw new Error('Răspuns neașteptat de la server: ' + text);
        }
    } catch (error) {
        console.error('Eroare la deconectare:', error);
        alert('A apărut o eroare la deconectare. Vă rugăm să încercați din nou.');
    }
}

let updateInterval;

function startRealTimeUpdates() {
    updateInterval = setInterval(getOrders, 30000);
}

function stopRealTimeUpdates() {
    clearInterval(updateInterval);
}

async function getOrders() {
    if (!isLoggedIn) return;

    const sortare = document.getElementById('sortare').value;
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'getOrders',
                sortBy: sortare
            }),
        });

        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            const data = await response.json();
            if (data.status && Array.isArray(data.data)) {
                afiseazaComenzi(data.data);
            } else {
                console.error('Date invalide primite de la server:', data);
                alert(data.message || 'Eroare la preluarea comenzilor');
            }
        } else {
            const text = await response.text();
            throw new Error('Răspuns neașteptat de la server: ' + text);
        }
    } catch (error) {
        console.error('Eroare la preluarea comenzilor:', error);
        alert('A apărut o eroare la preluarea comenzilor. Vă rugăm să încercați din nou.');
    }
}
function afiseazaComenzi(comenzi) {
    const listaComenzi = document.getElementById('comenzi-lista');
    listaComenzi.innerHTML = '';

    comenzi.forEach(comanda => {
        const comandaElement = document.createElement('div');
        comandaElement.classList.add('comanda');
        comandaElement.dataset.id = comanda.id;

        let produse = '';
        if (comanda.products && typeof comanda.products === 'string') {
            produse = comanda.products.split(';').map((produs, index) => {
                const parts = produs.split(' - ');
                const nume = parts[0] ? parts[0].trim() : 'Produs necunoscut';
                const detalii = parts[1] ? parts[1].trim() : 'Fără detalii';
                const culori = ['#FFD700', '#98FB98', '#87CEFA', '#FFA07A', '#DDA0DD']; // Culori pastelate
                const culoare = culori[index % culori.length];
                return `
                    <div style="background-color: ${culoare}; padding: 15px; margin: 10px 0; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                        <h4 style="margin: 0 0 10px 0; color: #333;">${nume}</h4>
                        <p style="margin: 0; font-size: 0.9em; color: #555;">${detalii}</p>
                    </div>`;
            }).join('');
        } else {
            produse = '<div style="background-color: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 10px;">Nu există informații despre produse</div>';
        }

        const statusuri = ['Preia Comanda', 'Procesare Comanda', 'Comanda în Curs', 'Comanda Livrată', 'Comanda Anulată'];
        let statusButtons = '';
        
        if (comanda.deliverer_id === null) {
            statusButtons = `<button onclick="acceptaComanda(${comanda.id})">Acceptă Comanda</button>`;
        } else if (parseInt(comanda.deliverer_id) === currentDelivererId) {
            statusButtons = statusuri.map(status => 
                `<button onclick="updateStatus(${comanda.id}, '${status}', this)" 
                         data-status="${status}" 
                         ${comanda.clicked_statuses && comanda.clicked_statuses.includes(status) ? 'disabled' : ''}
                         ${comanda.status === status ? 'disabled' : ''}>
                    ${status}
                </button>`
            ).join('');
        } else {
            statusButtons = '<p>Comandă acceptată de alt livrator</p>';
        }

        comandaElement.innerHTML = `
            <h3>Comanda #${comanda.order_id || 'N/A'}</h3>
            <p><strong>Client:</strong> ${comanda.customer_name || 'Necunoscut'}</p>
            <p><strong>Email:</strong> ${comanda.customer_email || 'N/A'}</p>
            <p><strong>Telefon:</strong> ${comanda.customer_phone || 'N/A'}</p>
            <p><strong>Adresă de livrare:</strong> <span class="delivery-address">${comanda.delivery_address || 'N/A'}</span></p>
            <div class="produse" style="background-color: #f9f9f9; padding: 20px; border-radius: 15px; margin: 15px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <h3 style="margin-top: 0; color: #2c3e50;">Produse:</h3>
                ${produse}
            </div>
            <p><strong>Subtotal:</strong> ${comanda.subtotal || '0'} RON</p>
            <p><strong>Cost livrare:</strong> ${comanda.shipping_cost || '0'} RON</p>
            <p><strong>Total:</strong> ${comanda.total || '0'} RON</p>
            <p><strong>Status:</strong> <span class="status-text">${comanda.status || 'Necunoscut'}</span></p>
            <p><strong>Data creării:</strong> ${comanda.created_at || 'N/A'}</p>
            <div class="status-buttons">
                ${statusButtons}
            </div>
            ${parseInt(comanda.deliverer_id) === currentDelivererId ? `
                <div class="comanda-actions">
                    <button class="trimite-bon" onclick="trimitebon(${comanda.id})">Trimite Bon</button>
                    <a class="vezi-harta" href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(comanda.delivery_address || '')}" target="_blank">Vezi pe hartă</a>
                </div>
            ` : ''}
        `;
        listaComenzi.appendChild(comandaElement);
    });
}

async function acceptaComanda(id) {
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'acceptaComanda',
                id: id
            }),
        });

        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            const data = await response.json();
            if (data.status) {
                alert('Comanda a fost acceptată cu succes!');
                getOrders(); // Reîncărcăm comenzile pentru a reflecta schimbarea
            } else {
                alert(data.message || 'Eroare la acceptarea comenzii');
            }
        } else {
            const text = await response.text();
            throw new Error('Răspuns neașteptat de la server: ' + text);
        }
    } catch (error) {
        console.error('Eroare la acceptarea comenzii:', error);
        alert('A apărut o eroare la acceptarea comenzii. Vă rugăm să încercați din nou.');
    }
}

async function updateStatus(id, status, button) {
    if (button.disabled) {
        alert("Acest status a fost deja setat pentru această comandă.");
        return;
    }

    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'updateOrderStatus',
                id: id,
                status: status,
                clicked_status: status
            }),
        });

        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            const data = await response.json();
            if (data.status) {
                alert('Statusul comenzii a fost actualizat cu succes!');
                button.disabled = true;
                updateStatusUI(id, status);
            } else {
                alert(data.message || 'Eroare la actualizarea statusului');
            }
        } else {
            const text = await response.text();
            throw new Error('Răspuns neașteptat de la server: ' + text);
        }
    } catch (error) {
        console.error('Eroare la actualizarea statusului:', error);
        alert('A apărut o eroare la actualizarea statusului. Vă rugăm să contactați administratorul.');
    }
}

function updateStatusUI(id, status) {
    const comandaElement = document.querySelector(`.comanda[data-id="${id}"]`);
    if (comandaElement) {
        const statusText = comandaElement.querySelector('.status-text');
        if (statusText) {
            statusText.textContent = status;
        }
    }
}

async function trimitebon(id) {
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'trimitebon',
                id: id
            }),
        });

        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            const data = await response.json();
            alert(data.message);
        } else {
            const text = await response.text();
            throw new Error('Răspuns neașteptat de la server: ' + text);
        }
    } catch (error) {
        console.error('Eroare la trimiterea bonului:', error);
        alert('A apărut o eroare la trimiterea bonului. Vă rugăm să încercați din nou.');
    }
}

function filtreazaComenzi() {
    const filtru = document.getElementById('filtru').value.toLowerCase();
    const comenziLista = document.getElementById('comenzi-lista');
    const comenzi = comenziLista.getElementsByClassName('comanda');

    for (let comanda of comenzi) {
        const text = comanda.textContent.toLowerCase();
        if (text.includes(filtru)) {
            comanda.style.display = "";
        } else {
            comanda.style.display = "none";
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('loginButton').addEventListener('click', autentificare);
    document.getElementById('logoutButton').addEventListener('click', deconectare);
    document.getElementById('sortare').addEventListener('change', getOrders);
    document.getElementById('filtru').addEventListener('input', filtreazaComenzi);
});



document.addEventListener('DOMContentLoaded', function () {
    const notificationSound = document.getElementById('notificationSound');

    function verificaComenziNoi() {
        fetch('api.php')
            .then(response => response.json())
            .then(data => {
                if (data.comenzi_noi) {
                    // Redă sunetul de notificare dacă există comenzi noi
                    notificationSound.play();
                    // Poți adăuga aici și un alert vizual sau alte funcționalități
                }
            })
            .catch(error => console.error('Eroare la verificarea comenzilor noi:', error));
    }

    // Verificăm periodic pentru comenzi noi la fiecare 30 de secunde
    setInterval(verificaComenziNoi, 30000);
});
