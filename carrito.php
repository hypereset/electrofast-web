<?php
ob_start(); // 1. Iniciar almacenamiento en búfer (Vital)
session_start();
include 'php/conexion.php'; // Ruta en raíz

// CONFIGURACIÓN DE NEGOCIO
$minimo_compra = 50.00; // Monto mínimo en pesos

// --- LÓGICA: AGREGAR AL CARRITO ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_nuevo'])) {
    $id = $_POST['id_producto'];
    $cantidad = intval($_POST['cantidad']);

    if (!isset($_SESSION['carrito'])) { $_SESSION['carrito'] = array(); }

    if (isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id] += $cantidad;
    } else {
        $_SESSION['carrito'][$id] = $cantidad;
    }

    // --- RESPUESTA AJAX (Para el JS) ---
    if (isset($_POST['ajax'])) {
        $total_items = 0;
        foreach($_SESSION['carrito'] as $c) { $total_items += $c; }
        
        ob_clean(); // 2. Borrar cualquier error o espacio previo
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'total_items' => $total_items]);
        exit; // 3. Detener todo aquí
    }

    // Fallback (Si no hay JS)
    header("Location: " . ($_POST['return_url'] ?? "carrito.php"));
    exit;
}

// --- LÓGICA: ACTUALIZAR CANTIDAD ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_qty') {
    $id = $_POST['id'];
    $cantidad = intval($_POST['cantidad']);
    if ($cantidad > 0) $_SESSION['carrito'][$id] = $cantidad; else unset($_SESSION['carrito'][$id]);
    header("Location: carrito.php"); exit;
}

// --- LÓGICA: ELIMINAR ---
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'eliminar') unset($_SESSION['carrito'][$_GET['id']]);
    if ($_GET['action'] == 'vaciar') unset($_SESSION['carrito']);
    header("Location: carrito.php"); exit;
}

include 'includes/header.php'; 
?>

<div class="container mx-auto px-4 py-12 max-w-6xl">
    <h1 class="text-3xl font-display font-bold mb-8 flex items-center gap-3 text-base-content">
        <i class="fas fa-shopping-cart text-primary"></i> Tu Carrito
    </h1>

    <?php if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])): ?>
        <div class="hero bg-base-200 rounded-3xl py-16">
            <div class="hero-content text-center">
                <div class="max-w-md">
                    <i class="fas fa-shopping-basket text-8xl text-base-content/10 mb-6"></i>
                    <h2 class="text-3xl font-bold">Tu carrito está vacío</h2>
                    <p class="py-6 opacity-60">Parece que aún no has agregado componentes a tu proyecto.</p>
                    <a href="catalogo.php" class="btn btn-primary mt-4">Ir al Catálogo</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 overflow-x-auto bg-base-100 rounded-2xl shadow-lg border border-base-200">
                <table class="table">
                    <thead>
                        <tr class="bg-base-200 text-base-content text-sm uppercase font-bold">
                            <th>Producto</th> <th class="text-center">Cant.</th> <th class="text-right">Total</th> <th></th>
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
                                $subtotal = $precio * $cantidad;
                                $total_final += $subtotal;
                                // Imagen segura
                                $ruta_img = "img/" . $prod['imagen_url'];
                                $img = ($prod['imagen_url'] != 'default.jpg' && file_exists($ruta_img)) ? $ruta_img : ((file_exists("img/default.jpg")) ? "img/default.jpg" : "https://via.placeholder.com/80");
                        ?>
                        <tr class="hover">
                            <td>
                                <div class="flex items-center gap-4">
                                    <div class="avatar"><div class="mask mask-squircle w-16 h-16 bg-white p-1 border border-base-200"><img src="<?php echo $img; ?>" /></div></div>
                                    <div><div class="font-bold text-base"><?php echo $prod['nombre']; ?></div><div class="text-xs opacity-50"><?php echo $prod['sku_barras']; ?></div></div>
                                </div>
                            </td>
                            <td>
                                <form action="carrito.php" method="POST" class="flex justify-center">
                                    <input type="hidden" name="action" value="update_qty">
                                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                                    <input type="number" name="cantidad" value="<?php echo $cantidad; ?>" min="1" max="<?php echo $prod['stock_actual']; ?>" class="input input-bordered input-sm w-20 text-center font-bold" onchange="this.form.submit()">
                                </form>
                            </td>
                            <td class="text-right"><div class="font-bold text-lg">$<?php echo number_format($subtotal, 2); ?></div></td>
                            <td class="text-right"><a href="carrito.php?action=eliminar&id=<?php echo $id; ?>" class="btn btn-circle btn-ghost btn-sm text-error"><i class="fas fa-trash"></i></a></td>
                        </tr>
                        <?php } } ?>
                    </tbody>
                </table>
            </div>

            <div>
                <div class="card bg-base-100 shadow-xl border border-base-200 sticky top-24">
                    <div class="card-body p-6">
                        <h2 class="card-title text-xl border-b pb-3 mb-4 border-base-200">Resumen</h2>
                        
                        <div class="flex justify-between mb-2 text-sm"><span class="opacity-70">Subtotal</span><span class="font-bold">$<?php echo number_format($total_final, 2); ?></span></div>
                        
                        <?php 
                            $falta = $minimo_compra - $total_final;
                            $cumple_minimo = ($total_final >= $minimo_compra);
                        ?>

                        <?php if (!$cumple_minimo): ?>
                            <div class="alert alert-warning text-xs shadow-sm my-2">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Mínimo de compra: <strong>$<?php echo number_format($minimo_compra, 2); ?></strong><br>Agrega <strong>$<?php echo number_format($falta, 2); ?></strong> más.</span>
                            </div>
                            <progress class="progress progress-warning w-full" value="<?php echo $total_final; ?>" max="<?php echo $minimo_compra; ?>"></progress>
                        <?php else: ?>
                            <div class="alert alert-success text-xs shadow-sm my-2 text-white">
                                <i class="fas fa-check-circle"></i>
                                <span>¡Mínimo alcanzado!</span>
                            </div>
                        <?php endif; ?>

                        <div class="divider my-2"></div>
                        <div class="flex justify-between items-end mb-6">
                            <span class="font-bold text-lg">Total</span>
                            <span class="font-black text-4xl text-primary">$<?php echo number_format($total_final, 2); ?></span>
                        </div>
                        
                        <?php 
                            $link = isset($_SESSION['id_usuario']) ? 'checkout.php' : 'login.php?redirect=checkout'; 
                            // Si no cumple el mínimo, desactivamos el botón
                            $btn_state = $cumple_minimo ? '' : 'btn-disabled opacity-50 cursor-not-allowed';
                            $btn_href = $cumple_minimo ? $link : '#';
                        ?>
                        
                        <a href="<?php echo $btn_href; ?>" class="btn btn-primary btn-block shadow-lg text-lg font-bold <?php echo $btn_state; ?>">
                            PAGAR AHORA <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                        
                        <?php if (!$cumple_minimo): ?>
                            <p class="text-center text-xs mt-2 opacity-50">Agrega más productos para activar el pago</p>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>