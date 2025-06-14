<?php
require("../php/conexion.php");

$sql = "SELECT id, nombre, correo, telefono, rol, created_at FROM usuarios WHERE rol = 'usuario'";
$resultado = $con->query($sql);
?>

<div class="usuarios-header">
    <h2>Lista de Usuarios</h2>
    <button id="btn-nuevo-usuario">+ Nuevo Usuario</button>
</div>

<table class="tabla-usuarios">
    <thead>
        <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Rol</th>
            <th>Registrado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $contador = 1;
        while ($row = $resultado->fetch_assoc()) { 
        ?>
        <tr class="fila-usuario"
            data-id="<?= $row['id'] ?>"
            data-nombre="<?= htmlspecialchars($row['nombre']) ?>"
            data-correo="<?= htmlspecialchars($row['correo']) ?>"
            data-telefono="<?= htmlspecialchars($row['telefono']) ?>"
            data-rol="<?= htmlspecialchars($row['rol']) ?>"
            data-fecha="<?= date("d/m/Y", strtotime($row['created_at'])) ?>">
            <td><?= $contador ?></td>
            <td><?= htmlspecialchars($row['nombre']) ?></td>
            <td><?= htmlspecialchars($row['correo']) ?></td>
            <td><?= htmlspecialchars($row['telefono']) ?></td>
            <td><?= htmlspecialchars($row['rol']) ?></td>
            <td><?= date("d/m/Y", strtotime($row['created_at'])) ?></td>
            <td>
                <button class="editar" data-id="<?= $row['id'] ?>">Editar</button>
                <button class="eliminar" data-id="<?= $row['id'] ?>">Eliminar</button>
            </td>
        </tr>
        <?php $contador++; } ?>
    </tbody>
</table>

<?php include("../php/modales-usuarios.html"); ?>
