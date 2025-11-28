<?php 
include 'php/conexion.php'; 
include 'includes/header.php'; 

// ... (Lógica de búsqueda se mantiene igual) ...
$busqueda = ""; $filtro_sql = "";
if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
    $busqueda = $conn->real_escape_string($_GET['busqueda']);
    $filtro_sql = "AND (nombre LIKE '%$busqueda%' OR descripcion LIKE '%$busqueda%')";
}

// --- FUNCIÓN DE RENDERIZADO ---
function renderCard($row, $esNuevo = false) {
    $img = (file_exists("img/".$row['imagen_url']) && $row['imagen_url']!='default.jpg') ? "img/".$row['imagen_url'] : "img/default.jpg";
    ?>
    
    <div class="card card-compact bg-base-100 shadow-xl hover:shadow-2xl transition-all border border-base-200 h-full flex flex-col justify-between">
        <figure class="relative px-4 pt-4 h-40 md:h-48 bg-base-200 flex items-center justify-center rounded-t-2xl shrink-0">
            <a href="producto.php?id=<?php echo $row['id_producto']; ?>" class="w-full h-full flex items-center justify-center">
                <img src="<?php echo $img; ?>" class="max-h-full object-contain hover:scale-110 transition-transform" loading="lazy" />
            </a>
            <?php if($esNuevo): ?><div class="absolute top-2 right-2 badge badge-secondary font-bold text-xs">NUEVO</div><?php endif; ?>
        </figure>

        <div class="card-body p-3 md:p-5 flex flex-col flex-grow">
            <h2 class="card-title text-sm md:text-base font-bold leading-tight">
                <a href="producto.php?id=<?php echo $row['id_producto']; ?>" class="hover:text-primary"><?php echo $row['nombre']; ?></a>
            </h2>
            
            <div class="card-actions justify-between items-end mt-auto pt-2 border-t border-base-200/50">
                <div class="flex flex-col">
                    <span class="text-xs font-bold opacity-70 uppercase">Precio</span>
                    <span class="text-lg md:text-xl font-black text-primary">$<?php echo $row['precio_unitario']; ?></span>
                </div>

                <form onsubmit="agregarAlCarritoAjax(this)">
                    <input type="hidden" name="agregar_nuevo" value="true">
                    <input type="hidden" name="id_producto" value="<?php echo $row['id_producto']; ?>">
                    <input type="hidden" name="cantidad" value="1">
                    <button type="submit" class="btn btn-circle btn-primary btn-sm shadow-md"><i class="fas fa-plus"></i></button>
                </form>
            </div>
        </div>
    </div>
    <?php
}
?>

<div class="container mx-auto px-4 py-8 max-w-7xl">
    <?php if (empty($filtro_sql)): ?>
        <div class="hero min-h-[200px] rounded-box mb-8 bg-base-200"><div class="hero-content text-center"><h1 class="text-4xl font-black">¡Salva tu semestre!</h1></div></div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php
            $res = $conn->query("SELECT * FROM productos WHERE estado='activo' LIMIT 8");
            while($row = $res->fetch_assoc()) { renderCard($row); }
            ?>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php
            $res = $conn->query("SELECT * FROM productos WHERE estado='activo' $filtro_sql LIMIT 20");
            if($res->num_rows > 0) { while($row = $res->fetch_assoc()) renderCard($row); }
            else { echo "No hay resultados."; }
            ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>