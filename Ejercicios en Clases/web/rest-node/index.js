const express = require('express');
const bodyParser = require('body-parser');
const mysql = require('mysql2/promise');

const app = express();
app.use(bodyParser.json());

// MySQL connection pool (adjust credentials as needed)
const pool = mysql.createPool({
  host: 'localhost',
  user: 'root',
  password: '', // TODO: set your MySQL password
  database: 'banco_central',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
});

// Helper to get a connection and run a query safely
async function query(sql, params) {
  const [rows] = await pool.execute(sql, params);
  return rows;
}

// GET /cotizacion?fecha=YYYY-MM-DD - obtain cotizacion by date
app.get('/cotizacion', async (req, res) => {
  const { fecha } = req.query;
  if (!fecha) {
    return res.status(400).json({ error: 'Parámetro "fecha" es requerido' });
  }
  try {
    const rows = await query(
      'SELECT fecha, cotizacion, cotizacion_oficial FROM cotizaciones WHERE fecha = ?',
      [fecha]
    );
    if (rows.length === 0) {
      return res.status(404).json({ error: 'Cotización no encontrada para la fecha solicitada' });
    }
    res.json(rows[0]);
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Error interno del servidor' });
  }
});

// POST /cotizacion - register a new cotizacion
// Expected JSON body: { "fecha": "YYYY-MM-DD", "monto": number }
app.post('/cotizacion', async (req, res) => {
  const { fecha, monto } = req.body;
  if (!fecha || typeof monto !== 'number') {
    return res.status(400).json({ error: 'Campos "fecha" (string) y "monto" (number) son requeridos' });
  }
  try {
    // Check duplicate
    const existing = await query('SELECT id FROM cotizaciones WHERE fecha = ?', [fecha]);
    if (existing.length > 0) {
      return res.status(409).json({ error: 'Ya existe una cotización para la fecha proporcionada' });
    }
    // Official rate is fixed at 6.97 for this exercise
    const oficial = 6.97;
    await query(
      'INSERT INTO cotizaciones (fecha, cotizacion, cotizacion_oficial) VALUES (?, ?, ?)',
      [fecha, monto, oficial]
    );
    res.status(201).json({ message: 'Registro exitoso' });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Error interno del servidor' });
  }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`REST API listening on http://localhost:${PORT}`);
});
