<?php
// =====================================================================
// QUÉ DATOS DEL PRODUCTO SALEN HACIA EL NAVEGADOR
// Recibe:  una fila de la tabla productos
// Entrega: el producto con los números ya convertidos a enteros y
//          'imagen_url' = la dirección lista para poner en <img src="...">
//          ('' si el producto no tiene foto)
// =====================================================================
require_once __DIR__ . '/../config.php';

// Qué hace: Convierte una fila de la tabla productos en lo que sale hacia el navegador, con los
//           números como enteros y la dirección lista para <img src>.
// Recibe:   $producto: una fila de la tabla productos.
// Entrega:  Un arreglo con id, nombre, categoria, precio, stock, imagen, imagen_url y creado_en.
function productoPublico($producto)
{
    $imagen = (string) $producto['imagen'];

    $imagenUrl = '';
    if ($imagen !== '') {
        if (preg_match('/^https?:\/\//i', $imagen)) {
            $imagenUrl = $imagen;                        // enlace externo (productos antiguos)
        } else {
            $imagenUrl = BASE_URL . '/' . $imagen;       // archivo de uploads/
        }
    }

    return [
        'id'         => (int) $producto['id'],
        'nombre'     => $producto['nombre'],
        'categoria'  => $producto['categoria'],
        'precio'     => (int) $producto['precio'],
        'stock'      => (int) $producto['stock'],
        'imagen'     => $imagen,
        'imagen_url' => $imagenUrl,
        'creado_en'  => $producto['creado_en'],
    ];
}
