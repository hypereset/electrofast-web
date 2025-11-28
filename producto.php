<?php
session_start();
include 'php/conexion.php';

// Validar ID
if (!isset($_GET['id'])) { echo "<script>window.location='index.php';</script>"; exit; }
$id_producto = $_GET['id'];

// Lógica de Reseña
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_resena'])) {
    if (!isset($_SESSION['id_usuario'])) { header("Location: login.php"); exit; }
    $id_user = $_SESSION['id_usuario'];
    $seguridad_compra = "SELECT p.id_pedido FROM pedidos p JOIN detalle_pedido d ON p.id_pedido = d.id_pedido WHERE p.id_usuario = $id_user AND d.id_producto = $id_producto";
    $seguridad_duplicado = "SELECT id_resena FROM resenas WHERE id_usuario = $id_user AND id_producto = $id_producto";
    if ($conn->query($seguridad_compra)->num_rows > 0 && $conn->query($seguridad_duplicado)->num_rows == 0) {
        $cal = $_POST['calificacion']; $com = $conn->real_escape_string($_POST['comentario']);
        $conn->query("INSERT INTO resenas (id_producto, id_usuario, calificacion, comentario) VALUES ($id_producto, $id_user, $cal, '$com')");
    }
    header("Location: producto.php?id=$id_producto"); exit;
}

include 'includes/header.php';

$sql = "SELECT * FROM productos WHERE id_producto = $id_producto AND estado = 'activo'";
$result = $conn->query($sql);
if ($result->num_rows == 0) { echo "<div class='text-center py-10'><h3 class='text-2xl font-bold'>Producto no encontrado 😢</h3><a href='index.php' class='btn btn-primary mt-4'>Volver</a></div>"; include 'includes/footer.php'; exit; }
$producto = $result->fetch_assoc();

// Promedio Estrellas
$sql_avg = "SELECT AVG(calificacion) as promedio, COUNT(*) as total FROM resenas WHERE id_producto = $id_producto";
$res_avg = $conn->query($sql_avg)->fetch_assoc();
$promedio = round((float)($res_avg['promedio'] ?? 0), 1);
$total_resenas = $res_avg['total'];

function dibujar_estrellas($num) {
    $html = '<div class="rating rating-sm rating-half disabled">';
    for ($i = 1; $i <= 5; $i++) {
        $checked = ($i <= $num) ? 'bg-orange-400' : 'bg-gray-300';
        $html .= "<input type='radio' class='mask mask-star-2 $checked' disabled />";
    }
    $html .= '</div>';
    return $html;
}
?>

<div class="container mx-auto px-4 py-6 lg:py-10 max-w-6xl">
    
    <div class="text-sm breadcrumbs mb-4 overflow-hidden whitespace-nowrap">
        <ul>
            <li><a href="index.php" class="opacity-60">Inicio</a></li>
            <li><a href="catalogo.php" class="opacity-60">Catálogo</a></li>
            <li class="font-bold text-primary truncate max-w-[150px]"><?php echo $producto['nombre']; ?></li>
        </ul>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-12">
        
        <div class="card bg-base-100 shadow-xl border border-base-200">
            <figure class="px-4 py-8 bg-white flex justify-center items-center min-h-[300px] rounded-xl relative">
                <?php 
                    $ruta = "img/" . $producto['imagen_url'];
                    $img = ($producto['imagen_url'] != 'default.jpg' && file_exists($ruta)) ? $ruta : "img/default.jpg";
                ?>
                <img src="<?php echo $img; ?>" class="max-h-[250px] lg:max-h-[400px] w-auto object-contain hover:scale-105 transition-transform duration-500" alt="Producto" />
                
                <?php if($producto['precio_mayoreo'] > 0): ?>
                    <div class="absolute top-3 left-3 badge badge-warning gap-1 p-3 font-bold text-xs shadow-md">Mayoreo</div>
                <?php endif; ?>
            </figure>
        </div>

        <div class="flex flex-col gap-4">
            
            <h1 class="font-display font-black text-base-content break-words hyphens-auto w-full" 
                style="font-size: clamp(1.5rem, 6vw, 3.5rem); line-height: 1.1;">
                <?php echo $producto['nombre']; ?>
            </h1>
            
            <div class="flex flex-wrap items-center gap-3 mt-1">
                <div class="flex items-center gap-1 bg-base-200 px-2 py-1 rounded-lg">
                    <?php echo dibujar_estrellas($promedio); ?>
                    <span class="text-xs font-bold opacity-70 ml-1 pt-0.5">(<?php echo $total_resenas; ?>)</span>
                </div>
                <div class="badge badge-outline badge-sm font-mono text-xs opacity-60">SKU: <?php echo $producto['sku_barras']; ?></div>
            </div>

            <div class="divider my-0 opacity-50"></div>
            
            <p class="text-sm lg:text-lg opacity-80 leading-relaxed">
                <?php echo nl2br($producto['descripcion']); ?>
            </p>

            <div class="card bg-base-200/50 shadow-inner border border-base-200 mt-2">
                <div class="card-body flex-row justify-between items-center p-4 lg:p-6">
                    <div>
                        <p class="text-[10px] uppercase font-bold opacity-50 mb-1">Precio Unitario</p>
                        <p class="font-display font-black text-primary" style="font-size: clamp(1.8rem, 4vw, 3rem);">
                            $<?php echo number_format($producto['precio_unitario'], 2); ?>
                        </p>
                        <?php if($producto['precio_mayoreo'] > 0): ?>
                            <div class="badge badge-success badge-outline badge-sm mt-1 font-bold">
                                $<?php echo number_format($producto['precio_mayoreo'], 2); ?> (5+ pzas)
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="text-right">
                        <div class="radial-progress text-primary text-xs font-bold bg-base-100 border-4 border-base-100" style="--value:<?php echo min(100, $producto['stock_actual']); ?>; --size:3.5rem;">
                            <?php echo $producto['stock_actual']; ?>
                        </div>
                        <p class="text-[10px] uppercase mt-1 opacity-60 font-bold">Stock</p>
                    </div>
                </div>
            </div>

            <form onsubmit="agregarAlCarritoAjax(this)" class="mt-2 flex gap-3 items-stretch h-14">
                <input type="hidden" name="agregar_nuevo" value="true">
                <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                
                <div class="join border border-base-300 rounded-btn bg-base-100 shadow-sm">
                    <button type="button" class="btn btn-ghost join-item px-3 text-xl" onclick="this.nextElementSibling.stepDown()">-</button>
                    <input type="number" name="cantidad" value="1" min="1" max="<?php echo $producto['stock_actual']; ?>" class="input input-ghost join-item w-12 text-center font-bold text-lg p-0 focus:outline-none bg-transparent appearance-none m-0" />
                    <button type="button" class="btn btn-ghost join-item px-3 text-xl" onclick="this.previousElementSibling.stepUp()">+</button>
                </div>
                
                <button type="submit" class="btn btn-primary flex-1 shadow-lg text-lg font-bold rounded-btn">
                    <i class="fas fa-cart-plus mr-2"></i> Agregar
                </button>
            </form>

            <div class="flex justify-center gap-4 mt-4 text-xs opacity-60">
                <span class="flex items-center gap-1"><i class="fas fa-shield-alt"></i> Garantía 48h</span>
                <span class="flex items-center gap-1"><i class="fas fa-truck"></i> Envío Local</span>
            </div>
        </div>
    </div>

    <hr class="my-8 opacity-20">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div>
            <h4 class="font-bold text-xl mb-4 flex items-center gap-2"><i class="fas fa-comments text-primary"></i> Opiniones</h4>
            <?php
            $show_form = false;
            if(isset($_SESSION['id_usuario'])){
                $u=$_SESSION['id_usuario'];
                $c=$conn->query("SELECT p.id_pedido FROM pedidos p JOIN detalle_pedido d ON p.id_pedido=d.id_pedido WHERE p.id_usuario=$u AND d.id_producto=$id_producto AND p.estatus_pedido!='cancelado'");
                $r=$conn->query("SELECT id_resena FROM resenas WHERE id_usuario=$u AND id_producto=$id_producto");
                if($c->num_rows > 0 && $r->num_rows == 0) $show_form = true;
            }
            if($show_form): ?>
                <div class="collapse collapse-arrow bg-base-200 mb-4 rounded-box border border-base-300">
                    <input type="checkbox" /> 
                    <div class="collapse-title font-bold text-sm">✨ Escribir reseña</div>
                    <div class="collapse-content"> 
                        <form action="" method="POST" class="flex flex-col gap-2">
                            <input type="hidden" name="id_producto" value="<?php echo $id_producto; ?>">
                            <input type="hidden" name="enviar_resena" value="true">
                            <select name="calificacion" class="select select-bordered select-sm w-full"><option value="5">5 Estrellas</option><option value="4">4 Estrellas</option><option value="3">3 Estrellas</option></select>
                            <textarea name="comentario" class="textarea textarea-bordered w-full text-sm" placeholder="Tu opinión..." required></textarea>
                            <button class="btn btn-neutral btn-sm w-full">Publicar</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            $res_com = $conn->query("SELECT r.*, u.nombre_completo FROM resenas r JOIN usuarios u ON r.id_usuario = u.id_usuario WHERE r.id_producto = $id_producto ORDER BY r.fecha DESC LIMIT 5");
            if($res_com->num_rows > 0){
                while($com = $res_com->fetch_assoc()){
            ?>
                <div class="chat chat-start mb-2">
                    <div class="chat-image avatar placeholder">
                        <div class="bg-neutral text-neutral-content rounded-full w-8">
                            <span class="text-xs"><?php echo substr($com['nombre_completo'],0,1); ?></span>
                        </div>
                    </div>
                    <div class="chat-header text-xs opacity-50 mb-1">
                        <?php echo $com['nombre_completo']; ?> <time class="text-[10px] opacity-40"><?php echo date('d/m', strtotime($com['fecha'])); ?></time>
                    </div>
                    <div class="chat-bubble chat-bubble-base-200 text-sm">
                        <div class="text-warning text-xs mb-1"><?php echo str_repeat('★', $com['calificacion']); ?></div>
                        <?php echo $com['comentario']; ?>
                    </div>
                </div>
            <?php } } else { echo "<div class='text-center opacity-40 text-sm py-4'>Sin reseñas.</div>"; } ?>
        </div>

        <div>
            <h4 class="font-bold text-xl mb-4 flex items-center gap-2"><i class="fas fa-lightbulb text-warning"></i> Relacionados</h4>
            <div class="grid grid-cols-2 gap-3">
                <?php
                $res_sug = $conn->query("SELECT p.* FROM productos p JOIN sugerencias_productos s ON p.id_producto = s.id_producto_sugerido WHERE s.id_producto_base = $id_producto AND p.stock_actual > 0 LIMIT 4");
                while($sug = $res_sug->fetch_assoc()) {
                    $img_s = (file_exists("img/".$sug['imagen_url']) && $sug['imagen_url']!='default.jpg') ? "img/".$sug['imagen_url'] : "img/default.jpg";
                ?>
                    <a href="producto.php?id=<?php echo $sug['id_producto']; ?>" class="flex items-center gap-3 p-2 bg-base-100 rounded-lg border border-base-200 hover:border-primary transition-colors group">
                        <img src="<?php echo $img_s; ?>" class="w-12 h-12 object-contain group-hover:scale-110 transition-transform">
                        <div class="overflow-hidden">
                            <h5 class="font-bold text-xs truncate"><?php echo $sug['nombre']; ?></h5>
                            <p class="text-success font-bold text-xs">$<?php echo $sug['precio_unitario']; ?></p>
                        </div>
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>