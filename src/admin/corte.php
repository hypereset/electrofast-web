<?php
include '../php/conexion.php';
include 'header.php';
$fecha = $_GET['fecha'] ?? date('Y-m-d');

$sql = "SELECT metodo_pago, COUNT(*) as cant, SUM(total_final) as total FROM pedidos WHERE DATE(fecha_pedido) = '$fecha' AND estatus_pedido != 'cancelado' GROUP BY metodo_pago";
$res = $conn->query($sql);

$efectivo = 0; $digital = 0; $total_dia = 0;
$filas = [];
while($r = $res->fetch_assoc()){
    $total_dia += $r['total'];
    if(strpos($r['metodo_pago'], 'efectivo') !== false){ $efectivo += $r['total']; $tipo = "💵 Efectivo"; $color="text-success"; }
    else { $digital += $r['total']; $tipo = "💳 Digital"; $color="text-info"; }
    $filas[] = ['metodo' => ucfirst(str_replace('_',' ',$r['metodo_pago'])), 'tipo'=>$tipo, 'color'=>$color, 'cant'=>$r['cant'], 'total'=>$r['total']];
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8 no-print">
        <h1 class="text-3xl font-display font-bold">💰 Corte de Caja</h1>
        <form class="join"><input type="date" name="fecha" value="<?php echo $fecha; ?>" class="input input-bordered input-sm join-item" onchange="this.form.submit()"><button class="btn btn-sm btn-neutral join-item">Ver</button></form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="stat bg-base-100 shadow border border-success/20">
            <div class="stat-title font-bold text-success">Efectivo en Caja</div>
            <div class="stat-value text-success">$<?php echo number_format($efectivo, 2); ?></div>
            <div class="stat-desc">Dinero físico</div>
        </div>
        <div class="stat bg-base-100 shadow border border-info/20">
            <div class="stat-title font-bold text-info">Banco / Digital</div>
            <div class="stat-value text-info">$<?php echo number_format($digital, 2); ?></div>
            <div class="stat-desc">Transferencias</div>
        </div>
        <div class="stat bg-base-100 shadow border border-base-300 bg-base-200">
            <div class="stat-title font-bold text-base-content">Venta Total</div>
            <div class="stat-value">$<?php echo number_format($total_dia, 2); ?></div>
        </div>
    </div>

    <div class="overflow-x-auto bg-base-100 rounded-box shadow-lg border border-base-200 mb-8">
        <table class="table table-zebra w-full">
            <thead class="bg-base-200"><tr><th>Método</th><th>Tipo</th><th class="text-center">Ventas</th><th class="text-right">Total</th></tr></thead>
            <tbody>
                <?php foreach($filas as $f): ?>
                <tr>
                    <td class="font-bold"><?php echo $f['metodo']; ?></td>
                    <td class="<?php echo $f['color']; ?> font-bold"><?php echo $f['tipo']; ?></td>
                    <td class="text-center"><div class="badge badge-ghost"><?php echo $f['cant']; ?></div></td>
                    <td class="text-right font-mono font-bold">$<?php echo number_format($f['total'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="text-center no-print">
        <a href="corte_pdf.php?fecha=<?php echo $fecha; ?>" target="_blank" class="btn btn-error shadow-lg gap-2"><i class="fas fa-file-pdf"></i> Generar PDF de Corte</a>
    </div>
</div>
</body>
</html>