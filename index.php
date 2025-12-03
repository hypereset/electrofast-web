<?php 
include 'php/conexion.php'; 
include 'includes/header.php'; 

// --- LÓGICA DE BÚSQUEDA ---
$busqueda = "";
$estamos_buscando = false;
$filtro_sql = "";
$titulo_seccion = "🔥 Lo más vendido";
$mensaje_busqueda = "";

if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
    $busqueda = $conn->real_escape_string($_GET['busqueda']);
    $estamos_buscando = true;
    $filtro_sql = "AND (nombre LIKE '%$busqueda%' OR descripcion LIKE '%$busqueda%' OR sku_barras LIKE '%$busqueda%')";
    $titulo_seccion = "🔍 Resultados para: \"$busqueda\"";
    $mensaje_busqueda = "<div class='mb-6'><a href='index.php' class='btn btn-sm btn-outline'>Limpiar búsqueda</a></div>";
}

// --- FUNCIÓN DE RENDERIZADO DE TARJETAS ---
function renderCard($row, $esNuevo = false) {
    // Lógica de imagen
    $ruta_local = "img/" . $row['imagen_url'];
    if ($row['imagen_url'] != 'default.jpg' && file_exists($ruta_local)) { 
        $img_final = $ruta_local; 
    } elseif (file_exists("img/default.jpg")) { 
        $img_final = "img/default.jpg"; 
    } else { 
        $img_final = "https://via.placeholder.com/300x300.png?text=ProtoHub"; 
    }
    ?>
    
    <div class="card card-compact bg-base-100 shadow-xl hover:shadow-2xl transition-all duration-300 border border-base-200 card-slider h-full flex flex-col justify-between">
        
        <figure class="relative px-4 pt-4 h-40 md:h-48 bg-base-200 flex items-center justify-center overflow-hidden rounded-t-2xl shrink-0">
            <a href="producto.php?id=<?php echo $row['id_producto']; ?>" class="w-full h-full flex items-center justify-center">
                <img src="<?php echo $img_final; ?>" alt="<?php echo $row['nombre']; ?>" class="max-h-full object-contain hover:scale-110 transition-transform duration-500" loading="lazy" />
            </a>
            
            <div class="absolute top-2 right-2 flex flex-col gap-1 items-end">
                <?php if($row['precio_mayoreo'] > 0): ?>
                    <div class="badge badge-warning gap-1 font-bold shadow-sm text-[10px] md:text-xs">Mayoreo</div>
                <?php endif; ?>
                <?php if($esNuevo): ?>
                    <div class="badge badge-secondary gap-1 font-bold shadow-sm text-[10px] md:text-xs">NUEVO</div>
                <?php endif; ?>
            </div>
        </figure>

        <div class="card-body p-3 md:p-5 flex flex-col flex-grow">
            
            <h2 class="card-title font-display font-bold leading-tight text-base-content mb-1 block h-12 overflow-hidden">
    <a href="producto.php?id=<?php echo $row['id_producto']; ?>" class="hover:text-primary transition-colors line-clamp-2 text-sm md:text-base">
        <?php echo $row['nombre']; ?>
    </a>
</h2>
            
            <div class="card-actions justify-between items-end mt-auto pt-2 border-t border-base-200/50">
                <div class="flex flex-col">
                    <span class="text-[10px] md:text-xs opacity-70 font-bold uppercase text-base-content">Precio</span>
                    <span class="text-lg md:text-xl font-black font-display text-primary">$<?php echo $row['precio_unitario']; ?></span>
                </div>

                <form onsubmit="agregarAlCarritoAjax(this)">
                    <input type="hidden" name="agregar_nuevo" value="true">
                    <input type="hidden" name="id_producto" value="<?php echo $row['id_producto']; ?>">
                    <input type="hidden" name="cantidad" value="1">
                    
                    <button type="submit" class="btn btn-circle btn-primary btn-sm shadow-lg tooltip tooltip-left" data-tip="Agregar">
                        <i class="fas fa-plus"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php
}
?>

<div class="container mx-auto px-4 py-6 md:py-8 max-w-7xl">
    
    <?php if (!$estamos_buscando): ?>

        <div class="hero min-h-[200px] md:min-h-[300px] rounded-box mb-8 md:mb-12 banner-neon bg-base-200 relative overflow-hidden">
            <div class="hero-content text-center z-10">
                <div class="max-w-md">
                    <h1 class="text-3xl md:text-5xl font-display font-black mb-2 md:mb-4 titulo-proto">¡Salva tu semestre!</h1>
                    <p class="py-2 md:py-6 text-sm md:text-lg opacity-80 text-base-content">Componentes urgentes para TESCO, UPVM y más.</p>
                    <a href="paquetes.php" class="btn btn-primary btn-sm md:btn-md shadow-lg gap-2">
                        <i class="fas fa-box-open"></i> Ver Paquetes
                    </a>
                </div>
            </div>
            <i class="fas fa-microchip absolute -bottom-10 -right-10 text-[8rem] md:text-[15rem] opacity-5 rotate-12 text-base-content"></i>
        </div>

        <div class="flex justify-between items-end mb-4 px-2 border-b border-base-300 pb-2">
            <h3 class="text-lg md:text-2xl font-display font-bold text-base-content flex items-center gap-2">
                <i class="fas fa-fire text-error"></i> Lo más vendido
            </h3>
            <div class="flex gap-2">
                <button class="btn btn-circle btn-sm btn-ghost" onclick="document.getElementById('slider1').scrollBy({left: -260, behavior:'smooth'})">❮</button>
                <button class="btn btn-circle btn-sm btn-ghost" onclick="document.getElementById('slider1').scrollBy({left: 260, behavior:'smooth'})">❯</button>
            </div>
        </div>

        <div id="slider1" class="flex gap-4 md:gap-6 overflow-x-auto pb-8 snap-x scrollbar-hide scroll-smooth px-1">
            <?php
            $sql_top = "SELECT p.*, COALESCE(SUM(d.cantidad), 0) as total_vendido FROM productos p LEFT JOIN detalle_pedido d ON p.id_producto = d.id_producto WHERE p.estado = 'activo' AND p.stock_actual > 0 GROUP BY p.id_producto ORDER BY total_vendido DESC, p.id_producto ASC LIMIT 12";
            $res_top = $conn->query($sql_top);
            if ($res_top && $res_top->num_rows > 0) {
                while($row = $res_top->fetch_assoc()) { 
                    // Ancho fijo para mantener tarjetas alineadas
                    echo '<div class="min-w-[160px] max-w-[160px] md:min-w-[260px] md:max-w-[260px] snap-start flex-none h-auto flex">'; 
                    renderCard($row); 
                    echo '</div>';
                }
            }
            ?>
        </div>

        <div class="flex justify-between items-end mb-4 mt-8 px-2 border-b border-base-300 pb-2">
            <h3 class="text-lg md:text-2xl font-display font-bold text-base-content flex items-center gap-2">
                <i class="fas fa-sparkles text-warning"></i> Descubre lo nuevo
            </h3>
            <div class="flex gap-2">
                <button class="btn btn-circle btn-sm btn-ghost" onclick="document.getElementById('slider2').scrollBy({left: -260, behavior:'smooth'})">❮</button>
                <button class="btn btn-circle btn-sm btn-ghost" onclick="document.getElementById('slider2').scrollBy({left: 260, behavior:'smooth'})">❯</button>
            </div>
        </div>

        <div id="slider2" class="flex gap-4 md:gap-6 overflow-x-auto pb-8 snap-x scrollbar-hide scroll-smooth px-1">
            <?php
            $sql_new = "SELECT * FROM productos WHERE estado = 'activo' AND stock_actual > 0 ORDER BY id_producto DESC LIMIT 12";
            $res_new = $conn->query($sql_new);
            if ($res_new && $res_new->num_rows > 0) {
                while($row = $res_new->fetch_assoc()) { 
                    echo '<div class="min-w-[160px] max-w-[160px] md:min-w-[260px] md:max-w-[260px] snap-start flex-none h-auto flex">'; 
                    renderCard($row, true); 
                    echo '</div>';
                }
            }
            ?>
        </div>

    <?php else: ?>
        <div class="mb-6">
            <h3 class="text-2xl font-display font-bold text-base-content">🔍 Resultados para: "<?php echo $busqueda; ?>"</h3>
            <?php echo $mensaje_busqueda; ?>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            <?php
            $sql_search = "SELECT * FROM productos WHERE estado = 'activo' AND stock_actual > 0 $filtro_sql LIMIT 20";
            $res_search = $conn->query($sql_search);
            if ($res_search && $res_search->num_rows > 0) {
                while($row = $res_search->fetch_assoc()) { renderCard($row); }
            } else {
                echo "<div class='col-span-full text-center py-12 opacity-50'><i class='fas fa-search text-6xl mb-4 text-base-content'></i><h4 class='text-xl font-bold text-base-content'>No encontramos productos.</h4></div>";
            }
            ?>
        </div>
    <?php endif; ?>

</div>

<style>.scrollbar-hide::-webkit-scrollbar { display: none; } .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }</style>

<?php include 'includes/footer.php'; ?>