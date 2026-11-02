<?php
// =====================================================================
// RECURSO: pedidos de TODOS los clientes   (solo ADMIN)
//
//   GET   /api/pedidos_admin.php               -> los pedidos más nuevos primero
//         filtros opcionales:  ?q=20005  (número de pedido, también "PF-20005")
//                              ?q=sofia  (nombre, apellido o correo del cliente)
//                              ?limite=5 (cuántos devolver; por defecto 100, máximo 100)
//         responde 200 {"pedidos":[...], "total": N}   (total = cuántos coinciden en total)
//   PATCH /api/pedidos_admin.php?id=20005      -> cambia el estado   200 {"pedido":{...}}
//         cuerpo: {"estado": "confirmado" | "enviado" | "entregado" | "cancelado"}
//         409 si el cambio no está permitido (pedido cancelado, o entregado -> cancelado)
//
// Al CANCELAR un pedido, las unidades vuelven al stock de los productos.
// (Los pedidos del propio cliente se piden en api/pedidos.php)
// =====================================================================
require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/http/respuesta.php';
require_once __DIR__ . '/../includes/http/peticion.php';
require_once __DIR__ . '/../includes/seguridad/autenticacion.php';
require_once __DIR__ . '/../includes/pedidos/admin.php';

$admin  = exigirRol('admin');                                  // 401 sin token · 403 si no es admin
$metodo = permitir(['GET', 'PATCH']);


/* ---------- GET: listar / buscar ---------- */
if ($metodo === 'GET') {
    $limite = (int) parametroUrl('limite');
    if ($limite < 1 || $limite > 100) {
        $limite = 100;
    }

    responder(listarPedidosAdmin(parametroUrl('q'), $limite));
}


/* ---------- PATCH: cambiar el estado de UN pedido (?id=) ---------- */
$id = (int) parametroUrl('id');
if ($id <= 0) {
    responderError(400, 'Falta el parámetro ?id= con el pedido.');
}

$datos  = leerJson();
$estado = texto($datos, 'estado');
if ($estado === '') {
    responderError(422, 'Falta el nuevo estado.');
}

responder(['pedido' => cambiarEstadoPedido($id, $estado)]);
