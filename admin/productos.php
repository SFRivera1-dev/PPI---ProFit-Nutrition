<?php
// Gestión de productos js/admin-productos.js habla con la API.
$rolPanel     = 'admin';
$titulo       = 'Productos';
$activo       = 'productos';
$accionesHtml = '<button type="button" class="btn-primary" id="btnNuevo">+ Nuevo producto</button>';
require __DIR__ . '/../includes/panel_inicio.php';
?>
        <div class="barra-productos">
            <label class="buscador-panel" aria-label="Buscar producto">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" id="buscar" placeholder="Buscar..." autocomplete="off">
            </label>
            <select id="filtroCategoria" class="select-panel" aria-label="Filtrar por categoría">
                <option value="">Todas las categorías</option>
            </select>
        </div>

        <article class="card">
            <div class="tabla-wrap">
                <table class="tabla tabla-productos">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th class="der">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyProductos">
                        <tr><td colspan="5" class="vacio">Cargando productos...</td></tr>
                    </tbody>
                </table>
            </div>
        </article>

        <!-- Ventana para crear / editar un producto (js/admin-productos.js la abre y la cierra) -->
        <div class="modal" id="modalProducto" hidden>
            <div class="modal-caja" role="dialog" aria-modal="true" aria-labelledby="modalTitulo">
                <div class="card-head">
                    <h2 id="modalTitulo">Nuevo producto</h2>
                    <button type="button" class="icon-btn" id="btnCerrarModal" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div id="mensajeModal"></div>

                <form id="formProducto" class="form-panel">
                    <label>Nombre
                        <input type="text" name="nombre" maxlength="120" required>
                    </label>
                    <label>Categoría
                        <select name="categoria" required></select>
                    </label>
                    <div class="fila-2">
                        <label>Precio (COP)
                            <input type="number" name="precio" min="1" step="1" required>
                        </label>
                        <label>Stock (unidades)
                            <input type="number" name="stock" min="0" step="1" required>
                        </label>
                    </div>
                    <div class="campo-imagen">
                        <span>Imagen (opcional · JPG, PNG o WEBP · máx. 2 MB)</span>
                        <div class="imagen-fila">
                            <img id="vistaPrevia" class="vista-previa" alt="Vista previa" hidden>
                            <div class="imagen-botones">
                                <label class="btn-secundario btn-archivo">
                                    <i class="fa-solid fa-upload"></i> <span id="textoArchivo">Subir imagen</span>
                                    <input type="file" name="imagen" id="inputImagen" accept="image/jpeg,image/png,image/webp">
                                </label>
                                <button type="button" class="btn-mini peligro" id="btnQuitarImagen" hidden>Quitar imagen</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-botones">
                        <button type="button" class="btn-secundario" id="btnCancelar">Cancelar</button>
                        <button type="submit" class="btn-primary" id="btnGuardar">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
<?php
$scripts = ['admin-productos.js'];
require __DIR__ . '/../includes/panel_fin.php';
