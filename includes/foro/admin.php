<?php
// =====================================================================
// MODERACIÓN DEL FORO (solo admin)
//   listarForoAdmin($busqueda)            -> TODAS las publicaciones (también las desactivadas)
//   cambiarActivaPublicacion($id,$activa) -> desactivar (deja de mostrarse) o reactivar
// =====================================================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../http/respuesta.php';
require_once __DIR__ . '/publico.php';

// Qué hace: Lista TODAS las publicaciones del foro, también las desactivadas, para que el admin las
//           modere.
// Recibe:   $busqueda: texto para buscar en título, contenido, autor o categoría ("" = todas).
// Entrega:  Una lista de publicaciones (publicacionPublica()).
function listarForoAdmin($busqueda)
{
    $condicion = '';
    $valores   = [];

    $busqueda = trim($busqueda);
    if ($busqueda !== '') {
        $condicion = '(p.titulo LIKE :t1 OR p.contenido LIKE :t2 OR u.nombre LIKE :t3 OR u.apellido LIKE :t4 OR p.categoria LIKE :t5)';
        $texto = '%' . $busqueda . '%';
        $valores[':t1'] = $texto;
        $valores[':t2'] = $texto;
        $valores[':t3'] = $texto;
        $valores[':t4'] = $texto;
        $valores[':t5'] = $texto;
    }
    return buscarPublicaciones($condicion, $valores, 300);
}

// Qué hace: Desactiva una publicación (deja de mostrarse en el foro) o la vuelve a activar.
// Recibe:   $id: el id de la publicación · $activa: false para desactivar, true para reactivar.
// Entrega:  La publicación actualizada. Responde 404 si no existe.
function cambiarActivaPublicacion($id, $activa)
{
    global $pdo;

    if (buscarPublicacion($id) === null) {
        responderError(404, 'La publicación no existe.');
    }

    $valor = 0;
    if ($activa) {
        $valor = 1;
    }
    $stmt = $pdo->prepare('UPDATE foro_publicaciones SET activa = :activa WHERE id = :id');
    $stmt->execute([':activa' => $valor, ':id' => $id]);

    return buscarPublicacion($id);
}
