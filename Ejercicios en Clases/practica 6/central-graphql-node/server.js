const http = require('http');
const path = require('path');
const { execFileSync } = require('child_process');

const PORT = 4000;
const MYSQL_HELPER = path.join(__dirname, 'query_mysql.php');

/**
 * Consulta la tabla `multas` directamente en la base de datos MySQL `bd_multas_transito`
 */
function queryMySQL(action, param = '') {
    try {
        const stdout = execFileSync('php', [MYSQL_HELPER, action, String(param || '')], {
            encoding: 'utf8',
            timeout: 5000
        });
        const parsed = JSON.parse(stdout);
        if (!parsed.ok) {
            throw new Error(parsed.error || 'Error al consultar MySQL');
        }
        return parsed;
    } catch (err) {
        throw new Error(`Error de conexión a MySQL (bd_multas_transito en 127.0.0.1:3306): ${err.message}`);
    }
}

// Formatear un registro de la tabla multas de MySQL para GraphQL
function formatMulta(row) {
    if (!row) return null;
    const estado = String(row.estado || 'pendiente').toLowerCase();
    return {
        id: Number(row.id),
        codigo_unico: row.codigo_unico,
        ci_conductor: row.ci_conductor,
        nombre_conductor: row.nombre_conductor,
        placa: row.placa || 'S/N',
        fecha: row.fecha,
        tipo_infraccion: row.tipo_infraccion,
        lugar: row.lugar,
        monto: Number(row.monto),
        estado: row.estado,
        fecha_pago: row.fecha_pago || null,
        entidad_pago: row.entidad_pago || null,
        habilitada_para_pago: estado === 'pendiente'
    };
}

// Extraer los campos solicitados dentro de un bloque GraphQL { campo1 campo2 }
function extractRequestedFields(selectionBlock) {
    if (!selectionBlock) return null;
    const clean = selectionBlock.replace(/#.*/g, ' ');
    const tokens = clean.match(/[a-zA-Z_][a-zA-Z0-9_]*/g) || [];
    const allowed = new Set([
        'id', 'codigo_unico', 'ci_conductor', 'nombre_conductor', 'placa',
        'fecha', 'tipo_infraccion', 'lugar', 'monto', 'estado',
        'fecha_pago', 'entidad_pago', 'habilitada_para_pago',
        'existe', 'pertenece_conductor', 'ya_pagada', 'autorizado_cobro', 'mensaje', 'multa'
    ]);
    const selected = tokens.filter(t => allowed.has(t));
    return selected.length > 0 ? selected : null;
}

function filterFields(obj, fields) {
    if (!obj || !fields) return obj;
    const out = {};
    for (const key of Object.keys(obj)) {
        if (fields.includes(key)) {
            out[key] = obj[key];
        }
    }
    return Object.keys(out).length > 0 ? out : obj;
}

// Resolver consultas GraphQL para la Entidad Recaudadora sobre MySQL
function executeGraphQL(query, variables = {}) {
    try {
        const q = (query || '').trim();
        const data = {};

        // Soportar introspección básica (__schema / __type)
        if (q.includes('__schema') || q.includes('__type')) {
            return {
                data: {
                    __schema: {
                        queryType: { name: 'Query' },
                        types: [
                            { name: 'Multa', description: 'Boleta de infracción de tránsito en MySQL (bd_multas_transito)' },
                            { name: 'ValidacionPago', description: 'Resultado de verificación en Entidad Recaudadora' }
                        ]
                    }
                }
            };
        }

        const resolveArg = (argName, inlineRegex) => {
            if (variables && variables[argName] !== undefined && variables[argName] !== null) {
                return String(variables[argName]).trim();
            }
            const m = q.match(inlineRegex);
            if (!m) return null;
            const rawVal = m[1].trim();
            if (rawVal.startsWith('$')) {
                const varName = rawVal.slice(1);
                return variables[varName] !== undefined ? String(variables[varName]).trim() : null;
            }
            return rawVal.replace(/^["']|["']$/g, '').trim();
        };

        // 1. Query: validarMultaParaPago(codigo_unico: "...", ci_conductor: "...")
        if (q.includes('validarMultaParaPago')) {
            const codigo = resolveArg('codigo_unico', /codigo_unico\s*:\s*("[^"]*"|'[^']*'|\$[a-zA-Z0-9_]+)/);
            const ci = resolveArg('ci_conductor', /ci_conductor\s*:\s*("[^"]*"|'[^']*'|\$[a-zA-Z0-9_]+)/);

            const row = codigo ? queryMySQL('by_codigo', codigo).row : null;

            let result;
            if (!row) {
                result = {
                    existe: false,
                    pertenece_conductor: false,
                    ya_pagada: false,
                    autorizado_cobro: false,
                    mensaje: `ERROR: La boleta '${codigo || ''}' NO existe en la base de datos MySQL de la Central de Registro.`,
                    multa: null
                };
            } else {
                const pertenece = !ci || String(row.ci_conductor).trim().toLowerCase() === String(ci).trim().toLowerCase();
                const estado = String(row.estado || 'pendiente').toLowerCase();
                const yaPagada = estado === 'pagada';
                const anulada = estado === 'anulada';

                if (!pertenece) {
                    result = {
                        existe: true,
                        pertenece_conductor: false,
                        ya_pagada: yaPagada,
                        autorizado_cobro: false,
                        mensaje: `ERROR: La boleta '${row.codigo_unico}' NO pertenece al conductor con CI '${ci}'. (Emitida para CI: ${row.ci_conductor}).`,
                        multa: formatMulta(row)
                    };
                } else if (yaPagada) {
                    result = {
                        existe: true,
                        pertenece_conductor: true,
                        ya_pagada: true,
                        autorizado_cobro: false,
                        mensaje: `ERROR: La boleta '${row.codigo_unico}' YA FUE PAGADA previamente (${row.fecha_pago || 'registrada'}).`,
                        multa: formatMulta(row)
                    };
                } else if (anulada) {
                    result = {
                        existe: true,
                        pertenece_conductor: true,
                        ya_pagada: false,
                        autorizado_cobro: false,
                        mensaje: `ERROR: La boleta '${row.codigo_unico}' se encuentra ANULADA.`,
                        multa: formatMulta(row)
                    };
                } else {
                    result = {
                        existe: true,
                        pertenece_conductor: true,
                        ya_pagada: false,
                        autorizado_cobro: true,
                        mensaje: `HABILITADA PARA PAGO: Boleta verificada correctamente en MySQL. Autorizado el cobro de Bs. ${Number(row.monto).toFixed(2)}.`,
                        multa: formatMulta(row)
                    };
                }
            }

            const multaSubMatch = q.match(/multa\s*\{([^}]+)\}/);
            if (multaSubMatch && result.multa) {
                const requestedMultaFields = extractRequestedFields(multaSubMatch[1]);
                result.multa = filterFields(result.multa, requestedMultaFields);
            }

            data.validarMultaParaPago = result;
        }

        // 2. Query: multaPorCodigo(codigo_unico: "...") o multa(codigo_unico: "...")
        if (q.includes('multaPorCodigo') || /\bmulta\s*\(/.test(q)) {
            const fieldName = q.includes('multaPorCodigo') ? 'multaPorCodigo' : 'multa';
            const codigo = resolveArg('codigo_unico', /codigo_unico\s*:\s*("[^"]*"|'[^']*'|\$[a-zA-Z0-9_]+)/);
            const row = codigo ? queryMySQL('by_codigo', codigo).row : null;
            const blockMatch = q.match(new RegExp(`${fieldName}\\s*\\([^)]*\\)\\s*\\{([^}]+)\\}`));
            const requestedFields = blockMatch ? extractRequestedFields(blockMatch[1]) : null;
            data[fieldName] = filterFields(formatMulta(row), requestedFields);
        }

        // 3. Query: multasPorCI(ci_conductor: "...")
        if (q.includes('multasPorCI')) {
            const ci = resolveArg('ci_conductor', /ci_conductor\s*:\s*("[^"]*"|'[^']*'|\$[a-zA-Z0-9_]+)/);
            const rows = ci ? queryMySQL('by_ci', ci).rows : [];
            const blockMatch = q.match(/multasPorCI\s*\([^)]*\)\s*\{([^}]+)\}/);
            const requestedFields = blockMatch ? extractRequestedFields(blockMatch[1]) : null;
            data.multasPorCI = rows.map(r => filterFields(formatMulta(r), requestedFields));
        }

        // 4. Query: multas (todas las multas o filtradas por estado)
        if (/\bmultas\b(?!PorCI)/.test(q)) {
            const estado = resolveArg('estado', /estado\s*:\s*("[^"]*"|'[^']*'|\$[a-zA-Z0-9_]+)/);
            const rows = estado ? queryMySQL('by_estado', estado).rows : queryMySQL('all').rows;
            const blockMatch = q.match(/\bmultas(?:\s*\([^)]*\))?\s*\{([^}]+)\}/);
            const requestedFields = blockMatch ? extractRequestedFields(blockMatch[1]) : null;
            data.multas = rows.map(r => filterFields(formatMulta(r), requestedFields));
        }

        return { data };
    } catch (err) {
        return {
            errors: [{ message: err.message }]
        };
    }
}

const GRAPHIQL_HTML = `<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Central de Registro - GraphQL (Node.js + MySQL)</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; margin: 0; padding: 1.5rem; }
        .container { max-width: 1100px; margin: 0 auto; }
        h1 { color: #ec4899; margin-bottom: 0.25rem; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem; }
        textarea, pre { width: 100%; height: 360px; box-sizing: border-box; background: #1e293b; color: #f8fafc; border: 1px solid #334155; border-radius: 8px; padding: 1rem; font-family: Consolas, monospace; font-size: 13px; }
        button { background: #ec4899; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; font-weight: 600; cursor: pointer; margin-right: 0.5rem; }
        button.secondary { background: #334155; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Central de Registro — Servidor GraphQL (Node.js + MySQL)</h1>
        <p>Endpoint: <code>POST http://localhost:4000/graphql</code> | Base de Datos MySQL: <code>bd_multas_transito</code> (127.0.0.1:3306)</p>
        <div style="margin-bottom: 0.75rem;">
            <button onclick="runQuery()">▶ Ejecutar Consulta GraphQL</button>
            <button class="secondary" onclick="setExample(1)">Ejemplo 1: Validar Boleta para Pago</button>
            <button class="secondary" onclick="setExample(2)">Ejemplo 2: Listar Todas las Multas</button>
        </div>
        <div class="grid">
            <textarea id="query">query {
  validarMultaParaPago(codigo_unico: "BOL-1001", ci_conductor: "1234567") {
    existe
    pertenece_conductor
    ya_pagada
    autorizado_cobro
    mensaje
    multa {
      codigo_unico
      ci_conductor
      nombre_conductor
      tipo_infraccion
      lugar
      fecha
      monto
      estado
    }
  }
}</textarea>
            <pre id="result">// Haz clic en "Ejecutar Consulta GraphQL" para ver el resultado desde MySQL</pre>
        </div>
    </div>
    <script>
        function setExample(n) {
            const q = document.getElementById('query');
            if (n === 1) {
                q.value = \`query {
  validarMultaParaPago(codigo_unico: "BOL-1001", ci_conductor: "1234567") {
    existe
    pertenece_conductor
    ya_pagada
    autorizado_cobro
    mensaje
    multa {
      codigo_unico
      ci_conductor
      nombre_conductor
      tipo_infraccion
      monto
      estado
    }
  }
}\`;
            } else {
                q.value = \`query {
  multas {
    codigo_unico
    ci_conductor
    nombre_conductor
    tipo_infraccion
    lugar
    fecha
    monto
    estado
  }
}\`;
            }
            runQuery();
        }
        async function runQuery() {
            const query = document.getElementById('query').value;
            const res = await fetch('/graphql', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ query })
            });
            const json = await res.json();
            document.getElementById('result').textContent = JSON.stringify(json, null, 2);
        }
        runQuery();
    </script>
</body>
</html>`;

const server = http.createServer((req, res) => {
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

    if (req.method === 'OPTIONS') {
        res.writeHead(200);
        res.end();
        return;
    }

    if (req.method === 'GET' && (req.url === '/' || req.url.startsWith('/graphql'))) {
        res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
        res.end(GRAPHIQL_HTML);
        return;
    }

    if (req.method === 'POST' && (req.url === '/graphql' || req.url === '/')) {
        let body = '';
        req.on('data', chunk => { body += chunk.toString(); });
        req.on('end', () => {
            try {
                const parsed = JSON.parse(body || '{}');
                const result = executeGraphQL(parsed.query || '', parsed.variables || {});
                res.writeHead(200, { 'Content-Type': 'application/json; charset=utf-8' });
                res.end(JSON.stringify(result));
            } catch (e) {
                res.writeHead(400, { 'Content-Type': 'application/json; charset=utf-8' });
                res.end(JSON.stringify({ errors: [{ message: 'Invalid JSON request: ' + e.message }] }));
            }
        });
        return;
    }

    res.writeHead(404, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ error: 'Endpoint no encontrado. Use /graphql' }));
});

server.listen(PORT, () => {
    console.log(`[GraphQL Node.js + MySQL] Servidor GraphQL escuchando en http://localhost:${PORT}/graphql (Base de datos MySQL: bd_multas_transito)`);
});
