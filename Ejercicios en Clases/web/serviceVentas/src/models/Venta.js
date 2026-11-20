const mongoose = require('mongoose');

const ventaSchema = new mongoose.Schema({
  medicamento_id: { type: Number, required: true },
  cliente_id: { type: Number, required: true },
  cantidad: { type: Number, required: true, min: 1 },
  precio_unitario: { type: Number, required: true, min: 0 },
  total: { type: Number },
  metodo_pago: { type: String, enum: ['Efectivo', 'QR', 'Tarjeta'], default: 'Efectivo' },
  estado: { type: String, enum: ['pagada', 'anulada'], default: 'pagada' }
}, { timestamps: true });

ventaSchema.pre('save', function(next) {
  this.total = this.cantidad * this.precio_unitario;
  next();
});

module.exports = mongoose.model('Venta', ventaSchema);
