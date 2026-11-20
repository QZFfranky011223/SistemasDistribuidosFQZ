require('dotenv').config();
const express = require('express');
const mongoose = require('mongoose');

const app = express();
app.use(express.json());

const mongoUri = `mongodb://${process.env.MONGO_HOST || 'localhost'}:${process.env.MONGO_PORT || 27017}/${process.env.DB_AUTH || 'bd_auth'}`;

mongoose.connect(mongoUri)
  .then(() => console.log('MongoDB conectado - serviceAuth'))
  .catch(err => console.error('Error MongoDB:', err));

app.use('/api/v1/auth', require('./routes/auth.routes'));

app.get('/health', (req, res) => res.json({ status: 'ok', service: 'auth' }));

const PORT = process.env.PORT || 3001;
app.listen(PORT, () => console.log(`serviceAuth corriendo en puerto ${PORT}`));
