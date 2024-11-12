let cart = []; // Definim variabila cart ca un array gol
let shippingCost = 15; // Definim shippingCost cu o valoare fixă

function processOrder() {
    const form = document.getElementById('order-form');
    if (!validateForm(form)) {
        alert('Vă rugăm să completați toate câmpurile obligatorii.');
        return;
    }

    const formData = new FormData(form);

    formData.append('products', JSON.stringify(cart));
    
    const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    formData.append('subtotal', subtotal.toFixed(2));
    formData.append('shippingCost', shippingCost.toFixed(2));
    formData.append('total', (subtotal + shippingCost).toFixed(2));

    console.log('Sending data:', Object.fromEntries(formData));

    fetch('process_order.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Received response:', data);
        if (data.confirmationUrl) {
            window.location.href = data.confirmationUrl;
        } else {
            throw new Error(data.error || 'Eroare necunoscută');
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        alert('A apărut o eroare la procesarea comenzii: ' + error.message);
    });
}

function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    return Array.from(requiredFields).every(field => field.value.trim() !== '');
}
