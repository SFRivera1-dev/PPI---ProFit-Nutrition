<?php
// =====================================================================
// RECURSO: pacientes   (solo PROFESIONAL y ADMIN)
// Paciente = cliente que ha tenido al menos una cita.
//
//   GET /api/pacientes.php            -> lista de pacientes (la cita más reciente primero)
//       filtro opcional:  ?q=ana  (nombre, apellido o correo)
//       200 {"pacientes":[{id,nombre,apellido,correo,total_citas,finalizadas,en_curso,ultima_fecha}]}
//   GET /api/pacientes.php?id=7       -> un paciente con todo su historial de citas
//       200 {"paciente":{...}, "citas":[...]}      404 si ese cliente no tiene citas
// =====================================================================
require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/http/respuesta.php';
require_once __DIR__ . '/../includes/http/peticion.php';
require_once __DIR__ . '/../includes/seguridad/autenticacion.php';
require_once __DIR__ . '/../includes/citas/pacientes.php';

permitir(['GET']);
$usuario = usuarioActual();                              // 401 si no hay token válido
if ($usuario['rol'] !== 'profesional' && $usuario['rol'] !== 'admin') {
    responderError(403, 'No tienes permiso para ver los pacientes.');
}

$id = (int) parametroUrl('id');
if ($id > 0) {
    responder(historialPaciente($id));
}

responder(['pacientes' => listarPacientes(parametroUrl('q'))]);
