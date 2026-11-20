package bo.edu.usfx.jgroups;

import org.jgroups.JChannel;
import org.jgroups.Message;
import org.jgroups.ObjectMessage;
import org.jgroups.Receiver;
import org.jgroups.View;
import java.util.Scanner;

public class ChatGrupo {
    public static void main(String[] args) throws Exception {
        String nombre = args.length > 0 ? args[0] : "usuario-" + (int) (Math.random() * 100);
        JChannel canal = new JChannel();
        canal.name(nombre);

        canal.setReceiver(new Receiver() {
            @Override
            public void viewAccepted(View vista) {
                System.out.println("\n[SISTEMA] Nueva vista: " + vista);
            }

            @Override
            public void receive(Message msg) {
                System.out.println("[" + msg.getSrc() + "]: " + msg.getObject());
            }
        });

        canal.connect("ChatSIS258");
        System.out.println("=== Conectado al chat como: " + nombre + " ===");
        System.out.println("Escribe un mensaje y presiona Enter (o 'salir' para terminar):");

        Scanner scanner = new Scanner(System.in);
        while (scanner.hasNextLine()) {
            String linea = scanner.nextLine();
            if (linea.equalsIgnoreCase("salir")) {
                break;
            }
            if (!linea.trim().isEmpty()) {
                canal.send(new ObjectMessage(null, linea));
            }
        }

        canal.close();
        scanner.close();
        System.out.println("Has salido del chat.");
    }
}