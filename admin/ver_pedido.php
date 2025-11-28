<?php
include '../php/conexion.php';
include 'header.php';

if(!isset($_GET['id'])){
    echo "<script>window.location='pedidos.php';</script>";
}

$id_pedido = $_GET['id'];

// --- LÓGICA DE ACTUALIZACIÓN DE ESTATUS ---
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nuevo_estatus'])){
    $nuevo_estatus = $_POST['nuevo_estatus'];
    
    // Obtener estatus anterior para lógica de stock
    $sql_check = "SELECT estatus_pedido FROM pedidos WHERE id_pedido = $id_pedido";
    $estatus_anterior = $conn->query($sql_check)->fetch_assoc()['estatus_pedido'];

    // A. Si se CANCELA -> Devolver stock
    if ($nuevo_estatus == 'cancelado' && $estatus_anterior != 'cancelado') {
        $sql_items = "SELECT id_producto, cantidad FROM detalle_pedido WHERE id_pedido = $id_pedido";
        $res_items = $conn->query($sql_items);
        while($item = $res_items->fetch_assoc()){
            $conn->query("UPDATE productos SET stock_actual = stock_actual + {$item['cantidad']} WHERE id_producto = {$item['id_producto']}");
        }
    }

    // B. Si se REACTIVA -> Restar stock de nuevo
    if ($estatus_anterior == 'cancelado' && $nuevo_estatus != 'cancelado') {
        $sql_items = "SELECT id_producto, cantidad FROM detalle_pedido WHERE id_pedido = $id_pedido";
        $res_items = $conn->query($sql_items);
        while($item = $res_items->fetch_assoc()){
            $conn->query("UPDATE productos SET stock_actual = stock_actual - {$item['cantidad']} WHERE id_producto = {$item['id_producto']}");
        }
    }

    $conn->query("UPDATE pedidos SET estatus_pedido = '$nuevo_estatus' WHERE id_pedido = $id_pedido");
    
    // Recargar para ver cambios
    echo "<script>
        Swal.fire({
            title: 'Actualizado',
            text: 'Estatus actualizado correctamente.',
            icon: 'success',
            confirmButtonColor: '#0d47a1'
        }).then(() => { 
            location.reload(); 
        });
    </script>";
}

// Consultar datos del pedido
$sql = "SELECT p.*, u.email, e.nombre as nombre_escuela 
        FROM pedidos p 
        JOIN usuarios u ON p.id_usuario = u.id_usuario 
        LEFT JOIN escuelas_coacalco e ON p.id_escuela_destino = e.id_escuela
        WHERE p.id_pedido = $id_pedido";
$pedido = $conn->query($sql)->fetch_assoc();

// --- CONFIGURACIÓN DE OPCIONES VISUALES ---
$es_tienda = ($pedido['tipo_entrega'] == 'tienda');

$opciones = [
    'pendiente' => [
        'titulo' => 'Pendiente', 
        'icono' => 'fas fa-clock', 
        'clase' => 'btn-outline-warning',
        'desc' => 'El pedido acaba de llegar.'
    ],
    'en_preparacion' => [
        'titulo' => 'En Preparación', 
        'icono' => 'fas fa-box-open', 
        'clase' => 'btn-outline-info', 
        'desc' => 'Estamos armando el paquete.'
    ],
    'en_camino' => [
        'titulo' => $es_tienda ? 'Listo para Recoger' : 'En Camino', 
        'icono' => $es_tienda ? 'fas fa-store' : 'fas fa-motorcycle', 
        'clase' => 'btn-outline-primary', 
        'desc' => $es_tienda ? 'El cliente ya puede pasar.' : 'El repartidor salió.'
    ],
    'entregado' => [
        'titulo' => 'Entregado (Finalizar)', 
        'icono' => 'fas fa-check-circle', 
        'clase' => 'btn-outline-success', 
        'desc' => 'Venta completada con éxito.'
    ],
    'cancelado' => [
        'titulo' => 'Cancelar Pedido', 
        'icono' => 'fas fa-times-circle', 
        'clase' => 'btn-outline-danger', 
        'desc' => 'Se devuelve el stock automáticamente.'
    ]
];
?>

<style>
    /* Clase visual para cuando una opción está seleccionada */
    .opcion-activa {
        background-color: #f8f9fa;
        border-width: 2px !important;
        transform: scale(1.02);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
</style>

<div class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0 text-dark">📦 Orden #<?php echo str_pad($pedido['id_pedido'], 6, "0", STR_PAD_LEFT); ?></h2>
            <p class="text-muted mb-0">Recibido el: <?php echo $pedido['fecha_pedido']; ?></p>
        </div>
        <a href="pedidos.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-dark text-white fw-bold">Contenido del Paquete</div>
                <div class="card-body p-0">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_det = "SELECT d.*, p.nombre, p.sku_barras 
                                        FROM detalle_pedido d 
                                        JOIN productos p ON d.id_producto = p.id_producto 
                                        WHERE d.id_pedido = $id_pedido";
                            $res_det = $conn->query($sql_det);
                            while($item = $res_det->fetch_assoc()){
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo $item['nombre']; ?></div>
                                    <small class="text-muted badge bg-light text-secondary border"><?php echo $item['sku_barras']; ?></small>
                                </td>
                                <td class="text-center"><span class="badge bg-white text-dark border fs-6"><?php echo $item['cantidad']; ?></span></td>
                                <td class="text-end text-dark fw-bold">$<?php echo number_format($item['precio_aplicado'] * $item['cantidad'], 2); ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2" class="text-end text-muted">Costo de Envío:</td>
                                <td class="text-end text-muted">+$<?php echo $pedido['costo_envio']; ?></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-end fw-bold fs-5 text-dark">TOTAL:</td>
                                <td class="text-end fw-bold fs-5 text-success">$<?php echo number_format($pedido['total_final'], 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-secondary text-white fw-bold">Datos de Entrega</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">CLIENTE</p>
                            <h6 class="fw-bold text-dark"><?php echo $pedido['receptor_nombre']; ?></h6>
                            <p class="mb-0"><i class="fas fa-phone me-1"></i> <a href="tel:<?php echo $pedido['receptor_telefono']; ?>"><?php echo $pedido['receptor_telefono']; ?></a></p>
                        </div>
                        <div class="col-md-6 border-start">
                            <p class="mb-1 text-muted small">DESTINO (<?php echo ucfirst($pedido['tipo_entrega']); ?>)</p>
                            
                            <?php if($pedido['tipo_entrega'] == 'escuela'): ?>
                                <h6 class="text-primary fw-bold"><i class="fas fa-university me-1"></i> <?php echo $pedido['nombre_escuela']; ?></h6>
                                <small class="text-dark"><?php echo $pedido['referencias']; ?></small>
                            <?php elseif($pedido['tipo_entrega'] == 'domicilio_particular'): ?>
                                <h6 class="text-primary fw-bold"><i class="fas fa-home me-1"></i> Domicilio</h6>
                                <small class="text-dark d-block"><?php echo $pedido['direccion_texto']; ?></small>
                                <small class="text-muted">Ref: <?php echo $pedido['referencias']; ?></small>
                            <?php else: ?>
                                 <div class="alert alert-info py-1 px-2 mb-0 small d-inline-block">
                                    <i class="fas fa-walking"></i> Cliente pasa a recoger a Tienda.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow border-0 sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white fw-bold text-center">
                    <i class="fas fa-tasks me-2"></i> Actualizar Estatus
                </div>
                <div class="card-body bg-white">
                    <form action="" method="POST">
                        
                        <div class="d-flex flex-column gap-2">
                            <?php foreach($opciones as $valor_bd => $opt): ?>
                                <?php 
                                    $checked = ($pedido['estatus_pedido'] == $valor_bd) ? 'checked' : '';
                                    // Si es el actual, le ponemos la clase activa
                                    $clase_extra = ($checked) ? 'opcion-activa active' : '';
                                ?>
                                
                                <label class="btn <?php echo $opt['clase']; ?> text-start p-3 border position-relative btn-opcion <?php echo $clase_extra; ?>" 
                                       style="height: auto; transition: all 0.2s;">
                                    
                                    <input type="radio" name="nuevo_estatus" value="<?php echo $valor_bd; ?>" class="btn-check" <?php echo $checked; ?>>
                                    
                                    <div class="d-flex align-items-center w-100">
                                        <div class="fs-4 me-3" style="width: 30px; text-align: center;">
                                            <i class="<?php echo $opt['icono']; ?>"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold"><?php echo $opt['titulo']; ?></div>
                                            <div class="small text-muted fw-normal" style="font-size: 0.75rem; line-height: 1.1;"><?php echo $opt['desc']; ?></div>
                                        </div>
                                        <i class="fas fa-check-circle ms-auto fs-4 check-icon <?php echo $checked ? '' : 'd-none'; ?>"></i>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <hr>
                        <button type="submit" class="btn btn-dark w-100 fw-bold py-2 shadow">
                            <i class="fas fa-save me-2"></i> Guardar Cambios
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</div> <script>
    // Seleccionamos todos los labels que funcionan como botones
    const botones = document.querySelectorAll('.btn-opcion');

    botones.forEach(btn => {
        btn.addEventListener('click', function() {
            // 1. Quitar clase activa y ocultar checks de todos
            botones.forEach(b => {
                b.classList.remove('opcion-activa', 'active');
                b.querySelector('.check-icon').classList.add('d-none');
            });

            // 2. Activar el que se clickeó
            this.classList.add('opcion-activa', 'active');
            this.querySelector('.check-icon').classList.remove('d-none');
        });
    });
</script>

</body>
</html>