<?php
// =====================================================================
// ¿QUIÉN HACE LA PETICIÓN Y QUÉ PUEDE HACER?
//   usuarioActual() -> el usuario dueño del token (o responde 401)
//   exigirRol()     -> igual, pero además exige un rol (o responde 403)
//
// El navegador manda el token en la cabecera:   Authorization: Bearer eyJhbGci...
// =====================================================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/token.php';

// Qué hace: Lee la cabecera Authorization tal como llegó (el navegador manda "Bearer <token>").
// Recibe:   Nada.
// Entrega:  El texto de la cabecera, o "" si no vino.
function leerCabeceraAuthorization()
{
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return $_SERVER['HTTP_AUTHORIZATION'];
    }
    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    return '';
}

// Qué hace: Dice quién hace la petición: saca el token de la cabecera, verifica la firma y el
//           vencimiento, y busca al usuario en la base de datos (así, si lo desactivan, deja de
//           funcionar aunque el token siga vigente).
// Recibe:   Nada.
// Entrega:  La fila del usuario (id, nombre, apellido, correo, rol, estado). Si no hay token válido
//           o la cuenta está desactivada, responde 401 y termina.
function usuarioActual()
{
    global $pdo;

    // 1. Sacar el token de la cabecera "Bearer <token>"
    $cabecera = leerCabeceraAuthorization();
    if (!preg_match('/^Bearer\s+(\S+)$/i', $cabecera, $coincidencias)) {
        responderError(401, 'Inicia sesión.');
    }
    $token = $coincidencias[1];

    // 2. Verificar la firma y el vencimiento
    $idUsuario = leerToken($token);
    if ($idUsuario === 0) {
        responderError(401, 'Token inválido o vencido. Inicia sesión.');
    }

    // 3. Buscar al usuario en la BD (así, si lo desactivan, deja de funcionar aunque el token siga vigente)
    $stmt = $pdo->prepare('SELECT id, nombre, apellido, correo, rol, estado FROM usuarios WHERE id = :id');
    $stmt->execute([':id' => $idUsuario]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        responderError(401, 'Tu cuenta no existe.');
    }
    if ($usuario['estado'] !== 'activo') {
        responderError(401, 'Tu cuenta está desactivada.');
    }
    return $usuario;
}

// Qué hace: Igual que usuarioActual(), pero además exige un rol. Sin token responde 401; con el rol
//           incorrecto responde 403.
// Recibe:   $rolesPermitidos: un rol ("admin") o una lista (["admin", "profesional"]).
// Entrega:  El usuario actual, si tiene permiso.
function exigirRol($rolesPermitidos)
{
    $usuario = usuarioActual();

    if (!in_array($usuario['rol'], (array) $rolesPermitidos)) {
        responderError(403, 'No tienes permiso para esta acción.');
    }
    return $usuario;
}
