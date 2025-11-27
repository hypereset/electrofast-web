<?php 
include 'php/conexion.php'; 
include 'includes/header.php'; 

// Lógica de Búsqueda
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
    $mensaje_busqueda = "<div class='mb-8'><a href='index.php' class='btn btn-outline btn-sm'>Limpiar búsqueda</a></div>";
}

// --- FUNCIÓN DE TARJETA (REDISEÑADA PARA QUE EL TEXTO NO SE CORTE) ---
function renderCard($row, $esNuevo = false) {
    $ruta_local = "img/" . $row['imagen_url'];
    if ($row['imagen_url'] != 'default.jpg' && file_exists($ruta_local)) { $img_final = $ruta_local; } 
    elseif (file_exists("img/default.jpg")) { $img_final = "img/default.jpg"; }
    else { $img_final = "https://via.placeholder.com/300x300.png?text=ProtoHub"; }
    ?>
    
    <div class="card card-compact bg-base-100 shadow-xl hover:shadow-2xl transition-all duration-300 border border-base-200 h-full group">
        
        <figure class="relative px-4 pt-4 h-48 bg-white flex items-center justify-center overflow-hidden rounded-t-2xl">
            <a href="producto.php?id=<?php echo $row['id_producto']; ?>" class="w-full h-full flex items-center justify-center">
                <img src="<?php echo $img_final; ?>" alt="<?php echo $row['nombre']; ?>" class="max-h-full object-contain group-hover:scale-110 transition-transform duration-500" />
            </a>
            
            <div class="absolute top-2 right-2 flex flex-col gap-1 items-end">
                <?php if($row['precio_mayoreo'] > 0): ?>
                    <div class="badge badge-warning font-bold text-xs shadow-sm">Mayoreo</div>
                <?php endif; ?>
                <?php if($esNuevo): ?>
                    <div class="badge badge-secondary font-bold text-xs shadow-sm">NUEVO</div>
                <?php endif; ?>
            </div>
        </figure>

        <div class="card-body p-4">
            <h2 class="card-title text-base font-display leading-tight h-10 overflow-hidden">
                <a href="producto.php?id=<?php echo $row['id_producto']; ?>" class="hover:text-primary transition-colors line-clamp-2">
                    <?php echo $row['nombre']; ?>
                </a>
            </h2>
            
            <p class="text-xs text-base-content/70 truncate mb-2">
                <?php echo substr($row['descripcion'], 0, 40) . '...'; ?>
            </p>

            <div class="card-actions justify-between items-end mt-auto pt-2 border-t border-base-200">
                <div class="flex flex-col">
                    <span class="text-xs font-bold opacity-60 uppercase">Precio</span>
                    <span class="text-xl font-black font-display text-primary">$<?php echo $row['precio_unitario']; ?></span>
                </div>
                
                <form action="carrito.php" method="POST">
                    <input type="hidden" name="agregar_nuevo" value="true">
                    <input type="hidden" name="id_producto" value="<?php echo $row['id_producto']; ?>">
                    <input type="hidden" name="cantidad" value="1">
                    <input type="hidden" name="return_url" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                    
                    <button type="submit" class="btn btn-circle btn-primary btn-sm shadow-lg tooltip tooltip-left" data-tip="Agregar al carrito">
                        <i class="fas fa-plus"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php
}
?>

<div class="container mx-auto px-4 py-8 max-w-7xl">
    
    <?php if (!$estamos_buscando): ?>

        <div class="hero min-h-[300px] rounded-box mb-12 banner-neon bg-base-200 relative overflow-hidden">
            <div class="hero-content text-center z-10">
                <div class="max-w-md">
                    <h1 class="text-5xl font-display font-black mb-4 titulo-proto">¡Salva tu semestre!</h1>
                    <p class="py-6 text-lg opacity-80">Componentes urgentes para TESCO, UPVM y más.</p>
                    <a href="paquetes.php" class="btn btn-primary shadow-lg gap-2">
                        <i class="fas fa-box-open"></i> Ver Paquetes Escolares
                    </a>
                </div>
            </div>
            <i class="fas fa-microchip absolute -bottom-10 -right-10 text-[15rem] opacity-5 rotate-12"></i>
        </div>

        <div class="flex justify-between items-end mb-4 px-2 border-b border-base-300 pb-2">
            <h3 class="text-2xl font-display font-bold text-base-content">
                <i class="fas fa-fire text-error mr-2"></i> Lo más vendido
            </h3>
            <div class="flex gap-2">
                <button class="btn btn-circle btn-sm btn-ghost" onclick="document.getElementById('slider1').scrollBy({left: -300, behavior:'smooth'})">❮</button>
                <button class="btn btn-circle btn-sm btn-ghost" onclick="document.getElementById('slider1').scrollBy({left: 300, behavior:'smooth'})">❯</button>
            </div>
        </div>

        <div id="slider1" class="flex gap-6 overflow-x-auto pb-8 snap-x scrollbar-hide scroll-smooth">
            <?php
            $sql_top = "SELECT p.*, COALESCE(SUM(d.cantidad), 0) as total_vendido 
                        FROM productos p LEFT JOIN detalle_pedido d ON p.id_producto = d.id_producto 
                        WHERE p.estado = 'activo' AND p.stock_actual > 0
                        GROUP BY p.id_producto ORDER BY total_vendido DESC, p.id_producto ASC LIMIT 12";
            $res_top = $conn->query($sql_top);
            if ($res_top && $res_top->num_rows > 0) {
                while($row = $res_top->fetch_assoc()) { 
                    echo '<div class="min-w-[260px] max-w-[260px] snap-start flex-none">'; 
                    renderCard($row); 
                    echo '</div>';
                }
            }
            ?>
        </div>

        <div class="flex justify-between items-end mb-4 mt-8 px-2 border-b border-base-300 pb-2">
            <h3 class="text-2xl font-display font-bold text-base-content">
                <i class="fas fa-sparkles text-warning mr-2"></i> Descubre lo nuevo
            </h3>
            <div class="flex gap-2">
                <button class="btn btn-circle btn-sm btn-ghost" onclick="document.getElementById('slider2').scrollBy({left: -300, behavior:'smooth'})">❮</button>
                <button class="btn btn-circle btn-sm btn-ghost" onclick="document.getElementById('slider2').scrollBy({left: 300, behavior:'smooth'})">❯</button>
            </div>
        </div>

        <div id="slider2" class="flex gap-6 overflow-x-auto pb-8 snap-x scrollbar-hide scroll-smooth">
            <?php
            $sql_new = "SELECT * FROM productos WHERE estado = 'activo' AND stock_actual > 0 ORDER BY id_producto DESC LIMIT 12";
            $res_new = $conn->query($sql_new);
            if ($res_new && $res_new->num_rows > 0) {
                while($row = $res_new->fetch_assoc()) { 
                    echo '<div class="min-w-[260px] max-w-[260px] snap-start flex-none">'; 
                    renderCard($row, true); 
                    echo '</div>';
                }
            }
            ?>
        </div>

    <?php else: ?>
        <div class="mb-6">
            <h3 class="text-2xl font-display font-bold">🔍 Resultados para: "<?php echo $busqueda; ?>"</h3>
            <?php echo $mensaje_busqueda; ?>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php
            $sql_search = "SELECT * FROM productos WHERE estado = 'activo' AND stock_actual > 0 $filtro_sql LIMIT 20";
            $res_search = $conn->query($sql_search);
            if ($res_search && $res_search->num_rows > 0) {
                while($row = $res_search->fetch_assoc()) { 
                    renderCard($row); // Aquí se adapta al ancho del grid
                }
            } else {
                echo "<div class='col-span-full text-center py-12 opacity-50'><i class='fas fa-search text-6xl mb-4'></i><h4 class='text-xl font-bold'>No encontramos productos.</h4><a href='index.php' class='btn btn-primary mt-4'>Volver al Inicio</a></div>";
            }
            ?>
        </div>
    <?php endif; ?>

</div>

<style>.scrollbar-hide::-webkit-scrollbar { display: none; } .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }</style>

<?php include 'includes/footer.php'; ?>