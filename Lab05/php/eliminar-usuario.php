<?php
require("conexion.php");

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo "ID inválido.";
    exit;
}

$id = intval($_POST['id']);

$stmt = $con->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "Usuario eliminado correctamente.";
    } else {
        echo "El usuario no existe.";
    }
} else {
    echo "Error al eliminar el usuario.";
}
?>
