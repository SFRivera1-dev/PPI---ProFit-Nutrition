<?php
// =====================================================================
// RECURSO: foro (publicaciones)
//
//   GET  /api/foro.php          -> las publicaciones ACTIVAS, separadas        (público: no requiere sesión)
//        200 {"oficiales":[...], "comunidad":[...]}     (las más nuevas primero)
//   GET  /api/foro.php?id=5     -> una publicación con todas sus respuestas    (público)
//        200 {"publicacion":{...}, "respuestas":[...]}  404 si no existe o está desactivada
//   POST /api/foro.php          -> crea una publicación        201 {"publicacion":{...}}   (hay que iniciar sesión)
//        cuerpo: {categoria, titulo, contenido}
//        Si quien publica es ADMIN o PROFESIONAL, sale como OFICIAL; si es CLIENTE, en la comunidad.
//
// Las respuestas se crean en api/foro_respuestas.php · la moderación en api/foro_admin.php
// =====================================================================
require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/http/respuesta.php';
require_once __DIR__ . '/../includes/http/peticion.php';
require_once __DIR__ . '/../includes/seguridad/autenticacion.php';
require_once __DIR__ . '/../includes/foro/publico.php';
require_once __DIR__ . '/../includes/foro/crear.php';

$metodo = permitir(['GET', 'POST']);


/* ---------- GET (sin sesión) ---------- */
if ($metodo === 'GET') {

    $id = (int) parametroUrl('id');
    if ($id > 0) {
        $publicacion = buscarPublicacion($id);
        if ($publicacion === null || !$publicacion['activa']) {
            responderError(404, 'Esa publicación no existe o ya no está disponible.');
        }
        responder(['publicacion' => $publicacion, 'respuestas' => respuestasDe($id)]);
    }

    responder([
        'oficiales' => buscarPublicaciones('p.activa = 1 AND p.oficial = 1', [], 12),
        'comunidad' => buscarPublicaciones('p.activa = 1 AND p.oficial = 0', [], 60),
    ]);
}


/* ---------- POST ---------- */
$usuario = usuarioActual();                              // 401 si no hay token válido
responder(['publicacion' => crearPublicacion($usuario, leerJson())], 201);
