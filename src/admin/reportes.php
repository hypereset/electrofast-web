<?php
include '../php/conexion.php';
include 'header.php';

$inicio = $_GET['inicio'] ?? date('Y-m-01');
$fin = $_GET['fin'] ?? date('Y-m-d');

$sql = "SELECT p.nombre, p.sku_barras, p.stock_actual, SUM(d.cantidad) as vendidos, SUM(d.cantidad * d.precio_aplicado) as dinero FROM detalle_pedido d JOIN pedidos ped ON d.id_pedido = ped.id_pedido JOIN productos p ON d.id_producto = p.id_producto WHERE ped.estatus_pedido != 'cancelado' AND DATE(ped.fecha_pedido) BETWEEN '$inicio' AND '$fin' GROUP BY p.id_producto ORDER BY vendidos DESC";
$res = $conn->query($sql);
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row justify-between items-end mb-6 gap-4 no-print">
        <div>
            <h1 class="text-3xl font-display font-bold text-base-content">📊 Reportes</h1>
            <p class="opacity-60 text-sm">Análisis de ventas y stock.</p>
        </div>
        <form class="join bg-base-100 shadow-sm rounded-lg border border-base-200 p-1">
            <input type="date" name="inicio" class="input input-sm join-item border-0" value="<?php echo $inicio; ?>">
            <span class="join-item btn btn-sm btn-ghost btn-disabled">a</span>
            <input type="date" name="fin" class="input input-sm join-item border-0" value="<?php echo $fin; ?>">
            <button class="btn btn-sm btn-primary join-item">Filtrar</button>
        </form>
        <a href="reporte_pdf.php?inicio=<?php echo $inicio; ?>&fin=<?php echo $fin; ?>" target="_blank" class="btn btn-error btn-sm text-white shadow"><i class="fas fa-file-pdf"></i> PDF</a>
    </div>

    <div class="overflow-x-auto bg-base-100 rounded-box shadow-lg border border-base-200">
        <table class="table table-zebra w-full">
            <thead class="bg-base-200 font-bold text-xs uppercase"><tr><th>Producto</th><th class="text-center">Vendidos</th><th class="text-center">Stock</th><th class="text-right">Ingresos</th><th>Estado</th></tr></thead>
            <tbody>
                <?php
                $total = 0;
                if($res->num_rows > 0){
                    while($row = $res->fetch_assoc()){
                        $total += $row['dinero'];
                        $stock = $row['stock_actual']; $vendidos = $row['vendidos'];
                        $status = ($stock == 0) ? ['bg-neutral text-white', 'AGOTADO'] : (($stock < $vendidos) ? ['badge-error text-white', 'CRÍTICO'] : (($stock < $vendidos*2) ? ['badge-warning', 'BAJO'] : ['badge-success text-white', 'BIEN']));
                ?>
                <tr>
                    <td><div class="font-bold"><?php echo $row['nombre']; ?></div><div class="text-xs opacity-50"><?php echo $row['sku_barras']; ?></div></td>
                    <td class="text-center font-bold text-lg"><?php echo $vendidos; ?></td>
                    <td class="text-center font-mono"><?php echo $stock; ?></td>
                    <td class="text-right font-bold text-success">+$<?php echo number_format($row['dinero'], 2); ?></td>
                    <td><div class="badge <?php echo $status[0]; ?> font-bold badge-sm"><?php echo $status[1]; ?></div></td>
                </tr>
                <?php } } else { echo "<tr><td colspan='5' class='text-center py-8 opacity-50'>Sin datos en este periodo.</td></tr>"; } ?>
            </tbody>
            <tfoot class="bg-base-200 text-lg font-bold text-primary"><tr><td colspan="3" class="text-right">TOTAL:</td><td class="text-right">$<?php echo number_format($total, 2); ?></td><td></td></tr></tfoot>
        </table>
    </div>
</div>
</body>
</html>