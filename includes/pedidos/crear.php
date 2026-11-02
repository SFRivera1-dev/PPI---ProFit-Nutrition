<?php
// =====================================================================
// CREAR UN PEDIDO
// Recibe:  el usuario que compra (de usuarioActual()) y el JSON
//          {nombre, apellido, ciudad, direccion, productos:[{id, cantidad}]}
// Entrega: el pedido completo (con su número de orden y sus productos)
//
// El navegador SOLO dice qué productos y cuántos. Los precios, el domicilio
// y el total los calcula el servidor con lo que hay en la base de datos.
// =====================================================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../http/peticion.php';
require_once __DIR__ . '/validar.php';
require_once __DIR__ . '/publico.php';

// Qué hace: Deshace todo lo hecho hasta ahora en la transacción de la compra (el stock descontado,
//           etc.) y responde el error.
// Recibe:   $codigo: el código HTTP · $mensaje: el texto del error.
// Entrega:  Nada: el script termina aquí.
function cancelarPedido($codigo, $mensaje)
{
    global $pdo;

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    responderError($codigo, $mensaje);
}

// Qué hace: Crea un pedido. El navegador solo dice qué productos y cuántos; el precio, el domicilio
//           y el total los calcula el servidor con la base de datos. Todo ocurre en una transacción:
//           el stock se descuenta solo si alcanza y, si algo falla, no se guarda nada.
// Recibe:   $usuario: el cliente que compra (de usuarioActual()) · $datos: el JSON {nombre,
//           apellido, ciudad, direccion, productos:[{id, cantidad}]}.
// Entrega:  El pedido completo, con su número de orden y sus productos. Responde 422 o 409 si algo
//           está mal.
function crearPedido($usuario, $datos)
{
    global $pdo;

    // 1. Limpiar y validar lo que mandó el navegador
    $nombre    = texto($datos, 'nombre');
    $apellido  = texto($datos, 'apellido');
    $ciudad    = texto($datos, 'ciudad');
    $direccion = texto($datos, 'direccion');

    validarEntrega($nombre, $apellido, $ciudad, $direccion);
    $lineas = leerLineasPedido($datos);                  // [id => cantidad]

    // 2. Todo o nada: si algo falla, no se descuenta stock ni se guarda nada
    $pdo->beginTransaction();

    $items    = [];
    $subtotal = 0;

    foreach ($lineas as $idProducto => $cantidad) {
        // 2a. El producto, con el precio REAL de la base de datos
        $stmt = $pdo->prepare('SELECT id, nombre, categoria, precio FROM productos WHERE id = :id');
        $stmt->execute([':id' => $idProducto]);
        $producto = $stmt->fetch();

        if (!$producto) {
            cancelarPedido(409, 'Uno de los productos ya no está disponible. Revisa tu carrito.');
        }

        // 2b. Descontar el stock SOLO si alcanza (una sola instrucción: no se puede vender de más
        //     aunque dos personas compren a la vez)
        $stmt = $pdo->prepare(
            'UPDATE productos SET stock = stock - :cantidad
             WHERE id = :id AND stock >= :minimo'
        );
        $stmt->execute([
            ':cantidad' => $cantidad,
            ':id'       => $idProducto,
            ':minimo'   => $cantidad,
        ]);

        if ($stmt->rowCount() === 0) {
            cancelarPedido(409, 'No hay suficiente stock de "' . $producto['nombre'] . '". Revisa tu carrito.');
        }

        $precio        = (int) $producto['precio'];
        $subtotalLinea = $precio * $cantidad;
        $subtotal      = $subtotal + $subtotalLinea;

        $items[] = [
            'producto_id' => (int) $producto['id'],
            'nombre'      => $producto['nombre'],
            'categoria'   => $producto['categoria'],
            'precio'      => $precio,
            'cantidad'    => $cantidad,
            'subtotal'    => $subtotalLinea,
        ];
    }

    $total = $subtotal + COSTO_DOMICILIO;

    // 3. Guardar el pedido (su id es el número de orden)
    $stmt = $pdo->prepare(
        'INSERT INTO pedidos
            (usuario_id, entrega_nombre, entrega_apellido, entrega_pais, entrega_ciudad, entrega_direccion,
             subtotal, domicilio, total, metodo_pago, estado)
         VALUES
            (:usuario, :nombre, :apellido, :pais, :ciudad, :direccion,
             :subtotal, :domicilio, :total, :metodo, :estado)'
    );
    $stmt->execute([
        ':usuario'   => $usuario['id'],
        ':nombre'    => $nombre,
        ':apellido'  => $apellido,
        ':pais'      => PAIS_ENTREGA,
        ':ciudad'    => $ciudad,
        ':direccion' => $direccion,
        ':subtotal'  => $subtotal,
        ':domicilio' => COSTO_DOMICILIO,
        ':total'     => $total,
        ':metodo'    => 'tarjeta',
        ':estado'    => 'confirmado',
    ]);
    $idPedido = (int) $pdo->lastInsertId();

    // 4. Guardar cada producto del pedido
    $stmt = $pdo->prepare(
        'INSERT INTO pedido_items (pedido_id, producto_id, nombre, categoria, precio_unitario, cantidad, subtotal)
         VALUES (:pedido, :producto, :nombre, :categoria, :precio, :cantidad, :subtotal)'
    );
    foreach ($items as $item) {
        $stmt->execute([
            ':pedido'    => $idPedido,
            ':producto'  => $item['producto_id'],
            ':nombre'    => $item['nombre'],
            ':categoria' => $item['categoria'],
            ':precio'    => $item['precio'],
            ':cantidad'  => $item['cantidad'],
            ':subtotal'  => $item['subtotal'],
        ]);
    }

    // 5. Todo salió bien: confirmar
    $pdo->commit();

    return buscarPedido($idPedido);
}
