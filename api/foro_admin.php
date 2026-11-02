<?php
// =====================================================================
// RECURSO: moderación del foro   (solo ADMIN)
//
//   GET   /api/foro_admin.php            -> TODAS las publicaciones, también las desactivadas (más nuevas primero)
//         filtro opcional: ?q=creatina   (título, contenido, autor o categoría)
//         200 {"publicaciones":[...]}
//   PATCH /api/foro_admin.php?id=5       -> desactiva o reactiva una publicación   200 {"publicacion":{...}}
//         cuerpo: {"activa": false}  (deja de mostrarse en el foro)  |  {"activa": true}  (vuelve a mostrarse)
// =====================================================================
require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/http/respuesta.php';
require_once __DIR__ . '/../includes/http/peticion.php';
require_once __DIR__ . '/../includes/seguridad/autenticacion.php';
require_once __DIR__ . '/../includes/foro/admin.php';

exigirRol('admin');                                      // 401 sin token · 403 si no es admin
$metodo = permitir(['GET', 'PATCH']);

if ($metodo === 'GET') {
    responder(['publicaciones' => listarForoAdmin(parametroUrl('q'))]);
}

$id = (int) parametroUrl('id');
if ($id <= 0) {
    responderError(400, 'Falta el parámetro ?id= con la publicación.');
}

$datos = leerJson();
if (!isset($datos['activa']) || !is_bool($datos['activa'])) {
    responderError(422, 'Indica "activa": true o false.');
}

responder(['publicacion' => cambiarActivaPublicacion($id, $datos['activa'])]);
