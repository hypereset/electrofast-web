<?php
session_start();
include 'php/conexion.php';
if (!isset($_SESSION['id_usuario'])) { header("Location: login.php"); exit; }
include 'includes/header.php';
$id_usuario = $_SESSION['id_usuario'];
?>

<div class="container mx-auto px-4 py-8 max-w-6xl">
    <h1 class="text-3xl font-display font-bold mb-8 text-primary"><i class="fas fa-box-open mr-2"></i> Mis Pedidos</h1>

    <div class="overflow-x-auto bg-base-100 rounded-box shadow-lg border border-base-200">
        <table class="table table-zebra w-full">
            <thead class="bg-base-200 font-bold text-xs uppercase">
                <tr><th>Orden</th><th>Fecha</th><th>Total</th><th>Estatus</th><th class="text-right">Acciones</th></tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM pedidos WHERE id_usuario = $id_usuario ORDER BY fecha_pedido DESC";
                $res = $conn->query($sql);
                if($res->num_rows > 0){
                    while($row = $res->fetch_assoc()){
                        $badge = match($row['estatus_pedido']) { 'pendiente'=>'badge-warning', 'en_camino'=>'badge-info text-white', 'entregado'=>'badge-success text-white', 'cancelado'=>'badge-error text-white', default=>'badge-ghost' };
                ?>
                <tr class="hover">
                    <td class="font-mono font-bold text-primary">#<?php echo str_pad($row['id_pedido'], 6, "0", STR_PAD_LEFT); ?></td>
                    <td class="text-sm"><?php echo date('d/M/Y', strtotime($row['fecha_pedido'])); ?></td>
                    <td class="font-bold text-success">$<?php echo number_format($row['total_final'], 2); ?></td>
                    <td><div class="badge <?php echo $badge; ?> gap-1 font-bold text-xs uppercase"><?php echo str_replace('_', ' ', $row['estatus_pedido']); ?></div></td>
                    <td class="text-right">
                        <div class="join">
                            <a href="ticket.php?id=<?php echo $row['id_pedido']; ?>" target="_blank" class="btn btn-xs join-item btn-outline" title="Ticket"><i class="fas fa-file-pdf"></i></a>
                            
                            <?php if($row['metodo_pago'] == 'linea' && !in_array($row['estatus_pedido'], ['cancelado','entregado'])): ?>
                                <?php if(empty($row['comprobante_pago'])): ?>
                                    <a href="subir_pago.php?id=<?php echo $row['id_pedido']; ?>" class="btn btn-xs join-item btn-warning font-bold">Pagar</a>
                                <?php else: ?>
                                    <span class="btn btn-xs join-item btn-disabled btn-ghost text-info">En Revisión</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php } } else { echo "<tr><td colspan='5' class='text-center py-10 opacity-50'>No tienes pedidos.</td></tr>"; } ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>