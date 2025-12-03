<?php
session_start();
include 'php/conexion.php';
if (!isset($_SESSION['id_usuario'])) { header("Location: login.php"); exit; }
include 'includes/header.php';

$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $producto = $conn->real_escape_string($_POST['producto']);
    $desc = $conn->real_escape_string($_POST['descripcion']);
    $link = $conn->real_escape_string($_POST['link']);
    $id_user = $_SESSION['id_usuario'];

    $sql = "INSERT INTO solicitudes_productos (id_usuario, producto_solicitado, descripcion, link_referencia) VALUES ($id_user, '$producto', '$desc', '$link')";
    
    if ($conn->query($sql)) {
        echo "<script>Swal.fire('¡Recibido!', 'Tu solicitud ha sido enviada. Trataremos de conseguirlo.', 'success');</script>";
    } else {
        $mensaje = "Error al enviar: " . $conn->error;
    }
}
?>

<div class="container mx-auto px-4 py-12 max-w-2xl">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-display font-bold text-primary">¿No encuentras lo que buscas?</h1>
        <p class="opacity-70">Dinos qué componente necesitas y nosotros lo conseguimos para la próxima.</p>
    </div>

    <div class="card bg-base-100 shadow-xl border border-base-200">
        <div class="card-body">
            <?php if($mensaje): ?><div class="alert alert-error"><?php echo $mensaje; ?></div><?php endif; ?>

            <form method="POST">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text font-bold">Nombre del Componente *</span></label>
                    <input type="text" name="producto" placeholder="Ej: Sensor LiDAR, Raspberry Pi 5..." class="input input-bordered" required />
                </div>

                <div class="form-control mb-4">
                    <label class="label"><span class="label-text font-bold">Detalles / Especificaciones</span></label>
                    <textarea name="descripcion" class="textarea textarea-bordered h-24" placeholder="¿Algún modelo en específico? ¿Para qué lo necesitas?"></textarea>
                </div>

                <div class="form-control mb-6">
                    <label class="label"><span class="label-text font-bold">Link de referencia (Opcional)</span></label>
                    <input type="url" name="link" placeholder="https://..." class="input input-bordered" />
                    <label class="label"><span class="label-text-alt opacity-50">Si lo viste en Amazon o MercadoLibre, pega el link.</span></label>
                </div>

                <button type="submit" class="btn btn-primary btn-block font-bold">Enviar Solicitud <i class="fas fa-paper-plane ml-2"></i></button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>