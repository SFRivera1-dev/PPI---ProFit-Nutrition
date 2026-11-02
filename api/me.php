<?php
// =====================================================================
// GET /api/me.php             (cualquier usuario con token)
// Responde:  200 {"usuario": {...}}   ·   401 si el token no sirve
// El front lo usa para comprobar que la sesión sigue siendo válida.
// =====================================================================
require_once __DIR__ . '/../includes/http/respuesta.php';
require_once __DIR__ . '/../includes/http/peticion.php';
require_once __DIR__ . '/../includes/seguridad/autenticacion.php';
require_once __DIR__ . '/../includes/usuarios/publico.php';

permitir(['GET']);

$usuario = usuarioActual();

responder(['usuario' => usuarioPublico($usuario)]);
