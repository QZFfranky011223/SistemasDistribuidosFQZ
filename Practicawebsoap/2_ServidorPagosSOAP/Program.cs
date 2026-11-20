using System.Text;
using System.Text.Json;
using System.Xml.Linq;

var builder = WebApplication.CreateBuilder(args);

// Configurar puerto del Servidor SOAP (8000)
builder.WebHost.UseUrls("http://localhost:8000");

var app = builder.Build();

var httpClient = new HttpClient();

// URLs de los microservicios
string SEGIP_GRAPHQL_URL = "http://localhost:5000/graphql";
string CESSA_REST_URL = "http://localhost:8001/facturas";
string ELAPAS_REST_URL = "http://localhost:8002/facturas";

// Endpoint para obtener el WSDL
app.MapGet("/ws/pagos", (HttpContext context) =>
{
    if (context.Request.QueryString.Value?.Contains("wsdl", StringComparison.OrdinalIgnoreCase) == true)
    {
        var wsdl = GetWsdl();
        context.Response.ContentType = "text/xml; charset=utf-8";
        return context.Response.WriteAsync(wsdl);
    }
    return context.Response.WriteAsync("Servidor de Pagos SOAP activo. Accede a /ws/pagos?wsdl para ver la definición.");
});

// Endpoint SOAP principal que procesa verDeuda y PagarDeuda
app.MapPost("/ws/pagos", async (HttpContext context) =>
{
    using var reader = new StreamReader(context.Request.Body, Encoding.UTF8);
    string requestBody = await reader.ReadToEndAsync();

    if (string.IsNullOrWhiteSpace(requestBody))
    {
        context.Response.StatusCode = 400;
        await context.Response.WriteAsync("Cuerpo SOAP vacio");
        return;
    }

    try
    {
        var doc = XDocument.Parse(requestBody);
        XNamespace soapenv = "http://schemas.xmlsoap.org/soap/envelope/";
        var body = doc.Descendants().FirstOrDefault(d => d.Name.LocalName == "Body");

        if (body == null)
        {
            context.Response.StatusCode = 400;
            await context.Response.WriteAsync("Mensaje SOAP no valido (falta Body)");
            return;
        }

        var operationElement = body.Elements().FirstOrDefault();
        string operationName = operationElement?.Name.LocalName ?? "";

        context.Response.ContentType = "text/xml; charset=utf-8";

        if (operationName == "verDeuda")
        {
            string ci = operationElement.Elements().FirstOrDefault(e => e.Name.LocalName.Equals("CI", StringComparison.OrdinalIgnoreCase))?.Value?.Trim() ?? "";
            string nombres = operationElement.Elements().FirstOrDefault(e => e.Name.LocalName.Equals("nombres", StringComparison.OrdinalIgnoreCase))?.Value?.Trim() ?? "";
            string primerApellido = operationElement.Elements().FirstOrDefault(e => e.Name.LocalName.Equals("primerApellido", StringComparison.OrdinalIgnoreCase))?.Value?.Trim() ?? "";
            string segundoApellido = operationElement.Elements().FirstOrDefault(e => e.Name.LocalName.Equals("segundoApellido", StringComparison.OrdinalIgnoreCase))?.Value?.Trim() ?? "";

            Console.WriteLine($"[SOAP verDeuda] Solicitud para CI: {ci}, Nombre: {nombres} {primerApellido} {segundoApellido}");

            // 1. Validar en SEGIP GraphQL
            var personaSegip = await ConsultarSegip(httpClient, SEGIP_GRAPHQL_URL, ci);
            if (personaSegip == null)
            {
                string soapError = GenerarSoapFault("Client", "El CI no existe en el sistema SEGIP.");
                context.Response.StatusCode = 500;
                await context.Response.WriteAsync(soapError);
                return;
            }

            // Validar que coincidan nombres y apellidos
            bool coincide = string.Equals(personaSegip.Nombres, nombres, StringComparison.OrdinalIgnoreCase) &&
                            string.Equals(personaSegip.PrimerApellido, primerApellido, StringComparison.OrdinalIgnoreCase) &&
                            string.Equals(personaSegip.SegundoApellido, segundoApellido, StringComparison.OrdinalIgnoreCase);

            if (!coincide)
            {
                string soapError = GenerarSoapFault("Client", $"Datos no coinciden con SEGIP. Registrado: {personaSegip.Nombres} {personaSegip.PrimerApellido} {personaSegip.SegundoApellido}");
                context.Response.StatusCode = 500;
                await context.Response.WriteAsync(soapError);
                return;
            }

            // 2. Consultar Facturas pendientes en CESSA y ELAPAS REST
            var deudas = new List<Deuda>();
            var deudasCessa = await ConsultarFacturasRest(httpClient, $"{CESSA_REST_URL}?ci={ci}", "CESSA");
            var deudasElapas = await ConsultarFacturasRest(httpClient, $"{ELAPAS_REST_URL}?ci={ci}", "ELAPAS");

            deudas.AddRange(deudasCessa);
            deudas.AddRange(deudasElapas);

            // 3. Responder SOAP con la lista de Deudas
            string soapResponse = GenerarSoapVerDeudaResponse(deudas);
            await context.Response.WriteAsync(soapResponse);
            return;
        }
        else if (operationName == "PagarDeuda")
        {
            var deudaElem = operationElement.Elements().FirstOrDefault(e => e.Name.LocalName.Equals("c", StringComparison.OrdinalIgnoreCase))
                         ?? operationElement;

            string idStr = deudaElem.Elements().FirstOrDefault(e => e.Name.LocalName.Equals("id", StringComparison.OrdinalIgnoreCase))?.Value ?? "0";
            string empresa = deudaElem.Elements().FirstOrDefault(e => e.Name.LocalName.Equals("empresa", StringComparison.OrdinalIgnoreCase))?.Value?.Trim() ?? "";
            string nroFactura = deudaElem.Elements().FirstOrDefault(e => e.Name.LocalName.Equals("nroFactura", StringComparison.OrdinalIgnoreCase))?.Value?.Trim() ?? "";
            string montoStr = deudaElem.Elements().FirstOrDefault(e => e.Name.LocalName.Equals("monto", StringComparison.OrdinalIgnoreCase))?.Value ?? "0";

            int.TryParse(idStr, out int id);
            decimal.TryParse(montoStr, out decimal monto);

            Console.WriteLine($"[SOAP PagarDeuda] Procesando pago para empresa: {empresa}, Factura: {nroFactura}, Id: {id}");

            string urlPago = empresa.Equals("CESSA", StringComparison.OrdinalIgnoreCase) ? $"{CESSA_REST_URL}/pagar" : $"{ELAPAS_REST_URL}/pagar";

            bool exito = await RegistrarPagoRest(httpClient, urlPago, id, nroFactura);

            string soapResponse = GenerarSoapPagarDeudaResponse(exito, exito ? $"Pago registrado exitosamente en {empresa} para la factura {nroFactura}." : $"Error al registrar el pago en {empresa}.");
            await context.Response.WriteAsync(soapResponse);
            return;
        }
        else
        {
            string soapError = GenerarSoapFault("Client", $"Operacion '{operationName}' desconocida.");
            context.Response.StatusCode = 404;
            await context.Response.WriteAsync(soapError);
        }
    }
    catch (Exception ex)
    {
        context.Response.StatusCode = 500;
        await context.Response.WriteAsync(GenerarSoapFault("Server", ex.Message));
    }
});

Console.WriteLine("=================================================");
Console.WriteLine("   SERVIDOR DE PAGOS SOAP C# INICIADO            ");
Console.WriteLine("   URL: http://localhost:8000/ws/pagos           ");
Console.WriteLine("   WSDL: http://localhost:8000/ws/pagos?wsdl     ");
Console.WriteLine("=================================================");

app.Run();

// Métodos auxiliares
static async Task<PersonaSegip?> ConsultarSegip(HttpClient client, string url, string ci)
{
    try
    {
        var gqlRequest = new
        {
            query = $@"{{
                obtenerPersona(CI: ""{ci}"") {{
                    CI
                    Nombres
                    PrimerApellido
                    SegundoApellido
                }}
            }}"
        };

        var content = new StringContent(JsonSerializer.Serialize(gqlRequest), Encoding.UTF8, "application/json");
        var response = await client.PostAsync(url, content);
        if (!response.IsSuccessStatusCode) return null;

        var json = await response.Content.ReadAsStringAsync();
        using var doc = JsonDocument.Parse(json);
        if (doc.RootElement.TryGetProperty("data", out var data) &&
            data.TryGetProperty("obtenerPersona", out var persona) &&
            persona.ValueKind != JsonValueKind.Null)
        {
            return new PersonaSegip
            {
                CI = persona.GetProperty("CI").GetString() ?? "",
                Nombres = persona.GetProperty("Nombres").GetString() ?? "",
                PrimerApellido = persona.GetProperty("PrimerApellido").GetString() ?? "",
                SegundoApellido = persona.GetProperty("SegundoApellido").GetString() ?? ""
            };
        }
    }
    catch (Exception ex)
    {
        Console.WriteLine($"[Error al consultar SEGIP]: {ex.Message}");
    }
    return null;
}

static async Task<List<Deuda>> ConsultarFacturasRest(HttpClient client, string url, string empresa)
{
    var list = new List<Deuda>();
    try
    {
        var response = await client.GetAsync(url);
        if (response.IsSuccessStatusCode)
        {
            var json = await response.Content.ReadAsStringAsync();
            using var doc = JsonDocument.Parse(json);
            if (doc.RootElement.ValueKind == JsonValueKind.Array)
            {
                foreach (var item in doc.RootElement.EnumerateArray())
                {
                    list.Add(new Deuda
                    {
                        Id = item.GetProperty("id").GetInt32(),
                        NroFactura = item.GetProperty("nroFactura").GetString() ?? "",
                        Empresa = empresa,
                        CI = item.GetProperty("ci").GetString() ?? "",
                        Periodo = item.GetProperty("periodo").GetString() ?? "",
                        Monto = item.GetProperty("monto").GetDecimal()
                    });
                }
            }
        }
    }
    catch (Exception ex)
    {
        Console.WriteLine($"[Error al consultar REST {empresa}]: {ex.Message}");
    }
    return list;
}

static async Task<bool> RegistrarPagoRest(HttpClient client, string url, int id, string nroFactura)
{
    try
    {
        var body = new { id = id, nroFactura = nroFactura };
        var content = new StringContent(JsonSerializer.Serialize(body), Encoding.UTF8, "application/json");
        var response = await client.PostAsync(url, content);
        return response.IsSuccessStatusCode;
    }
    catch
    {
        return false;
    }
}

static string GenerarSoapVerDeudaResponse(List<Deuda> deudas)
{
    var sb = new StringBuilder();
    sb.AppendLine(@"<?xml version=""1.0"" encoding=""utf-8""?>");
    sb.AppendLine(@"<soapenv:Envelope xmlns:soapenv=""http://schemas.xmlsoap.org/soap/envelope/"" xmlns:pag=""http://servicios.pagos.com/"">");
    sb.AppendLine(@"  <soapenv:Body>");
    sb.AppendLine(@"    <pag:verDeudaResponse>");
    sb.AppendLine(@"      <pag:return>");
    foreach (var d in deudas)
    {
        sb.AppendLine(@"        <pag:Deuda>");
        sb.AppendLine($"          <pag:id>{d.Id}</pag:id>");
        sb.AppendLine($"          <pag:empresa>{d.Empresa}</pag:empresa>");
        sb.AppendLine($"          <pag:nroFactura>{d.NroFactura}</pag:nroFactura>");
        sb.AppendLine($"          <pag:ci>{d.CI}</pag:ci>");
        sb.AppendLine($"          <pag:periodo>{d.Periodo}</pag:periodo>");
        sb.AppendLine($"          <pag:monto>{d.Monto}</pag:monto>");
        sb.AppendLine(@"        </pag:Deuda>");
    }
    sb.AppendLine(@"      </pag:return>");
    sb.AppendLine(@"    </pag:verDeudaResponse>");
    sb.AppendLine(@"  </soapenv:Body>");
    sb.AppendLine(@"</soapenv:Envelope>");
    return sb.ToString();
}

static string GenerarSoapPagarDeudaResponse(bool exito, string mensaje)
{
    var sb = new StringBuilder();
    sb.AppendLine(@"<?xml version=""1.0"" encoding=""utf-8""?>");
    sb.AppendLine(@"<soapenv:Envelope xmlns:soapenv=""http://schemas.xmlsoap.org/soap/envelope/"" xmlns:pag=""http://servicios.pagos.com/"">");
    sb.AppendLine(@"  <soapenv:Body>");
    sb.AppendLine(@"    <pag:PagarDeudaResponse>");
    sb.AppendLine($"      <pag:resultado>{(exito ? "OK" : "ERROR")}</pag:resultado>");
    sb.AppendLine($"      <pag:mensaje>{mensaje}</pag:mensaje>");
    sb.AppendLine(@"    </pag:PagarDeudaResponse>");
    sb.AppendLine(@"  </soapenv:Body>");
    sb.AppendLine(@"</soapenv:Envelope>");
    return sb.ToString();
}

static string GenerarSoapFault(string code, string message)
{
    return $@"<?xml version=""1.0"" encoding=""utf-8""?>
<soapenv:Envelope xmlns:soapenv=""http://schemas.xmlsoap.org/soap/envelope/"">
  <soapenv:Body>
    <soapenv:Fault>
      <faultcode>soapenv:{code}</faultcode>
      <faultstring>{message}</faultstring>
    </soapenv:Fault>
  </soapenv:Body>
</soapenv:Envelope>";
}

static string GetWsdl()
{
    return @"<?xml version=""1.0"" encoding=""utf-8""?>
<wsdl:definitions xmlns:wsdl=""http://schemas.xmlsoap.org/wsdl/""
                  xmlns:soap=""http://schemas.xmlsoap.org/wsdl/soap/""
                  xmlns:tns=""http://servicios.pagos.com/""
                  xmlns:xsd=""http://www.w3.org/2001/XMLSchema""
                  targetNamespace=""http://servicios.pagos.com/"">
  <wsdl:types>
    <xsd:schema targetNamespace=""http://servicios.pagos.com/"">
      <xsd:complexType name=""Deuda"">
        <xsd:sequence>
          <xsd:element name=""id"" type=""xsd:int""/>
          <xsd:element name=""empresa"" type=""xsd:string""/>
          <xsd:element name=""nroFactura"" type=""xsd:string""/>
          <xsd:element name=""ci"" type=""xsd:string""/>
          <xsd:element name=""periodo"" type=""xsd:string""/>
          <xsd:element name=""monto"" type=""xsd:decimal""/>
        </xsd:sequence>
      </xsd:complexType>
      <xsd:element name=""verDeuda"">
        <xsd:complexType>
          <xsd:sequence>
            <xsd:element name=""CI"" type=""xsd:string""/>
            <xsd:element name=""primerApellido"" type=""xsd:string""/>
            <xsd:element name=""segundoApellido"" type=""xsd:string""/>
            <xsd:element name=""nombres"" type=""xsd:string""/>
          </xsd:sequence>
        </xsd:complexType>
      </xsd:element>
      <xsd:element name=""PagarDeuda"">
        <xsd:complexType>
          <xsd:sequence>
            <xsd:element name=""c"" type=""tns:Deuda""/>
          </xsd:sequence>
        </xsd:complexType>
      </xsd:element>
    </xsd:schema>
  </wsdl:types>

  <wsdl:message name=""verDeudaRequest"">
    <wsdl:part name=""parameters"" element=""tns:verDeuda""/>
  </wsdl:message>
  <wsdl:message name=""PagarDeudaRequest"">
    <wsdl:part name=""parameters"" element=""tns:PagarDeuda""/>
  </wsdl:message>

  <wsdl:portType name=""ServidorPagosPortType"">
    <wsdl:operation name=""verDeuda"">
      <wsdl:input message=""tns:verDeudaRequest""/>
    </wsdl:operation>
    <wsdl:operation name=""PagarDeuda"">
      <wsdl:input message=""tns:PagarDeudaRequest""/>
    </wsdl:operation>
  </wsdl:portType>

  <wsdl:binding name=""ServidorPagosBinding"" type=""tns:ServidorPagosPortType"">
    <soap:binding style=""document"" transport=""http://schemas.xmlsoap.org/soap/http""/>
    <wsdl:operation name=""verDeuda"">
      <soap:operation soapAction=""http://servicios.pagos.com/verDeuda""/>
    </wsdl:operation>
    <wsdl:operation name=""PagarDeuda"">
      <soap:operation soapAction=""http://servicios.pagos.com/PagarDeuda""/>
    </wsdl:operation>
  </wsdl:binding>

  <wsdl:service name=""ServidorPagosService"">
    <wsdl:port name=""ServidorPagosPort"" binding=""tns:ServidorPagosBinding"">
      <soap:address location=""http://localhost:8000/ws/pagos""/>
    </wsdl:port>
  </wsdl:service>
</wsdl:definitions>";
}

public class PersonaSegip
{
    public string CI { get; set; } = "";
    public string Nombres { get; set; } = "";
    public string PrimerApellido { get; set; } = "";
    public string SegundoApellido { get; set; } = "";
}

public class Deuda
{
    public int Id { get; set; }
    public string Empresa { get; set; } = "";
    public string NroFactura { get; set; } = "";
    public string CI { get; set; } = "";
    public string Periodo { get; set; } = "";
    public decimal Monto { get; set; }
}
