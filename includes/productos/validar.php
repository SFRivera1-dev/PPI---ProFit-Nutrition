<?php
// =====================================================================
// REGLAS PARA LOS DATOS DE UN PRODUCTO
// El servidor SIEMPRE valida (el navegador se puede saltar).
// Si algo está mal, responde el error y termina el script.
// =====================================================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../http/respuesta.php';

// Las categorías que existen. Es la única lista: el panel y la tienda la piden a la API.
const CATEGORIAS_VALIDAS = ['Proteina', 'Creatina', 'Pre-entreno', 'Vitaminas', 'Snacks fit'];

// Qué hace: Convierte un valor (123 o "123") en un número entero.
// Recibe:   $valor: lo que vino en el JSON.
// Entrega:  El número entero, o -1 si no es un entero válido de 0 en adelante.
function aEntero($valor)
{
    if (is_int($valor)) {
        if ($valor >= 0 && $valor <= 999999999) {
            return $valor;
        }
        return -1;
    }
    if (is_string($valor) && ctype_digit($valor) && strlen($valor) <= 9) {
        return (int) $valor;
    }
    return -1;
}

// Qué hace: Valida un producto: nombre, categoría, precio mayor que 0 y stock de 0 en adelante.
// Recibe:   $nombre, $categoria, $precio, $stock: ya limpios ($precio y $stock ya pasaron por
//           aEntero).
// Entrega:  Nada. Si algo está mal, responde 422 y termina.
function validarProducto($nombre, $categoria, $precio, $stock)
{
    if ($nombre === '') {
        responderError(422, 'Escribe el nombre del producto.');
    }
    if (mb_strlen($nombre) > 120) {
        responderError(422, 'El nombre no puede superar 120 caracteres.');
    }
    if (!in_array($categoria, CATEGORIAS_VALIDAS)) {
        responderError(422, 'Categoría no válida.');
    }
    if ($precio <= 0) {
        responderError(422, 'El precio debe ser un número entero mayor a 0.');
    }
    if ($stock < 0) {
        responderError(422, 'El stock debe ser un número entero de 0 en adelante.');
    }

}

// Qué hace: Revisa que ningún otro producto tenga ya ese nombre (responde 409 si lo tiene).
// Recibe:   $nombre: el nombre · $idExcepto: 0 al crear; al modificar, el id del producto que se
//           modifica.
// Entrega:  Nada. Si el nombre está repetido, responde 409 y termina.
function revisarNombreLibre($nombre, $idExcepto)
{
    global $pdo;

    $stmt = $pdo->prepare('SELECT id FROM productos WHERE nombre = :nombre AND id <> :id');
    $stmt->execute([':nombre' => $nombre, ':id' => $idExcepto]);

    if ($stmt->fetch()) {
        responderError(409, 'Ya existe un producto con ese nombre.');
    }
}
