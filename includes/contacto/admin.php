<?php
// =====================================================================
// MENSAJES DE CONTACTO PARA EL ADMIN
//   listarContactosAdmin($busqueda)          -> {mensajes, nuevos}
//   cambiarEstadoContacto($id, $estado)      -> el mensaje actualizado
// =====================================================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../http/respuesta.php';
require_once __DIR__ . '/validar.php';
require_once __DIR__ . '/publico.php';

// Qué hace: Lista los mensajes del formulario Contáctanos (el más nuevo primero) y cuenta cuántos
//           están sin leer.
// Recibe:   $busqueda: texto para buscar en nombre, correo, asunto o mensaje ("" = todos).
// Entrega:  ["mensajes" => lista, "nuevos" => cuántos hay sin leer en total].
function listarContactosAdmin($busqueda)
{
    global $pdo;

    $condiciones = [];
    $valores     = [];

    $busqueda = trim($busqueda);
    if ($busqueda !== '') {
        $condiciones[] = '(nombre LIKE :t1 OR correo LIKE :t2 OR asunto LIKE :t3 OR mensaje LIKE :t4)';
        $texto = '%' . $busqueda . '%';
        $valores[':t1'] = $texto;
        $valores[':t2'] = $texto;
        $valores[':t3'] = $texto;
        $valores[':t4'] = $texto;
    }

    $nuevos = (int) $pdo->query("SELECT COUNT(*) FROM contactos WHERE estado = 'nuevo'")->fetchColumn();

    return [
        'mensajes' => buscarContactos(implode(' AND ', $condiciones), $valores, 300),
        'nuevos'   => $nuevos,
    ];
}

// Qué hace: Marca un mensaje como "leido" o como "nuevo".
// Recibe:   $id: el id del mensaje · $estado: "leido" o "nuevo".
// Entrega:  El mensaje actualizado. Responde 422 si el estado no es válido y 404 si el mensaje no
//           existe.
function cambiarEstadoContacto($id, $estado)
{
    global $pdo;

    if (!in_array($estado, ESTADOS_CONTACTO, true)) {
        responderError(422, 'Estado no válido. Usa: ' . implode(', ', ESTADOS_CONTACTO) . '.');
    }
    if (buscarContacto($id) === null) {
        responderError(404, 'El mensaje no existe.');
    }

    $stmt = $pdo->prepare('UPDATE contactos SET estado = :estado WHERE id = :id');
    $stmt->execute([':estado' => $estado, ':id' => $id]);

    return buscarContacto($id);
}
