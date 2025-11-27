<?php
session_start();
include 'php/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_nuevo'])) {
    $id = $_POST['id_producto'];
    $cantidad = intval($_POST['cantidad']);

    if (!isset($_SESSION['carrito'])) { $_SESSION['carrito'] = array(); }
    if (isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id] += $cantidad;
    } else {
        $_SESSION['carrito'][$id] = $cantidad;
    }
    if (isset($_POST['ajax'])) {
        $total_items = 0;
        foreach($_SESSION['carrito'] as $c) { $total_items += $c; }
        echo json_encode(['status' => 'success', 'total_items' => $total_items]);
        exit;
    }
    header("Location: " . ($_POST['return_url'] ?? "carrito.php"));
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_qty') {
    $id = $_POST['id'];
    $cantidad = intval($_POST['cantidad']);
    if ($cantidad > 0) {
        $_SESSION['carrito'][$id] = $cantidad;
    } else {
        unset($_SESSION['carrito'][$id]);
    }
    header("Location: carrito.php");
    exit;
}
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'eliminar' && isset($_GET['id'])) {
        unset($_SESSION['carrito'][$_GET['id']]);
    }
    if ($_GET['action'] == 'vaciar') {
        unset($_SESSION['carrito']);
    }
    header("Location: carrito.php");
    exit;
}
include 'includes/header.php'; 
?>

<div class="container mx-auto px-4 py-8 max-w-6xl">
    <h1 class="text-3xl font-display font-bold mb-8 flex items-center gap-3">
        <span class="text-primary"><i class="fas fa-shopping-cart"></i></span> Tu Carrito
    </h1>

    <?php if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])): ?>
        <div class="hero bg-base-200 rounded-box py-12">
            <div class="hero-content text-center">
                <div class="max-w-md">
                    <i class="fas fa-shopping-basket text-6xl text-base-content/20 mb-4"></i>
                    <h2 class="text-2xl font-bold">Tu carrito está vacío</h2>
                    <p class="py-6 opacity-70">Parece que aún no has agregado componentes a tu proyecto.</p>
                    <a href="catalogo.php" class="btn btn-primary">Ir al Catálogo</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2">
                <div class="overflow-x-auto bg-base-100 rounded-box shadow border border-base-200">
                    <table class="table">
                        <thead>
                            <tr class="bg-base-200 text-base-content">
                                <th>Producto</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-right">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_final = 0;
                            foreach ($_SESSION['carrito'] as $id => $cantidad) {
                                $res = $conn->query("SELECT * FROM productos WHERE id_producto = $id");
                                if ($res && $res->num_rows > 0) {
                                    $prod = $res->fetch_assoc();
                                    $precio = ($cantidad >= 5) ? $prod['precio_mayoreo'] : $prod['precio_unitario'];
                                    $es_mayoreo = ($cantidad >= 5);
                                    $subtotal = $precio * $cantidad;
                                    $total_final += $subtotal;
                                    
                                    $ruta = "img/" . $prod['imagen_url'];
                                    $img = (file_exists($ruta) && $prod['imagen_url']!='default.jpg') ? $ruta : "img/default.jpg";
                            ?>
                            <tr class="hover">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar">
                                            <div class="mask mask-squircle w-12 h-12 bg-white p-1">
                                                <img src="<?php echo $img; ?>" class="object-contain" />
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold text-sm"><?php echo $prod['nombre']; ?></div>
                                            <div class="text-xs opacity-50"><?php echo $prod['sku_barras']; ?></div>
                                            <?php if($es_mayoreo): ?><span class="badge badge-xs badge-warning">Mayoreo</span><?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <form action="carrito.php" method="POST" class="flex justify-center">
                                        <input type="hidden" name="action" value="update_qty">
                                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                                        <input type="number" name="cantidad" value="<?php echo $cantidad; ?>" min="1" max="<?php echo $prod['stock_actual']; ?>" 
                                               class="input input-bordered input-sm w-16 text-center font-bold" onchange="this.form.submit()">
                                    </form>
                                </td>
                                <td class="text-right font-display font-bold">
                                    $<?php echo number_format($subtotal, 2); ?>
                                    <div class="text-xs font-sans font-normal opacity-50">$<?php echo $precio; ?> c/u</div>
                                </td>
                                <td>
                                    <a href="carrito.php?action=eliminar&id=<?php echo $id; ?>" class="btn btn-ghost btn-xs text-error"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php } } ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="flex justify-between mt-4">
                    <a href="carrito.php?action=vaciar" class="btn btn-outline btn-error btn-sm">Vaciar Carrito</a>
                    <a href="catalogo.php" class="btn btn-outline btn-sm">Seguir Comprando</a>
                </div>
            </div>

            <div>
                <div class="card bg-base-100 shadow-xl border border-base-200 sticky top-24">
                    <div class="card-body">
                        <h2 class="card-title text-lg border-b pb-2 border-base-200">Resumen del Pedido</h2>
                        
                        <div class="flex justify-between my-2 text-sm">
                            <span class="opacity-70">Subtotal Productos</span>
                            <span class="font-bold">$<?php echo number_format($total_final, 2); ?></span>
                        </div>

                        <?php if($total_final >= 250): ?>
                            <div class="alert alert-success py-2 text-xs mt-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-4 w-4" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>¡Envío GRATIS!</span>
                            </div>
                        <?php else: ?>
                            <div class="flex justify-between text-xs text-warning mt-2">
                                <span>Falta para envío gratis:</span>
                                <span>$<?php echo number_format(250 - $total_final, 2); ?></span>
                            </div>
                            <progress class="progress progress-warning w-full" value="<?php echo ($total_final/250)*100; ?>" max="100"></progress>
                        <?php endif; ?>

                        <div class="divider my-2"></div>
                        
                        <div class="flex justify-between items-end mb-4">
                            <span class="font-bold text-lg">Total</span>
                            <span class="font-display font-black text-3xl text-primary">$<?php echo number_format($total_final, 2); ?></span>
                        </div>

                        <?php $link_pago = isset($_SESSION['id_usuario']) ? 'checkout.php' : 'login.php?redirect=checkout'; ?>
                        <a href="<?php echo $link_pago; ?>" class="btn btn-primary btn-block shadow-lg text-lg">
                            Proceder al Pago <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>