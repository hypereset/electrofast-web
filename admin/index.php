<?php
include '../php/conexion.php';
include 'header.php';

// ==============================================================
// 1. CONSULTAS DE DATOS (KPIs y Gráficas)
// ==============================================================

// Pendientes
$sql_pend = "SELECT COUNT(*) as total FROM pedidos WHERE estatus_pedido = 'pendiente'";
$row_pend = $conn->query($sql_pend)->fetch_assoc();

// Ingresos Reales (Solo entregados)
$sql_gan = "SELECT SUM(total_final) as total FROM pedidos WHERE estatus_pedido = 'entregado'";
$row_gan = $conn->query($sql_gan)->fetch_assoc();
$ganancias = $row_gan['total'] ?? 0;

// Stock Bajo
$sql_stock = "SELECT COUNT(*) as total FROM productos WHERE stock_actual < 10 AND estado = 'activo'";
$row_stock = $conn->query($sql_stock)->fetch_assoc();

// Datos para Gráfica 1: Top Productos
$labels_prod = []; $data_prod = [];
$sql_top = "SELECT p.nombre, SUM(d.cantidad) as total FROM detalle_pedido d JOIN productos p ON d.id_producto = p.id_producto JOIN pedidos pe ON d.id_pedido = pe.id_pedido WHERE pe.estatus_pedido != 'cancelado' GROUP BY p.id_producto ORDER BY total DESC LIMIT 5";
$res_top = $conn->query($sql_top);
if($res_top) while($r=$res_top->fetch_assoc()){ $labels_prod[]=mb_substr($r['nombre'],0,15).'...'; $data_prod[]=(int)$r['total']; }

// Datos para Gráfica 2: Estatus
$labels_status = []; $data_status = [];
$res_status = $conn->query("SELECT estatus_pedido, COUNT(*) as c FROM pedidos GROUP BY estatus_pedido");
if($res_status) while($r=$res_status->fetch_assoc()){ $labels_status[]=ucfirst(str_replace('_',' ',$r['estatus_pedido'])); $data_status[]=(int)$r['c']; }
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-0">Tablero de Control</h2>
        <span class="badge bg-light text-dark border"><?php echo date('d/m/Y'); ?></span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-warning fw-bold mb-1">Pendientes</h6>
                    <h2 class="mb-0 fw-black text-dark display-6"><?php echo $row_pend['total']; ?></h2>
                    <a href="pedidos.php?filtro=pendiente" class="small text-warning text-decoration-none fw-bold mt-2 d-inline-block">Ver pedidos <i class="fas fa-arrow-right small"></i></a>
                </div>
                <div class="bg-warning bg-opacity-25 rounded-circle p-3 text-warning d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-clock fa-2x"></i>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-success fw-bold mb-1">Ingresos Reales</h6>
                    <h2 class="mb-0 fw-black text-dark display-6">$<?php echo number_format($ganancias); ?></h2>
                    <small class="text-success fw-bold mt-2 d-inline-block">Total entregado</small>
                </div>
                <div class="bg-success bg-opacity-25 rounded-circle p-3 text-success d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-dollar-sign fa-2x"></i>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-danger fw-bold mb-1">Stock Crítico</h6>
                    <h2 class="mb-0 fw-black text-dark display-6"><?php echo $row_stock['total']; ?></h2>
                    <a href="productos.php?filtro=bajo_stock" class="small text-danger text-decoration-none fw-bold mt-2 d-inline-block">Resurtir ahora <i class="fas fa-arrow-right small"></i></a>
                </div>
                <div class="bg-danger bg-opacity-25 rounded-circle p-3 text-danger d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent fw-bold border-0 py-3"><i class="fas fa-chart-bar me-2 text-primary"></i> Más Vendidos</div>
                <div class="card-body">
                    <div style="height: 300px;"><canvas id="chartVentas"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent fw-bold border-0 py-3"><i class="fas fa-chart-pie me-2 text-info"></i> Estatus Pedidos</div>
                <div class="card-body">
                    <div style="height: 300px;"><canvas id="chartEstatus"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent fw-bold border-0 py-3"><i class="fas fa-list me-2"></i> Actividad Reciente</div>
        <div class="table-responsive mb-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light"><tr><th>Orden</th><th>Cliente</th><th>Total</th><th>Estatus</th><th></th></tr></thead>
                <tbody>
                    <?php
                    $res_ult = $conn->query("SELECT p.*, u.nombre_completo FROM pedidos p JOIN usuarios u ON p.id_usuario = u.id_usuario ORDER BY p.fecha_pedido DESC LIMIT 5");
                    if($res_ult && $res_ult->num_rows > 0){
                        while($p = $res_ult->fetch_assoc()){
                            $badge='bg-secondary';
                            if($p['estatus_pedido']=='pendiente') $badge='bg-warning text-dark';
                            if($p['estatus_pedido']=='entregado') $badge='bg-success';
                            if($p['estatus_pedido']=='cancelado') $badge='bg-danger';
                    ?>
                    <tr>
                        <td class="fw-bold text-primary">#<?php echo str_pad($p['id_pedido'],6,"0",STR_PAD_LEFT); ?></td>
                        <td><?php echo $p['receptor_nombre']; ?></td>
                        <td class="fw-bold">$<?php echo number_format($p['total_final'],2); ?></td>
                        <td><span class="badge <?php echo $badge; ?>"><?php echo ucfirst($p['estatus_pedido']); ?></span></td>
                        <td class="text-end"><a href="ver_pedido.php?id=<?php echo $p['id_pedido']; ?>" class="btn btn-sm btn-light border">Ver</a></td>
                    </tr>
                    <?php }} else { echo "<tr><td colspan='5' class='text-center py-4 text-muted'>Sin actividad.</td></tr>"; } ?>
                </tbody>
            </table>
        </div>
    </div>

</div> <script>
    const dProd=<?php echo json_encode($labels_prod)?:'[]';?>, vProd=<?php echo json_encode($data_prod)?:'[]';?>;
    const dStat=<?php echo json_encode($labels_status)?:'[]';?>, vStat=<?php echo json_encode($data_status)?:'[]';?>;

    if(document.getElementById('chartVentas')){
        new Chart(document.getElementById('chartVentas'),{type:'bar',data:{labels:dProd,datasets:[{label:'Uds',data:vProd,backgroundColor:'#0d6efd',borderRadius:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
    }
    if(document.getElementById('chartEstatus')){
        new Chart(document.getElementById('chartEstatus'),{type:'doughnut',data:{labels:dStat,datasets:[{data:vStat,backgroundColor:['#ffc107','#198754','#0dcaf0','#dc3545','#6c757d'],borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'right'}}}});
    }
</script>

<?php 
// Importante: No cerrar </body> ni </html> aquí, el header lo hace.
?>