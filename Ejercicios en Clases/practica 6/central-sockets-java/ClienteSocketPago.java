import java.io.*;
import java.net.Socket;
import java.nio.charset.StandardCharsets;
import java.util.Scanner;

/**
 * Cliente Java de Consola para la Entidad Recaudadora (Socket TCP)
 * Permite enviar directamente una orden de pago al ServidorSocketPago (Puerto 5000).
 *
 * Uso:
 *   java ClienteSocketPago BOL-1001 1234567 "Banco Union"
 *   (o sin argumentos en modo interactivo)
 */
public class ClienteSocketPago {

    private static final String HOST = "localhost";
    private static final int PORT = 5000;

    public static void main(String[] args) {
        String codigo;
        String ci;
        String entidad;

        if (args.length >= 1) {
            codigo = args[0];
            ci = args.length >= 2 ? args[1] : "";
            entidad = args.length >= 3 ? args[2] : "Banco Unión (Java Socket Client)";
        } else {
            Scanner sc = new Scanner(System.in);
            System.out.println("=== ENTIDAD RECAUDADORA - CLIENTE SOCKET TCP (JAVA) ===");
            System.out.print("Ingrese Código Único de Boleta (ej. BOL-1001): ");
            codigo = sc.nextLine().trim();
            System.out.print("Ingrese CI del Conductor (ej. 1234567): ");
            ci = sc.nextLine().trim();
            System.out.print("Nombre Entidad Recaudadora (ej. Banco Unión): ");
            entidad = sc.nextLine().trim();
            if (entidad.isEmpty()) entidad = "Banco Unión";
        }

        String trama = "PAGAR|" + codigo + "|" + ci + "|" + entidad;
        System.out.println("\n[Socket TCP] Conectando a " + HOST + ":" + PORT + "...");
        System.out.println("[Socket TCP] Enviando trama -> " + trama);

        try (
            Socket socket = new Socket(HOST, PORT);
            PrintWriter out = new PrintWriter(new OutputStreamWriter(socket.getOutputStream(), StandardCharsets.UTF_8), true);
            BufferedReader in = new BufferedReader(new InputStreamReader(socket.getInputStream(), StandardCharsets.UTF_8))
        ) {
            out.println(trama);
            String respuesta = in.readLine();
            System.out.println("[Socket TCP] Respuesta de Central de Registro:");
            System.out.println("   " + respuesta);
        } catch (IOException e) {
            System.err.println("[Error Socket TCP] No se pudo conectar al servidor en " + HOST + ":" + PORT + " -> " + e.getMessage());
        }
    }
}
