<?php
include '../php/conexion.php';
include 'header.php';

// Seguridad: Solo Admin
if($_SESSION['rol'] != 1){
    echo "<script>window.location='index.php';</script>";
    exit;
}

if (!isset($_GET['id'])) {
    echo "<script>window.location='usuarios.php';</script>";
    exit;
}
$id_edit = $_GET['id'];

// 1. PROCESAR GUARDADO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];
    
    // Validar si escribieron contraseña nueva
    $sql_pass = "";
    if (!empty($_POST['password'])) {
        $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $sql_pass = ", password_hash = '$hash'";
    }

    // Actualizamos datos (y pass solo si cambió)
    $sql = "UPDATE usuarios SET nombre_completo='$nombre', email='$email', id_role='$rol' $sql_pass WHERE id_usuario=$id_edit";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
            Swal.fire('Actualizado', 'Datos de usuario modificados correctamente.', 'success').then(() => {
                window.location = 'usuarios.php';
            });
        </script>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// 2. OBTENER DATOS ACTUALES DEL USUARIO
$user = $conn->query("SELECT * FROM usuarios WHERE id_usuario = $id_edit")->fetch_assoc();
?>

<div class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">✏️ Editar Usuario</h2>
        <a href="usuarios.php" class="btn btn-outline-secondary">Cancelar</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-light fw-bold">
                    Editando a: <span class="text-primary"><?php echo $user['nombre_completo']; ?></span>
                </div>
                <div class="card-body p-5">
                    <form action="editar_usuario.php?id=<?php echo $id_edit; ?>" method="POST">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo $user['nombre_completo']; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Rol / Permisos</label>
                                <select name="rol" class="form-select bg-light" required>
                                    <option value="1" <?php if($user['id_role']==1) echo 'selected'; ?>>🛑 Administrador</option>
                                    <option value="2" <?php if($user['id_role']==2) echo 'selected'; ?>>👷 Trabajador</option>
                                    <option value="3" <?php if($user['id_role']==3) echo 'selected'; ?>>👤 Cliente</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-danger">Cambiar Contraseña</label>
                                <input type="password" name="password" class="form-control" placeholder="Dejar vacío para no cambiar">
                                <div class="form-text small">Solo escribe aquí si quieres resetearla.</div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
</body>
</html>