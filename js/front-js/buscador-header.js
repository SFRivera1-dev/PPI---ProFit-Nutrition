// =====================================================================
// Buscador del header (el de escritorio y el del menú responsive)
//  - En pages/suplementos.php: filtra los productos mientras se escribe
//    (llama a buscarProductos() de js/suplementos.js).
//  - En cualquier otra página: al dar Enter te lleva a
//    suplementos.php?q=lo-que-escribiste y allá salen los resultados.
// Va DESPUÉS de search-delete.js. BASE_URL viene de js/api.js.
// =====================================================================
(function () {
    const camposBuscador = document.querySelectorAll('header form .SearchInput');
    const esTienda       = document.getElementById('gridProductos') !== null;
    let temporizador     = null;

    // Si se entró con ?q=..., el texto aparece escrito en el buscador
    const textoInicial = new URLSearchParams(window.location.search).get('q');
    if (esTienda && textoInicial !== null) {
        camposBuscador.forEach(function (campo) {
            campo.value = textoInicial;
            const botonX = campo.form.querySelector('.ButtonClear');
            if (textoInicial !== '' && botonX) {
                botonX.style.visibility = 'visible';
            }
        });
    }

    camposBuscador.forEach(function (campo) {

        // Tienda: buscar enseguida (espera 250 ms a que termine de teclear)
        campo.addEventListener('input', function () {
            // el buscador de escritorio y el responsive muestran lo mismo
            camposBuscador.forEach(function (otro) {
                if (otro !== campo) {
                    otro.value = campo.value;
                }
            });

            if (!esTienda) {
                return;
            }
            clearTimeout(temporizador);
            temporizador = setTimeout(function () {
                buscarProductos(campo.value);
            }, 250);
        });

        // Enter
        campo.form.addEventListener('submit', function (e) {
            e.preventDefault();
            const texto = campo.value.trim();

            if (esTienda) {
                clearTimeout(temporizador);
                buscarProductos(texto);
            } else if (texto !== '') {
                window.location.href = BASE_URL + '/pages/suplementos.php?q=' + encodeURIComponent(texto);
            }
        });
    });
})();
