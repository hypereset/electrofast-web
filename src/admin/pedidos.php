<?php
include '../php/conexion.php';
include 'header.php';

$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';
?>

<div class="container-fluid">
    
    <div class="flex flex-col md:flex-row justify-between items-end mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-display font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-shipping-fast text-primary"></i> Pedidos
            </h1>
            <p class="text-gray-500 text-sm mt-1">Monitor de entregas y estado de órdenes.</p>
        </div>
        
        <div class="join shadow-sm bg-white rounded-lg border border-gray-200 p-1">
            <a href="pedidos.php" class="btn btn-sm join-item border-0 <?php echo ($filtro=='todos') ? 'btn-primary' : 'btn-ghost hover:bg-gray-100'; ?>">
                Todos
            </a>
            <a href="pedidos.php?filtro=pendiente" class="btn btn-sm join-item border-0 <?php echo ($filtro=='pendiente') ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' : 'btn-ghost hover:bg-gray-100'; ?>">
                <i class="fas fa-clock text-xs"></i> Pendientes
            </a>
            <a href="pedidos.php?filtro=entregado" class="btn btn-sm join-item border-0 <?php echo ($filtro=='entregado') ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'btn-ghost hover:bg-gray-100'; ?>">
                <i class="fas fa-check text-xs"></i> Entregados
            </a>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-2xl shadow-sm border border-gray-200">
        <table class="table w-full">
            <thead class="bg-gray-50 text-gray-500 font-bold uppercase text-xs">
                <tr>
                    <th class="py-4 pl-6">Orden</th>
                    <th>Cliente / Contacto</th>
                    <th>Destino</th>
                    <th>Total</th>
                    <th>Estatus</th>
                    <th class="text-right pr-6">Acciones</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php
                $filtro_sql = "";
                if($filtro == 'pendiente') $filtro_sql = "WHERE estatus_pedido = 'pendiente'";
                if($filtro == 'entregado') $filtro_sql = "WHERE estatus_pedido = 'entregado'";

                $sql = "SELECT p.*, u.nombre_completo FROM pedidos p JOIN usuarios u ON p.id_usuario = u.id_usuario $filtro_sql ORDER BY p.fecha_pedido DESC";
                $res = $conn->query($sql);

                if($res->num_rows > 0){
                    while($row = $res->fetch_assoc()){
                        
                        // Estilos de Estado
                        $estatus = $row['estatus_pedido'];
                        $badge_cls = 'bg-gray-100 text-gray-600';
                        $icon = '';
                        
                        if($estatus == 'pendiente') { $badge_cls = 'bg-yellow-100 text-yellow-700 border border-yellow-200'; $icon='fa-clock'; }
                        if($estatus == 'en_camino') { $badge_cls = 'bg-blue-100 text-blue-700 border border-blue-200'; $icon='fa-motorcycle'; }
                        if($estatus == 'entregado') { $badge_cls = 'bg-green-100 text-green-700 border border-green-200'; $icon='fa-check-circle'; }
                        if($estatus == 'cancelado') { $badge_cls = 'bg-red-100 text-red-700 border border-red-200'; $icon='fa-times-circle'; }

                        // Icono Destino
                        $destino_icon = match($row['tipo_entrega']) {
                            'tienda' => '<i class="fas fa-store text-purple-500"></i>',
                            'escuela' => '<i class="fas fa-university text-orange-500"></i>',
                            default => '<i class="fas fa-home text-blue-500"></i>'
                        };
                ?>
                <tr class="hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-none">
                    <td class="pl-6 py-4">
                        <div class="font-mono font-bold text-primary text-base">#<?php echo str_pad($row['id_pedido'], 6, "0", STR_PAD_LEFT); ?></div>
                        <div class="text-[10px] text-gray-400 uppercase mt-1"><?php echo date('d M Y - H:i', strtotime($row['fecha_pedido'])); ?></div>
                    </td>
                    <td>
                        <div class="font-bold text-gray-800"><?php echo $row['receptor_nombre']; ?></div>
                        <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                            <i class="fas fa-phone text-[10px]"></i> <?php echo $row['receptor_telefono']; ?>
                        </div>
                    </td>
                    <td>
                        <div class="flex items-center gap-2 text-gray-600 font-medium">
                            <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs">
                                <?php echo $destino_icon; ?>
                            </div>
                            <?php echo ucfirst($row['tipo_entrega']); ?>
                        </div>
                    </td>
                    <td class="font-bold text-emerald-600 font-display text-base">
                        $<?php echo number_format($row['total_final'], 2); ?>
                    </td>
                    <td>
                        <span class="px-3 py-1 rounded-full text-xs font-bold flex items-center w-fit gap-2 <?php echo $badge_cls; ?>">
                            <i class="fas <?php echo $icon; ?>"></i>
                            <?php echo strtoupper(str_replace('_', ' ', $estatus)); ?>
                        </span>
                    </td>
                    <td class="text-right pr-6">
                        <a href="ver_pedido.php?id=<?php echo $row['id_pedido']; ?>" class="btn btn-sm btn-ghost text-primary hover:bg-blue-50">
                            Gestionar <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center py-16'>
                            <div class='flex flex-col items-center justify-center opacity-40'>
                                <i class='fas fa-box-open text-6xl mb-4 text-gray-300'></i>
                                <p class='font-bold text-gray-500'>No hay pedidos en esta lista.</p>
                            </div>
                          </td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>