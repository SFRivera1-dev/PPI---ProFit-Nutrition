<?php
// =====================================================================
// GUARDAR UN MENSAJE DEL FORMULARIO (público: no hace falta iniciar sesión)
//   - Valida todo en el servidor.
//   - Campo trampa "sitio_web": los bots lo llenan, las personas no lo ven.
//     Si viene lleno respondemos "ok" pero no guardamos nada.
//   - Si ya llegó EXACTAMENTE el mismo mensaje del mismo correo, responde 409
//     (evita duplicados por doble clic o por reenviar el formulario).
// =====================================================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../http/respuesta.php';
require_once __DIR__ . '/../http/peticion.php';
require_once __DIR__ . '/validar.php';

// Qué hace: Guarda un mensaje del formulario público. Valida todo, ignora a los bots (si el campo
//           trampa "sitio_web" viene lleno no guarda nada) y rechaza el mismo mensaje repetido del
//           mismo correo.
// Recibe:   $datos: el JSON {nombre, correo, asunto, mensaje, sitio_web}.
// Entrega:  Nada. Responde 422 si hay datos inválidos y 409 si el mensaje ya había llegado.
function guardarContacto($datos)
{
    global $pdo;

    if (texto($datos, 'sitio_web') !== '') {
        return;                                              // un bot: fingimos que salió bien
    }

    $nombre  = texto($datos, 'nombre');
    $correo  = texto($datos, 'correo');
    $asunto  = texto($datos, 'asunto');
    $mensaje = texto($datos, 'mensaje');
    validarContacto($nombre, $correo, $asunto, $mensaje);

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM contactos WHERE correo = :correo AND asunto = :asunto AND mensaje = :mensaje');
    $stmt->execute([':correo' => $correo, ':asunto' => $asunto, ':mensaje' => $mensaje]);
    if ((int) $stmt->fetchColumn() > 0) {
        responderError(409, 'Ya recibimos este mismo mensaje. Te responderemos pronto.');
    }

    $stmt = $pdo->prepare('INSERT INTO contactos (nombre, correo, asunto, mensaje) VALUES (:nombre, :correo, :asunto, :mensaje)');
    $stmt->execute([':nombre' => $nombre, ':correo' => $correo, ':asunto' => $asunto, ':mensaje' => $mensaje]);
}
