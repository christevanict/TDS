// server/server.js
const WebSocket = require('ws'); // Import WebSocket library
const wss = new WebSocket.Server({ port: 8000 }); // WebSocket server running on port 8080

// Handle new connections
wss.on('connection', ws => {
    console.log('A client connected');

    // Listen for messages from POS page (cart updates)
    ws.on('message', message => {
        console.log('Received message:', message);

        const data = JSON.parse(message);
        if (data.type === 'updateCart') {
            // Broadcast the updated cart to all connected clients (including Customer page)
            wss.clients.forEach(client => {
                if (client.readyState === WebSocket.OPEN) {
                    client.send(JSON.stringify({ type: 'updateCart', cart: data.cart }));
                }
            });
        }
    });

    // Handle disconnections
    ws.on('close', () => {
        console.log('A client disconnected');
    });
});

console.log('WebSocket server running on ws://localhost:8080');
