<?php
session_start();
include 'php/conexion.php';

if(isset($_SESSION['id_usuario'])){ header("Location: index.php"); exit; }

$mensaje = "";
$tipo_alerta = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $email = $conn->real_escape_string($_POST['email']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $password = $_POST['password'];
    $pregunta = $conn->real_escape_string($_POST['pregunta_seguridad']);
    $respuesta = $conn->real_escape_string(strtolower($_POST['respuesta_seguridad']));
    
    $pass_hash = password_hash($password, PASSWORD_BCRYPT);

    $check_email = "SELECT id_usuario FROM usuarios WHERE email = '$email'";
    if($conn->query($check_email)->num_rows > 0){
        $mensaje = "Este correo ya está registrado.";
        $tipo_alerta = "danger";
    } else {
        $sql = "INSERT INTO usuarios (id_role, nombre_completo, email, password_hash, telefono, pregunta_seguridad, respuesta_seguridad) 
                VALUES (3, '$nombre', '$email', '$pass_hash', '$telefono', '$pregunta', '$respuesta')";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['id_usuario'] = $conn->insert_id;
            $_SESSION['nombre_usuario'] = $nombre;
            $_SESSION['email'] = $email;
            $_SESSION['rol'] = 3;

            if(isset($_GET['redirect']) && $_GET['redirect'] == 'checkout'){
                echo "<script>window.location='checkout.php';</script>";
            } else {
                echo "<script>window.location='index.php';</script>";
            }
            exit;
        } else {
            $mensaje = "Error al registrar: " . $conn->error;
            $tipo_alerta = "danger";
        }
    }
}

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header navbar-proto text-white text-center py-3">
                    <h4 class="mb-0 fw-bold">Registro de Estudiante</h4>
                </div>
                <div class="card-body p-4">
                    
                    <?php if($mensaje): ?>
                        <div class="alert alert-<?php echo $tipo_alerta; ?>" role="alert"><?php echo $mensaje; ?></div>
                    <?php endif; ?>

                    <?php $redirect_link = (isset($_GET['redirect']) && $_GET['redirect'] == 'checkout') ? '?redirect=checkout' : ''; ?>

                    <form action="registro.php<?php echo $redirect_link; ?>" method="POST">
                        <h5 class="text-muted mb-3 border-bottom pb-2 small text-uppercase fw-bold">Datos de la Cuenta</h5>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre Completo</label>
                            <input type="text" class="form-control" name="nombre" placeholder="Juan Pérez" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Correo Electrónico</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Teléfono</label>
                                <input type="tel" class="form-control" name="telefono" placeholder="55..." required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Contraseña</label>
                            <input type="password" class="form-control" name="password" required minlength="6">
                            <div class="form-text">Mínimo 6 caracteres.</div>
                        </div>

                        <h5 class="text-muted mt-4 mb-3 border-bottom pb-2 small text-uppercase fw-bold">Seguridad</h5>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Pregunta Secreta</label>
                            <select class="form-select" name="pregunta_seguridad" required>
                                <option value="" selected disabled>Selecciona una pregunta...</option>
                                <option value="¿Cómo se llamaba tu primera mascota?">¿Cómo se llamaba tu primera mascota?</option>
                                <option value="¿Cuál es el segundo nombre de tu madre?">¿Cuál es el segundo nombre de tu madre?</option>
                                <option value="¿En qué ciudad se conocieron tus padres?">¿En qué ciudad se conocieron tus padres?</option>
                                <option value="¿Cuál fue tu primer videojuego favorito?">¿Cuál fue tu primer videojuego favorito?</option>
                                <option value="¿Cómo se llamaba tu escuela primaria?">¿Cómo se llamaba tu escuela primaria?</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Respuesta</label>
                            <input type="text" class="form-control" name="respuesta_seguridad" placeholder="Ej: Firulais" required>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-proto py-2">¡Registrarme Ahora!</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p class="small">¿Ya tienes cuenta? <a href="login.php" class="text-decoration-none fw-bold text-primary">Inicia Sesión aquí</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>