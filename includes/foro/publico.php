<?php
// =====================================================================
// QUÉ DATOS DEL FORO SALEN HACIA EL NAVEGADOR
//   publicacionPublica($fila)  -> una publicación (el correo del autor NUNCA sale)
//   buscarPublicaciones(...)   -> lista de publicaciones
//   buscarPublicacion($id)     -> una publicación (o null)
//   respuestasDe($idPub)       -> las respuestas de una publicación
// =====================================================================
require_once __DIR__ . '/../conexion.php';

const SELECT_PUBLICACIONES =
    'SELECT p.*, u.nombre AS autor_nombre, u.apellido AS autor_apellido, u.rol AS autor_rol,
            (SELECT COUNT(*) FROM foro_respuestas r WHERE r.publicacion_id = p.id) AS total_respuestas
     FROM foro_publicaciones p
     JOIN usuarios u ON u.id = p.usuario_id';

// Qué hace: Convierte una fila de la consulta de publicaciones en lo que sale hacia el navegador. El
//           correo del autor NUNCA sale.
// Recibe:   $fila: una fila de SELECT_PUBLICACIONES.
// Entrega:  Un arreglo con id, categoria, titulo, contenido, oficial, activa, creado_en, respuestas
//           y autor.
function publicacionPublica($fila)
{
    return [
        'id'         => (int) $fila['id'],
        'categoria'  => $fila['categoria'],
        'titulo'     => $fila['titulo'],
        'contenido'  => $fila['contenido'],
        'oficial'    => ((int) $fila['oficial']) === 1,
        'activa'     => ((int) $fila['activa']) === 1,
        'creado_en'  => $fila['creado_en'],
        'respuestas' => (int) $fila['total_respuestas'],
        'autor'      => [
            'nombre'   => $fila['autor_nombre'],
            'apellido' => $fila['autor_apellido'],
            'rol'      => $fila['autor_rol'],
        ],
    ];
}

// Qué hace: Consulta publicaciones con una condición y un límite (la más nueva primero). La
//           $condicion es un pedazo de WHERE escrito por nosotros, nunca texto del navegador.
// Recibe:   $condicion: el WHERE ("" = todas) · $valores: los datos de la condición · $limite:
//           cuántas como máximo.
// Entrega:  Una lista de publicaciones (publicacionPublica()).
function buscarPublicaciones($condicion, $valores, $limite)
{
    global $pdo;

    $sql = SELECT_PUBLICACIONES;
    if ($condicion !== '') {
        $sql = $sql . ' WHERE ' . $condicion;
    }
    $sql = $sql . ' ORDER BY p.creado_en DESC, p.id DESC LIMIT ' . (int) $limite;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    $lista = [];
    foreach ($stmt->fetchAll() as $fila) {
        $lista[] = publicacionPublica($fila);
    }
    return $lista;
}

// Qué hace: Busca UNA publicación por su id (esté activa o no).
// Recibe:   $id: el id de la publicación.
// Entrega:  La publicación, o null si no existe.
function buscarPublicacion($id)
{
    $lista = buscarPublicaciones('p.id = :id', [':id' => $id], 1);
    if (count($lista) === 0) {
        return null;
    }
    return $lista[0];
}

// Qué hace: Convierte una fila de la tabla de respuestas en lo que sale hacia el navegador.
// Recibe:   $fila: una fila de la consulta de respuestas (con los datos del autor).
// Entrega:  Un arreglo con id, contenido, creado_en y autor (nombre, apellido y rol).
function respuestaPublica($fila)
{
    return [
        'id'        => (int) $fila['id'],
        'contenido' => $fila['contenido'],
        'creado_en' => $fila['creado_en'],
        'autor'     => [
            'nombre'   => $fila['autor_nombre'],
            'apellido' => $fila['autor_apellido'],
            'rol'      => $fila['autor_rol'],
        ],
    ];
}

// Qué hace: Trae las respuestas de una publicación, de la más antigua a la más nueva.
// Recibe:   $idPublicacion: el id de la publicación.
// Entrega:  Una lista de respuestas (respuestaPublica()).
function respuestasDe($idPublicacion)
{
    global $pdo;

    $stmt = $pdo->prepare(
        'SELECT r.*, u.nombre AS autor_nombre, u.apellido AS autor_apellido, u.rol AS autor_rol
         FROM foro_respuestas r
         JOIN usuarios u ON u.id = r.usuario_id
         WHERE r.publicacion_id = :id
         ORDER BY r.creado_en ASC, r.id ASC
         LIMIT 500'
    );
    $stmt->execute([':id' => $idPublicacion]);

    $lista = [];
    foreach ($stmt->fetchAll() as $fila) {
        $lista[] = respuestaPublica($fila);
    }
    return $lista;
}
