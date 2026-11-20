<?php
// Servicio SOAP del SEGIP: Bool verificarCI(String ci)
// Puerto: 8001 (php -S localhost:8001 server.php)

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, SOAPAction');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Base de datos de ciudadanos registrados en SEGIP (para demostración y validación)
// También aceptamos CIs numéricos válidos de 5 a 10 dígitos que no estén en lista negra
$ciudadanos_segip = [
    '1234567'  => ['nombre' => 'Juan Pérez Mamani', 'valido' => true],
    '7654321'  => ['nombre' => 'María Quispe Condori', 'valido' => true],
    '8888888'  => ['nombre' => 'Carlos Flores Vargas', 'valido' => true],
    '9999999'  => ['nombre' => 'Ana Torres Gutiérrez', 'valido' => true],
    '4567890'  => ['nombre' => 'Pedro Rojas Choque', 'valido' => true],
    '1111111'  => ['nombre' => 'Luis Fernando Camacho', 'valido' => true],
    '0000000'  => ['nombre' => 'CI Inválido / Observado', 'valido' => false],
    '999'      => ['nombre' => 'CI Inexistente', 'valido' => false],
];

/**
 * Función principal solicitada en el examen:
 * Bool verificarCI(String ci)
 */
function verificarCI(string $ci): bool {
    global $ciudadanos_segip;
    $ci = trim($ci);

    if ($ci === '' || $ci === '0000000' || $ci === '999' || strtolower($ci) === 'invalido') {
        return false;
    }

    if (isset($ciudadanos_segip[$ci])) {
        return $ciudadanos_segip[$ci]['valido'];
    }

    // Para facilitar pruebas en el examen: cualquier CI de 6 a 10 caracteres alfanuméricos válido
    // que no empiece con '0' se considera registrado en el padrón del SEGIP.
    return (bool) preg_match('/^[1-9][0-9]{5,9}(-[A-Z0-9]{1,3})?$/i', $ci);
}

// 1. Si solicitan el WSDL (?wsdl)
if (isset($_GET['wsdl']) || isset($_GET['WSDL'])) {
    header('Content-Type: text/xml; charset=utf-8');
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8001';
    echo <<<WSDL
<?xml version="1.0" encoding="UTF-8"?>
<definitions name="SegipService"
             targetNamespace="http://segip.gob.bo/soap"
             xmlns:tns="http://segip.gob.bo/soap"
             xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
             xmlns:xsd="http://www.w3.org/2001/XMLSchema"
             xmlns="http://schemas.xmlsoap.org/wsdl/">

    <message name="verificarCIRequest">
        <part name="ci" type="xsd:string"/>
    </message>

    <message name="verificarCIResponse">
        <part name="verificarCIResult" type="xsd:boolean"/>
    </message>

    <portType name="SegipPortType">
        <operation name="verificarCI">
            <input message="tns:verificarCIRequest"/>
            <output message="tns:verificarCIResponse"/>
        </operation>
    </portType>

    <binding name="SegipBinding" type="tns:SegipPortType">
        <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
        <operation name="verificarCI">
            <soap:operation soapAction="http://segip.gob.bo/soap/verificarCI"/>
            <input>
                <soap:body use="literal" namespace="http://segip.gob.bo/soap"/>
            </input>
            <output>
                <soap:body use="literal" namespace="http://segip.gob.bo/soap"/>
            </output>
        </operation>
    </binding>

    <service name="SegipService">
        <port name="SegipPort" binding="tns:SegipBinding">
            <soap:address location="http://{$host}/server.php"/>
        </port>
    </service>
</definitions>
WSDL;
    exit;
}

// 2. Si es una petición POST SOAP XML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawPost = file_get_contents('php://input');
    header('Content-Type: text/xml; charset=utf-8');

    // Extraer el parámetro <ci>...</ci> del envelope SOAP (con o sin namespace prefix)
    $ci = '';
    if (preg_match('/<(?:\w+:)?ci[^>]*>(.*?)<\/(?:\w+:)?ci>/is', $rawPost, $matches)) {
        $ci = trim(html_entity_decode($matches[1], ENT_QUOTES | ENT_XML1, 'UTF-8'));
    }

    $resultado = verificarCI($ci);
    $boolStr = $resultado ? 'true' : 'false';
    $nombre = $ciudadanos_segip[$ci]['nombre'] ?? ($resultado ? 'Ciudadano Verificado SEGIP' : 'No Registrado');

    echo <<<SOAP
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:tns="http://segip.gob.bo/soap">
    <soap:Body>
        <tns:verificarCIResponse>
            <verificarCIResult>{$boolStr}</verificarCIResult>
            <return>{$boolStr}</return>
            <nombre>{$nombre}</nombre>
        </tns:verificarCIResponse>
    </soap:Body>
</soap:Envelope>
SOAP;
    exit;
}

// 3. Vista informativa en navegador (GET)
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SEGIP - Servicio SOAP verificarCI</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; padding: 2rem; }
        .card { max-width: 750px; margin: 0 auto; background: #1e293b; padding: 1.5rem 2rem; border-radius: 12px; border: 1px solid #334155; }
        h1 { color: #38bdf8; margin-top: 0; }
        code, pre { background: #0f172a; color: #7dd3fc; padding: 0.2rem 0.4rem; border-radius: 6px; }
        pre { padding: 1rem; overflow-x: auto; }
        a { color: #38bdf8; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { text-align: left; padding: 0.6rem; border-bottom: 1px solid #334155; }
    </style>
</head>
<body>
    <div class="card">
        <h1>SEGIP - Servicio Web SOAP Activo</h1>
        <p>Método expuesto: <code>Bool verificarCI(String ci)</code></p>
        <p>WSDL disponible en: <a href="/server.php?wsdl">http://localhost:8001/server.php?wsdl</a></p>
        <h3>CIs de Prueba Registrados:</h3>
        <table>
            <thead><tr><th>CI</th><th>Nombre en Padrón</th><th>Resultado verificarCI(ci)</th></tr></thead>
            <tbody>
                <?php foreach ($ciudadanos_segip as $c => $info): ?>
                <tr>
                    <td><code><?= htmlspecialchars($c) ?></code></td>
                    <td><?= htmlspecialchars($info['nombre']) ?></td>
                    <td><?= $info['valido'] ? '✅ true (Válido)' : '❌ false (Inválido)' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><small>Nota: Cualquier otro CI numérico de 6 a 10 dígitos que no empiece en 0 también retorna <code>true</code>.</small></p>
    </div>
</body>
</html>
