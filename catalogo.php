<?php
include 'php/conexion.php';
include 'includes/header.php';

$productos_por_pagina = 30;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$inicio_sql = ($pagina_actual - 1) * $productos_por_pagina;

$sql_total = "SELECT COUNT(*) as total FROM productos WHERE estado = 'activo' AND stock_actual > 0";
$total_productos = $conn->query($sql_total)->fetch_assoc()['total'];
$total_paginas = ceil($total_productos / $productos_por_pagina);

$sql_prods = "SELECT * FROM productos WHERE estado = 'activo' AND stock_actual > 0 LIMIT $inicio_sql, $productos_por_pagina";
$result = $conn->query($sql_prods);

// Función auxiliar para renderizar (reutilizada para consistencia)
function renderCardCatalogo($row) {
    $ruta = "img/" . $row['imagen_url'];
    $img = ($row['imagen_url'] != 'default.jpg' && file_exists($ruta)) ? $ruta : ((file_exists("img/default.jpg")) ? "img/default.jpg" : "https://via.placeholder.com/300?text=ProtoHub");
    ?>
    <div class="card card-compact bg-base-100 shadow-lg hover:shadow-2xl transition-all duration-300 border border-base-200 group">
        <figure class="relative px-4 pt-4 h-40 bg-white flex items-center justify-center overflow-hidden">
            <a href="producto.php?id=<?php echo $row['id_producto']; ?>" class="w-full h-full flex items-center justify-center">
                <img src="<?php echo $img; ?>" alt="<?php echo $row['nombre']; ?>" class="max-h-full object-contain group-hover:scale-110 transition-transform duration-500" />
            </a>
            <?php if($row['precio_mayoreo'] > 0): ?>
                <div class="absolute top-2 right-2 badge badge-warning font-bold text-xs shadow-sm">Mayoreo</div>
            <?php endif; ?>
        </figure>
        <div class="card-body">
            <h2 class="card-title text-sm font-display leading-tight h-10 overflow-hidden">
    <a href="producto.php?id=<?php echo $row['id_producto']; ?>" class="hover:text-primary transition-colors line-clamp-2">
        <?php echo $row['nombre']; ?>
    </a>
</h2>
            <div class="card-actions justify-between items-end mt-2">
                <div class="flex flex-col">
                    <span class="text-xs opacity-70 font-bold">Precio</span>
                    <span class="text-lg font-bold font-display text-primary">$<?php echo $row['precio_unitario']; ?></span>
                </div>
                <form action="carrito.php" method="POST">
                    <input type="hidden" name="agregar_nuevo" value="true">
                    <input type="hidden" name="id_producto" value="<?php echo $row['id_producto']; ?>">
                    <input type="hidden" name="cantidad" value="1">
                    <input type="hidden" name="return_url" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                    <button class="btn btn-circle btn-sm btn-primary shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        
        <aside class="w-full md:w-64 flex-shrink-0">
            <div class="card bg-base-100 shadow-sm border border-base-200 sticky top-24">
                <div class="card-body p-4">
                    <h3 class="font-display font-bold text-lg mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" /></svg>
                        Categorías
                    </h3>
                    <ul class="menu bg-base-200 rounded-box w-full p-2">
                        <li><a href="catalogo.php" class="active font-bold">Ver Todo</a></li>
                        <?php
                        $cats = $conn->query("SELECT * FROM categorias ORDER BY nombre ASC");
                        while($c = $cats->fetch_assoc()){
                            echo '<li><a href="categoria.php?id='.$c['id_categoria'].'">'.$c['nombre'].'</a></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </aside>

        <main class="flex-1">
            <div class="flex justify-between items-end mb-6">
                <div>
                    <h1 class="text-3xl font-display font-bold text-base-content">Catálogo</h1>
                    <p class="text-sm opacity-70">Mostrando <?php echo $result->num_rows; ?> de <?php echo $total_productos; ?> productos</p>
                </div>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php while($row = $result->fetch_assoc()) { renderCardCatalogo($row); } ?>
                </div>

                <?php if($total_paginas > 1): ?>
                <div class="flex justify-center mt-10">
                    <div class="join shadow-sm">
                        <a href="catalogo.php?pagina=<?php echo max(1, $pagina_actual-1); ?>" class="join-item btn btn-sm <?php echo ($pagina_actual <= 1) ? 'btn-disabled' : ''; ?>">«</a>
                        <button class="join-item btn btn-sm no-animation">Página <?php echo $pagina_actual; ?></button>
                        <a href="catalogo.php?pagina=<?php echo min($total_paginas, $pagina_actual+1); ?>" class="join-item btn btn-sm <?php echo ($pagina_actual >= $total_paginas) ? 'btn-disabled' : ''; ?>">»</a>
                    </div>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert alert-info shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>No hay productos disponibles en este momento.</span>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>