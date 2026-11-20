# Sistema Distribuido de Gestión de Multas de Tránsito en Línea (`practica 6` — MySQL)

Implementación completa del examen para el **Organismo Operativo de Tránsito de la Policía Boliviana**, trabajando con **MySQL (`bd_multas_transito`)** e integrando **SOAP**, **REST (Laravel)**, **GraphQL (Node.js)**, **Sockets TCP (Java)**, **C# Windows Forms** y **Clientes Web**.

---

## Configuracion de Base de Datos MySQL

- **Motor:** MySQL / MariaDB (`127.0.0.1:3306`)
- **Base de Datos:** `bd_multas_transito`
- **Tabla:** `multas`
- **Usuario:** `root` (sin contraseña, estándar de XAMPP)
- **Script SQL incluido:** [bd_multas_transito.sql](file:///C:/Users/72828/OneDrive/Escritorio/Com350/practica%206/bd_multas_transito.sql)
- **Inicializador automático:** [init_mysql.php](file:///C:/Users/72828/OneDrive/Escritorio/Com350/practica%206/central-rest-laravel/database/init_mysql.php) (crea automáticamente la BD `bd_multas_transito`, la tabla `multas` y los datos de prueba al iniciar).

---

## Arquitectura y Módulos Implementados

| Actor / Componente | Tecnología Solicitada | Carpeta | Puerto / Endpoint |
| :--- | :--- | :--- | :--- |
| **1. SEGIP (Servicio SOAP)** | PHP SOAP (`Bool verificarCI(String ci)`) | `segip-soap/` | `http://localhost:8001/server.php?wsdl` |
| **2. Policía de Tránsito** | **Forms C#** (.NET Windows Forms) | `policia-forms-csharp/` | Aplicación de Escritorio (`PoliciaTransitoForms.exe`) |
| **3. Central de Registro — REST** | **Laravel + MySQL** (Recurso: `multa`) | `central-rest-laravel/` | `http://localhost:8000/api/multa` |
| **4. Central de Registro — GraphQL** | **Node.js + MySQL** | `central-graphql-node/` | `http://localhost:4000/graphql` |
| **5. Central de Registro — Sockets TCP** | **Java** (`ServerSocket` TCP) | `central-sockets-java/` | `localhost:5000` (TCP) |
| **6. Entidad Recaudadora (Cajero)** | Cliente Web **GraphQL** + Cliente **Socket TCP** | `entidad-recaudadora/` y `central-sockets-java/ClienteSocketPago.java` | `http://localhost:3001` |
| **7. Módulo Cliente Web (Ciudadano)** | Cliente Web **REST** | `cliente-web/` | `http://localhost:8000` |

---

## Cómo Iniciar Todo el Sistema

1. Asegúrate de tener **MySQL iniciado** (por ejemplo desde el panel de XAMPP).
2. Ejecuta con doble clic:

```bat
INICIAR_SISTEMA_MULTAS.bat
```

Este script crea automáticamente la base de datos `bd_multas_transito` en MySQL (o puedes importar manualmente `bd_multas_transito.sql` en phpMyAdmin) y levanta todos los servicios.
