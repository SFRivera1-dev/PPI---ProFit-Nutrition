<?php
// =====================================================================
// QUÉ DATOS DE UN MENSAJE DE CONTACTO SALEN HACIA EL NAVEGADOR (solo al admin)
//   contactoPublico($fila)   -> un mensaje
//   buscarContactos(...)     -> lista
//   buscarContacto($id)      -> uno (o null)
// =====================================================================
require_once __DIR__ . '/../conexion.php';

// Qué hace: Convierte una fila de la tabla contactos en lo que sale hacia el navegador (solo se usa
//           para el admin).
// Recibe:   $fila: una fila de la tabla contactos.
// Entrega:  Un arreglo con id, nombre, correo, asunto, mensaje, estado y creado_en.
function contactoPublico($fila)
{
    return [
        'id'        => (int) $fila['id'],
        'nombre'    => $fila['nombre'],
        'correo'    => $fila['correo'],
        'asunto'    => $fila['asunto'],
        'mensaje'   => $fila['mensaje'],
        'estado'    => $fila['estado'],
        'creado_en' => $fila['creado_en'],
    ];
}

// Qué hace: Consulta mensajes de contacto con una condición y un límite (el más nuevo primero). La
//           $condicion es un pedazo de WHERE escrito por nosotros, nunca texto del navegador.
// Recibe:   $condicion: el WHERE ("" = todos) · $valores: los datos de la condición · $limite:
//           cuántos como máximo.
// Entrega:  Una lista de mensajes (contactoPublico()).
function buscarContactos($condicion, $valores, $limite)
{
    global $pdo;

    $sql = 'SELECT * FROM contactos';
    if ($condicion !== '') {
        $sql = $sql . ' WHERE ' . $condicion;
    }
    $sql = $sql . ' ORDER BY creado_en DESC, id DESC LIMIT ' . (int) $limite;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    $lista = [];
    foreach ($stmt->fetchAll() as $fila) {
        $lista[] = contactoPublico($fila);
    }
    return $lista;
}

// Qué hace: Busca UN mensaje de contacto por su id.
// Recibe:   $id: el id del mensaje.
// Entrega:  El mensaje, o null si no existe.
function buscarContacto($id)
{
    $lista = buscarContactos('id = :id', [':id' => $id], 1);
    if (count($lista) === 0) {
        return null;
    }
    return $lista[0];
}
