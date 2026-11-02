<?php
// =====================================================================
// RECURSO: respuestas del foro   (hay que iniciar sesión; cualquier rol puede responder)
//
//   POST /api/foro_respuestas.php?publicacion=5   -> responde a la publicación 5
//        cuerpo: {"contenido": "Yo la tomo después de entrenar"}
//        201 {"respuesta":{...}}   404 si la publicación no existe o está desactivada
// (Para leer las respuestas: GET /api/foro.php?id=5)
// =====================================================================
require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/http/respuesta.php';
require_once __DIR__ . '/../includes/http/peticion.php';
require_once __DIR__ . '/../includes/seguridad/autenticacion.php';
require_once __DIR__ . '/../includes/foro/crear.php';

permitir(['POST']);
$usuario = usuarioActual();                              // 401 si no hay token válido

$idPublicacion = (int) parametroUrl('publicacion');
if ($idPublicacion <= 0) {
    responderError(400, 'Falta el parámetro ?publicacion= con la publicación.');
}

responder(['respuesta' => crearRespuesta($usuario, $idPublicacion, leerJson())], 201);
