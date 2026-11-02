<?php
// =====================================================================
// ¿QUÉ HORAS ESTÁN LIBRES UN DÍA?
// Recibe:  una fecha "2026-11-02"
// Entrega: [['hora' => '08:00', 'libre' => true], ['hora' => '09:00', 'libre' => false], ...]
// (solo dice si está libre u ocupada: nunca quién la tomó)
// =====================================================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/validar.php';

// Qué hace: Dice qué horas de un día están libres y cuáles ocupadas. No revela quién tomó cada hora.
// Recibe:   $fecha: "2026-11-02" (si no es una fecha real, responde 422).
// Entrega:  Una lista: [["hora" => "08:00", "libre" => true], ["hora" => "09:00", "libre" => false],
//           ...]
function horasDelDia($fecha)
{
    global $pdo;

    leerFechaCita($fecha);                                 // responde 422 si no es una fecha real

    $stmt = $pdo->prepare('SELECT hora FROM citas WHERE fecha = :fecha');
    $stmt->execute([':fecha' => $fecha]);

    $ocupadas = [];
    foreach ($stmt->fetchAll() as $fila) {
        $ocupadas[] = $fila['hora'];
    }

    $horas = [];
    foreach (HORAS_CITA as $hora) {
        $horas[] = [
            'hora'  => $hora,
            'libre' => !in_array($hora, $ocupadas, true),
        ];
    }
    return $horas;
}
