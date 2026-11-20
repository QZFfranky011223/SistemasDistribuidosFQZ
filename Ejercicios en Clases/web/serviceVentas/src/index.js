require('dotenv').config();
const express = require('express');
const mongoose = require('mongoose');

const app = express();
app.use(express.json());

const mongoUri = `mongodb://${process.env.MONGO_HOST || 'localhost'}:${process.env.MONGO_PORT || 27017}/${process.env.DB_VENTAS || 'bd_ventas'}`;

mongoose.connect(mongoUri)
  .then(() => console.log('MongoDB conectado - serviceVentas'))
  .catch(err => console.error('Error MongoDB:', err));

app.use('/api/v1/ventas', require('./routes/ventas.routes'));

app.get('/health', (req, res) => res.json({ status: 'ok', service: 'ventas' }));

const PORT = process.env.PORT || 3002;
app.listen(PORT, () => console.log(`serviceVentas corriendo en puerto ${PORT}`));
