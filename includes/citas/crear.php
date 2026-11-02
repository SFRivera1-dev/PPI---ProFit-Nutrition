<?php
// =====================================================================
// CREAR UNA CITA (la agenda un cliente)
// Recibe:  el usuario (de usuarioActual()) y el JSON {servicio, objetivo, tiempo, fecha, hora}
// Entrega: la cita creada, en estado 'pendiente'
//
// Reglas:  1) una fecha y hora solo se puede tomar una vez
//          2) un cliente solo puede tener una cita pendiente o confirmada
//             (cuando esa queda 'finalizada', puede agendar otra)
// Las dos reglas las vuelve a garantizar la base de datos (UNIQUE), por si dos personas
// piden lo mismo en el mismo instante.
// =====================================================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../http/peticion.php';
require_once __DIR__ . '/validar.php';
require_once __DIR__ . '/publico.php';

const MENSAJE_HORARIO_OCUPADO = 'Esa fecha y hora ya están ocupadas. Elige otra.';
const MENSAJE_CITA_ACTIVA     = 'Ya tienes una cita pendiente o confirmada. Podrás agendar otra cuando esa quede finalizada.';

// Qué hace: Revisa si el cliente tiene una cita pendiente o confirmada (es decir, que aún no se
//           finaliza).
// Recibe:   $idUsuario: el id del cliente.
// Entrega:  true si tiene una cita activa, false si no.
function clienteTieneCitaActiva($idUsuario)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM citas WHERE usuario_id = :usuario AND estado <> 'finalizada'");
    $stmt->execute([':usuario' => $idUsuario]);
    return (int) $stmt->fetchColumn() > 0;
}

// Qué hace: Revisa si ya existe una cita en esa fecha y hora (una cita finalizada también deja
//           ocupada la hora).
// Recibe:   $fecha: "2026-11-02" · $hora: "14:00".
// Entrega:  true si está ocupada, false si está libre.
function horarioOcupado($fecha, $hora)
{
    global $pdo;

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM citas WHERE fecha = :fecha AND hora = :hora');
    $stmt->execute([':fecha' => $fecha, ':hora' => $hora]);
    return (int) $stmt->fetchColumn() > 0;
}

// Qué hace: Agenda una cita: valida los datos, revisa que el cliente no tenga otra cita activa y que
//           la hora esté libre, y la guarda. Si dos personas piden la misma hora a la vez, la base
//           de datos solo deja pasar a una (error 23000 -> 409).
// Recibe:   $usuario: el cliente que agenda (de usuarioActual()) · $datos: el JSON {servicio,
//           objetivo, tiempo, fecha, hora}.
// Entrega:  La cita creada, lista para el navegador (citaPublica()). Si algo falla, responde 422 o
//           409 y termina.
function crearCita($usuario, $datos)
{
    global $pdo;

    // 1. Limpiar y validar
    $servicio = texto($datos, 'servicio');
    $objetivo = texto($datos, 'objetivo');
    $tiempo   = texto($datos, 'tiempo');
    $fecha    = texto($datos, 'fecha');
    $hora     = texto($datos, 'hora');

    validarCita($servicio, $objetivo, $tiempo, $fecha, $hora);

    // 2. Las reglas de negocio
    if (clienteTieneCitaActiva($usuario['id'])) {
        responderError(409, MENSAJE_CITA_ACTIVA);
    }
    if (horarioOcupado($fecha, $hora)) {
        responderError(409, MENSAJE_HORARIO_OCUPADO);
    }

    // 3. Guardar. Si otra petición ganó por un instante, la base de datos rechaza el duplicado (código 23000)
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO citas (usuario_id, servicio, objetivo, tiempo, fecha, hora, estado, activa)
             VALUES (:usuario, :servicio, :objetivo, :tiempo, :fecha, :hora, 'pendiente', 1)"
        );
        $stmt->execute([
            ':usuario'  => $usuario['id'],
            ':servicio' => $servicio,
            ':objetivo' => $objetivo,
            ':tiempo'   => $tiempo,
            ':fecha'    => $fecha,
            ':hora'     => $hora,
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            if (clienteTieneCitaActiva($usuario['id'])) {
                responderError(409, MENSAJE_CITA_ACTIVA);
            }
            responderError(409, MENSAJE_HORARIO_OCUPADO);
        }
        throw $e;                                          // otro error: lo atiende el manejador general
    }

    return buscarCita((int) $pdo->lastInsertId());
}
