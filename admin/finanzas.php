<?php
include '../php/conexion.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_gasto'])) {
    $cat = $_POST['categoria']; $monto = $_POST['monto']; $desc = $conn->real_escape_string($_POST['descripcion']); $fecha = $_POST['fecha'];
    $conn->query("INSERT INTO gastos_operativos (categoria, descripcion, monto, fecha) VALUES ('$cat', '$desc', $monto, '$fecha')");
    echo "<script>Swal.fire({toast:true, position:'top-end', icon:'success', title:'Gasto registrado', showConfirmButton:false, timer:3000});</script>";
}


$ingresos = $conn->query("SELECT SUM(total_final) as total FROM pedidos WHERE estatus_pedido = 'entregado'")->fetch_assoc()['total'] ?? 0;
$egresos = $conn->query("SELECT SUM(monto) as total FROM gastos_operativos")->fetch_assoc()['total'] ?? 0;
$balance = $ingresos - $egresos;
?>

<div class="w-full max-w-7xl mx-auto px-4 py-6">
    
    <div class="flex items-center gap-4 mb-8 border-b pb-4 border-base-300">
        <div class="p-3 bg-green-100 text-green-700 rounded-xl">
            <i class="fas fa-wallet text-2xl"></i>
        </div>
        <div>
            <h1 class="text-3xl font-black text-base-content">Finanzas</h1>
            <p class="text-sm opacity-60">Reporte de flujo de caja</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        
        <div class="card bg-base-100 shadow-md border-l-8 border-success">
            <div class="card-body flex flex-row items-center justify-between p-6">
                <div>
                    <div class="text-xs font-bold uppercase opacity-50">Ingresos Totales</div>
                    <div class="text-3xl font-black text-success">$<?php echo number_format($ingresos); ?></div>
                    <div class="text-xs font-medium text-success mt-1"><i class="fas fa-check"></i> Cobrados</div>
                </div>
                <div class="text-success opacity-20"><i class="fas fa-arrow-up text-5xl"></i></div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-md border-l-8 border-error">
            <div class="card-body flex flex-row items-center justify-between p-6">
                <div>
                    <div class="text-xs font-bold uppercase opacity-50">Gastos Operativos</div>
                    <div class="text-3xl font-black text-error">$<?php echo number_format($egresos); ?></div>
                    <div class="text-xs font-medium text-error mt-1"><i class="fas fa-exclamation"></i> Salidas</div>
                </div>
                <div class="text-error opacity-20"><i class="fas fa-arrow-down text-5xl"></i></div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-md border-l-8 border-primary">
            <div class="card-body flex flex-row items-center justify-between p-6">
                <div>
                    <div class="text-xs font-bold uppercase opacity-50">Utilidad Neta</div>
                    <div class="text-3xl font-black <?php echo ($balance>=0)?'text-primary':'text-warning'; ?>">
                        $<?php echo number_format($balance); ?>
                    </div>
                    <div class="text-xs font-bold mt-1">
                        <?php echo ($balance>=0)?'Rentable 📈':'Pérdida 📉'; ?>
                    </div>
                </div>
                <div class="text-primary opacity-20"><i class="fas fa-scale-balanced text-5xl"></i></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
        
        <div class="xl:col-span-4 h-fit">
            <div class="card bg-base-100 shadow-xl border border-base-200">
                <div class="card-body">
                    <h2 class="card-title text-lg text-error border-b pb-2 mb-2">
                        <i class="fas fa-minus-circle"></i> Registrar Gasto
                    </h2>
                    <form method="POST" class="flex flex-col gap-3">
                        <input type="hidden" name="registrar_gasto" value="true">
                        
                        <div class="form-control">
                            <label class="label font-bold text-xs uppercase">Concepto</label>
                            <input type="text" name="descripcion" placeholder="Ej: Luz" class="input input-bordered w-full" required>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2">
                            <div class="form-control">
                                <label class="label font-bold text-xs uppercase">Monto</label>
                                <input type="number" step="0.01" name="monto" placeholder="$0.00" class="input input-bordered text-error font-bold w-full" required>
                            </div>
                            <div class="form-control">
                                <label class="label font-bold text-xs uppercase">Fecha</label>
                                <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" class="input input-bordered w-full" required>
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="label font-bold text-xs uppercase">Categoría</label>
                            <select name="categoria" class="select select-bordered w-full">
                                <option value="nomina">👷 Nómina</option>
                                <option value="renta">🏠 Renta</option>
                                <option value="servicios">💡 Servicios</option>
                                <option value="transporte">⛽ Transporte</option>
                                <option value="insumos">📦 Insumos</option>
                                <option value="otro">❓ Otro</option>
                            </select>
                        </div>

                        <button class="btn btn-error w-full mt-4 text-white font-bold shadow-md">Guardar</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="xl:col-span-8">
            <div class="card bg-base-100 shadow-xl border border-base-200">
                <div class="card-body p-0">
                    <div class="p-4 border-b font-bold text-lg bg-base-100 rounded-t-xl">Últimos Movimientos</div>
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead class="bg-base-200 uppercase text-xs font-bold">
                                <tr><th>Fecha</th><th>Categoría</th><th>Descripción</th><th class="text-right">Monto</th></tr>
                            </thead>
                            <tbody>
                                <?php
                                $res = $conn->query("SELECT * FROM gastos_operativos ORDER BY fecha DESC LIMIT 8");
                                if($res->num_rows > 0){
                                    while($row = $res->fetch_assoc()){
                                        $bdg = ($row['categoria']=='nomina') ? 'badge-primary' : 'badge-ghost';
                                ?>
                                <tr>
                                    <td class="font-mono text-xs opacity-60"><?php echo date('d/m/y', strtotime($row['fecha'])); ?></td>
                                    <td><span class="badge <?php echo $bdg; ?> badge-sm font-bold"><?php echo ucfirst($row['categoria']); ?></span></td>
                                    <td class="font-medium"><?php echo $row['descripcion']; ?></td>
                                    <td class="text-right font-mono text-error font-bold">-$<?php echo number_format($row['monto'], 2); ?></td>
                                </tr>
                                <?php } } else { echo "<tr><td colspan='4' class='text-center py-10 opacity-50'>Sin datos.</td></tr>"; } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?php include 'footer.php'; ?>