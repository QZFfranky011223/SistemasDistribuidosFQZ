using System.Text;
using System.Xml.Linq;

namespace ClientePagos
{
    class Program
    {
        private static readonly HttpClient httpClient = new HttpClient();
        private const string SOAP_SERVICE_URL = "http://localhost:8000/ws/pagos";

        static async Task Main(string[] args)
        {
            Console.OutputEncoding = Encoding.UTF8;

            while (true)
            {
                Console.Clear();
                ImprimirEncabezado();

                Console.ForegroundColor = ConsoleColor.Cyan;
                Console.WriteLine("╔══════════════════════════════════════════════════════════════════╗");
                Console.WriteLine("║            FORMULARIO DE CONSULTA Y PAGO DE FACTURAS             ║");
                Console.WriteLine("╚══════════════════════════════════════════════════════════════════╝");
                Console.ResetColor();
                Console.WriteLine();

                // Datos de prueba sugeridos
                Console.ForegroundColor = ConsoleColor.DarkGray;
                Console.WriteLine("  (Sugerencia de prueba: CI: 1234567, Juan Perez Gomez)");
                Console.ResetColor();
                Console.WriteLine();

                Console.Write("  [ Campo 1 ] Ingrese C.I.              : ");
                string ci = Console.ReadLine()?.Trim() ?? "";

                Console.Write("  [ Campo 2 ] Ingrese Nombres           : ");
                string nombres = Console.ReadLine()?.Trim() ?? "";

                Console.Write("  [ Campo 3 ] Ingrese Primer Apellido   : ");
                string primerApellido = Console.ReadLine()?.Trim() ?? "";

                Console.Write("  [ Campo 4 ] Ingrese Segundo Apellido  : ");
                string segundoApellido = Console.ReadLine()?.Trim() ?? "";

                Console.WriteLine();
                Console.ForegroundColor = ConsoleColor.Yellow;
                Console.WriteLine("  ┌──────────────────────────────────────────────────────────────┐");
                Console.WriteLine("  │  [ BOTON ]: Presione ENTER para 'Obtener Detalle de Deudas'  │");
                Console.WriteLine("  └──────────────────────────────────────────────────────────────┘");
                Console.ResetColor();
                Console.ReadLine();

                Console.ForegroundColor = ConsoleColor.Blue;
                Console.WriteLine("  >> Conectando con Servidor Pagos SOAP...");
                Console.WriteLine("  >> Validando datos en SEGIP y consultando deudas en CESSA y ELAPAS...");
                Console.ResetColor();

                var (exito, errorMensaje, deudas) = await LlamarSoapVerDeuda(ci, nombres, primerApellido, segundoApellido);

                if (!exito)
                {
                    Console.WriteLine();
                    Console.ForegroundColor = ConsoleColor.Red;
                    Console.WriteLine("  ╔════════════════════════════════════════════════════════════════╗");
                    Console.WriteLine("  ║                             ERROR                              ║");
                    Console.WriteLine("  ╚════════════════════════════════════════════════════════════════╝");
                    Console.WriteLine($"  {errorMensaje}");
                    Console.ResetColor();
                    Console.WriteLine();
                    Console.WriteLine("  Presione cualquier tecla para reiniciar...");
                    Console.ReadKey();
                    continue;
                }

                Console.WriteLine();
                Console.ForegroundColor = ConsoleColor.Green;
                Console.WriteLine("  ✓ Validación en SEGIP exitosa. Cliente verificado correctamente.");
                Console.ResetColor();
                Console.WriteLine();

                if (deudas.Count == 0)
                {
                    Console.ForegroundColor = ConsoleColor.Yellow;
                    Console.WriteLine("  No se encontraron deudas pendientes en CESSA ni en ELAPAS.");
                    Console.ResetColor();
                    Console.WriteLine();
                    Console.WriteLine("  Presione cualquier tecla para volver al inicio...");
                    Console.ReadKey();
                    continue;
                }

                // Mostrar lista de deudas
                Console.ForegroundColor = ConsoleColor.Magenta;
                Console.WriteLine("  ═════════════════════════════ DETALLE DE DEUDAS ═════════════════════════════");
                Console.ResetColor();
                Console.WriteLine("  {0,-5} | {1,-10} | {2,-15} | {3,-12} | {4,-10}", "#", "EMPRESA", "NRO FACTURA", "PERIODO", "MONTO (Bs.)");
                Console.WriteLine("  " + new string('-', 62));

                for (int i = 0; i < deudas.Count; i++)
                {
                    var d = deudas[i];
                    Console.WriteLine("  [{0}]   | {1,-10} | {2,-15} | {3,-12} | {4,10:F2}", (i + 1), d.Empresa, d.NroFactura, d.Periodo, d.Monto);
                }
                Console.WriteLine("  " + new string('-', 62));
                Console.WriteLine();

                Console.ForegroundColor = ConsoleColor.Cyan;
                Console.Write($"  Seleccione el número de deuda a pagar (1 - {deudas.Count}) [o 0 para cancelar]: ");
                Console.ResetColor();

                if (int.TryParse(Console.ReadLine(), out int seleccion) && seleccion >= 1 && seleccion <= deudas.Count)
                {
                    var deudaSeleccionada = deudas[seleccion - 1];

                    Console.WriteLine();
                    Console.ForegroundColor = ConsoleColor.Yellow;
                    Console.WriteLine($"  >> Enviando solicitud SOAP PagarDeuda({deudaSeleccionada.Empresa} - {deudaSeleccionada.NroFactura} - Bs. {deudaSeleccionada.Monto:F2})...");
                    Console.ResetColor();

                    var (pagoExitoso, respuestaPago) = await LlamarSoapPagarDeuda(deudaSeleccionada);

                    Console.WriteLine();
                    if (pagoExitoso)
                    {
                        Console.ForegroundColor = ConsoleColor.Green;
                        Console.WriteLine("  ╔════════════════════════════════════════════════════════════════╗");
                        Console.WriteLine("  ║                   PAGO REALIZADO CON EXITO                    ║");
                        Console.WriteLine("  ╚════════════════════════════════════════════════════════════════╝");
                        Console.WriteLine($"  Resultado: {respuestaPago}");
                        Console.WriteLine($"  Empresa  : {deudaSeleccionada.Empresa}");
                        Console.WriteLine($"  Factura  : {deudaSeleccionada.NroFactura}");
                        Console.WriteLine($"  Monto    : Bs. {deudaSeleccionada.Monto:F2}");
                        Console.ResetColor();
                    }
                    else
                    {
                        Console.ForegroundColor = ConsoleColor.Red;
                        Console.WriteLine("  ╔════════════════════════════════════════════════════════════════╗");
                        Console.WriteLine("  ║                     ERROR AL PROCESAR PAGO                     ║");
                        Console.WriteLine("  ╚════════════════════════════════════════════════════════════════╝");
                        Console.WriteLine($"  {respuestaPago}");
                        Console.ResetColor();
                    }
                }
                else
                {
                    Console.WriteLine("  Operación cancelada o selección inválida.");
                }

                Console.WriteLine();
                Console.WriteLine("  Presione cualquier tecla para realizar otra consulta o 'S' para salir...");
                var key = Console.ReadKey();
                if (key.Key == ConsoleKey.S) break;
            }
        }

        static void ImprimirEncabezado()
        {
            Console.ForegroundColor = ConsoleColor.White;
            Console.BackgroundColor = ConsoleColor.DarkBlue;
            Console.WriteLine("********************************************************************");
            Console.WriteLine("*                CENTRAL DE PAGOS DISTRIBUIDA (C#)                 *");
            Console.WriteLine("*           SEGIP (GraphQL) | CESSA (REST) | ELAPAS (REST)          *");
            Console.WriteLine("********************************************************************");
            Console.ResetColor();
            Console.WriteLine();
        }

        static async Task<(bool Exito, string Error, List<DeudaItem> Deudas)> LlamarSoapVerDeuda(
            string ci, string nombres, string primerApellido, string segundoApellido)
        {
            var deudas = new List<DeudaItem>();
            string soapEnvelope = $@"<?xml version=""1.0"" encoding=""utf-8""?>
<soapenv:Envelope xmlns:soapenv=""http://schemas.xmlsoap.org/soap/envelope/"" xmlns:pag=""http://servicios.pagos.com/"">
  <soapenv:Header/>
  <soapenv:Body>
    <pag:verDeuda>
      <pag:CI>{ci}</pag:CI>
      <pag:primerApellido>{primerApellido}</pag:primerApellido>
      <pag:segundoApellido>{segundoApellido}</pag:segundoApellido>
      <pag:nombres>{nombres}</pag:nombres>
    </pag:verDeuda>
  </soapenv:Body>
</soapenv:Envelope>";

            try
            {
                var content = new StringContent(soapEnvelope, Encoding.UTF8, "text/xml");
                var response = await httpClient.PostAsync(SOAP_SERVICE_URL, content);
                string responseXml = await response.Content.ReadAsStringAsync();

                var doc = XDocument.Parse(responseXml);

                // Verificar si hay Fault
                var fault = doc.Descendants().FirstOrDefault(d => d.Name.LocalName == "Fault");
                if (fault != null)
                {
                    string faultString = fault.Elements().FirstOrDefault(e => e.Name.LocalName == "faultstring")?.Value ?? "Error SOAP";
                    return (false, faultString, deudas);
                }

                // Parsear deudas
                var deudasElements = doc.Descendants().Where(d => d.Name.LocalName == "Deuda");
                foreach (var d in deudasElements)
                {
                    int.TryParse(d.Elements().FirstOrDefault(e => e.Name.LocalName == "id")?.Value, out int id);
                    string empresa = d.Elements().FirstOrDefault(e => e.Name.LocalName == "empresa")?.Value ?? "";
                    string nroFactura = d.Elements().FirstOrDefault(e => e.Name.LocalName == "nroFactura")?.Value ?? "";
                    string ciDeuda = d.Elements().FirstOrDefault(e => e.Name.LocalName == "ci")?.Value ?? "";
                    string periodo = d.Elements().FirstOrDefault(e => e.Name.LocalName == "periodo")?.Value ?? "";
                    decimal.TryParse(d.Elements().FirstOrDefault(e => e.Name.LocalName == "monto")?.Value, out decimal monto);

                    deudas.Add(new DeudaItem
                    {
                        Id = id,
                        Empresa = empresa,
                        NroFactura = nroFactura,
                        CI = ciDeuda,
                        Periodo = periodo,
                        Monto = monto
                    });
                }

                return (true, "", deudas);
            }
            catch (Exception ex)
            {
                return (false, $"Error de comunicación con el Servidor SOAP: {ex.Message}", deudas);
            }
        }

        static async Task<(bool Exito, string Mensaje)> LlamarSoapPagarDeuda(DeudaItem deuda)
        {
            string soapEnvelope = $@"<?xml version=""1.0"" encoding=""utf-8""?>
<soapenv:Envelope xmlns:soapenv=""http://schemas.xmlsoap.org/soap/envelope/"" xmlns:pag=""http://servicios.pagos.com/"">
  <soapenv:Header/>
  <soapenv:Body>
    <pag:PagarDeuda>
      <pag:c>
        <pag:id>{deuda.Id}</pag:id>
        <pag:empresa>{deuda.Empresa}</pag:empresa>
        <pag:nroFactura>{deuda.NroFactura}</pag:nroFactura>
        <pag:ci>{deuda.CI}</pag:ci>
        <pag:periodo>{deuda.Periodo}</pag:periodo>
        <pag:monto>{deuda.Monto}</pag:monto>
      </pag:c>
    </pag:PagarDeuda>
  </soapenv:Body>
</soapenv:Envelope>";

            try
            {
                var content = new StringContent(soapEnvelope, Encoding.UTF8, "text/xml");
                var response = await httpClient.PostAsync(SOAP_SERVICE_URL, content);
                string responseXml = await response.Content.ReadAsStringAsync();

                var doc = XDocument.Parse(responseXml);

                var fault = doc.Descendants().FirstOrDefault(d => d.Name.LocalName == "Fault");
                if (fault != null)
                {
                    string faultString = fault.Elements().FirstOrDefault(e => e.Name.LocalName == "faultstring")?.Value ?? "Error al pagar";
                    return (false, faultString);
                }

                var resultadoElem = doc.Descendants().FirstOrDefault(d => d.Name.LocalName == "resultado");
                var mensajeElem = doc.Descendants().FirstOrDefault(d => d.Name.LocalName == "mensaje");

                bool esOk = resultadoElem?.Value == "OK";
                string mensaje = mensajeElem?.Value ?? (esOk ? "Pago efectuado" : "Error al procesar pago");

                return (esOk, mensaje);
            }
            catch (Exception ex)
            {
                return (false, $"Error de conexión al pagar deuda: {ex.Message}");
            }
        }
    }

    class DeudaItem
    {
        public int Id { get; set; }
        public string Empresa { get; set; } = "";
        public string NroFactura { get; set; } = "";
        public string CI { get; set; } = "";
        public string Periodo { get; set; } = "";
        public decimal Monto { get; set; }
    }
}
