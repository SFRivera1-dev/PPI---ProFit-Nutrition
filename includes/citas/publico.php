<?php
// =====================================================================
// QUÉ DATOS DE LA CITA SALEN HACIA EL NAVEGADOR
//   citaPublica($fila)     -> una fila de la consulta con el cliente ya armado
//   buscarCitas($condicion, $valores, $orden, $limite) -> lista de citas
//   buscarCita($id)        -> una cita (o null)
// ¡Ojo! Nunca sale el correo de otro cliente a quien no sea profesional/admin: eso lo decide api/citas.php.
// =====================================================================
require_once __DIR__ . '/../conexion.php';

const SELECT_CITAS =
    'SELECT c.*, u.nombre AS cliente_nombre, u.apellido AS cliente_apellido, u.correo AS cliente_correo
     FROM citas c
     JOIN usuarios u ON u.id = c.usuario_id';

// Qué hace: Convierte una fila de la consulta de citas en la cita que sale hacia el navegador, con
//           los datos del cliente adentro.
// Recibe:   $fila: una fila de SELECT_CITAS.
// Entrega:  Un arreglo con id, servicio, objetivo, tiempo, fecha, hora, estado, creado_en y cliente.
function citaPublica($fila)
{
    return [
        'id'        => (int) $fila['id'],
        'servicio'  => $fila['servicio'],
        'objetivo'  => $fila['objetivo'],
        'tiempo'    => $fila['tiempo'],
        'fecha'     => $fila['fecha'],                  // 2026-11-02
        'hora'      => $fila['hora'],                   // 14:00
        'estado'    => $fila['estado'],
        'creado_en' => $fila['creado_en'],
        'cliente'   => [
            'id'       => (int) $fila['usuario_id'],
            'nombre'   => $fila['cliente_nombre'],
            'apellido' => $fila['cliente_apellido'],
            'correo'   => $fila['cliente_correo'],
        ],
    ];
}

// Qué hace: Consulta citas con una condición, un orden y un límite. La $condicion es un pedazo de
//           WHERE escrito por nosotros (ej: "c.usuario_id = :u"), nunca texto que venga del
//           navegador.
// Recibe:   $condicion: el WHERE ("" = todas) · $valores: los datos de la condición · $orden: ORDER
//           BY · $limite: cuántas como máximo.
// Entrega:  Una lista de citas (citaPublica()).
function buscarCitas($condicion, $valores, $orden, $limite)
{
    global $pdo;

    $sql = SELECT_CITAS;
    if ($condicion !== '') {
        $sql = $sql . ' WHERE ' . $condicion;
    }
    $sql = $sql . ' ORDER BY ' . $orden . ' LIMIT ' . (int) $limite;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    $lista = [];
    foreach ($stmt->fetchAll() as $fila) {
        $lista[] = citaPublica($fila);
    }
    return $lista;
}

// Qué hace: Busca UNA cita por su id.
// Recibe:   $id: el id de la cita.
// Entrega:  La cita (citaPublica()), o null si no existe.
function buscarCita($id)
{
    $lista = buscarCitas('c.id = :id', [':id' => $id], 'c.id', 1);
    if (count($lista) === 0) {
        return null;
    }
    return $lista[0];
}
