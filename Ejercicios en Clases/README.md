# Project Overview

Este repositorio contiene **tres** implementaciones distintas de un mismo servicio de cotizaciones del Banco Central, cada una usando una tecnología diferente:

| Servicio | Tecnologías | Ruta del código |
|----------|-------------|-----------------|
| **SOAP** | Java (JAX‑WS) + Maven | `web/soap-java` |
| **REST** | Node.js + Express | `web/rest-node` |
| **GraphQL** | Laravel (PHP) + Lighthouse | `web/graphql-laravel` |

El objetivo es comparar cómo se definen, consumen y gestionan errores en SOAP, REST y GraphQL manteniendo la misma lógica de negocio y la misma base de datos relacional.

---

## 1. Base de datos

1. **Instalar MySQL** (ejemplo en Windows con XAMPP/MariaDB).  
2. Ejecutar el script SQL que crea la base y carga los datos iniciales:
   ```powershell
   mysql -u root -p < create_database.sql
   ```
   *La base se llamará `banco_central` y la tabla `cotizaciones` contendrá los registros indicados en el enunciado.*

---

## 2. Servicio SOAP (Java)

### Requisitos
- Java 8+ y **Maven** (`mvn` en PATH).
- MySQL en `localhost:3306` con usuario `root` (sin contraseña por defecto, modifíquela en `DatabaseUtil.java` si es necesario).

### Compilación y ejecución
```powershell
cd "C:\Users\72828\SistemasDistribuidosFQZ\Ejercicios en Clases\web\soap-java"
# Compilar y empaquetar
mvn clean package
# Ejecutar el servicio
java -cp target/cotizacion-soap-1.0-SNAPSHOT.jar com.example.cotizacion.Main
```
El endpoint queda disponible en **http://localhost:8080/cotizacion?wsdl**.

### Operaciones
| Operación | Parámetro | Resultado |
|-----------|-----------|-----------|
| `obtenerCotizacion(fecha)` | `String fecha` (`YYYY-MM-DD`) | Objeto `Cotizacion` o valores `0` si no existe. |
| `registrarCotizacion(fecha, monto)` | `String fecha`, `double monto` | Mensaje de éxito o error (duplicado, etc.). |

### Pruebas rápidas (PowerShell + `curl`)
Se incluyen ejemplos en `web/soap-java/README.md`.

---

## 3. Servicio REST (Node.js)

### Requisitos
- **Node.js 18+** y `npm`.
- MySQL con la base creada anteriormente.

### Instalación y ejecución
```powershell
cd "C:\Users\72828\SistemasDistribuidosFQZ\Ejercicios en Clases\web\rest-node"
npm install
npm start
```
La API escucha en **http://localhost:3000**.

### Endpoints
| Método | Ruta | Parámetros / Body | Respuesta |
|--------|------|-------------------|-----------|
| `GET` | `/cotizacion?fecha=YYYY-MM-DD` | `fecha` en query string | JSON con `fecha`, `cotizacion`, `cotizacion_oficial` o error. |
| `POST` | `/cotizacion` | `{ "fecha": "YYYY-MM-DD", "monto": number }` | Mensaje de éxito o error (404/409). |

### Pruebas rápidas (`curl`)
```powershell
# Consulta existente
curl "http://localhost:3000/cotizacion?fecha=2025-04-04"
# Registro nuevo
curl -X POST http://localhost:3000/cotizacion -H "Content-Type: application/json" -d "{\"fecha\":\"2025-04-13\",\"monto\":9.50}"
```

---

## 4. Servicio GraphQL (Laravel)

### Requisitos
- **PHP 8+**, Composer y una instalación de **Laravel 10**.
- MySQL con la base creada.

### Instalación
```powershell
cd "C:\Users\72828\SistemasDistribuidosFQZ\Ejercicios en Clases\web\graphql-laravel"
composer install
# Copiar .env de ejemplo y configurar credenciales DB
copy .env.example .env
# Generar la clave de aplicación
php artisan key:generate
# Ejecutar migraciones (crea la tabla)
php artisan migrate
```

### Ejecutar el servidor
```powershell
php artisan serve --port=8000
```
El endpoint GraphQL será **http://localhost:8000/graphql**.

### Schema y resolvers
- **Schema**: `graphql/schema.graphql` define el `Query cotizacion(fecha: String!)` y la `Mutation registrarCotizacion(fecha: String!, monto: Float!)`.
- **Resolvers**: `app/GraphQL/Queries/CotizacionQuery.php` y `app/GraphQL/Mutations/RegistrarCotizacion.php`.
- **Modelo Eloquent**: `app/Models/Cotizacion.php`.

### Pruebas rápidas (`curl`)
```powershell
# Consulta (Query)
curl -X POST http://localhost:8000/graphql ^
  -H "Content-Type: application/json" ^
  -d '{"query":"{ cotizacion(fecha: \"2025-04-04\") { fecha cotizacion cotizacionOficial } }"}'

# Registro (Mutation)
curl -X POST http://localhost:8000/graphql ^
  -H "Content-Type: application/json" ^
  -d '{"query":"mutation { registrarCotizacion(fecha: \"2025-04-13\", monto: 9.5) { mensaje } }"}'
```

---

## 5. Comparación
Una tabla comparativa y análisis breve están en **`web/COMPARACION.md`**.

---

## 6. Evidencias de pruebas
Para cada servicio se pueden generar logs o capturas usando los comandos `curl`/`Postman` mostrados arriba. Guarde la salida en archivos `*.log` o capturas de pantalla y añádalas al entregable.

---

## 7. ¿Qué falta?
- **Docker** (opcional) para levantar MySQL + los tres servicios.
- **Scripts de pruebas automatizadas** (por ejemplo, usando `npm test` o `phpunit`).
- **Documentación de API** (OpenAPI para REST, WSDL ya disponible para SOAP, introspección de GraphQL).

Si necesita alguna de esas piezas adicionales, hágamelo saber.
