const http = require('http');
const net = require('net');
const fs = require('fs');
const path = require('path');

const PORT = 3001;
const GRAPHQL_URL = 'http://localhost:4000/graphql';
const SOCKET_HOST = 'localhost';
const SOCKET_PORT = 5000;

/**
 * Envía el pago por Socket TCP al ServidorSocketPago en Java (Puerto 5000)
 */
function enviarPagoPorSocketTCP(codigoUnico, ciConductor, entidad) {
    return new Promise((resolve, reject) => {
        const client = new net.Socket();
        let responseData = '';
        client.setTimeout(6000);

        client.connect(SOCKET_PORT, SOCKET_HOST, () => {
            const trama = `PAGAR|${codigoUnico}|${ciConductor || ''}|${entidad || 'Banco Unión'}\n`;
            client.write(trama, 'utf8');
        });

        client.on('data', (data) => {
            responseData += data.toString('utf8');
        });

        client.on('end', () => {
            resolve(responseData.trim());
        });

        client.on('close', () => {
            if (responseData) resolve(responseData.trim());
        });

        client.on('timeout', () => {
            client.destroy();
            reject(new Error('Tiempo de espera agotado al conectar con Servidor Socket TCP Java (puerto 5000).'));
        });

        client.on('error', (err) => {
            reject(err);
        });
    });
}

const server = http.createServer((req, res) => {
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        res.writeHead(200);
        res.end();
        return;
    }

    if (req.method === 'GET' && (req.url === '/' || req.url === '/index.html')) {
        const htmlPath = path.join(__dirname, 'index.html');
        fs.readFile(htmlPath, 'utf8', (err, html) => {
            if (err) {
                res.writeHead(500);
                res.end('Error cargando index.html');
                return;
            }
            res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
            res.end(html);
        });
        return;
    }

    // Endpoint para que el cajero dispare el pago vía Socket TCP hacia el servidor Java
    if (req.method === 'POST' && req.url === '/api/pagar-socket') {
        let body = '';
        req.on('data', chunk => { body += chunk.toString(); });
        req.on('end', async () => {
            try {
                const { codigo_unico, ci_conductor, entidad } = JSON.parse(body || '{}');
                const tramaEnviada = `PAGAR|${codigo_unico}|${ci_conductor || ''}|${entidad || 'Banco Unión'}`;
                const respuestaSocket = await enviarPagoPorSocketTCP(codigo_unico, ci_conductor, entidad);
                const exito = respuestaSocket.startsWith('OK|');

                res.writeHead(exito ? 200 : 400, { 'Content-Type': 'application/json; charset=utf-8' });
                res.end(JSON.stringify({
                    exito,
                    protocolo: 'TCP Socket (localhost:5000 - Java)',
                    trama_enviada: tramaEnviada,
                    respuesta_socket: respuestaSocket
                }));
            } catch (err) {
                res.writeHead(500, { 'Content-Type': 'application/json; charset=utf-8' });
                res.end(JSON.stringify({
                    exito: false,
                    error: 'Error comunicando con Servidor Socket TCP Java (Puerto 5000): ' + err.message
                }));
            }
        });
        return;
    }

    res.writeHead(404);
    res.end('No encontrado');
});

server.listen(PORT, () => {
    console.log(`[Entidad Recaudadora] Módulo Cajero disponible en http://localhost:${PORT}`);
    console.log(`  -> Consultas GraphQL hacia: ${GRAPHQL_URL}`);
    console.log(`  -> Pagos por Socket TCP hacia: ${SOCKET_HOST}:${SOCKET_PORT} (Java)`);
});
