<?php
require("conexion.php");

$id       = $_POST['id'] ?? '';
$nombre   = trim($_POST['nombre'] ?? '');
$correo   = trim($_POST['correo'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$clave    = trim($_POST['clave'] ?? '');
$rol      = 'usuario'; // por defecto

if (empty($nombre) || empty($correo) || empty($telefono)) {
    echo "Faltan campos obligatorios.";
    exit;
}

if (empty($id)) {
    // Crear nuevo usuario
    if (empty($clave)) {
        echo "La contraseña es obligatoria para nuevos usuarios.";
        exit;
    }

    $stmt = $con->prepare("INSERT INTO usuarios (nombre, correo, telefono, password, rol) VALUES (?, ?, ?, MD5(?), ?)");
    $stmt->bind_param("sssss", $nombre, $correo, $telefono, $clave, $rol);

    if ($stmt->execute()) {
        echo "Usuario creado correctamente.";
    } else {
        echo "Error al crear usuario: " . $stmt->error;
    }
} else {
    // Editar usuario
    // Verificamos si se desea actualizar la clave
    if (!empty($clave)) {
        $stmt = $con->prepare("UPDATE usuarios SET nombre=?, correo=?, telefono=?, password=MD5(?) WHERE id=?");
        $stmt->bind_param("ssssi", $nombre, $correo, $telefono, $clave, $id);
    } else {
        $stmt = $con->prepare("UPDATE usuarios SET nombre=?, correo=?, telefono=? WHERE id=?");
        $stmt->bind_param("sssi", $nombre, $correo, $telefono, $id);
    }

    if ($stmt->execute()) {
        echo "Usuario actualizado correctamente.";
    } else {
        echo "Error al actualizar usuario: " . $stmt->error;
    }
}
