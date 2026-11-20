const Usuario = require('../models/Usuario');
const jwt = require('jsonwebtoken');

exports.register = async (req, res) => {
  try {
    const { nombre, email, password, rol } = req.body;
    if (!nombre || !email || !password) {
      return res.status(400).json({ error: 'nombre, email y password son requeridos' });
    }
    const existe = await Usuario.findOne({ email });
    if (existe) return res.status(409).json({ error: 'El email ya existe' });

    const usuario = await Usuario.create({ nombre, email, password, rol });
    return res.status(201).json({ id: usuario._id, email: usuario.email, rol: usuario.rol });
  } catch (err) {
    return res.status(500).json({ error: err.message });
  }
};

exports.login = async (req, res) => {
  try {
    const { email, password } = req.body;
    if (!email || !password) {
      return res.status(400).json({ error: 'email y password son requeridos' });
    }
    const usuario = await Usuario.findOne({ email });
    if (!usuario) return res.status(401).json({ error: 'Credenciales incorrectas' });

    const valid = await usuario.comparePassword(password);
    if (!valid) return res.status(401).json({ error: 'Credenciales incorrectas' });

    const token = jwt.sign(
      { id: usuario._id, rol: usuario.rol },
      process.env.JWT_SECRET || 'secret',
      { expiresIn: process.env.JWT_EXPIRES || '24h' }
    );
    return res.json({ token, id: usuario._id, rol: usuario.rol });
  } catch (err) {
    return res.status(500).json({ error: err.message });
  }
};
