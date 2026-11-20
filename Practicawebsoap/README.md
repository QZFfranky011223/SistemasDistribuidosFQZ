# Sistema Distribuido: Central de Pagos

Este proyecto implementa el sistema completo de **Central de Pagos** distribuida solicitado en el examen/práctica, integrando múltiples tecnologías y paradigmas de comunicación:

```
[Cliente Pagos (C#)]
        │
        ▼ (SOAP)
[Servidor Pagos (C# SOAP)] ────────► [SEGIP (GraphQL / Node.js)]
        │
        ├──────────────────────────► [CESSA (REST Facturas / Laravel-PHP)]
        │
        └──────────────────────────► [ELAPAS (REST Facturas / Laravel-PHP)]
```

---

## 📁 Estructura del Proyecto

- **`1_ClientePagos/`** (Punto 1):
  - Aplicación en **C#** con interfaz interactiva por pantalla.
  - Permite ingresar `ci`, `nombres`, `primerApellido` y `segundoApellido`.
  - Botón para **Obtener el Detalle de Deudas**.
  - Si los datos son validados correctamente por el SEGIP, muestra la lista de deudas pendientes de CESSA y ELAPAS. Caso contrario muestra el error descriptivo.
  - Permite seleccionar una de las deudas y enviar la solicitud `PagarDeuda` al Servidor SOAP.

- **`2_ServidorPagosSOAP/`** (Punto 2):
  - Servidor **SOAP en C# (.NET 10)**.
  - Endpoint: `http://localhost:8000/ws/pagos` (WSDL en `http://localhost:8000/ws/pagos?wsdl`).
  - Operaciones:
    - `Deuda[] verDeuda(String CI, String primerApellido, String segundoApellido, String nombres)`:
      1. Realiza consulta GraphQL al servicio SEGIP.
      2. Valida que el CI exista y que coincidan nombres y apellidos.
      3. Si es válido, consulta en paralelo las facturas REST de CESSA y ELAPAS.
      4. Consolida y retorna el arreglo `Deuda[]`.
    - `PagarDeuda(Deuda c)`:
      - Identifica la empresa deudora (`CESSA` o `ELAPAS`).
      - Llama al endpoint REST `/pagar` de la empresa correspondiente para asentar el pago.
      - Retorna la confirmación del pago en formato SOAP.

- **`3_SEGIP_GraphQL/`** (Punto 3):
  - Servicio **GraphQL en Node.js (Express + GraphQL)**.
  - Puerto: `5000` (`http://localhost:5000/graphql`).
  - Esquema con los campos: `CI`, `Nombres`, `PrimerApellido`, `SegundoApellido`.
  - Permite buscar por `CI` mediante la query `obtenerPersona(CI: String!)`.

- **`4_CESSA_Rest/`** (Punto 4):
  - Servicio **REST para la empresa eléctrica CESSA**.
  - Puerto: `8001` (`http://localhost:8001/facturas`).
  - Recurso: `Facturas`.
  - Incluye:
    - Controlador de Laravel `CessaFacturaController.php` (registrado en el proyecto Laravel).
    - Servidor REST PHP autónomo `server.php` para ejecución directa en puerto 8001.

- **`5_ELAPAS_Rest/`** (Punto 5):
  - Servicio **REST para la empresa de agua ELAPAS**.
  - Puerto: `8002` (`http://localhost:8002/facturas`).
  - Recurso: `Facturas`.
  - Incluye:
    - Controlador de Laravel `ElapasFacturaController.php` (registrado en el proyecto Laravel).
    - Servidor REST PHP autónomo `server.php` para ejecución directa en puerto 8002.

---

## 🚀 Cómo Ejecutar

### Opción Rápida (Recomendada):

1. **Iniciar todos los servidores (SEGIP, CESSA, ELAPAS y Servidor SOAP):**
   - Haz doble clic en `iniciar_servicios.bat` (o ejecuta `.\iniciar_servicios.ps1` en PowerShell).
   - Se abrirán 4 consolas, una por cada servicio.

2. **Iniciar la aplicación Cliente:**
   - Haz doble clic en `iniciar_cliente.bat` (o ejecuta `.\iniciar_cliente.ps1` en PowerShell).

---

## 🧪 Datos de Prueba Configurados

| CI | Nombres | Primer Apellido | Segundo Apellido | Empresa | Factura | Monto (Bs.) |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **1234567** | **Juan** | **Perez** | **Gomez** | CESSA | C-101 | 150.50 |
| **1234567** | **Juan** | **Perez** | **Gomez** | CESSA | C-102 | 180.00 |
| **1234567** | **Juan** | **Perez** | **Gomez** | ELAPAS | E-201 | 85.00 |
| **1234567** | **Juan** | **Perez** | **Gomez** | ELAPAS | E-202 | 92.50 |
| **7654321** | **Maria** | **Lopez** | **Rojas** | CESSA | C-103 | 95.00 |
| **8888888** | **Carlos** | **Fernandez** | **Quiroga** | ELAPAS | E-203 | 110.00 |

### Caso de Prueba 1: Validación y Pago Exitoso
1. En el Cliente, introduce:
   - CI: `1234567`
   - Nombres: `Juan`
   - Primer Apellido: `Perez`
   - Segundo Apellido: `Gomez`
2. Presiona ENTER para **Obtener Detalle de Deudas**.
3. Verás la lista de facturas de CESSA y ELAPAS.
4. Selecciona una deuda (ej. la `1`) y presiona ENTER para pagar.
5. El pago se procesa vía SOAP ➔ REST y la factura queda marcada como pagada.

### Caso de Prueba 2: Datos no coinciden en SEGIP (Manejo de Error)
1. Introduce:
   - CI: `1234567`
   - Nombres: `Pedro` (nombre incorrecto)
   - Primer Apellido: `Perez`
   - Segundo Apellido: `Gomez`
2. El sistema consultará a SEGIP, detectará la discordancia y detendrá el flujo mostrando el error en pantalla.
