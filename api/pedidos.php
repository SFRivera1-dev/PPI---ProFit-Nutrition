<?php
// =====================================================================
// RECURSO: pedidos   (hay que iniciar sesión)
//
//   POST /api/pedidos.php          -> crea el pedido     201 {"pedido":{...}}
//        cuerpo: {nombre, apellido, ciudad, direccion, productos:[{id, cantidad}]}
//        (el servidor calcula precios, domicilio y total; la tarjeta NO viaja)
//   GET  /api/pedidos.php          -> mis pedidos        200 {"pedidos":[...]}  (cada uno con sus productos; máx. 100)
// =====================================================================
require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/http/respuesta.php';
require_once __DIR__ . '/../includes/http/peticion.php';
require_once __DIR__ . '/../includes/seguridad/autenticacion.php';
require_once __DIR__ . '/../includes/pedidos/validar.php';
require_once __DIR__ . '/../includes/pedidos/publico.php';
require_once __DIR__ . '/../includes/pedidos/crear.php';

$metodo  = permitir(['GET', 'POST']);
$usuario = usuarioActual();                          // 401 si no hay token válido


/* ---------- POST: crear un pedido ---------- */
if ($metodo === 'POST') {
    $pedido = crearPedido($usuario, leerJson());
    unset($pedido['usuario_id']);
    responder(['pedido' => $pedido], 201);
}


/* ---------- GET: mis pedidos (los más nuevos primero, con sus productos) ---------- */
responder(['pedidos' => pedidosDeUsuario($usuario['id'])]);
