<?php
include '../php/conexion.php';
include 'header.php';

// Detectar si venimos del POS para registrar un cliente rápido
$es_pos_cliente = (isset($_GET['tipo']) && $_GET['tipo'] == 'cliente');
$origen = isset($_GET['origen']) ? $_GET['origen'] : 'usuarios';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email']; // Correo completo
    $pass_raw = $_POST['password'];
    $password = password_hash($pass_raw, PASSWORD_BCRYPT);
    
    // Si es registro POS, el rol es 3 (Cliente), si no, lo que elija el admin
    $rol = isset($_POST['rol']) ? $_POST['rol'] : 3; 
    
    // Datos de recuperación
    $pregunta = $_POST['pregunta_seguridad'];
    $respuesta = strtolower($_POST['respuesta_seguridad']);

    $sql = "INSERT INTO usuarios (id_role, nombre_completo, email, password_hash, pregunta_seguridad, respuesta_seguridad) 
            VALUES ($rol, '$nombre', '$email', '$password', '$pregunta', '$respuesta')";

    if ($conn->query($sql) === TRUE) {
        $msg = "Usuario registrado correctamente.";
        // Redirección inteligente
        $link_volver = ($origen == 'pos') ? 'pos.php' : 'usuarios.php';
        
        echo "<script>
            Swal.fire('Registrado', '$msg', 'success').then(() => {
                window.location = '$link_volver';
            });
        </script>";
    } else {
        echo "<div class='alert alert-danger m-3'>Error: " . $conn->error . "</div>";
    }
}
?>

<div class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">
            <?php echo $es_pos_cliente ? '<i class="fas fa-user-plus text-success"></i> Registrar Cliente (POS)' : '👔 Alta de Personal'; ?>
        </h2>
        <a href="<?php echo ($origen=='pos') ? 'pos.php' : 'usuarios.php'; ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header <?php echo $es_pos_cliente ? 'bg-success' : 'bg-dark'; ?> text-white fw-bold">
                    Datos del Usuario
                </div>
                <div class="card-body p-4">
                    <form action="" method="POST">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nombre Completo</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control" placeholder="cliente@correo.com" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Contraseña</label>
                                <input type="text" name="password" class="form-control" value="123456" required>
                                <div class="form-text">Por defecto: 123456 (Pueden cambiarla después)</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Rol</label>
                                <?php if($es_pos_cliente): ?>
                                    <input type="text" class="form-control bg-light" value="Cliente (Comprador)" readonly>
                                    <input type="hidden" name="rol" value="3">
                                <?php else: ?>
                                    <select name="rol" class="form-select bg-light" required>
                                        <option value="2">👷 Trabajador</option>
                                        <option value="1">🛑 Administrador</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr>
                        <h6 class="text-muted mb-3">Seguridad (Recuperación de Cuenta)</h6>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Pregunta Secreta</label>
                                <select class="form-select" name="pregunta_seguridad" required>
                                    <option value="¿Cómo se llamaba tu primera mascota?">¿Cómo se llamaba tu primera mascota?</option>
                                    <option value="¿En qué ciudad se conocieron tus padres?">¿En qué ciudad se conocieron tus padres?</option>
                                    <option value="¿Cuál fue tu primer videojuego favorito?">¿Cuál fue tu primer videojuego favorito?</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Respuesta</label>
                                <input type="text" name="respuesta_seguridad" class="form-control" placeholder="Ej: Firulais" required>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn <?php echo $es_pos_cliente ? 'btn-success' : 'btn-primary'; ?> btn-lg fw-bold">
                                Guardar Registro
                            </button>
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