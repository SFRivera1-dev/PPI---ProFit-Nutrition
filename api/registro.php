<?php
// =====================================================================
// POST /api/registro.php      (público)
// Recibe:    {"nombre":"Ana","apellido":"Paz","correo":"ana@x.com","password":"12345678"}
// Responde:  201 {"token": "...", "usuario": {...}}
//            409 el correo ya existe · 422 datos inválidos
// El registro público SIEMPRE crea clientes.
//
// Recorrido: peticion.php -> crear.php (validar.php + MySQL) -> token.php -> respuesta.php
// =====================================================================
require_once __DIR__ . '/../includes/http/respuesta.php';
require_once __DIR__ . '/../includes/http/peticion.php';
require_once __DIR__ . '/../includes/seguridad/token.php';
require_once __DIR__ . '/../includes/usuarios/crear.php';
require_once __DIR__ . '/../includes/usuarios/publico.php';

permitir(['POST']);

$datos   = leerJson();
$usuario = crearUsuario($datos, 'cliente');

responder([
    'token'   => crearToken($usuario['id']),
    'usuario' => usuarioPublico($usuario),
], 201);
