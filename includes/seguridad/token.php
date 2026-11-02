<?php
// =====================================================================
// TOKEN JWT: el "pase de entrada" que recibe el usuario al iniciar sesión
//
// Un token son 3 partes separadas por puntos:   cabecera.datos.firma
//   datos:  {"sub": 5, "exp": 1760000000}   (id del usuario y cuándo vence)
//   firma:  se calcula con JWT_SECRETO (includes/config.php).
//           Si alguien cambia los datos, la firma ya no coincide y el token se rechaza.
//
// OJO: el token va codificado, NO cifrado. Nunca guardes contraseñas dentro.
// =====================================================================
require_once __DIR__ . '/../config.php';

// Qué hace: Convierte texto a base64 apto para URLs (cambia + y / por - y _, y quita los =).
// Recibe:   $texto: el texto a codificar.
// Entrega:  El texto codificado.
function base64url($texto)
{
    $codificado = base64_encode($texto);
    $codificado = strtr($codificado, '+/', '-_');
    return rtrim($codificado, '=');
}

// Qué hace: Crea el token JWT que recibe el usuario al iniciar sesión: cabecera, datos (id y
//           vencimiento) y firma.
// Recibe:   $idUsuario: el id del usuario.
// Entrega:  El token (texto).
function crearToken($idUsuario)
{
    $cabecera = base64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $datos    = base64url(json_encode(['sub' => (int) $idUsuario, 'exp' => time() + JWT_DURACION]));
    $firma    = base64url(hash_hmac('sha256', $cabecera . '.' . $datos, JWT_SECRETO, true));

    return $cabecera . '.' . $datos . '.' . $firma;
}

// Qué hace: Verifica un token: que la firma coincida y que no haya vencido.
// Recibe:   $token: el token (texto).
// Entrega:  El id del usuario, o 0 si el token es falso, está alterado o venció.
function leerToken($token)
{
    $partes = explode('.', $token);
    if (count($partes) !== 3) {
        return 0;
    }
    $cabecera = $partes[0];
    $datos    = $partes[1];
    $firma    = $partes[2];

    // 1. ¿La firma coincide? Si no, alguien manipuló el token.
    $firmaEsperada = base64url(hash_hmac('sha256', $cabecera . '.' . $datos, JWT_SECRETO, true));
    if (!hash_equals($firmaEsperada, $firma)) {
        return 0;
    }

    // 2. ¿Se pueden leer los datos?
    $contenido = json_decode(base64_decode(strtr($datos, '-_', '+/')), true);
    if (!is_array($contenido)) {
        return 0;
    }
    if (!isset($contenido['sub']) || !isset($contenido['exp'])) {
        return 0;
    }

    // 3. ¿Ya venció?
    if ($contenido['exp'] < time()) {
        return 0;
    }

    return (int) $contenido['sub'];
}
