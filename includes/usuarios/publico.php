<?php
// =====================================================================
// QUÉ DATOS DEL USUARIO SALEN HACIA EL NAVEGADOR
// Recibe:  una fila de la tabla usuarios
// Entrega: solo los campos que se pueden mostrar (NUNCA la contraseña)
// =====================================================================
function usuarioPublico($usuario)
{
    return [
        'id'       => (int) $usuario['id'],
        'nombre'   => $usuario['nombre'],
        'apellido' => $usuario['apellido'],
        'correo'   => $usuario['correo'],
        'rol'      => $usuario['rol'],
        'estado'   => $usuario['estado'],
    ];
}
