        <!-- Ventana para publicar como OFICIAL (la usan admin/foro.php y profesional/foro.php; js/foro-publicar.js la maneja) -->
        <div class="modal" id="modalNueva" hidden>
            <div class="modal-caja" role="dialog" aria-modal="true" aria-labelledby="nuevaTitulo">
                <div class="card-head">
                    <h2 id="nuevaTitulo">Nueva publicación oficial</h2>
                    <button type="button" class="icon-btn" id="btnCerrarNueva" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <p class="nota-oficial">Saldrá en la parte de arriba del foro, con la insignia “Oficial”.</p>
                <div id="mensajeNueva"></div>

                <form id="formNueva" class="form-panel">
                    <label>Categoría
                        <select name="categoria" required>
                            <option value="" disabled selected>Selecciona una categoría</option>
                            <option value="Entrenamiento">Entrenamiento</option>
                            <option value="Nutricion">Nutrición</option>
                            <option value="Suplementos">Suplementos</option>
                            <option value="Motivacion">Motivación</option>
                        </select>
                    </label>
                    <label>Título
                        <input type="text" name="titulo" minlength="5" maxlength="100" placeholder="¿Sobre qué quieres publicar?" required>
                    </label>
                    <label>Contenido
                        <textarea name="contenido" class="area-panel" minlength="10" maxlength="1000" rows="6" placeholder="Escribe aquí la publicación..." required></textarea>
                    </label>
                    <div class="modal-botones">
                        <button type="button" class="btn-secundario" id="btnCancelarNueva">Cancelar</button>
                        <button type="submit" class="btn-primary" id="btnPublicarNueva">Publicar</button>
                    </div>
                </form>
            </div>
        </div>
