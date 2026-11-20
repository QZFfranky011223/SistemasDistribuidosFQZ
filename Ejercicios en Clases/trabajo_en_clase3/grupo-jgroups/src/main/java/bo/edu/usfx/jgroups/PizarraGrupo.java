package bo.edu.usfx.jgroups;

import org.jgroups.Address;
import org.jgroups.JChannel;
import org.jgroups.Message;
import org.jgroups.ObjectMessage;
import org.jgroups.Receiver;
import org.jgroups.View;
import org.jgroups.util.Util;

import java.io.BufferedReader;
import java.io.DataInputStream;
import java.io.DataOutputStream;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.util.ArrayList;
import java.util.List;

public class PizarraGrupo implements Receiver {

    private JChannel canal;
    private View vistaAnterior;

    // ESTADO REPLICADO: cada nodo tiene su copia, JGroups las mantiene iguales
    private final List<String> historial = new ArrayList<>();

    @Override
    public void viewAccepted(View vista) {
        if (vistaAnterior != null) {
            Address[][] cambios = View.diff(vistaAnterior, vista);
            for (Address a : cambios[0]) {
                System.out.println("** ENTRO: " + a);
            }
            for (Address a : cambios[1]) {
                System.out.println("** SALIO: " + a);
            }
        }
        vistaAnterior = vista;
        System.out.println("** Vista " + vista.getViewId().getId()
                + " | coordinador: " + vista.getCoord()
                + " | miembros: " + vista.getMembers());
    }

    @Override
    public void receive(Message msg) {
        String tipo = (msg.getDest() == null) ? "" : "(privado) ";
        String texto = tipo + msg.getSrc() + ": " + msg.getObject();

        synchronized (historial) {
            historial.add(texto);
        }
        System.out.println(texto);
    }

    @Override
    public void getState(OutputStream salida) throws Exception {
        synchronized (historial) {
            Util.objectToStream(historial, new DataOutputStream(salida));
        }
        System.out.println("** Estado enviado a un nuevo miembro ("
                + historial.size() + " lineas)");
    }

    @Override
    @SuppressWarnings("unchecked")
    public void setState(InputStream entrada) throws Exception {
        List<String> recibido = Util.objectFromStream(new DataInputStream(entrada));
        synchronized (historial) {
            historial.clear();
            historial.addAll(recibido);
        }
        System.out.println("** Estado recibido: " + recibido.size() + " lineas");
        recibido.forEach(l -> System.out.println("  " + l));
    }

    public void iniciar(String nombre) throws Exception {
        canal = new JChannel(System.getProperty("config", "udp.xml"));
        if (nombre != null && !nombre.trim().isEmpty()) {
            canal.name(nombre);
        }
        canal.setReceiver(this);
        canal.connect("PizarraSIS258");
        canal.getState(null, 10000);

        leerTeclado();
        canal.close();
    }

    private void leerTeclado() throws Exception {
        BufferedReader teclado = new BufferedReader(new InputStreamReader(System.in));
        System.out.println("Comandos: /quien /historial /privado <nombre> <texto> /salir");
        String linea;
        while (true) {
            System.out.print("> ");
            System.out.flush();
            linea = teclado.readLine();
            if (linea == null) {
                Thread.sleep(500);
                continue;
            }
            linea = linea.trim();
            if (linea.equals("/salir")) {
                break;
            }
            if (linea.equals("/quien")) {
                System.out.println("Miembros: " + canal.getView().getMembers()
                        + " | yo: " + canal.getAddress());
            } else if (linea.equals("/historial")) {
                System.out.println("--- Historial local ---");
                synchronized (historial) {
                    historial.forEach(System.out::println);
                }
            } else if (linea.startsWith("/privado ")) {
                enviarPrivado(linea);
            } else if (!linea.isEmpty()) {
                canal.send(new ObjectMessage(null, linea)); // multicast al grupo
            }
        }
    }

    private void enviarPrivado(String linea) throws Exception {
        String[] partes = linea.split(" ", 3);
        if (partes.length < 3) {
            System.out.println("Uso: /privado <nombre> <texto>");
            return;
        }
        Address destino = buscarPorNombre(partes[1]);
        if (destino == null) {
            System.out.println("No existe el miembro " + partes[1]);
            return;
        }
        canal.send(new ObjectMessage(destino, partes[2])); // unicast: solo a ese miembro
    }

    private Address buscarPorNombre(String nombreBuscado) {
        for (Address a : canal.getView().getMembers()) {
            if (a.toString().equals(nombreBuscado)) {
                return a;
            }
        }
        return null;
    }

    public static void main(String[] args) {
        try {
            String nombre = (args.length > 0) ? args[0] : null;
            new PizarraGrupo().iniciar(nombre);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}