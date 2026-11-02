<?php
// =====================================================================
// REGLAS PARA LOS DATOS DE UN USUARIO
// El servidor SIEMPRE valida (el navegador se puede saltar).
// Si algo está mal, responde el error y termina el script.
// =====================================================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../http/respuesta.php';

const ROLES_VALIDOS   = ['admin', 'profesional', 'cliente'];
const ESTADOS_VALIDOS = ['activo', 'inactivo'];

// Qué hace: Valida los datos de un usuario: nombre y apellido (máximo 60), correo con formato válido
//           (máximo 120) y rol de la lista.
// Recibe:   $nombre, $apellido, $correo, $rol: ya limpios.
// Entrega:  Nada. Si algo está mal, responde 422 y termina.
function validarUsuario($nombre, $apellido, $correo, $rol)
{
    if ($nombre === '' || $apellido === '') {
        responderError(422, 'Escribe el nombre y el apellido.');
    }
    if (mb_strlen($nombre) > 60 || mb_strlen($apellido) > 60) {
        responderError(422, 'El nombre y el apellido no pueden superar 60 caracteres.');
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        responderError(422, 'Escribe un correo válido.');
    }
    if (strlen($correo) > 120) {
        responderError(422, 'El correo no puede superar 120 caracteres.');
    }
    if (!in_array($rol, ROLES_VALIDOS)) {
        responderError(422, 'Rol no válido.');
    }
}

// Qué hace: Valida la contraseña: mínimo 8 caracteres.
// Recibe:   $password: la contraseña.
// Entrega:  Nada. Si es muy corta, responde 422 y termina.
function validarPassword($password)
{
    if (strlen($password) < 8) {
        responderError(422, 'La contraseña debe tener mínimo 8 caracteres.');
    }
}

// Qué hace: Valida el estado de una cuenta: "activo" o "inactivo".
// Recibe:   $estado: el estado.
// Entrega:  Nada. Si no es válido, responde 422 y termina.
function validarEstado($estado)
{
    if (!in_array($estado, ESTADOS_VALIDOS)) {
        responderError(422, 'Estado no válido.');
    }
}

// Qué hace: Revisa que ningún otro usuario ya use ese correo (responde 409 si lo usa).
// Recibe:   $correo: el correo · $idExcepto: 0 al crear; al modificar, el id del usuario que se
//           modifica.
// Entrega:  Nada. Si el correo está en uso, responde 409 y termina.
function revisarCorreoLibre($correo, $idExcepto)
{
    global $pdo;

    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE correo = :correo AND id <> :id');
    $stmt->execute([':correo' => $correo, ':id' => $idExcepto]);

    if ($stmt->fetch()) {
        responderError(409, 'Ya existe una cuenta con ese correo.');
    }
}
