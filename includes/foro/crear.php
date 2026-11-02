<?php
// =====================================================================
// CREAR UNA PUBLICACIÓN O UNA RESPUESTA
//   Una publicación de un ADMIN o un PROFESIONAL sale como OFICIAL.
//   La de un CLIENTE sale en la comunidad.
//   (Esto lo decide el servidor según el rol del token; el navegador no puede pedir ser "oficial".)
// =====================================================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../http/respuesta.php';
require_once __DIR__ . '/../http/peticion.php';
require_once __DIR__ . '/validar.php';
require_once __DIR__ . '/publico.php';

// Qué hace: Crea una publicación del foro. Si quien publica es admin o profesional sale como
//           OFICIAL; si es cliente, en la comunidad. Esto lo decide el servidor según el rol, no el
//           navegador.
// Recibe:   $usuario: quien publica (de usuarioActual()) · $datos: el JSON {categoria, titulo,
//           contenido}.
// Entrega:  La publicación creada (publicacionPublica()). Responde 422 si hay datos inválidos.
function crearPublicacion($usuario, $datos)
{
    global $pdo;

    $categoria = texto($datos, 'categoria');
    $titulo    = texto($datos, 'titulo');
    $contenido = texto($datos, 'contenido');
    validarPublicacion($categoria, $titulo, $contenido);

    $oficial = 0;
    if ($usuario['rol'] === 'admin' || $usuario['rol'] === 'profesional') {
        $oficial = 1;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO foro_publicaciones (usuario_id, categoria, titulo, contenido, oficial)
         VALUES (:usuario, :categoria, :titulo, :contenido, :oficial)'
    );
    $stmt->execute([
        ':usuario'   => $usuario['id'],
        ':categoria' => $categoria,
        ':titulo'    => $titulo,
        ':contenido' => $contenido,
        ':oficial'   => $oficial,
    ]);

    return buscarPublicacion((int) $pdo->lastInsertId());
}

// Qué hace: Guarda una respuesta a una publicación. Solo se puede responder a publicaciones que
//           existen y están activas.
// Recibe:   $usuario: quien responde · $idPublicacion: a cuál publicación · $datos: el JSON
//           {contenido}.
// Entrega:  La respuesta creada (id, contenido, creado_en y autor). Responde 422 o 404.
function crearRespuesta($usuario, $idPublicacion, $datos)
{
    global $pdo;

    $contenido = texto($datos, 'contenido');
    validarRespuesta($contenido);

    $publicacion = buscarPublicacion($idPublicacion);
    if ($publicacion === null || !$publicacion['activa']) {
        responderError(404, 'Esa publicación no existe o ya no está disponible.');
    }

    $stmt = $pdo->prepare(
        'INSERT INTO foro_respuestas (publicacion_id, usuario_id, contenido) VALUES (:publicacion, :usuario, :contenido)'
    );
    $stmt->execute([
        ':publicacion' => $idPublicacion,
        ':usuario'     => $usuario['id'],
        ':contenido'   => $contenido,
    ]);

    return [
        'id'        => (int) $pdo->lastInsertId(),
        'contenido' => $contenido,
        'creado_en' => date('Y-m-d H:i:s'),
        'autor'     => ['nombre' => $usuario['nombre'], 'apellido' => $usuario['apellido'], 'rol' => $usuario['rol']],
    ];
}
