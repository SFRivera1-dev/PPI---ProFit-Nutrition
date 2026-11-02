<?php
// =====================================================================
// REGLAS PARA UN PEDIDO
// El servidor SIEMPRE valida (el navegador se puede saltar).
// Si algo está mal, responde el error y termina el script.
// =====================================================================
require_once __DIR__ . '/../http/respuesta.php';

const COSTO_DOMICILIO        = 10000;        // el domicilio SIEMPRE cuesta esto (lo fija el servidor, no el navegador)
const PAIS_ENTREGA           = 'Colombia';   // solo se entrega en Colombia
const MAX_LINEAS_PEDIDO      = 50;           // productos distintos en un pedido
const MAX_UNIDADES_PRODUCTO  = 99;           // unidades de un mismo producto

// Qué hace: Valida los datos de entrega: nombre y apellido (2 a 60), ciudad (2 a 60) y dirección (5
//           a 120).
// Recibe:   $nombre, $apellido, $ciudad, $direccion: ya limpios con texto().
// Entrega:  Nada. Si algo está mal, responde 422 y termina.
function validarEntrega($nombre, $apellido, $ciudad, $direccion)
{
    if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 60) {
        responderError(422, 'El nombre debe tener entre 2 y 60 caracteres.');
    }
    if (mb_strlen($apellido) < 2 || mb_strlen($apellido) > 60) {
        responderError(422, 'El apellido debe tener entre 2 y 60 caracteres.');
    }
    if (mb_strlen($ciudad) < 2 || mb_strlen($ciudad) > 60) {
        responderError(422, 'La ciudad debe tener entre 2 y 60 caracteres.');
    }
    if (mb_strlen($direccion) < 5 || mb_strlen($direccion) > 120) {
        responderError(422, 'La dirección debe tener entre 5 y 120 caracteres.');
    }
}

// Qué hace: Convierte "productos": [{"id":1,"cantidad":2}, ...] en un arreglo simple id => cantidad.
//           Si un producto viene repetido, suma las cantidades. Cualquier cosa rara responde 422.
// Recibe:   $datos: el JSON del pedido.
// Entrega:  Un arreglo [id_producto => cantidad].
function leerLineasPedido($datos)
{
    if (!isset($datos['productos']) || !is_array($datos['productos'])) {
        responderError(422, 'Falta la lista de productos del pedido.');
    }
    if (count($datos['productos']) === 0) {
        responderError(422, 'Tu carrito está vacío.');
    }
    if (count($datos['productos']) > MAX_LINEAS_PEDIDO) {
        responderError(422, 'El pedido tiene demasiados productos.');
    }

    $lineas = [];
    foreach ($datos['productos'] as $linea) {
        if (!is_array($linea) || !isset($linea['id']) || !isset($linea['cantidad'])) {
            responderError(422, 'Cada producto necesita id y cantidad.');
        }
        if (!is_int($linea['id']) || $linea['id'] <= 0) {
            responderError(422, 'Hay un producto con id no válido.');
        }
        if (!is_int($linea['cantidad']) || $linea['cantidad'] <= 0) {
            responderError(422, 'La cantidad de cada producto debe ser un número entero mayor a 0.');
        }

        $id = $linea['id'];
        if (!isset($lineas[$id])) {
            $lineas[$id] = 0;
        }
        $lineas[$id] = $lineas[$id] + $linea['cantidad'];

        if ($lineas[$id] > MAX_UNIDADES_PRODUCTO) {
            responderError(422, 'No puedes pedir más de ' . MAX_UNIDADES_PRODUCTO . ' unidades de un mismo producto.');
        }
    }
    return $lineas;
}
