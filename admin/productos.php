<?php
include '../php/conexion.php';
include 'header.php';

// Lógica de borrar
if(isset($_GET['borrar'])){
    if(!$es_admin){ echo "<script>window.location='productos.php';</script>"; exit; }
    $id_borrar = $_GET['borrar'];
    $conn->query("UPDATE productos SET estado = 'inactivo' WHERE id_producto = $id_borrar");
    echo "<script>window.location='productos.php';</script>";
}

// Filtros
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'reciente';
$sql_order = "ORDER BY id_producto DESC";
switch ($orden) {
    case 'sin_imagen': $sql_order = "ORDER BY (imagen_url = 'default.jpg') DESC, id_producto DESC"; break;
    case 'categoria': $sql_order = "ORDER BY id_categoria ASC, nombre ASC"; break;
    case 'precio_mayor': $sql_order = "ORDER BY precio_unitario DESC"; break;
    case 'az': $sql_order = "ORDER BY nombre ASC"; break;
}
?>

<div class="p-6 w-full">
    
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold">Inventario</h1>
            <p class="opacity-60 text-sm">Gestión de catálogo.</p>
        </div>
        
        <div class="flex gap-2">
             <form method="GET">
                <select name="orden" class="select select-bordered select-sm" onchange="this.form.submit()">
                    <option disabled selected>Ordenar...</option>
                    <option value="reciente">Recientes</option>
                    <option value="sin_imagen">Sin Foto</option>
                    <option value="precio_mayor">Precio Mayor</option>
                    <option value="az">Nombre A-Z</option>
                </select>
            </form>
            <a href="agregar_producto.php" class="btn btn-primary btn-sm gap-2"><i class="fas fa-plus"></i> Nuevo</a>
        </div>
    </div>

    <div class="overflow-x-auto border rounded-lg shadow-sm">
        <table class="table w-full">
            <thead class="bg-base-200 uppercase text-xs font-bold">
                <tr>
                    <th style="width: 80px;">Img</th>
                    <th>Producto / SKU</th>
                    <th>Precio</th>
                    <th class="text-center">Stock</th>
                    <th class="text-center">Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM productos WHERE estado = 'activo' $sql_order";
                $res = $conn->query($sql);

                if($res->num_rows > 0){
                    while($row = $res->fetch_assoc()){
                        
                        // CAMBIO 1: Color Negro para Stock Normal
                        // Si < 10: Rojo con letras blancas.
                        // Si >= 10: Gris claro con LETRAS NEGRAS.
                        $stock_cls = ($row['stock_actual'] < 10) 
                            ? 'badge-error text-white font-bold' 
                            : 'bg-gray-200 text-black font-bold border-0';

                        $ruta = "../img/" . $row['imagen_url'];
                        $img = ($row['imagen_url'] != 'default.jpg' && file_exists($ruta)) ? $ruta : "../img/default.jpg";
                ?>
                <tr class="hover">
                    <td class="p-2">
                        <div style="width: 50px; height: 50px; border: 1px solid #ccc; border-radius: 8px; overflow: hidden; background: #fff; display: flex; align-items: center; justify-content: center;">
                            <img src="<?php echo $img; ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;" alt="img">
                        </div>
                    </td>
                    
                    <td>
                        <div class="font-bold text-sm text-black"><?php echo $row['nombre']; ?></div>
                        <div class="text-xs opacity-50"><?php echo $row['sku_barras']; ?></div>
                    </td>

                    <td class="font-bold text-sm text-black">
                        $<?php echo number_format($row['precio_unitario'], 2); ?>
                    </td>

                    <td class="font-bold text-center">
                        <span class="badge <?php echo $stock_cls; ?> badge-sm">
                            <?php echo $row['stock_actual']; ?>
                        </span>
                    </td>

                    <td class="text-center">
                        <div class="badge badge-success badge-xs text-black font-bold border-0">Activo</div>
                    </td>

                    <td class="text-right">
                        <div class="join">
                            <a href="editar_producto.php?id=<?php echo $row['id_producto']; ?>" class="btn btn-xs join-item btn-ghost text-info">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if($es_admin): ?>
                            <a href="productos.php?borrar=<?php echo $row['id_producto']; ?>" onclick="return confirm('¿Borrar?')" class="btn btn-xs join-item btn-ghost text-error">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center py-10 opacity-50'>Sin productos.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>