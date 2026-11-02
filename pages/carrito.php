<?php require_once __DIR__ . '/../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi carrito - ProFit Nutrition</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/carrito.css">
    <link rel="icon" href="../src/image/p amarilla bg.png">
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <main class="carrito">

        <h1 class="carrito__titulo">Mi carrito</h1>

        <!-- Avisos (producto agotado, cantidad ajustada, etc.) -->
        <div class="carrito__aviso" id="avisoCarrito" hidden></div>

        <!-- Cargando -->
        <p class="carrito__cargando" id="cargandoCarrito">Cargando tu carrito...</p>

        <!-- Carrito vacío -->
        <section class="carrito__vacio" id="carritoVacio" hidden>
            <i class="fa-solid fa-cart-shopping"></i>
            <h2>Tu carrito está vacío</h2>
            <p>Agrega suplementos desde el catálogo para verlos aquí.</p>
            <a href="suplementos.php" class="btn-amarillo">Ver suplementos</a>
        </section>

        <!-- Carrito con productos -->
        <div class="carrito__contenido" id="carritoContenido" hidden>

            <section class="carrito__lista-caja">
                <ul class="carrito__lista" id="listaCarrito"></ul>
            </section>

            <aside class="resumen">
                <h2 class="resumen__titulo">Resumen de compra</h2>

                <div class="resumen__fila">
                    <span id="resumenProductos">Productos (0)</span>
                    <span id="resumenSubtotal">$0</span>
                </div>
                <div class="resumen__fila resumen__fila--total">
                    <span>Total</span>
                    <span id="resumenTotal">$0</span>
                </div>

                <button type="button" class="btn-amarillo btn-amarillo--ancho" id="btnPagar">Continuar a pagar</button>
                <a href="suplementos.php" class="resumen__seguir">Seguir comprando</a>
            </aside>

        </div>
    </main>

    <div class="modal-pago" id="modalPago" hidden>
        <div class="modal-pago__caja" id="pagoCaja" role="dialog" aria-modal="true" aria-labelledby="pagoTitulo">

            <div class="modal-pago__cabecera">
                <h2 id="pagoTitulo"> Finalizar compra</h2>
                <button type="button" class="modal-pago__cerrar" id="pagoCerrar" aria-label="Cerrar">&times;</button>
            </div>

            <div class="modal-pago__cuerpo" id="pagoCuerpo">

                <form id="formPago" class="pago__columna" novalidate autocomplete="on">

                    <section class="pago__tarjeta">
                        <h3 class="pago__paso">Datos de entrega</h3>
                        <div class="pago__fila">
                            <div class="campo">
                                <label for="pagoNombre">Nombre</label>
                                <input type="text" id="pagoNombre" placeholder="Tu nombre" maxlength="60" autocomplete="given-name">
                                <small class="campo__error" data-error-de="pagoNombre"></small>
                            </div>
                            <div class="campo">
                                <label for="pagoApellido">Apellido</label>
                                <input type="text" id="pagoApellido" placeholder="Tu apellido" maxlength="60" autocomplete="family-name">
                                <small class="campo__error" data-error-de="pagoApellido"></small>
                            </div>
                        </div>
                        <div class="pago__fila">
                            <div class="campo">
                                <label for="pagoPais">Pais</label>
                                <input type="text" id="pagoPais" value="Colombia" readonly>
                            </div>
                            <div class="campo">
                                <label for="pagoCiudad">Ciudad</label>
                                <input type="text" id="pagoCiudad" placeholder="Tu ciudad" maxlength="60" autocomplete="address-level2">
                                <small class="campo__error" data-error-de="pagoCiudad"></small>
                            </div>
                        </div>
                        <div class="campo">
                            <label for="pagoDireccion">Direccion</label>
                            <input type="text" id="pagoDireccion" placeholder="Calle, numero, barrio, apartamento" maxlength="120" autocomplete="street-address">
                            <small class="campo__error" data-error-de="pagoDireccion"></small>
                        </div>
                    </section>

                    <section class="pago__tarjeta">
                        <h3 class="pago__paso">Metodo de pago</h3>
                        <div class="pago__metodo"><i class="fa-regular fa-credit-card"></i> Tarjeta de credito o debito</div>

                        <div class="campo">
                            <label for="pagoTarjeta">Numero de tarjeta</label>
                            <input type="text" id="pagoTarjeta" placeholder="0000 0000 0000 0000" inputmode="numeric" maxlength="23" autocomplete="cc-number">
                            <small class="campo__error" data-error-de="pagoTarjeta"></small>
                        </div>
                        <div class="campo">
                            <label for="pagoTitular">Nombre del titular</label>
                            <input type="text" id="pagoTitular" placeholder="Como aparece en la tarjeta" maxlength="80" autocomplete="cc-name">
                            <small class="campo__error" data-error-de="pagoTitular"></small>
                        </div>
                        <div class="pago__fila">
                            <div class="campo">
                                <label for="pagoVence">Fecha de vencimiento</label>
                                <input type="text" id="pagoVence" placeholder="MM / AA" inputmode="numeric" maxlength="7" autocomplete="cc-exp">
                                <small class="campo__error" data-error-de="pagoVence"></small>
                            </div>
                            <div class="campo">
                                <label for="pagoCvv">CVV</label>
                                <input type="password" id="pagoCvv" placeholder="***" inputmode="numeric" maxlength="3" autocomplete="cc-csc">
                                <small class="campo__error" data-error-de="pagoCvv"></small>
                            </div>
                        </div>
                    </section>
                </form>

                <aside class="pago__columna">
                    <section class="pago__tarjeta pago__resumen">
                        <h3>Informacion del pago</h3>
                        <div class="resumen__fila"><span id="pagoResumenProductos">Productos (0)</span><span id="pagoSubtotal">$0</span></div>
                        <div class="resumen__fila"><span>Domicilio</span><span id="pagoDomicilio">$10.000</span></div>
                        <div class="resumen__fila resumen__fila--total"><span>Total a pagar</span><span id="pagoTotal">$0</span></div>

                        <!-- Aquí sale el error si el servidor rechaza el pedido (sin stock, etc.) -->
                        <p class="pago__error" id="pagoError" role="alert" hidden></p>

                        <!-- form="formPago": este botón envía el formulario aunque esté en la otra columna -->
                        <button type="submit" form="formPago" class="btn-amarillo btn-amarillo--ancho" id="btnCompletar">Completar compra</button>
                        <p class="pago__seguro"><i class="fa-solid fa-shield-halved"></i> Tus datos viajan protegidos</p>
                    </section>
                </aside>
            </div>

            <!-- Confirmación del pedido (se llena con lo que responde la API) -->
            <div class="exito" id="pagoExito" hidden>
                <div class="exito__icono"><i class="fa-solid fa-check"></i></div>
                <h3 class="exito__titulo">¡Pedido recibido!</h3>
                <p class="exito__gracias" id="exitoGracias"></p>

                <div class="exito__caja exito__datos">
                    <div>
                        <span class="exito__etiqueta">Numero de orden</span>
                        <strong class="exito__orden" id="exitoOrden"></strong>
                    </div>
                    <div>
                        <span class="exito__etiqueta">Fecha</span>
                        <strong id="exitoFecha"></strong>
                    </div>
                    <div>
                        <span class="exito__etiqueta">Estado</span>
                        <span class="exito__estado" id="exitoEstado"></span>
                    </div>
                </div>

                <div class="exito__caja">
                    <h4 class="exito__subtitulo">Datos de entrega</h4>
                    <p class="exito__destinatario" id="exitoDestinatario"></p>
                    <p class="exito__texto" id="exitoDireccion"></p>
                    <p class="exito__texto" id="exitoCiudad"></p>
                </div>

                <div class="exito__caja">
                    <h4 class="exito__subtitulo">Productos del pedido</h4>
                    <ul class="exito__lista" id="exitoProductos"></ul>

                    <div class="exito__totales">
                        <div><span>Productos</span><span id="exitoSubtotal"></span></div>
                        <div><span>Domicilio</span><span id="exitoDomicilio"></span></div>
                        <div class="exito__total"><span>Total pagado</span><span id="exitoTotal"></span></div>
                    </div>
                </div>

                <button type="button" class="btn-amarillo exito__listo" id="pagoExitoCerrar">Listo</button>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/front-js/search-delete.js"></script>
    <script src="../js/front-js/buscador-header.js"></script>
    <script src="../js/front-js/menu-header.js"></script>
    <script src="../js/front-js/buscador-responsive.js"></script>
    <script src="../js/carrito-pagina.js"></script>
    <script src="../js/carrito-pago.js"></script>
</body>

</html>
