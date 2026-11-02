<?php
// =====================================================================
// REGLAS PARA UNA CITA
// El servidor SIEMPRE valida (el navegador se puede saltar).
// Si algo está mal, responde el error y termina el script.
// =====================================================================
require_once __DIR__ . '/../http/respuesta.php';

const SERVICIOS_CITA     = ['Nutricion', 'Fisico', 'Deporte', 'Seguimiento', 'General'];
const HORAS_CITA         = ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];    // las mismas del formulario
const ESTADOS_CITA       = ['pendiente', 'confirmada', 'finalizada'];                          // en este orden: solo se avanza
const DIAS_MAXIMOS_CITA  = 90;                                                                // hasta cuántos días adelante se puede agendar
const ZONA_COLOMBIA      = 'America/Bogota';

// Qué hace: Convierte "2026-11-02" en una fecha de PHP, en la hora de Colombia. Si no es una fecha
//           real (ej: 2026-02-31), responde 422.
// Recibe:   $texto: la fecha escrita como AAAA-MM-DD.
// Entrega:  Un objeto DateTime.
function leerFechaCita($texto)
{
    $zona  = new DateTimeZone(ZONA_COLOMBIA);
    $fecha = DateTime::createFromFormat('!Y-m-d', $texto, $zona);

    if ($fecha === false || $fecha->format('Y-m-d') !== $texto) {
        responderError(422, 'La fecha no es válida.');
    }
    return $fecha;
}

// Qué hace: Valida los datos de una cita: servicio de la lista, objetivo y tiempo con largo
//           correcto, fecha desde mañana hasta 90 días adelante y hora de la lista de horas.
// Recibe:   $servicio, $objetivo, $tiempo, $fecha, $hora: ya limpios con texto().
// Entrega:  Nada. Si algo está mal, responde 422 y termina.
function validarCita($servicio, $objetivo, $tiempo, $fecha, $hora)
{
    if (!in_array($servicio, SERVICIOS_CITA, true)) {
        responderError(422, 'Elige un tipo de servicio.');
    }
    if (mb_strlen($objetivo) < 3 || mb_strlen($objetivo) > 200) {
        responderError(422, 'Cuéntanos tu objetivo (entre 3 y 200 caracteres).');
    }
    if (mb_strlen($tiempo) < 2 || mb_strlen($tiempo) > 100) {
        responderError(422, 'Cuéntanos cuánto tiempo llevas (entre 2 y 100 caracteres).');
    }

    $fechaCita = leerFechaCita($fecha);
    $zona      = new DateTimeZone(ZONA_COLOMBIA);
    $manana    = new DateTime('tomorrow', $zona);                                  // las citas empiezan desde mañana
    $maxima    = new DateTime('today', $zona);
    $maxima->modify('+' . DIAS_MAXIMOS_CITA . ' days');

    if ($fechaCita < $manana) {
        responderError(422, 'La cita debe ser desde mañana en adelante.');
    }
    if ($fechaCita > $maxima) {
        responderError(422, 'Solo puedes agendar con máximo ' . DIAS_MAXIMOS_CITA . ' días de anticipación.');
    }

    if (!in_array($hora, HORAS_CITA, true)) {
        responderError(422, 'Elige una hora válida.');
    }
}
