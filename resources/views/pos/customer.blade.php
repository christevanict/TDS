<!-- resources/views/transaction/pos/customer.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{__('Customer')}} Page</title>
</head>
<body>
    <h1>{{__('Customer')}} Page</h1>

    <ul id="cart"></ul> <!-- Cart display -->
    <p id="total">Total: $0</p> <!-- Total display -->

    <script>
        // Connect to the WebSocket server
        const socket = new WebSocket('ws://localhost:8000');
        let cart = [];

        // When the WebSocket connection opens
        socket.onopen = () => {
            console.log('Connected to WebSocket server');
        };

        // Listen for messages (cart updates) from the WebSocket server
        socket.onmessage = (event) => {
            const message = JSON.parse(event.data);

            if (message.type === 'updateCart') {
                cart = message.cart;
                updateCartDisplay();
            }
        };

        // Update the cart display on the customer page
        function updateCartDisplay() {
            const cartElement = document.getElementById('cart');
            cartElement.innerHTML = ''; // Clear existing cart

            let total = 0;

            // Display each item in the cart
            cart.forEach(item => {
                const itemElement = document.createElement('li');
                itemElement.textContent = `${item.name} - $${item.price} x ${item.qty}`;
                cartElement.appendChild(itemElement);
                total += item.price * item.qty; // Update total
            });

            // Update the total price
            document.getElementById('total').textContent = `Total: $${total}`;
        }
    </script>
</body>
</html>
