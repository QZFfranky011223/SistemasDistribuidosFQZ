import java.io.*;
import java.net.*;
import java.net.http.*;
import java.nio.charset.StandardCharsets;
import java.time.Duration;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;

/**
 * Central de Registro - Servidor de Sockets TCP en Java
 * Puerto TCP: 5000
 *
 * Recibe mensajes por Socket TCP desde la Entidad Recaudadora para registrar el pago
 * de una boleta en tiempo real y actualizar instantáneamente su estado de "pendiente" a "pagada".
 *
 * Protocolos de mensaje aceptados por el socket TCP (una línea terminada en \n):
 *   1) PAGAR|<codigo_unico>|<ci_conductor>|<entidad_recaudadora>
 *   2) PAGAR|<codigo_unico>
 *   3) JSON: {"accion":"PAGAR","codigo_unico":"BOL-1001","ci_conductor":"1234567","entidad":"Banco Unión"}
 */
public class ServidorSocketPago {

    private static final int PORT = 5000;
    private static final String CENTRAL_REST_URL = "http://localhost:8000/api/multa";
    private static final HttpClient httpClient = HttpClient.newBuilder()
            .connectTimeout(Duration.ofSeconds(5))
            .build();

    public static void main(String[] args) {
        System.out.println("=================================================================");
        System.out.println(" [Central de Registro - JAVA TCP SOCKET SERVER]");
        System.out.println(" Escuchando conexiones TCP en el puerto: " + PORT);
        System.out.println(" Conectado con Central REST Laravel en: " + CENTRAL_REST_URL);
        System.out.println("=================================================================");

        try (ServerSocket serverSocket = new ServerSocket(PORT)) {
            while (true) {
                Socket clientSocket = serverSocket.accept();
                new Thread(() -> handleClient(clientSocket)).start();
            }
        } catch (IOException e) {
            System.err.println("[Error Servidor Socket TCP] " + e.getMessage());
            e.printStackTrace();
        }
    }

    private static void handleClient(Socket clientSocket) {
        String clientAddr = clientSocket.getRemoteSocketAddress().toString();
        String timestamp = LocalDateTime.now().format(DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss"));
        System.out.println("\n[" + timestamp + "] Nueva conexión TCP entrante desde: " + clientAddr);

        try (
            BufferedReader in = new BufferedReader(new InputStreamReader(clientSocket.getInputStream(), StandardCharsets.UTF_8));
            PrintWriter out = new PrintWriter(new OutputStreamWriter(clientSocket.getOutputStream(), StandardCharsets.UTF_8), true)
        ) {
            String requestLine = in.readLine();
            if (requestLine == null || requestLine.trim().isEmpty()) {
                out.println("ERROR|Mensaje vacío recibido en el Socket TCP");
                return;
            }

            requestLine = requestLine.trim();
            System.out.println("  -> Trama recibida: " + requestLine);

            String codigoUnico = "";
            String ciConductor = "";
            String entidad = "Entidad Recaudadora Autorizada";

            if (requestLine.startsWith("{")) {
                // Parseo simple de JSON sin librerías externas
                codigoUnico = extractJsonString(requestLine, "codigo_unico");
                ciConductor = extractJsonString(requestLine, "ci_conductor");
                String ent = extractJsonString(requestLine, "entidad");
                if (!ent.isEmpty()) entidad = ent;
            } else {
                // Formato separado por pipes: PAGAR|BOL-1001|1234567|Banco Union
                String[] parts = requestLine.split("\\|");
                if (parts.length >= 2 && parts[0].equalsIgnoreCase("PAGAR")) {
                    codigoUnico = parts[1].trim();
                    if (parts.length >= 3) ciConductor = parts[2].trim();
                    if (parts.length >= 4 && !parts[3].trim().isEmpty()) entidad = parts[3].trim();
                } else {
                    codigoUnico = parts[0].trim();
                }
            }

            if (codigoUnico.isEmpty()) {
                String err = "ERROR|Código de boleta no especificado. Formato esperado: PAGAR|<codigo_unico>|<ci_conductor>|<entidad>";
                System.out.println("  <- Respuesta: " + err);
                out.println(err);
                return;
            }

            String resultado = procesarPagoEnCentral(codigoUnico, ciConductor, entidad);
            System.out.println("  <- Respuesta enviada a Entidad Recaudadora: " + resultado);
            out.println(resultado);

        } catch (Exception e) {
            System.err.println("[Error procesando cliente TCP] " + e.getMessage());
        } finally {
            try {
                clientSocket.close();
            } catch (IOException ignored) {}
        }
    }

    /**
     * Comunica con el recurso REST `multa` de Laravel para actualizar el estado de la boleta
     * de "pendiente" a "pagada" validando existencia, conductor y estado previo.
     */
    private static String procesarPagoEnCentral(String codigoUnico, String ciConductor, String entidad) {
        try {
            String encodedCodigo = URLEncoder.encode(codigoUnico, StandardCharsets.UTF_8);
            String jsonBody = String.format(
                "{\"estado\":\"pagada\",\"ci_conductor\":\"%s\",\"entidad_pago\":\"%s\"}",
                escapeJson(ciConductor),
                escapeJson(entidad)
            );

            HttpRequest request = HttpRequest.newBuilder()
                    .uri(URI.create(CENTRAL_REST_URL + "/" + encodedCodigo + "/pagar"))
                    .timeout(Duration.ofSeconds(5))
                    .header("Content-Type", "application/json")
                    .header("Accept", "application/json")
                    .PUT(HttpRequest.BodyPublishers.ofString(jsonBody, StandardCharsets.UTF_8))
                    .build();

            HttpResponse<String> response = httpClient.send(request, HttpResponse.BodyHandlers.ofString());
            int status = response.statusCode();
            String body = response.body();

            String mensaje = extractJsonString(body, "mensaje");
            if (mensaje.isEmpty()) {
                mensaje = extractJsonString(body, "message");
            }

            if (status == 200) {
                String monto = extractJsonValue(body, "monto");
                String fechaPago = extractJsonString(body, "fecha_pago");
                return "OK|PAGO_REGISTRADO|" + codigoUnico + "|pagada|" +
                       (mensaje.isEmpty() ? "Estado actualizado de 'pendiente' a 'pagada' exitosamente." : mensaje) +
                       "|Monto: Bs. " + monto + "|FechaPago: " + fechaPago;
            } else {
                return "ERROR|HTTP_" + status + "|" + codigoUnico + "|" +
                       (mensaje.isEmpty() ? "No se pudo procesar el pago de la boleta." : mensaje);
            }

        } catch (Exception e) {
            return "ERROR|CONEXION_CENTRAL|" + codigoUnico + "|No se pudo conectar con Central REST Laravel: " + e.getMessage();
        }
    }

    private static String escapeJson(String s) {
        if (s == null) return "";
        return s.replace("\\", "\\\\").replace("\"", "\\\"");
    }

    private static String extractJsonString(String json, String key) {
        if (json == null) return "";
        String pattern = "\"" + key + "\"\\s*:\\s*\"([^\"]*)\"";
        java.util.regex.Matcher m = java.util.regex.Pattern.compile(pattern).matcher(json);
        return m.find() ? m.group(1) : "";
    }

    private static String extractJsonValue(String json, String key) {
        if (json == null) return "";
        String pattern = "\"" + key + "\"\\s*:\\s*([^,}]+)";
        java.util.regex.Matcher m = java.util.regex.Pattern.compile(pattern).matcher(json);
        return m.find() ? m.group(1).replace("\"", "").trim() : "";
    }
}
