<?php
// =====================================================================
// PACIENTES = clientes que han tenido (o tienen) al menos una cita
//   listarPacientes($busqueda)  -> lista con el resumen de cada paciente
//   historialPaciente($id)      -> el paciente y TODAS sus citas (la más nueva primero)
// Lo usa api/pacientes.php (profesional y admin).
// =====================================================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../http/respuesta.php';
require_once __DIR__ . '/publico.php';

// Qué hace: Lista los pacientes: los clientes que han tenido al menos una cita, con el resumen de
//           cada uno (total, finalizadas, en curso y fecha de la última). La más reciente primero.
// Recibe:   $busqueda: texto para buscar por nombre, apellido o correo (vacío = todos).
// Entrega:  Una lista de pacientes (pacientePublico()).
function listarPacientes($busqueda)
{
    global $pdo;

    $condicion = '';
    $valores   = [];

    $busqueda = trim($busqueda);
    if ($busqueda !== '') {
        $condicion = 'WHERE (u.nombre LIKE :t1 OR u.apellido LIKE :t2 OR u.correo LIKE :t3)';
        $texto = '%' . $busqueda . '%';
        $valores[':t1'] = $texto;
        $valores[':t2'] = $texto;
        $valores[':t3'] = $texto;
    }

    // JOIN (no LEFT JOIN): solo salen los clientes que tienen alguna cita
    $sql = "SELECT u.id, u.nombre, u.apellido, u.correo,
                   COUNT(c.id) AS total_citas,
                   SUM(CASE WHEN c.estado = 'finalizada' THEN 1 ELSE 0 END) AS finalizadas,
                   SUM(CASE WHEN c.estado <> 'finalizada' THEN 1 ELSE 0 END) AS en_curso,
                   MAX(c.fecha) AS ultima_fecha
            FROM usuarios u
            JOIN citas c ON c.usuario_id = u.id
            $condicion
            GROUP BY u.id, u.nombre, u.apellido, u.correo
            ORDER BY ultima_fecha DESC, u.id DESC
            LIMIT 500";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    $lista = [];
    foreach ($stmt->fetchAll() as $fila) {
        $lista[] = pacientePublico($fila);
    }
    return $lista;
}

// Qué hace: Convierte la fila del resumen de un paciente en lo que sale hacia el navegador, con los
//           números ya como enteros.
// Recibe:   $fila: una fila de la consulta de pacientes.
// Entrega:  Un arreglo con id, nombre, apellido, correo, total_citas, finalizadas, en_curso y
//           ultima_fecha.
function pacientePublico($fila)
{
    return [
        'id'           => (int) $fila['id'],
        'nombre'       => $fila['nombre'],
        'apellido'     => $fila['apellido'],
        'correo'       => $fila['correo'],
        'total_citas'  => (int) $fila['total_citas'],
        'finalizadas'  => (int) $fila['finalizadas'],
        'en_curso'     => (int) $fila['en_curso'],
        'ultima_fecha' => $fila['ultima_fecha'],
    ];
}

// Qué hace: Arma el historial de un paciente: su resumen y todas sus citas, la más nueva primero.
// Recibe:   $id: el id del cliente.
// Entrega:  ["paciente" => resumen, "citas" => lista]. Responde 404 si ese cliente no tiene citas.
function historialPaciente($id)
{
    $pacientes = resumenPaciente($id);
    if ($pacientes === null) {
        responderError(404, 'Ese paciente no tiene citas registradas.');
    }

    $citas = buscarCitas('c.usuario_id = :usuario', [':usuario' => $id], 'c.fecha DESC, c.hora DESC', 500);

    return ['paciente' => $pacientes, 'citas' => $citas];
}

// Qué hace: Calcula el resumen de UN paciente (total de citas, finalizadas, en curso y última
//           fecha).
// Recibe:   $id: el id del cliente.
// Entrega:  El resumen (pacientePublico()), o null si no existe o nunca tuvo citas.
function resumenPaciente($id)
{
    global $pdo;

    $sql = "SELECT u.id, u.nombre, u.apellido, u.correo,
                   COUNT(c.id) AS total_citas,
                   SUM(CASE WHEN c.estado = 'finalizada' THEN 1 ELSE 0 END) AS finalizadas,
                   SUM(CASE WHEN c.estado <> 'finalizada' THEN 1 ELSE 0 END) AS en_curso,
                   MAX(c.fecha) AS ultima_fecha
            FROM usuarios u
            JOIN citas c ON c.usuario_id = u.id
            WHERE u.id = :id
            GROUP BY u.id, u.nombre, u.apellido, u.correo";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $fila = $stmt->fetch();

    if (!$fila) {
        return null;
    }
    return pacientePublico($fila);
}
