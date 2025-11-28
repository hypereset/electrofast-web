<?php
session_start();
include 'php/conexion.php';
include 'includes/header.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) { header("Location: index.php"); exit; }
$id_pedido = $_GET['id'];
$id_user = $_SESSION['id_usuario'];

$res = $conn->query("SELECT * FROM pedidos WHERE id_pedido = $id_pedido AND id_usuario = $id_user");
if ($res->num_rows == 0) { echo "<div class='alert alert-error m-4'>Pedido no encontrado</div>"; include 'includes/footer.php'; exit; }
$pedido = $res->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['comprobante'])) {
    $archivo = $_FILES['comprobante'];
    $ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    if (!file_exists('img/comprobantes')) { mkdir('img/comprobantes', 0777, true); }
    $nombre = "pago_" . $id_pedido . "_" . time() . "." . $ext;
    
    if (move_uploaded_file($archivo['tmp_name'], "img/comprobantes/" . $nombre)) {
        $conn->query("UPDATE pedidos SET comprobante_pago = '$nombre', estatus_pedido = 'en_preparacion' WHERE id_pedido = $id_pedido");
        echo "<script>Swal.fire('¡Enviado!', 'Comprobante subido.', 'success').then(() => { window.location='mis_pedidos.php'; });</script>";
    } else {
        echo "<script>Swal.fire('Error', 'No se pudo subir.', 'error');</script>";
    }
}
?>

<div class="container mx-auto px-4 py-12 flex justify-center">
    <div class="card w-full max-w-lg bg-base-100 shadow-xl border border-base-200">
        <div class="card-body items-center text-center">
            <div class="w-16 h-16 rounded-full bg-success/10 flex items-center justify-center text-success mb-2">
                <i class="fas fa-file-invoice-dollar text-3xl"></i>
            </div>
            <h2 class="card-title font-display text-2xl">Subir Comprobante</h2>
            <p class="opacity-70">Orden #<?php echo str_pad($id_pedido, 6, "0", STR_PAD_LEFT); ?></p>
            
            <div class="stat my-4 bg-base-200 rounded-box p-4 w-full">
                <div class="stat-title">Total a Pagar</div>
                <div class="stat-value text-primary text-3xl">$<?php echo number_format($pedido['total_final'], 2); ?></div>
            </div>

            <div class="alert alert-info text-left text-sm mb-4 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <div class="font-bold">Instrucciones:</div>
                    <div>Realiza tu transferencia y sube aquí la captura clara del movimiento.</div>
                </div>
            </div>

            <form action="" method="POST" enctype="multipart/form-data" class="w-full form-control gap-4">
                <input type="file" name="comprobante" class="file-input file-input-bordered file-input-primary w-full" accept="image/*" required />
                <button type="submit" class="btn btn-primary w-full font-bold">
                    <i class="fas fa-upload mr-2"></i> Enviar Comprobante
                </button>
            </form>
            
            <a href="mis_pedidos.php" class="link link-hover text-sm mt-4">Volver a mis pedidos</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>