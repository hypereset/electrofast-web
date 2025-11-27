<?php
session_start();
include 'php/conexion.php';

if(isset($_SESSION['id_usuario'])){
    if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) { header("Location: admin/index.php"); } 
    else { header("Location: index.php"); }
    exit;
}

$mensaje_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $sql = "SELECT * FROM usuarios WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password_hash']) || $password === $row['password_hash']) {
            $_SESSION['id_usuario'] = $row['id_usuario'];
            $_SESSION['nombre_usuario'] = $row['nombre_completo'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['rol'] = $row['id_role']; 
            $conn->query("UPDATE usuarios SET ultimo_login = NOW() WHERE id_usuario = " . $row['id_usuario']);

            if(isset($_GET['redirect']) && $_GET['redirect'] == 'checkout'){ header("Location: checkout.php"); } 
            elseif ($row['id_role'] == 1 || $row['id_role'] == 2) { header("Location: admin/index.php"); } 
            else { header("Location: index.php"); }
            exit;
        } else { $mensaje_error = "Contraseña incorrecta."; }
    } else { $mensaje_error = "Usuario no encontrado."; }
}

include 'includes/header.php';
?>

<div class="flex items-center justify-center min-h-[80vh] bg-base-200">
    <div class="card w-full max-w-md bg-base-100 shadow-2xl">
        <div class="card-body">
            
            <div class="text-center mb-6">
                <h2 class="text-3xl font-display font-bold text-primary">¡Bienvenido!</h2>
                <p class="opacity-60">Ingresa a tu cuenta ProtoHub</p>
            </div>

            <?php if($mensaje_error): ?>
                <div role="alert" class="alert alert-error mb-4 text-sm py-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span><?php echo $mensaje_error; ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="flex flex-col gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-bold">Correo Electrónico</span></label>
                    <input type="email" name="email" placeholder="tu@correo.com" class="input input-bordered" required />
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-bold">Contraseña</span></label>
                    <input type="password" name="password" placeholder="••••••••" class="input input-bordered" required />
                    <label class="label">
                        <a href="recuperar.php" class="label-text-alt link link-hover text-primary">¿Olvidaste tu contraseña?</a>
                    </label>
                </div>
                <div class="form-control mt-6">
                    <button class="btn btn-primary w-full font-bold text-lg shadow-md">INICIAR SESIÓN</button>
                </div>
            </form>

            <div class="divider my-6">O</div>

            <div class="text-center">
                <p class="text-sm mb-3">¿Eres nuevo en ProtoHub?</p>
                <a href="registro.php<?php echo (isset($_GET['redirect']) ? '?redirect='.$_GET['redirect'] : ''); ?>" class="btn btn-outline btn-block">
                    Crear Cuenta Gratuita
                </a>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>