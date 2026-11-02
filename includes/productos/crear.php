<?php
// =====================================================================
// CREAR UN PRODUCTO
// Recibe:  los datos del JSON {nombre, categoria, precio, stock}
// (la foto se sube aparte: api/productos_imagen.php)
// Entrega: el producto creado (fila de la tabla, con su id)
// =====================================================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../http/peticion.php';
require_once __DIR__ . '/validar.php';

// Qué hace: Crea un producto: limpia y valida los datos, revisa que el nombre no esté repetido y lo
//           guarda. La foto se sube aparte (api/productos_imagen.php).
// Recibe:   $datos: el JSON {nombre, categoria, precio, stock}.
// Entrega:  La fila del producto recién creado, con su id.
function crearProducto($datos)
{
    global $pdo;

    // 1. Limpiar los datos
    $nombre    = texto($datos, 'nombre');
    $categoria = texto($datos, 'categoria');
    $precio    = aEntero(valor($datos, 'precio'));
    $stock     = aEntero(valor($datos, 'stock'));

    // 2. Validar
    validarProducto($nombre, $categoria, $precio, $stock);
    revisarNombreLibre($nombre, 0);

    // 3. Guardar en MySQL
    $stmt = $pdo->prepare(
        'INSERT INTO productos (nombre, categoria, precio, stock)
         VALUES (:nombre, :categoria, :precio, :stock)'
    );
    $stmt->execute([
        ':nombre'    => $nombre,
        ':categoria' => $categoria,
        ':precio'    => $precio,
        ':stock'     => $stock,
    ]);

    // 4. Devolver la fila recién creada
    $stmt = $pdo->prepare('SELECT * FROM productos WHERE id = :id');
    $stmt->execute([':id' => $pdo->lastInsertId()]);
    return $stmt->fetch();
}
