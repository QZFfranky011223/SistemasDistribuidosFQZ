const router = require('express').Router();
const verifyToken = require('../middleware/verifyToken');
const ctrl = require('../controllers/ventas.controller');

router.use(verifyToken);

router.post('/', ctrl.create);
router.get('/', ctrl.list);
router.get('/resumen', ctrl.resumen);
router.get('/:id', ctrl.getOne);
router.put('/:id/anular', ctrl.anular);

module.exports = router;
