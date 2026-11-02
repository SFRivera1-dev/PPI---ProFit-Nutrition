<?php
// =====================================================================
// RECURSO: contáctanos   (público: NO hace falta iniciar sesión)
//
//   POST /api/contacto.php   -> guarda el mensaje del formulario      201 {"ok":true,"mensaje":"..."}
//        cuerpo: {nombre, correo, asunto, mensaje}
//        422 datos inválidos · 409 si ese mismo mensaje ya llegó
// (El admin lee los mensajes en api/contacto_admin.php)
// =====================================================================
require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/http/respuesta.php';
require_once __DIR__ . '/../includes/http/peticion.php';
require_once __DIR__ . '/../includes/contacto/crear.php';

permitir(['POST']);

guardarContacto(leerJson());

responder(['ok' => true, 'mensaje' => 'Recibimos tu mensaje. Te responderemos en menos de 24 horas.'], 201);
