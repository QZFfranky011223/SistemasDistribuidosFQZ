# REST Service (Node.js) – Cotizacion

## Requisitos
- **Node.js 18+**
- **npm** (incluido con Node)
- **MySQL** con la base `banco_central` (ejecutar `create_database.sql`).

## Instalación
```powershell
cd "C:\Users\72828\SistemasDistribuidosFQZ\Ejercicios en Clases\web\rest-node"
npm install
```

## Ejecutar
```powershell
npm start
```
La API escuchará en `http://localhost:3000`.

## Endpoints
| Método | Ruta | Parámetros / Body | Respuesta | Código HTTP |
|--------|------|-------------------|-----------|-------------|
| `GET` | `/cotizacion?fecha=YYYY-MM-DD` | `fecha` como query string | `{ fecha, cotizacion, cotizacion_oficial }` o `{ error }` | 200 / 404 / 400 / 500 |
| `POST` | `/cotizacion` | `{ "fecha": "YYYY-MM-DD", "monto": number }` | `{ message }` o `{ error }` | 201 / 409 / 400 / 500 |

## Pruebas rápidas con `curl`
```powershell
# Consulta existente
curl "http://localhost:3000/cotizacion?fecha=2025-04-04"

# Registro nuevo
curl -X POST http://localhost:3000/cotizacion -H "Content-Type: application/json" -d "{\"fecha\":\"2025-04-13\",\"monto\":9.50}"
```

## Notas
- La tasa oficial está fijada en **6.97** según el enunciado.
- El pool de conexiones está configurado en `index.js`; ajuste `user`, `password` y `host` según su entorno.
