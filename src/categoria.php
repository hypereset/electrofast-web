<?php 
include 'php/conexion.php'; 
include 'includes/header.php'; 

if(!isset($_GET['id']) || empty($_GET['id'])){ echo "<script>window.location='index.php';</script>"; exit; }
$id_cat = $_GET['id'];

$sql_nombre = "SELECT nombre, descripcion FROM categorias WHERE id_categoria = $id_cat";
$res_nombre = $conn->query($sql_nombre);
if($res_nombre->num_rows == 0){ echo "<div class='text-center py-10'>Categoría no encontrada</div>"; include 'includes/footer.php'; exit; }
$cat_data = $res_nombre->fetch_assoc();

// Función de renderizado (la misma del index para consistencia)
function renderCardCat($row) {
    $ruta = "img/" . $row['imagen_url'];
    $img = ($row['imagen_url'] != 'default.jpg' && file_exists($ruta)) ? $ruta : ((file_exists("img/default.jpg")) ? "img/default.jpg" : "https://via.placeholder.com/300?text=ProtoHub");
    ?>
    <div class="card card-compact bg-base-100 shadow-xl hover:shadow-2xl transition-all duration-300 border border-base-200 group">
        <figure class="relative px-4 pt-4 h-48 bg-white flex items-center justify-center overflow-hidden">
            <a href="producto.php?id=<?php echo $row['id_producto']; ?>" class="w-full h-full flex items-center justify-center">
                <img src="<?php echo $img; ?>" class="max-h-full object-contain group-hover:scale-110 transition-transform duration-500" />
            </a>
            <?php if($row['precio_mayoreo'] > 0): ?>
                <div class="absolute top-2 right-2 badge badge-warning font-bold text-xs shadow-sm">Mayoreo</div>
            <?php endif; ?>
        </figure>
        <div class="card-body">
            <h2 class="card-title text-sm font-display leading-tight h-10 overflow-hidden">
                <a href="producto.php?id=<?php echo $row['id_producto']; ?>" class="hover:text-primary transition-colors"><?php echo $row['nombre']; ?></a>
            </h2>
            <div class="card-actions justify-between items-end mt-2">
                <div class="flex flex-col"><span class="text-xs opacity-70 font-bold">Precio</span><span class="text-xl font-bold font-display text-primary">$<?php echo $row['precio_unitario']; ?></span></div>
                <form action="carrito.php" method="POST">
                    <input type="hidden" name="agregar_nuevo" value="true">
                    <input type="hidden" name="id_producto" value="<?php echo $row['id_producto']; ?>">
                    <input type="hidden" name="cantidad" value="1">
                    <input type="hidden" name="return_url" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                    <button class="btn btn-circle btn-primary btn-sm shadow-lg"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg></button>
                </form>
            </div>
        </div>
    </div>
    <?php
}
?>

<div class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="hero bg-base-200 rounded-3xl mb-10 p-8">
        <div class="hero-content text-center">
            <div class="max-w-lg">
                <h1 class="text-5xl font-display font-bold text-primary mb-4"><?php echo $cat_data['nombre']; ?></h1>
                <p class="py-2 text-xl opacity-70"><?php echo $cat_data['descripcion']; ?></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php
        $sql = "SELECT * FROM productos WHERE id_categoria = $id_cat AND estado = 'activo' AND stock_actual > 0";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) { renderCardCat($row); }
        } else {
            echo "<div class='col-span-full text-center py-12 opacity-50'><i class='fas fa-box-open text-6xl mb-4'></i><h4 class='text-xl font-bold'>Sin productos aquí.</h4><a href='index.php' class='btn btn-outline mt-4'>Volver</a></div>";
        }
        ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>