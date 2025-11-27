<?php
session_start();
include 'php/conexion.php';
if (!isset($_SESSION['id_usuario'])) { header("Location: login.php"); exit; }

$id_usuario = $_SESSION['id_usuario'];
$mensaje = ""; $tipo = "";

// Lógica de Actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $email = $conn->real_escape_string($_POST['email']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    
    $check = $conn->query("SELECT id_usuario FROM usuarios WHERE email = '$email' AND id_usuario != $id_usuario");
    if ($check->num_rows > 0) {
        $mensaje = "El correo ya está en uso."; $tipo = "alert-error";
    } else {
        $sql_pass = "";
        if (!empty($_POST['password_nueva'])) {
            $hash = password_hash($_POST['password_nueva'], PASSWORD_BCRYPT);
            $sql_pass = ", password_hash = '$hash'";
        }
        $conn->query("UPDATE usuarios SET nombre_completo='$nombre', email='$email', telefono='$telefono' $sql_pass WHERE id_usuario=$id_usuario");
        $_SESSION['nombre_usuario'] = $nombre;
        $mensaje = "Perfil actualizado correctamente."; $tipo = "alert-success";
    }
}

$usuario = $conn->query("SELECT * FROM usuarios WHERE id_usuario = $id_usuario")->fetch_assoc();
include 'includes/header.php'; 
?>

<div class="max-w-2xl mx-auto">
    <div class="text-center mb-8">
        <div class="avatar placeholder mb-4">
            <div class="bg-neutral text-neutral-content rounded-full w-24 text-3xl">
                <?php echo strtoupper(substr($usuario['nombre_completo'], 0, 2)); ?>
            </div>
        </div>
        <h1 class="text-3xl font-display font-bold"><?php echo $usuario['nombre_completo']; ?></h1>
        <p class="opacity-60 text-sm">Miembro desde <?php echo date('Y', strtotime($usuario['fecha_registro'])); ?></p>
        <div class="badge badge-primary mt-2 font-bold text-lg p-3">$<?php echo number_format($usuario['puntos'], 2); ?> Puntos</div>
    </div>

    <?php if($mensaje): ?>
        <div role="alert" class="alert <?php echo $tipo; ?> mb-6 shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span><?php echo $mensaje; ?></span>
        </div>
    <?php endif; ?>

    <div class="card bg-base-100 shadow-xl border border-base-200">
        <div class="card-body">
            <h2 class="card-title border-b pb-2 mb-4">Editar Datos</h2>
            
            <form action="" method="POST" class="flex flex-col gap-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="form-control w-full">
                        <div class="label"><span class="label-text font-bold">Nombre Completo</span></div>
                        <input type="text" name="nombre" value="<?php echo $usuario['nombre_completo']; ?>" class="input input-bordered w-full" required />
                    </label>
                    <label class="form-control w-full">
                        <div class="label"><span class="label-text font-bold">Teléfono</span></div>
                        <input type="tel" name="telefono" value="<?php echo $usuario['telefono']; ?>" class="input input-bordered w-full" required />
                    </label>
                </div>

                <label class="form-control w-full">
                    <div class="label"><span class="label-text font-bold">Correo Electrónico</span></div>
                    <input type="email" name="email" value="<?php echo $usuario['email']; ?>" class="input input-bordered w-full" required />
                </label>

                <div class="collapse collapse-arrow bg-base-200 mt-4 rounded-box">
                    <input type="checkbox" /> 
                    <div class="collapse-title font-bold text-sm">Cambiar Contraseña (Opcional)</div>
                    <div class="collapse-content"> 
                        <input type="password" name="password_nueva" placeholder="Nueva contraseña..." class="input input-bordered w-full mt-2" />
                    </div>
                </div>

                <div class="card-actions justify-end mt-6">
                    <button type="submit" class="btn btn-primary px-8">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>