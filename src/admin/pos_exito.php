<?php
include '../php/conexion.php';
include 'header.php';

if (!isset($_GET['id'])) { echo "<script>window.location='pos.php';</script>"; exit; }

$id_pedido = $_GET['id'];
$recibido = isset($_GET['recibido']) ? floatval($_GET['recibido']) : 0;

// Consultar info del pedido
$sql = "SELECT * FROM pedidos WHERE id_pedido = $id_pedido";
$pedido = $conn->query($sql)->fetch_assoc();
$total = $pedido['total_final'];
$cambio = $recibido - $total;
if($cambio < 0) $cambio = 0;
?>

<div class="container mt-5 text-center">
    <div class="card shadow-lg border-0 mx-auto" style="max-width: 500px;">
        <div class="card-header bg-success text-white py-3">
            <h3 class="mb-0 fw-bold"><i class="fas fa-check-circle"></i> ¡Venta Exitosa!</h3>
        </div>
        <div class="card-body p-5">
            
            <h5 class="text-muted mb-4">Orden #<?php echo str_pad($id_pedido, 6, "0", STR_PAD_LEFT); ?></h5>

            <div class="row mb-4">
                <div class="col-6 text-end border-end">
                    <div class="small text-uppercase text-muted fw-bold">Total a Pagar</div>
                    <div class="fs-3 fw-bold text-dark">$<?php echo number_format($total, 2); ?></div>
                </div>
                <div class="col-6 text-start">
                    <div class="small text-uppercase text-muted fw-bold">Recibido</div>
                    <div class="fs-3 text-dark">$<?php echo number_format($recibido, 2); ?></div>
                </div>
            </div>

            <div class="alert alert-warning py-4 mb-4">
                <div class="text-uppercase text-muted fw-bold small mb-1">Entregar Cambio</div>
                <div class="display-3 fw-bold text-dark">$<?php echo number_format($cambio, 2); ?></div>
            </div>

            <div class="d-grid gap-3">
                <a href="../ticket.php?id=<?php echo $id_pedido; ?>" target="_blank" class="btn btn-outline-dark btn-lg fw-bold">
                    <i class="fas fa-print me-2"></i> Imprimir Ticket
                </a>
                
                <a href="pos.php" class="btn btn-primary btn-lg fw-bold">
                    <i class="fas fa-cash-register me-2"></i> Nueva Venta
                </a>
            </div>

        </div>
    </div>
</div>

</div>
</body>
</html>