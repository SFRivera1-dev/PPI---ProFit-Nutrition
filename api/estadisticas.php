<?php
// =====================================================================
// GET /api/estadisticas.php   (solo ADMIN)
// Responde:  200 {"clientes_activos": 120, "profesionales_activos": 8,
//                 "ventas_mes": 1250000, "pedidos_nuevos": 4}
// =====================================================================
require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/http/respuesta.php';
require_once __DIR__ . '/../includes/http/peticion.php';
require_once __DIR__ . '/../includes/seguridad/autenticacion.php';

exigirRol('admin');
permitir(['GET']);

// Qué hace: Cuenta los usuarios con estado "activo" que tienen un rol.
// Recibe:   $rol: "cliente" o "profesional".
// Entrega:  Un número entero.
function contarActivos($rol)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE rol = :rol AND estado = 'activo'");
    $stmt->execute([':rol' => $rol]);
    return (int) $stmt->fetchColumn();
}

// Qué hace: Suma el total de los pedidos de este mes que NO están cancelados.
// Recibe:   Nada.
// Entrega:  El total vendido en pesos (número entero).
function ventasDelMes()
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE estado <> 'cancelado' AND creado_en >= :desde");
    $stmt->execute([':desde' => date('Y-m-01 00:00:00')]);
    return (int) $stmt->fetchColumn();
}

// Qué hace: Cuenta los pedidos "confirmados" que todavía no se han enviado.
// Recibe:   Nada.
// Entrega:  Un número entero.
function pedidosNuevos()
{
    global $pdo;

    return (int) $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'confirmado'")->fetchColumn();
}

responder([
    'clientes_activos'      => contarActivos('cliente'),
    'profesionales_activos' => contarActivos('profesional'),
    'ventas_mes'            => ventasDelMes(),
    'pedidos_nuevos'        => pedidosNuevos(),
]);
