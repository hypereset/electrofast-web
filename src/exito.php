<?php
session_start();
include 'php/conexion.php';
include 'includes/header.php';

if (!isset($_GET['id'])) { echo "<script>window.location='index.php';</script>"; exit; }
$id_pedido = $_GET['id'];
$res = $conn->query("SELECT * FROM pedidos WHERE id_pedido = $id_pedido");
$pedido = $res->fetch_assoc();
?>

<div class="container mx-auto px-4 py-16 flex justify-center">
    <div class="card w-full max-w-2xl bg-base-100 shadow-2xl border border-base-200">
        <div class="card-body items-center text-center">
            <div class="w-20 h-20 rounded-full bg-success/10 flex items-center justify-center text-success mb-4 animate-bounce">
                <i class="fas fa-check-circle text-5xl"></i>
            </div>
            <h1 class="text-4xl font-display font-bold text-success">¡Pedido Confirmado!</h1>
            <p class="opacity-70 text-lg">Gracias por tu compra en ProtoHub.</p>
            
            <div class="stats shadow w-full my-6 bg-base-200">
                <div class="stat place-items-center">
                    <div class="stat-title">Número de Orden</div>
                    <div class="stat-value text-primary">#<?php echo str_pad($id_pedido, 6, "0", STR_PAD_LEFT); ?></div>
                    <div class="stat-desc">Guarda este número</div>
                </div>
            </div>

            <?php if($pedido['tipo_entrega'] == 'tienda'): ?>
                <div class="alert alert-info shadow-sm text-left mb-6">
                    <i class="fas fa-store-alt text-xl"></i>
                    <div>
                        <h3 class="font-bold">Recolección en Sucursal</h3>
                        <div class="text-xs opacity-80">Blvd de las Rosas 45, Coacalco. <br>L-V 8am-7pm</div>
                    </div>
                    <a href="https://maps.app.goo.gl/GTvVbP6Evp63iyMM8" target="_blank" class="btn btn-sm btn-ghost border-current">Ver Mapa</a>
                </div>
            <?php endif; ?>

            <div class="card-actions gap-4 mt-2">
                <a href="index.php" class="btn btn-outline">Volver al Inicio</a>
                <a href="ticket.php?id=<?php echo $id_pedido; ?>" target="_blank" class="btn btn-error text-white shadow-lg gap-2">
                    <i class="fas fa-file-pdf"></i> Descargar Ticket
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>