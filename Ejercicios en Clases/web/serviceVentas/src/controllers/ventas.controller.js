const Venta = require('../models/Venta');

exports.create = async (req, res) => {
  try {
    const { medicamento_id, cliente_id, cantidad, precio_unitario, metodo_pago } = req.body;
    if (!medicamento_id || !cliente_id || !cantidad || precio_unitario === undefined) {
      return res.status(400).json({ error: 'Datos inválidos: medicamento_id, cliente_id, cantidad y precio_unitario son requeridos' });
    }
    if (cantidad < 1) return res.status(400).json({ error: 'La cantidad debe ser al menos 1' });
    if (precio_unitario < 0) return res.status(400).json({ error: 'El precio unitario debe ser >= 0' });

    const venta = new Venta({ medicamento_id, cliente_id, cantidad, precio_unitario, metodo_pago });
    await venta.save();
    return res.status(201).json(venta);
  } catch (err) {
    return res.status(400).json({ error: err.message });
  }
};

exports.list = async (req, res) => {
  try {
    const filter = {};
    if (req.query.estado) filter.estado = req.query.estado;
    const ventas = await Venta.find(filter).sort({ createdAt: -1 });
    return res.json(ventas);
  } catch (err) {
    return res.status(500).json({ error: err.message });
  }
};

exports.resumen = async (req, res) => {
  try {
    const resumen = await Venta.aggregate([
      {
        $group: {
          _id: '$metodo_pago',
          total_vendido: { $sum: '$total' },
          num_ventas: { $sum: 1 },
          ventas_pagadas: {
            $sum: { $cond: [{ $eq: ['$estado', 'pagada'] }, 1, 0] }
          }
        }
      }
    ]);
    return res.json(resumen);
  } catch (err) {
    return res.status(500).json({ error: err.message });
  }
};

exports.getOne = async (req, res) => {
  try {
    const venta = await Venta.findById(req.params.id);
    if (!venta) return res.status(404).json({ error: 'Venta no encontrada' });
    return res.json(venta);
  } catch (err) {
    return res.status(400).json({ error: 'ID inválido' });
  }
};

exports.anular = async (req, res) => {
  try {
    if (req.user.rol !== 'admin') {
      return res.status(403).json({ error: 'Solo el admin puede anular ventas' });
    }
    const venta = await Venta.findById(req.params.id);
    if (!venta) return res.status(404).json({ error: 'Venta no encontrada' });
    venta.estado = 'anulada';
    await venta.save();
    return res.json(venta);
  } catch (err) {
    return res.status(400).json({ error: 'ID inválido' });
  }
};
