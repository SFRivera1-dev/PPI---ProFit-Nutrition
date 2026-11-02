<?php
// Gestión de usuarios  admin-usuarios.js habla con la API.
$rolPanel = 'admin';
$titulo   = 'Usuarios';
$activo   = 'usuarios';
require __DIR__ . '/../includes/panel_inicio.php';
?>
        <section class="grid-usuarios">
            <article class="card">
                <div class="card-head">
                    <h2 id="tituloLista">Todos los usuarios</h2>
                    <div class="filtros" id="filtros"></div>
                </div>
                <div class="tabla-wrap">
                    <table class="tabla">
                        <thead><tr><th>Usuario</th><th>Rol</th><th>Estado</th><th class="der">Acciones</th></tr></thead>
                        <tbody id="tbodyUsuarios"></tbody>
                    </table>
                </div>
            </article>

            <article class="card">
                <div class="card-head"><h2>Crear usuario</h2></div>
                <form id="formCrear" class="form-panel">
                    <label>Nombre<input type="text" name="nombre" maxlength="60" required></label>
                    <label>Apellido<input type="text" name="apellido" maxlength="60" required></label>
                    <label>Correo<input type="email" name="correo" required></label>
                    <label>Contraseña<input type="password" name="password" minlength="8" placeholder="Mínimo 8 caracteres" required></label>
                    <label>Rol
                        <select name="rol" required>
                            <option value="admin">Administrador</option>
                            <option value="profesional" selected>Profesional</option>
                            <option value="cliente">Cliente</option>
                        </select>
                    </label>
                    <button class="btn-primary" type="submit">Crear usuario</button>
                </form>
            </article>
        </section>
<?php
$scripts = ['admin-usuarios.js'];
require __DIR__ . '/../includes/panel_fin.php';
