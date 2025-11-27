<?php
include '../php/conexion.php';
include 'header.php';

if($_SESSION['rol'] != 1){ echo "<script>window.location='index.php';</script>"; exit; }
if(isset($_GET['borrar']) && $_GET['borrar'] != $_SESSION['id_usuario']){
    $conn->query("DELETE FROM usuarios WHERE id_usuario = ".$_GET['borrar']);
    echo "<script>window.location='usuarios.php';</script>";
}
?>

<div class="w-full max-w-7xl mx-auto p-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-display font-bold">Personal y Clientes</h1>
        <a href="agregar_usuario.php" class="btn btn-primary btn-sm gap-2"><i class="fas fa-user-plus"></i> Nuevo</a>
    </div>

    <div class="overflow-x-auto bg-base-100 rounded-box shadow border border-base-200">
        <table class="table table-zebra w-full">
            <thead class="bg-base-200 uppercase text-xs"><tr><th>Usuario</th><th>Contacto</th><th>Rol</th><th>Registro</th><th class="text-right">Acciones</th></tr></thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT u.*, r.nombre_rol FROM usuarios u JOIN roles r ON u.id_role = r.id_role ORDER BY u.id_role ASC");
                while($row = $res->fetch_assoc()){
                    $rol_cls = match($row['id_role']) { 1 => 'badge-error', 2 => 'badge-warning', default => 'badge-ghost' };
                ?>
                <tr class="hover">
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder"><div class="bg-neutral text-neutral-content rounded-full w-8"><span class="text-xs"><?php echo strtoupper(substr($row['nombre_completo'],0,2)); ?></span></div></div>
                            <div><div class="font-bold"><?php echo $row['nombre_completo']; ?></div><div class="text-xs opacity-50">ID: <?php echo $row['id_usuario']; ?></div></div>
                        </div>
                    </td>
                    <td><div class="text-sm"><?php echo $row['email']; ?></div><div class="text-xs opacity-50"><?php echo $row['telefono']; ?></div></td>
                    <td><div class="badge <?php echo $rol_cls; ?> font-bold badge-sm"><?php echo strtoupper($row['nombre_rol']); ?></div></td>
                    <td class="text-xs font-mono"><?php echo date('d/m/Y', strtotime($row['fecha_registro'])); ?></td>
                    <td class="text-right">
                        <div class="join">
                            <a href="editar_usuario.php?id=<?php echo $row['id_usuario']; ?>" class="btn btn-xs join-item btn-ghost"><i class="fas fa-edit"></i></a>
                            <?php if($row['id_usuario'] != $_SESSION['id_usuario']): ?>
                                <a href="usuarios.php?borrar=<?php echo $row['id_usuario']; ?>" onclick="return confirm('¿Borrar?')" class="btn btn-xs join-item btn-ghost text-error"><i class="fas fa-trash"></i></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'footer.php'; ?>