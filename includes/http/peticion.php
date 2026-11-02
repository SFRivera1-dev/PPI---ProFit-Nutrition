<?php
// =====================================================================
// LEER LO QUE MANDA EL NAVEGADOR
//   permitir()     -> ¿con qué método llegó? (GET, POST, PUT, DELETE...)
//   leerJson()     -> el cuerpo de la petición, convertido en arreglo
//   texto()        -> un campo de texto del JSON, sin espacios sobrantes
//   contrasena()   -> un campo de contraseña del JSON (sin recortar)
//   valor()        -> un campo del JSON tal cual vino (números incluidos)
//   parametroUrl() -> un valor de la URL, como ?id=5
// =====================================================================
require_once __DIR__ . '/respuesta.php';

// Qué hace: Revisa con qué método HTTP llegó la petición. Cada endpoint dice cuáles acepta: $metodo
//           = permitir(["GET", "POST"]). Si llega otro método, responde 405 y termina.
// Recibe:   $metodosPermitidos: lista de métodos, ej: ["GET", "POST"].
// Entrega:  El método con el que llegó (por ejemplo "GET").
function permitir($metodosPermitidos)
{
    $metodo = $_SERVER['REQUEST_METHOD'];

    if (!in_array($metodo, $metodosPermitidos)) {
        responderError(405, 'Método no permitido. Usa: ' . implode(', ', $metodosPermitidos));
    }
    return $metodo;
}

// Qué hace: Lee el cuerpo de la petición. El navegador lo manda como JSON
//           ({"correo":"a@a.com","password":"123"}) y aquí se convierte en un arreglo de PHP. Si no
//           es un JSON válido, responde 400.
// Recibe:   Nada (lee php://input).
// Entrega:  Un arreglo de PHP con los datos.
function leerJson()
{
    $cuerpo = file_get_contents('php://input');
    $datos  = json_decode($cuerpo, true);

    if (!is_array($datos)) {
        responderError(400, 'El cuerpo de la petición debe ser un JSON válido.');
    }
    return $datos;
}

// Qué hace: Saca un campo de texto del JSON, sin espacios al principio ni al final.
// Recibe:   $datos: el arreglo de leerJson() · $clave: el nombre del campo.
// Entrega:  El texto limpio, o "" si el campo no viene o no es texto.
function texto($datos, $clave)
{
    if (!isset($datos[$clave])) {
        return '';
    }
    if (!is_string($datos[$clave])) {
        return '';
    }
    return trim($datos[$clave]);
}

// Qué hace: Igual que texto(), pero SIN quitar los espacios (los espacios pueden ser parte de la
//           contraseña).
// Recibe:   $datos: el arreglo de leerJson() · $clave: el campo (por defecto "password").
// Entrega:  La contraseña tal cual, o "" si no viene o no es texto.
function contrasena($datos, $clave = 'password')
{
    if (!isset($datos[$clave])) {
        return '';
    }
    if (!is_string($datos[$clave])) {
        return '';
    }
    return $datos[$clave];
}

// Qué hace: Saca un campo del JSON tal cual vino (sirve para números, true/false, etc.).
// Recibe:   $datos: el arreglo de leerJson() · $clave: el nombre del campo.
// Entrega:  El valor, o null si no viene.
function valor($datos, $clave)
{
    if (!isset($datos[$clave])) {
        return null;
    }
    return $datos[$clave];
}

// Qué hace: Lee un valor de la URL: en /api/usuarios.php?id=5, parametroUrl("id") devuelve "5".
// Recibe:   $nombre: el nombre del parámetro.
// Entrega:  El valor como texto, o "" si no viene.
function parametroUrl($nombre)
{
    if (!isset($_GET[$nombre])) {
        return '';
    }
    if (!is_string($_GET[$nombre])) {
        return '';
    }
    return $_GET[$nombre];
}
