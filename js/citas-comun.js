// =====================================================================
// COSAS QUE COMPARTEN TODAS LAS PANTALLAS DE CITAS
// (agenda del cliente, "Tu cuenta", panel del profesional y dashboard del admin)
// Si quieres cambiar cómo se llama un estado o su color, cámbialo AQUÍ y ya.
// =====================================================================

// Estado de la base de datos -> cómo se ve en pantalla.
// "clase" es el color de la etiqueta en el panel (css/panel.css: ok = verde, warn = amarillo, info = azul).
const ESTADOS_CITA = {
    pendiente:  { texto: 'Pendiente',  clase: 'warn' },
    confirmada: { texto: 'Confirmada', clase: 'info' },
    finalizada: { texto: 'Finalizada', clase: 'ok' }
};

const MESES_CITA = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

// "2026-11-02" -> "2 nov 2026"
function fechaCita(texto) {
    const partes = String(texto).slice(0, 10).split('-');
    return Number(partes[2]) + ' ' + MESES_CITA[Number(partes[1]) - 1] + ' ' + partes[0];
}

// "14:00" -> "2:00 pm"      "08:00" -> "8:00 am"
function horaCita(texto) {
    const partes = String(texto).split(':');
    const hora24 = Number(partes[0]);

    let sufijo = 'am';
    if (hora24 >= 12) {
        sufijo = 'pm';
    }
    let hora12 = hora24 % 12;
    if (hora12 === 0) {
        hora12 = 12;
    }
    return hora12 + ':' + partes[1] + ' ' + sufijo;
}

// El nombre del estado en pantalla ("pendiente" -> "Pendiente")
function textoEstadoCita(estado) {
    if (ESTADOS_CITA[estado]) {
        return ESTADOS_CITA[estado].texto;
    }
    return estado;
}

function claseEstadoCita(estado) {
    if (ESTADOS_CITA[estado]) {
        return ESTADOS_CITA[estado].clase;
    }
    return 'warn';
}

// "Nutricion · Ganar masa" (lo que quiere el cliente, en una línea)
function motivoCita(cita) {
    return cita.servicio + ' · ' + cita.objetivo;
}
