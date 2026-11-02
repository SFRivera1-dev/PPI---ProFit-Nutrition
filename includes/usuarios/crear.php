<?php
// =====================================================================
// CREAR UN USUARIO
// Lo usan dos endpoints:
//   registro.php  -> siempre crea clientes
//   usuarios.php  -> el admin elige el rol
//
// Recibe:  los datos del JSON y el rol
// Entrega: el usuario creado (arreglo con id, nombre, apellido, correo, rol, estado)
// =====================================================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../http/peticion.php';
require_once __DIR__ . '/validar.php';

// Qué hace: Crea un usuario: limpia y valida los datos, revisa que el correo no esté en uso y lo
//           guarda. La contraseña se guarda como HASH, nunca en texto plano.
// Recibe:   $datos: el JSON {nombre, apellido, correo, password} · $rol: "admin", "profesional" o
//           "cliente".
// Entrega:  El usuario creado (id, nombre, apellido, correo, rol, estado).
function crearUsuario($datos, $rol)
{
    global $pdo;

    // 1. Limpiar los datos
    $nombre   = texto($datos, 'nombre');
    $apellido = texto($datos, 'apellido');
    $correo   = strtolower(texto($datos, 'correo'));
    $password = contrasena($datos);

    // 2. Validar
    validarUsuario($nombre, $apellido, $correo, $rol);
    validarPassword($password);
    revisarCorreoLibre($correo, 0);

    // 3. Guardar en MySQL (la contraseña se guarda como HASH, nunca en texto plano)
    $stmt = $pdo->prepare(
        'INSERT INTO usuarios (nombre, apellido, correo, password, rol)
         VALUES (:nombre, :apellido, :correo, :password, :rol)'
    );
    $stmt->execute([
        ':nombre'   => $nombre,
        ':apellido' => $apellido,
        ':correo'   => $correo,
        ':password' => password_hash($password, PASSWORD_DEFAULT),
        ':rol'      => $rol,
    ]);

    // 4. Devolver el usuario creado
    return [
        'id'       => (int) $pdo->lastInsertId(),
        'nombre'   => $nombre,
        'apellido' => $apellido,
        'correo'   => $correo,
        'rol'      => $rol,
        'estado'   => 'activo',
    ];
}
