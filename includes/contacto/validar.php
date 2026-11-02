<?php
// =====================================================================
// REGLAS DEL FORMULARIO DE CONTÁCTANOS (el servidor SIEMPRE valida)
// =====================================================================
require_once __DIR__ . '/../http/respuesta.php';

const ESTADOS_CONTACTO = ['nuevo', 'leido'];

// Qué hace: Valida los datos del formulario Contáctanos: largo del nombre, correo con formato
//           válido, asunto y mensaje.
// Recibe:   $nombre, $correo, $asunto, $mensaje: ya limpios con texto().
// Entrega:  Nada. Si algo está mal, responde 422 y termina.
function validarContacto($nombre, $correo, $asunto, $mensaje)
{
    if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 100) {
        responderError(422, 'Escribe tu nombre (entre 2 y 100 caracteres).');
    }
    if (mb_strlen($correo) > 150 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        responderError(422, 'Escribe un correo válido.');
    }
    if (mb_strlen($asunto) < 3 || mb_strlen($asunto) > 150) {
        responderError(422, 'Cuéntanos el asunto (entre 3 y 150 caracteres).');
    }
    if (mb_strlen($mensaje) < 10 || mb_strlen($mensaje) > 2000) {
        responderError(422, 'El mensaje debe tener entre 10 y 2000 caracteres.');
    }
}
