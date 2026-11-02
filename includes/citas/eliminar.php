<?php
// =====================================================================
// CANCELAR (BORRAR) UNA CITA — solo admin
//   Solo se pueden cancelar las citas pendientes o confirmadas.
//   Una cita finalizada es historial del paciente: no se borra.
//   Al borrarla, la fecha y hora quedan libres otra vez y el cliente
//   puede agendar una nueva.
// =====================================================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../http/respuesta.php';

// Qué hace: Cancela (borra) una cita pendiente o confirmada. La fecha y hora quedan libres y el
//           cliente puede agendar otra. Una cita finalizada es historial y no se borra.
// Recibe:   $id: el id de la cita.
// Entrega:  Nada. Responde 404 si no existe y 409 si ya está finalizada.
function cancelarCita($id)
{
    global $pdo;

    $stmt = $pdo->prepare('SELECT estado FROM citas WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $fila = $stmt->fetch();

    if (!$fila) {
        responderError(404, 'La cita no existe.');
    }
    if ($fila['estado'] === 'finalizada') {
        responderError(409, 'Una cita finalizada es parte del historial y no se puede cancelar.');
    }

    // Solo se borra si sigue sin finalizar (por si la finalizaron justo ahora)
    $stmt = $pdo->prepare("DELETE FROM citas WHERE id = :id AND estado <> 'finalizada'");
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        responderError(409, 'La cita cambió mientras la editabas. Recarga la página.');
    }
}
