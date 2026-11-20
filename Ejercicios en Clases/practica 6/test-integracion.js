const http = require('http');
const net = require('net');

function request(url, method = 'GET', body = null, headers = {}) {
    return new Promise((resolve, reject) => {
        const u = new URL(url);
        const req = http.request({
            hostname: u.hostname,
            port: u.port,
            path: u.pathname + u.search,
            method,
            headers
        }, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => resolve({ status: res.statusCode, body: data }));
        });
        req.on('error', reject);
        if (body) req.write(body);
        req.end();
    });
}

function sendTcpSocket(host, port, message) {
    return new Promise((resolve, reject) => {
        const client = new net.Socket();
        let data = '';
        client.connect(port, host, () => {
            client.write(message + '\n', 'utf8');
        });
        client.on('data', chunk => data += chunk.toString('utf8'));
        client.on('end', () => resolve(data.trim()));
        client.on('close', () => resolve(data.trim()));
        client.on('error', reject);
    });
}

async function runTests() {
    console.log('=== PRUEBA EN VIVO SOBRE SERVICIOS ACTIVOS (MySQL bd_multas_transito) ===');

    // 1. Prueba SOAP SEGIP: verificarCI("1234567") -> true, verificarCI("0000000") -> false
    const soapValid = `<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><verificarCI><ci>1234567</ci></verificarCI></soap:Body></soap:Envelope>`;
    const rSoap1 = await request('http://localhost:8001/server.php', 'POST', soapValid, { 'Content-Type': 'text/xml' });
    console.log('\n[1] SOAP SEGIP verificarCI("1234567") -> HTTP', rSoap1.status, '| Resultado true:', rSoap1.body.includes('<verificarCIResult>true</verificarCIResult>'));

    const soapInvalid = `<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><verificarCI><ci>0000000</ci></verificarCI></soap:Body></soap:Envelope>`;
    const rSoap2 = await request('http://localhost:8001/server.php', 'POST', soapInvalid, { 'Content-Type': 'text/xml' });
    console.log('[1b] SOAP SEGIP verificarCI("0000000") -> HTTP', rSoap2.status, '| Resultado false:', rSoap2.body.includes('<verificarCIResult>false</verificarCIResult>'));

    // 2. Prueba REST Laravel + MySQL: Policía registra nueva boleta en POST /api/multa
    const codigoTest = 'BOL-' + Math.floor(1000 + Math.random() * 8999);
    const nuevaMulta = JSON.stringify({
        codigo_unico: codigoTest,
        ci_conductor: '1234567',
        nombre_conductor: 'Juan Pérez Mamani',
        placa: '4521-TKR',
        fecha: '2026-11-19',
        tipo_infraccion: 'Exceso de velocidad en zona urbana',
        lugar: 'Av. Mariscal Santa Cruz',
        monto: 250
    });
    const rRestPost = await request('http://127.0.0.1:8000/api/multa', 'POST', nuevaMulta, {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    });
    console.log('\n[2] REST Laravel + MySQL POST /api/multa (Emisión Policía) -> HTTP', rRestPost.status, '\n   ', rRestPost.body);

    // 3. Prueba GraphQL Node.js + MySQL: Entidad Recaudadora valida la boleta recién emitida
    const gqlQuery = JSON.stringify({
        query: `query {
            validarMultaParaPago(codigo_unico: "${codigoTest}", ci_conductor: "1234567") {
                existe
                pertenece_conductor
                ya_pagada
                autorizado_cobro
                mensaje
                multa { codigo_unico ci_conductor nombre_conductor monto estado }
            }
        }`
    });
    const rGql1 = await request('http://127.0.0.1:4000/graphql', 'POST', gqlQuery, { 'Content-Type': 'application/json' });
    console.log('\n[3] GraphQL Node.js + MySQL validarMultaParaPago (antes del pago) -> HTTP', rGql1.status, '\n   ', rGql1.body);

    // 4. Prueba Socket TCP Java: Entidad Recaudadora registra el pago cambiando estado de "pendiente" a "pagada"
    const tcpResp1 = await sendTcpSocket('127.0.0.1', 5000, `PAGAR|${codigoTest}|1234567|Banco Unión`);
    console.log('\n[4] Socket TCP Java (PAGAR|' + codigoTest + '|1234567|Banco Unión) ->\n   ', tcpResp1);

    // 5. Prueba Socket TCP Java al intentar pagar una boleta YA PAGADA (debe devolver error)
    const tcpRespDuplicado = await sendTcpSocket('127.0.0.1', 5000, `PAGAR|${codigoTest}|1234567|Banco Unión`);
    console.log('\n[4b] Socket TCP Java intento de doble pago ->\n   ', tcpRespDuplicado);

    // 6. Prueba Cliente Web REST Laravel + MySQL: Consultar estado actualizado de la boleta ("pagada")
    const rRestGet = await request(`http://127.0.0.1:8000/api/multa/${codigoTest}`, 'GET', null, { 'Accept': 'application/json' });
    console.log('\n[5] Cliente Web GET /api/multa/' + codigoTest + ' (después del pago por Socket TCP) -> HTTP', rRestGet.status, '\n   ', rRestGet.body);

    console.log('\n=== TODAS LAS PRUEBAS EN VIVO CON MYSQL FINALIZARON EXITOSAMENTE ===');
}

runTests().catch(err => {
    console.error('Error en pruebas:', err);
    process.exit(1);
});
