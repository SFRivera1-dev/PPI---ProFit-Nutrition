<?php
// =====================================================================
// RECURSO: usuarios            (solo ADMIN)
//
//   GET    /api/usuarios.php               -> lista todos        200 {"usuarios":[...]}
//   GET    /api/usuarios.php?rol=cliente   -> lista filtrada
//   POST   /api/usuarios.php               -> crea un usuario    201 {"usuario":{...}}
//          cuerpo: {nombre, apellido, correo, password, rol}
//   PATCH  /api/usuarios.php?id=5          -> cambia el rol o el estado   200 {"usuario":{...}}
//          cuerpo: {"rol": "cliente"}  o  {"estado": "activo" | "inactivo"}   (lo que no mandes se queda igual)
// =====================================================================
require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/http/respuesta.php';
require_once __DIR__ . '/../includes/http/peticion.php';
require_once __DIR__ . '/../includes/seguridad/autenticacion.php';
require_once __DIR__ . '/../includes/usuarios/validar.php';
require_once __DIR__ . '/../includes/usuarios/crear.php';
require_once __DIR__ . '/../includes/usuarios/publico.php';

$admin  = exigirRol('admin');                                          // 401 sin token · 403 si no es admin
$metodo = permitir(['GET', 'POST', 'PATCH']);


/* ---------------------------------------------------------------------
   GET: listar usuarios
   --------------------------------------------------------------------- */
if ($metodo === 'GET') {
    $rol = parametroUrl('rol');

    if (in_array($rol, ROLES_VALIDOS)) {
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE rol = :rol ORDER BY id DESC');
        $stmt->execute([':rol' => $rol]);
    } else {
        $stmt = $pdo->query('SELECT * FROM usuarios ORDER BY id DESC');
    }

    $lista = [];
    foreach ($stmt->fetchAll() as $fila) {
        $usuario = usuarioPublico($fila);
        $usuario['creado_en'] = $fila['creado_en'];
        $lista[] = $usuario;
    }
    responder(['usuarios' => $lista]);
}


/* ---------------------------------------------------------------------
   POST: crear un usuario (el admin elige el rol)
   --------------------------------------------------------------------- */
if ($metodo === 'POST') {
    $datos   = leerJson();
    $usuario = crearUsuario($datos, texto($datos, 'rol'));

    responder(['usuario' => usuarioPublico($usuario)], 201);
}


/* ---------------------------------------------------------------------
   PATCH: cambiar el rol o el estado de UN usuario, que viene en ?id=
   (los dos botones de la página de Usuarios: el selector de rol y Activar / Desactivar)
   --------------------------------------------------------------------- */
$id = (int) parametroUrl('id');

if ($id <= 0) {
    responderError(400, 'Falta el parámetro ?id= con el usuario.');
}
if ($id === (int) $admin['id']) {
    responderError(403, 'No puedes modificar tu propia cuenta desde aquí.');
}

$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
$stmt->execute([':id' => $id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    responderError(404, 'El usuario no existe.');
}

// Para cada campo: si viene en el JSON se usa el nuevo; si no, se deja el que ya tenía.
$datos = leerJson();

$rol = texto($datos, 'rol');
if ($rol === '') {
    $rol = $usuario['rol'];
}
if (!in_array($rol, ROLES_VALIDOS)) {
    responderError(422, 'Rol no válido.');
}

$estado = texto($datos, 'estado');
if ($estado === '') {
    $estado = $usuario['estado'];
}
validarEstado($estado);

$stmt = $pdo->prepare('UPDATE usuarios SET rol = :rol, estado = :estado WHERE id = :id');
$stmt->execute([':rol' => $rol, ':estado' => $estado, ':id' => $id]);

$usuario['rol']    = $rol;
$usuario['estado'] = $estado;
responder(['usuario' => usuarioPublico($usuario)]);
