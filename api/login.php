<?php
// =====================================================================
// POST /api/login.php         (público)
// Recibe:    {"correo": "admin@profit.com", "password": "Admin12345"}
// Responde:  200 {"token": "eyJ...", "usuario": {id, nombre, apellido, correo, rol, estado}}
//            401 credenciales incorrectas · 403 cuenta desactivada · 422 datos incompletos
//
// Recorrido: peticion.php -> MySQL -> token.php -> respuesta.php
// =====================================================================
require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/http/respuesta.php';
require_once __DIR__ . '/../includes/http/peticion.php';
require_once __DIR__ . '/../includes/seguridad/token.php';
require_once __DIR__ . '/../includes/usuarios/publico.php';

permitir(['POST']);

// 1. Leer lo que mandó el navegador
$datos    = leerJson();
$correo   = strtolower(texto($datos, 'correo'));
$password = contrasena($datos);

if ($correo === '' || $password === '') {
    responderError(422, 'Escribe tu correo y tu contraseña.');
}

// 2. Buscar al usuario por su correo
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE correo = :correo');
$stmt->execute([':correo' => $correo]);
$usuario = $stmt->fetch();

// 3. Comprobar la contraseña (mismo mensaje si falla el correo o la contraseña: no damos pistas)
if (!$usuario) {
    responderError(401, 'Correo o contraseña incorrectos.');
}
if (!password_verify($password, $usuario['password'])) {
    responderError(401, 'Correo o contraseña incorrectos.');
}

// 4. Comprobar que la cuenta esté activa
if ($usuario['estado'] !== 'activo') {
    responderError(403, 'Tu cuenta está desactivada. Contacta al administrador.');
}

// 5. Guardar la fecha del último acceso
$stmt = $pdo->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id');
$stmt->execute([':id' => $usuario['id']]);

// 6. Responder con el token y los datos públicos del usuario
responder([
    'token'   => crearToken($usuario['id']),
    'usuario' => usuarioPublico($usuario),
]);
