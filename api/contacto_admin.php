<?php
// =====================================================================
// RECURSO: mensajes de contáctanos   (solo ADMIN)
//
//   GET   /api/contacto_admin.php             -> los mensajes, el más nuevo primero
//         filtro opcional:  ?q=ana   (nombre, correo, asunto o mensaje)
//         200 {"mensajes":[...], "nuevos": N}     (nuevos = cuántos mensajes sin abrir hay en total)
//   PATCH /api/contacto_admin.php?id=3        -> cambia el estado   200 {"mensaje":{...}}
//         cuerpo: {"estado": "leido"}  o  {"estado": "nuevo"}
// =====================================================================
require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/http/respuesta.php';
require_once __DIR__ . '/../includes/http/peticion.php';
require_once __DIR__ . '/../includes/seguridad/autenticacion.php';
require_once __DIR__ . '/../includes/contacto/admin.php';

exigirRol('admin');                                      // 401 sin token · 403 si no es admin
$metodo = permitir(['GET', 'PATCH']);

if ($metodo === 'GET') {
    responder(listarContactosAdmin(parametroUrl('q')));
}

$id = (int) parametroUrl('id');
if ($id <= 0) {
    responderError(400, 'Falta el parámetro ?id= con el mensaje.');
}

$datos  = leerJson();
$estado = texto($datos, 'estado');
if ($estado === '') {
    responderError(422, 'Falta el nuevo estado.');
}

responder(['mensaje' => cambiarEstadoContacto($id, $estado)]);
