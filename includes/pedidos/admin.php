<?php
// =====================================================================
// PEDIDOS PARA EL ADMINISTRADOR
//   listarPedidosAdmin($busqueda, $limite) -> los pedidos de TODOS los clientes (con su cliente y productos)
//   cambiarEstadoPedido($id, $nuevoEstado) -> cambia el estado (y devuelve el stock si se cancela)
// =====================================================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../http/respuesta.php';
require_once __DIR__ . '/publico.php';

// Los estados que el admin puede poner
const ESTADOS_PEDIDO = ['confirmado', 'enviado', 'entregado', 'cancelado'];

// Qué hace: Convierte filas de la consulta en pedidos listos para el navegador, con el cliente y los
//           productos de cada uno (los productos se piden todos juntos, en una sola consulta).
// Recibe:   $filas: las filas de SELECT_PEDIDOS_ADMIN.
// Entrega:  Una lista de pedidos. El cliente de cada pedido es null si esa cuenta ya no existe.
function armarPedidosAdmin($filas)
{
    $ids = [];
    foreach ($filas as $fila) {
        $ids[] = (int) $fila['id'];
    }
    $porPedido = productosDePedidos($ids);          // una sola consulta para todos los productos

    $lista = [];
    foreach ($filas as $fila) {
        $productos = [];
        if (isset($porPedido[(int) $fila['id']])) {
            $productos = $porPedido[(int) $fila['id']];
        }

        $pedido = conProductos(pedidoPublico($fila), $productos);

        // La cuenta de quien compró (null si borraron esa cuenta después)
        $pedido['cliente'] = null;
        if ($fila['cuenta_correo'] !== null) {
            $pedido['cliente'] = [
                'id'       => (int) $fila['usuario_id'],
                'nombre'   => $fila['cuenta_nombre'],
                'apellido' => $fila['cuenta_apellido'],
                'correo'   => $fila['cuenta_correo'],
            ];
        }
        $lista[] = $pedido;
    }
    return $lista;
}

const SELECT_PEDIDOS_ADMIN =
    'SELECT p.*, u.nombre AS cuenta_nombre, u.apellido AS cuenta_apellido, u.correo AS cuenta_correo
     FROM pedidos p
     LEFT JOIN usuarios u ON u.id = p.usuario_id';

// Qué hace: Busca pedidos para el admin. $busqueda puede ser un número de pedido ("20005",
//           "PF-20005" o "#PF-20005"; también "200" trae los que empiezan así) o texto: nombre,
//           apellido o correo del cliente.
// Recibe:   $busqueda: lo que se escribió en el buscador ("" = todos) · $limite: cuántos pedidos
//           como máximo.
// Entrega:  ["pedidos" => lista (los más nuevos primero), "total" => cuántos coinciden en total].
function listarPedidosAdmin($busqueda, $limite)
{
    global $pdo;

    $condicion = '';
    $valores   = [];

    $busqueda = trim($busqueda);
    if ($busqueda !== '') {
        $sinPrefijo = preg_replace('/^(#|pf-?)+/i', '', $busqueda);      // "#PF-20005" -> "20005"

        if ($sinPrefijo !== '' && ctype_digit($sinPrefijo)) {
            $condicion = 'WHERE CAST(p.id AS CHAR) LIKE :id';
            $valores[':id'] = $sinPrefijo . '%';
        } else {
            $condicion = 'WHERE (p.entrega_nombre LIKE :t1 OR p.entrega_apellido LIKE :t2
                              OR u.nombre LIKE :t3 OR u.apellido LIKE :t4 OR u.correo LIKE :t5)';
            $texto = '%' . $busqueda . '%';
            $valores[':t1'] = $texto;
            $valores[':t2'] = $texto;
            $valores[':t3'] = $texto;
            $valores[':t4'] = $texto;
            $valores[':t5'] = $texto;
        }
    }

    // Cuántos coinciden en total (aunque solo mostremos los primeros)
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM pedidos p LEFT JOIN usuarios u ON u.id = p.usuario_id ' . $condicion);
    $stmt->execute($valores);
    $total = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare(SELECT_PEDIDOS_ADMIN . ' ' . $condicion . ' ORDER BY p.id DESC LIMIT ' . (int) $limite);
    $stmt->execute($valores);

    return [
        'pedidos' => armarPedidosAdmin($stmt->fetchAll()),
        'total'   => $total,
    ];
}

// Qué hace: Busca UN pedido por su número, con cliente y productos.
// Recibe:   $id: el número de pedido (20005).
// Entrega:  El pedido, o null si no existe.
function pedidoAdminPorId($id)
{
    global $pdo;

    $stmt = $pdo->prepare(SELECT_PEDIDOS_ADMIN . ' WHERE p.id = :id');
    $stmt->execute([':id' => $id]);
    $filas = $stmt->fetchAll();

    if (count($filas) === 0) {
        return null;
    }
    $lista = armarPedidosAdmin($filas);
    return $lista[0];
}

// Qué hace: Deshace lo hecho en la transacción de cambiar estado y responde el error.
// Recibe:   $codigo: el código HTTP · $mensaje: el texto del error.
// Entrega:  Nada: el script termina aquí.
function rechazarCambioEstado($codigo, $mensaje)
{
    global $pdo;

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    responderError($codigo, $mensaje);
}

// Qué hace: Cambia el estado de un pedido. Entre confirmado, enviado y entregado se puede ir y
//           volver (por si el admin se equivoca). Se puede cancelar un pedido confirmado o enviado,
//           pero NO uno entregado. Un pedido cancelado no se puede reabrir. Al cancelar, las
//           unidades vuelven al stock.
// Recibe:   $id: el número de pedido · $nuevoEstado: "confirmado", "enviado", "entregado" o
//           "cancelado".
// Entrega:  El pedido actualizado. Responde 422, 404 o 409 si el cambio no está permitido.
function cambiarEstadoPedido($id, $nuevoEstado)
{
    global $pdo;

    if (!in_array($nuevoEstado, ESTADOS_PEDIDO, true)) {
        responderError(422, 'Estado no válido. Usa: ' . implode(', ', ESTADOS_PEDIDO) . '.');
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT estado FROM pedidos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $fila = $stmt->fetch();

    if (!$fila) {
        rechazarCambioEstado(404, 'El pedido no existe.');
    }

    $actual = $fila['estado'];

    if ($actual === $nuevoEstado) {                       // no hay nada que cambiar
        $pdo->rollBack();
        return pedidoAdminPorId($id);
    }
    if ($actual === 'cancelado') {
        rechazarCambioEstado(409, 'Un pedido cancelado no se puede reabrir.');
    }
    if ($actual === 'entregado' && $nuevoEstado === 'cancelado') {
        rechazarCambioEstado(409, 'Un pedido entregado no se puede cancelar.');
    }

    // Cambiamos el estado SOLO si sigue siendo el que vimos (si otro admin lo cambió justo ahora, no pisamos su cambio)
    $stmt = $pdo->prepare('UPDATE pedidos SET estado = :nuevo WHERE id = :id AND estado = :actual');
    $stmt->execute([':nuevo' => $nuevoEstado, ':id' => $id, ':actual' => $actual]);

    if ($stmt->rowCount() === 0) {
        rechazarCambioEstado(409, 'El pedido cambió mientras lo editabas. Recarga la página.');
    }

    // Cancelado: devolver las unidades al stock (los productos que ya no existen se ignoran)
    if ($nuevoEstado === 'cancelado') {
        $stmt = $pdo->prepare('SELECT producto_id, cantidad FROM pedido_items WHERE pedido_id = :id AND producto_id IS NOT NULL');
        $stmt->execute([':id' => $id]);

        $devolver = $pdo->prepare('UPDATE productos SET stock = stock + :cantidad WHERE id = :producto');
        foreach ($stmt->fetchAll() as $item) {
            $devolver->execute([':cantidad' => (int) $item['cantidad'], ':producto' => (int) $item['producto_id']]);
        }
    }

    $pdo->commit();
    return pedidoAdminPorId($id);
}
