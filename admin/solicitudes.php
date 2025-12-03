<?php
session_start();
include '../php/conexion.php';
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] != 1 && $_SESSION['rol'] != 2)) { header("Location: ../login.php"); exit; }
include 'header.php';

// Actualizar estatus
if(isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $st = $conn->real_escape_string($_GET['status']);
    $conn->query("UPDATE solicitudes_productos SET estatus = '$st' WHERE id_solicitud = $id");
    echo "<script>window.location='solicitudes.php';</script>";
}
?>

<div class="p-6">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">📢 Solicitudes de Productos</h1>
    
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="table w-full">
            <thead class="bg-gray-100">
                <tr><th>Fecha</th><th>Usuario</th><th>Producto</th><th>Link</th><th>Estatus</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT s.*, u.nombre_completo FROM solicitudes_productos s JOIN usuarios u ON s.id_usuario = u.id_usuario ORDER BY s.fecha_solicitud DESC";
                $res = $conn->query($sql);
                while($row = $res->fetch_assoc()){
                    $color = match($row['estatus']){ 'pendiente'=>'badge-warning', 'revisado'=>'badge-info', 'conseguido'=>'badge-success', 'rechazado'=>'badge-error', default=>'badge-ghost' };
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="text-xs"><?php echo date('d/m H:i', strtotime($row['fecha_solicitud'])); ?></td>
                    <td class="font-bold text-xs"><?php echo $row['nombre_completo']; ?></td>
                    <td>
                        <div class="font-bold text-sm"><?php echo $row['producto_solicitado']; ?></div>
                        <div class="text-xs opacity-70"><?php echo substr($row['descripcion'], 0, 50); ?>...</div>
                    </td>
                    <td>
                        <?php if($row['link_referencia']): ?>
                            <a href="<?php echo $row['link_referencia']; ?>" target="_blank" class="text-blue-500 hover:underline text-xs"><i class="fas fa-external-link-alt"></i> Ver</a>
                        <?php endif; ?>
                    </td>
                    <td><div class="badge <?php echo $color; ?> text-white text-xs"><?php echo ucfirst($row['estatus']); ?></div></td>
                    <td>
                        <div class="join">
                            <a href="solicitudes.php?id=<?php echo $row['id_solicitud']; ?>&status=revisado" class="btn btn-xs join-item btn-info" title="Marcar Revisado">👁️</a>
                            <a href="solicitudes.php?id=<?php echo $row['id_solicitud']; ?>&status=conseguido" class="btn btn-xs join-item btn-success" title="Ya lo tenemos">✅</a>
                            <a href="solicitudes.php?id=<?php echo $row['id_solicitud']; ?>&status=rechazado" class="btn btn-xs join-item btn-error" title="Rechazar">❌</a>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'footer.php'; ?>