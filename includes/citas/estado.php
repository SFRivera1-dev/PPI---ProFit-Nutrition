<?php
// =====================================================================
// CAMBIAR EL ESTADO DE UNA CITA (profesional o admin)
//   pendiente -> confirmada -> finalizada     (solo se avanza, nunca se retrocede)
// Al finalizar, el cliente queda libre para agendar otra cita.
// =====================================================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../http/respuesta.php';
require_once __DIR__ . '/validar.php';
require_once __DIR__ . '/publico.php';

// Qué hace: Cambia el estado de una cita. Solo se avanza: pendiente -> confirmada -> finalizada. Al
//           finalizar, el cliente queda libre para agendar otra cita (activa = NULL).
// Recibe:   $id: el id de la cita · $nuevoEstado: "confirmada" o "finalizada".
// Entrega:  La cita actualizada. Responde 422 (estado inválido), 404 (no existe) o 409 (retroceder o
//           cambio simultáneo).
function cambiarEstadoCita($id, $nuevoEstado)
{
    global $pdo;

    if (!in_array($nuevoEstado, ESTADOS_CITA, true)) {
        responderError(422, 'Estado no válido. Usa: ' . implode(', ', ESTADOS_CITA) . '.');
    }

    $stmt = $pdo->prepare('SELECT estado FROM citas WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $fila = $stmt->fetch();

    if (!$fila) {
        responderError(404, 'La cita no existe.');
    }

    $actual = $fila['estado'];
    if ($actual === $nuevoEstado) {
        return buscarCita($id);                            // ya estaba así: nada que hacer
    }

    // Solo se avanza: pendiente (0) -> confirmada (1) -> finalizada (2)
    if (array_search($nuevoEstado, ESTADOS_CITA, true) < array_search($actual, ESTADOS_CITA, true)) {
        responderError(409, 'No se puede volver a un estado anterior.');
    }

    // Al finalizar, "activa" pasa a NULL: así el cliente puede agendar otra
    if ($nuevoEstado === 'finalizada') {
        $sql = 'UPDATE citas SET estado = :nuevo, activa = NULL WHERE id = :id AND estado = :actual';
    } else {
        $sql = 'UPDATE citas SET estado = :nuevo WHERE id = :id AND estado = :actual';
    }

    // Solo cambia si sigue en el estado que vimos (si otro profesional la cambió justo ahora, no pisamos su cambio)
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':nuevo' => $nuevoEstado, ':id' => $id, ':actual' => $actual]);

    if ($stmt->rowCount() === 0) {
        responderError(409, 'La cita cambió mientras la editabas. Recarga la página.');
    }

    return buscarCita($id);
}
