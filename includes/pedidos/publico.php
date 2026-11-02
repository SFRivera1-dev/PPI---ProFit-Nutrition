<?php
// =====================================================================
// QUÉ DATOS DEL PEDIDO SALEN HACIA EL NAVEGADOR
//   buscarPedido($id)      -> el pedido completo (con sus productos) o null si no existe
//   pedidoPublico($fila)   -> una fila de pedidos con los números ya convertidos y el número de orden
// =====================================================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../config.php';

// Qué hace: Convierte una fila de la tabla pedidos en lo que sale hacia el navegador: números como
//           enteros, el número de orden (PF-20001) y los datos de entrega.
// Recibe:   $fila: una fila de la tabla pedidos.
// Entrega:  Un arreglo con id, numero, estado, fecha, entrega, subtotal, domicilio y total.
function pedidoPublico($fila)
{
    return [
        'id'        => (int) $fila['id'],
        'numero'    => 'PF-' . $fila['id'],               // lo que ve el cliente: PF-20001
        'estado'    => $fila['estado'],
        'fecha'     => $fila['creado_en'],
        'entrega'   => [
            'nombre'    => $fila['entrega_nombre'],
            'apellido'  => $fila['entrega_apellido'],
            'pais'      => $fila['entrega_pais'],
            'ciudad'    => $fila['entrega_ciudad'],
            'direccion' => $fila['entrega_direccion'],
        ],
        'subtotal'  => (int) $fila['subtotal'],
        'domicilio' => (int) $fila['domicilio'],
        'total'     => (int) $fila['total'],
    ];
}

// Qué hace: Trae los productos de varios pedidos a la vez, con una sola consulta.
// Recibe:   $idsPedidos: una lista de ids de pedido.
// Entrega:  [id_pedido => [producto, producto, ...]]
function productosDePedidos($idsPedidos)
{
    global $pdo;

    $porPedido = [];
    if (count($idsPedidos) === 0) {
        return $porPedido;
    }

    $marcadores = [];
    $valores    = [];
    foreach ($idsPedidos as $posicion => $idPedido) {
        $marcador            = ':p' . $posicion;
        $marcadores[]        = $marcador;
        $valores[$marcador]  = (int) $idPedido;
    }

    // La imagen se toma del catálogo si el producto todavía existe
    $stmt = $pdo->prepare(
        'SELECT i.pedido_id, i.producto_id, i.nombre, i.categoria, i.precio_unitario, i.cantidad, i.subtotal, p.imagen
         FROM pedido_items i
         LEFT JOIN productos p ON p.id = i.producto_id
         WHERE i.pedido_id IN (' . implode(', ', $marcadores) . ')
         ORDER BY i.id'
    );
    $stmt->execute($valores);

    foreach ($stmt->fetchAll() as $item) {
        $imagenUrl = '';
        if ($item['imagen'] !== null && $item['imagen'] !== '') {
            if (preg_match('/^https?:\/\//i', $item['imagen'])) {
                $imagenUrl = $item['imagen'];
            } else {
                $imagenUrl = BASE_URL . '/' . $item['imagen'];
            }
        }

        $idPedido = (int) $item['pedido_id'];
        $porPedido[$idPedido][] = [
            'producto_id'     => $item['producto_id'],
            'nombre'          => $item['nombre'],
            'categoria'       => $item['categoria'],
            'precio_unitario' => (int) $item['precio_unitario'],
            'cantidad'        => (int) $item['cantidad'],
            'subtotal'        => (int) $item['subtotal'],
            'imagen_url'      => $imagenUrl,
        ];
    }
    return $porPedido;
}

// Qué hace: Agrega "productos" y "unidades" a un pedido que ya se convirtió con pedidoPublico().
// Recibe:   $pedido: el pedido ya convertido · $productos: los productos de ese pedido.
// Entrega:  El mismo pedido con "productos" y "unidades".
function conProductos($pedido, $productos)
{
    $unidades = 0;
    foreach ($productos as $producto) {
        $unidades = $unidades + $producto['cantidad'];
    }
    $pedido['productos'] = $productos;
    $pedido['unidades']  = $unidades;
    return $pedido;
}

// Qué hace: Trae un pedido con sus productos.
// Recibe:   $id: el número de pedido.
// Entrega:  El pedido completo, o null si no existe.
function buscarPedido($id)
{
    global $pdo;

    $stmt = $pdo->prepare('SELECT * FROM pedidos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $fila = $stmt->fetch();

    if (!$fila) {
        return null;
    }

    $pedido = pedidoPublico($fila);
    $pedido['usuario_id'] = $fila['usuario_id'];

    $porPedido = productosDePedidos([$id]);
    $productos = [];
    if (isset($porPedido[(int) $id])) {
        $productos = $porPedido[(int) $id];
    }
    return conProductos($pedido, $productos);
}

// Qué hace: Trae los pedidos de un usuario (los más nuevos primero), cada uno con sus productos.
//           Hace solo 2 consultas, sin importar cuántos pedidos tenga.
// Recibe:   $idUsuario: el id del cliente.
// Entrega:  Una lista de pedidos.
function pedidosDeUsuario($idUsuario)
{
    global $pdo;

    $stmt = $pdo->prepare('SELECT * FROM pedidos WHERE usuario_id = :usuario ORDER BY id DESC LIMIT 100');
    $stmt->execute([':usuario' => $idUsuario]);
    $filas = $stmt->fetchAll();

    $ids = [];
    foreach ($filas as $fila) {
        $ids[] = (int) $fila['id'];
    }
    $porPedido = productosDePedidos($ids);

    $lista = [];
    foreach ($filas as $fila) {
        $productos = [];
        if (isset($porPedido[(int) $fila['id']])) {
            $productos = $porPedido[(int) $fila['id']];
        }
        $lista[] = conProductos(pedidoPublico($fila), $productos);
    }
    return $lista;
}
