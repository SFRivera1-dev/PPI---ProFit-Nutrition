// =====================================================================
// CARRITO (por ahora solo en el navegador)
// Guarda en localStorage cuántas unidades de cada producto se agregaron:
//     {"7": 2, "3": 1}      ->  producto 7 x2, producto 3 x1
// y pinta el total en el numerito del carrito del header.
//
// Lo carga includes/header.php, así que funciona en todas las páginas con header.
// La página del carrito (pages/carrito.php) usa estas mismas funciones.
// Cuando el carrito pase a la base de datos, se cambia solo este archivo.
// =====================================================================
const Carrito = {

    // Devuelve el carrito guardado ({} si no hay o si el navegador no deja leerlo)
    leer() {
        try {
            const guardado = JSON.parse(localStorage.getItem('profit_carrito'));
            if (guardado && typeof guardado === 'object' && !Array.isArray(guardado)) {
                return guardado;
            }
        } catch (e) { /* sin acceso al almacenamiento */ }
        return {};
    },

    guardar(carrito) {
        try {
            localStorage.setItem('profit_carrito', JSON.stringify(carrito));
        } catch (e) { /* sin acceso al almacenamiento */ }
        this.pintarContador();
    },

    // Cuántas unidades de ese producto hay en el carrito
    cantidad(idProducto) {
        const cantidad = Number(this.leer()[idProducto]);
        if (Number.isInteger(cantidad) && cantidad > 0) {
            return cantidad;
        }
        return 0;
    },

    // Suma una unidad del producto. Si se pasa "maximo" (el stock), no deja pasarse.
    // Devuelve true si se agregó, false si ya estaba en el máximo.
    agregar(idProducto, maximo) {
        const actual = this.cantidad(idProducto);
        if (maximo !== undefined && actual >= maximo) {
            return false;
        }
        const carrito = this.leer();
        carrito[idProducto] = actual + 1;
        this.guardar(carrito);
        return true;
    },

    // Deja el producto con esa cantidad exacta (si es 0 o menos, lo quita)
    cambiar(idProducto, cantidad) {
        const carrito = this.leer();
        if (cantidad > 0) {
            carrito[idProducto] = cantidad;
        } else {
            delete carrito[idProducto];
        }
        this.guardar(carrito);
    },

    quitar(idProducto) {
        this.cambiar(idProducto, 0);
    },

    // Total de unidades en el carrito
    total() {
        const carrito = this.leer();
        let suma = 0;
        Object.keys(carrito).forEach(function (id) {
            const cantidad = Number(carrito[id]);
            if (Number.isInteger(cantidad) && cantidad > 0) {
                suma = suma + cantidad;
            }
        });
        return suma;
    },

    // Escribe el total en el numerito del icono del carrito
    pintarContador() {
        const total = this.total();
        document.querySelectorAll('.cart-shopping span').forEach(function (numero) {
            numero.textContent  = total;
        });
    }
};

Carrito.pintarContador();       // al cargar cada página
