<?php
// =====================================================================
// RESPONDER AL NAVEGADOR
// Todo lo que sale de la API sale en JSON, con un código HTTP:
//   200 OK · 201 Creado · 400 Petición mal hecha · 401 Sin sesión
//   403 Sin permiso · 404 No existe · 405 Método no permitido
//   409 Ya existe · 422 Datos inválidos · 500 Error del servidor
// =====================================================================

header('Content-Type: application/json; charset=utf-8');

// Si algo falla sin que lo hayamos previsto (por ejemplo, falta una tabla en la BD),
// respondemos un JSON y NO una página de error de PHP (que muestra rutas internas).
// El detalle real queda en el registro de errores de Apache (xampp/apache/logs/error.log).
set_exception_handler(function ($error) {
    error_log('[ProFit API] ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor. Revisa el registro de errores de Apache.'], JSON_UNESCAPED_UNICODE);
});

// Qué hace: Envía los datos en JSON con un código HTTP y TERMINA el script.
// Recibe:   $datos: lo que se envía (arreglo) · $codigo: el código HTTP (por defecto 200).
// Entrega:  Nada: el script termina aquí.
function responder($datos, $codigo = 200)
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

// Qué hace: Envía un error en JSON, {"error": "mensaje"}, y TERMINA el script.
// Recibe:   $codigo: el código HTTP (400, 401, 403, 404, 409, 422...) · $mensaje: el texto del
//           error.
// Entrega:  Nada: el script termina aquí.
function responderError($codigo, $mensaje)
{
    responder(['error' => $mensaje], $codigo);
}
