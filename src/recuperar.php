<?php
session_start();
include 'php/conexion.php';

$paso = 1; // Controla qué formulario mostramos (1=Email, 2=Pregunta)
$mensaje = "";
$tipo_alerta = "";
$datos_usuario = [];

// --- PROCESAR FORMULARIOS ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // PASO 1: BUSCAR USUARIO POR CORREO
    if (isset($_POST['accion']) && $_POST['accion'] == 'buscar_email') {
        $email = $conn->real_escape_string($_POST['email']);
        $sql = "SELECT * FROM usuarios WHERE email = '$email'";
        $res = $conn->query($sql);
        
        if ($res->num_rows > 0) {
            $datos_usuario = $res->fetch_assoc();
            $paso = 2; // Pasamos al siguiente nivel (Pregunta)
        } else {
            $mensaje = "No encontramos ninguna cuenta con ese correo.";
            $tipo_alerta = "danger";
        }
    }

    // PASO 2: VERIFICAR RESPUESTA Y CAMBIAR CONTRASEÑA
    if (isset($_POST['accion']) && $_POST['accion'] == 'cambiar_pass') {
        $id_usuario = $_POST['id_usuario'];
        $respuesta_input = $conn->real_escape_string(strtolower($_POST['respuesta'])); // Convertimos a minúsculas
        $pass_nueva = $_POST['password_nueva'];
        
        // Volvemos a consultar para seguridad
        $sql = "SELECT * FROM usuarios WHERE id_usuario = $id_usuario";
        $row = $conn->query($sql)->fetch_assoc();
        
        // Comparamos respuesta guardada (en minúsculas) vs input
        if ($respuesta_input === strtolower($row['respuesta_seguridad'])) {
            // ¡CORRECTO! Encriptamos y guardamos
            $hash_nuevo = password_hash($pass_nueva, PASSWORD_BCRYPT);
            $conn->query("UPDATE usuarios SET password_hash = '$hash_nuevo' WHERE id_usuario = $id_usuario");
            
            // Éxito y redirección con JS
            echo "<script>
                alert('¡Contraseña restablecida con éxito! Ahora puedes iniciar sesión.');
                window.location = 'login.php';
            </script>";
        } else {
            $mensaje = "La respuesta a la pregunta de seguridad es incorrecta.";
            $tipo_alerta = "danger";
            $datos_usuario = $row; // Mantenemos datos para no perder el formulario
            $paso = 2;
        }
    }
}

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0">
                
                <div class="card-header navbar-proto text-white text-center py-3">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-lock"></i> Recuperar Acceso</h4>
                </div>
                
                <div class="card-body p-4">
                    
                    <?php if($mensaje): ?>
                        <div class="alert alert-<?php echo $tipo_alerta; ?> text-center mb-4">
                            <?php echo $mensaje; ?>
                        </div>
                    <?php endif; ?>

                    <?php if($paso == 1): ?>
                        <p class="text-muted text-center mb-4 small">Ingresa el correo electrónico con el que te registraste.</p>
                        <form action="recuperar.php" method="POST">
                            <input type="hidden" name="accion" value="buscar_email">
                            <div class="mb-3">
                                <label class="fw-bold form-label">Correo Registrado</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" required placeholder="ejemplo@electrofast.com">
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-proto fw-bold py-2">Buscar Cuenta</button>
                            </div>
                        </form>
                    
                    <?php elseif($paso == 2): ?>
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-light text-primary rounded-circle mb-2 fw-bold border" style="width: 60px; height: 60px; font-size: 24px;">
                                <?php echo strtoupper(substr($datos_usuario['nombre_completo'], 0, 1)); ?>
                            </div>
                            <h5 class="fw-bold mb-0"><?php echo $datos_usuario['nombre_completo']; ?></h5>
                            <small class="text-muted">Responde tu pregunta de seguridad</small>
                        </div>

                        <form action="recuperar.php" method="POST">
                            <input type="hidden" name="accion" value="cambiar_pass">
                            <input type="hidden" name="id_usuario" value="<?php echo $datos_usuario['id_usuario']; ?>">

                            <div class="mb-3">
                                <label class="fw-bold text-primary small text-uppercase">Pregunta Secreta:</label>
                                <input type="text" class="form-control bg-light" value="<?php echo $datos_usuario['pregunta_seguridad']; ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold form-label">Tu Respuesta:</label>
                                <input type="text" name="respuesta" class="form-control" required placeholder="Escribe tu respuesta...">
                            </div>

                            <hr class="my-4">

                            <div class="mb-3">
                                <label class="fw-bold form-label">Nueva Contraseña:</label>
                                <input type="password" name="password_nueva" class="form-control" required minlength="6" placeholder="Mínimo 6 caracteres">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-proto fw-bold py-2">Restablecer Contraseña</button>
                            </div>
                        </form>
                    <?php endif; ?>

                    <div class="text-center mt-4">
                        <a href="login.php" class="text-decoration-none text-secondary small hover-link">
                            <i class="fas fa-arrow-left"></i> Volver al Inicio de Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>