<?php
session_start();
include 'php/conexion.php';
if (!isset($_SESSION['id_usuario'])) { header("Location: login.php?redirect=checkout"); exit; }
if (empty($_SESSION['carrito'])) { header("Location: index.php"); exit; }

// 1. Calcular Totales
$subtotal_productos = 0; $total_items = 0; $hay_stock_insuficiente = false;
$id_user = $_SESSION['id_usuario'];
$res_pts = $conn->query("SELECT puntos FROM usuarios WHERE id_usuario = $id_user");
$mis_puntos = ($res_pts->num_rows > 0) ? floatval($res_pts->fetch_assoc()['puntos']) : 0;

foreach ($_SESSION['carrito'] as $id => $cantidad) {
    $res = $conn->query("SELECT * FROM productos WHERE id_producto = $id");
    if ($res->num_rows > 0) {
        $prod = $res->fetch_assoc();
        if($cantidad > $prod['stock_actual']) { $hay_stock_insuficiente = true; }
        $precio = ($cantidad >= 5) ? $prod['precio_mayoreo'] : $prod['precio_unitario'];
        $subtotal_productos += ($precio * $cantidad);
    }
}

$es_envio_gratis = ($subtotal_productos >= 250);
$solo_tarjeta = ($subtotal_productos > 1000);
$costo_normal = $es_envio_gratis ? 0 : 20;
$costo_urgente = $es_envio_gratis ? 10 : 30;

include 'includes/header.php'; 
?>

<h1 class="text-3xl font-display font-bold mb-8 flex items-center gap-2">
    <span class="text-primary"><i class="fas fa-cash-register"></i></span> Finalizar Compra
</h1>

<?php if ($hay_stock_insuficiente): ?>
    <div role="alert" class="alert alert-error mb-6 shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span class="font-bold">Stock insuficiente en algunos artículos.</span>
        <a href="carrito.php" class="btn btn-sm btn-outline">Ir al Carrito</a>
    </div>
    <?php include 'includes/footer.php'; exit; ?>
<?php endif; ?>

<form action="procesar_pedido.php" method="POST" id="formCheckout" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 flex flex-col gap-6">
        
        <div class="card bg-base-100 shadow-md border border-base-200">
            <div class="card-body">
                <h2 class="card-title text-lg border-b pb-2 mb-4"><i class="fas fa-map-marker-alt text-primary"></i> ¿Dónde entregamos?</h2>
                
                <div class="join w-full mb-4 grid grid-cols-3">
                    <input class="join-item btn" type="radio" name="tipo_entrega" value="escuela" aria-label="🏫 Escuela" checked onclick="toggleDireccion('escuela')"/>
                    <input class="join-item btn" type="radio" name="tipo_entrega" value="domicilio_particular" aria-label="🏠 Casa" onclick="toggleDireccion('casa')"/>
                    <input class="join-item btn" type="radio" name="tipo_entrega" value="tienda" aria-label="🏪 Tienda" onclick="toggleDireccion('tienda')"/>
                </div>

                <div id="seccion_escuela" class="form-control gap-4">
                    <select class="select select-bordered w-full" name="id_escuela">
                        <?php $res_esc = $conn->query("SELECT * FROM escuelas_coacalco"); while($e=$res_esc->fetch_assoc()) echo "<option value='".$e['id_escuela']."'>".$e['nombre']."</option>"; ?>
                    </select>
                    <input type="text" class="input input-bordered w-full" name="ref_escuela" placeholder="Ubicación exacta (Ej: Puerta 5)">
                </div>

                <div id="seccion_casa" class="form-control gap-4 hidden">
                    <textarea class="textarea textarea-bordered h-24" name="direccion_casa" placeholder="Dirección completa"></textarea>
                    <input type="text" class="input input-bordered" name="ref_casa" placeholder="Referencias de fachada">
                </div>

                <div id="seccion_tienda" class="hidden">
                    <div class="alert shadow-sm bg-base-200 border-l-4 border-primary">
                        <div>
                            <h3 class="font-bold text-primary">Sucursal Central</h3>
                            <div class="text-xs opacity-70">Blvd de las Rosas 45, Coacalco. <br>L-V: 8am-7pm</div>
                        </div>
                        <a href="https://maps.app.goo.gl/GTvVbP6Evp63iyMM8" target="_blank" class="btn btn-sm btn-outline btn-primary"><i class="fas fa-map-marked-alt"></i> Mapa</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-md border border-base-200">
            <div class="card-body">
                <h2 class="card-title text-lg border-b pb-2 mb-4"><i class="fas fa-wallet text-primary"></i> Método de Pago</h2>
                
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-3 border rounded-lg p-3 mb-2 hover:bg-base-200 <?php echo $solo_tarjeta ? 'opacity-50' : ''; ?>">
                        <input type="radio" name="metodo_pago" value="efectivo_contraentrega" class="radio radio-primary" <?php echo $solo_tarjeta ? 'disabled' : 'checked'; ?> onclick="toggleInfoPago('efectivo')">
                        <span class="label-text font-bold <?php echo $solo_tarjeta ? 'line-through' : ''; ?>">💵 Efectivo contra entrega</span>
                        <?php if($solo_tarjeta): ?><span class="badge badge-error badge-sm">Máx $1,000</span><?php endif; ?>
                    </label>

                    <label class="label cursor-pointer justify-start gap-3 border rounded-lg p-3 hover:bg-base-200">
                        <input type="radio" name="metodo_pago" value="linea" class="radio radio-primary" <?php echo $solo_tarjeta ? 'checked' : ''; ?> onclick="toggleInfoPago('linea')">
                        <span class="label-text font-bold text-success">💳 Transferencia / Depósito</span>
                    </label>
                </div>

                <div id="info_banco" class="alert bg-success/10 text-success-content mt-4 hidden border border-success/20">
                    <i class="fas fa-university text-xl"></i>
                    <div class="text-xs">
                        <div class="font-bold">BBVA Bancomer - ElectroFast S.A.</div>
                        <div>CLABE: <span id="clabeInput" class="font-mono font-bold">012345678901234567</span> 
                            <button type="button" onclick="copiarClabe()" class="btn btn-xs btn-ghost text-success"><i class="fas fa-copy"></i></button>
                        </div>
                        <div class="opacity-70 mt-1">* Sube tu comprobante al finalizar.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card bg-base-100 shadow-xl border border-base-200 sticky top-24">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Resumen</h2>
                <div class="flex justify-between text-sm mb-2"><span class="opacity-70">Productos</span><span class="font-bold">$<?php echo number_format($subtotal_productos, 2); ?></span></div>
                
                <div class="divider my-2"></div>
                <div class="form-control">
                    <label class="label cursor-pointer">
                        <span class="label-text">🐢 Normal <?php echo $es_envio_gratis?'<span class="text-success font-bold">GRATIS</span>':'+$20'; ?></span>
                        <input type="radio" name="tipo_envio" value="normal" class="radio radio-sm" checked onclick="calcularTotal()">
                    </label>
                    <label class="label cursor-pointer">
                        <span class="label-text">🚀 Urgente <?php echo $es_envio_gratis?'+$10':'+$30'; ?></span>
                        <input type="radio" name="tipo_envio" value="urgente" class="radio radio-sm" onclick="calcularTotal()">
                    </label>
                </div>

                <?php if($mis_puntos > 0): ?>
                    <div class="form-control bg-base-200 rounded-box p-2 mt-2">
                        <label class="label cursor-pointer">
                            <span class="label-text text-xs font-bold text-success">Usar $<?php echo $mis_puntos; ?> Puntos</span>
                            <input type="checkbox" class="toggle toggle-success toggle-sm" id="switchPuntos" name="usar_puntos" onchange="calcularTotal()">
                        </label>
                    </div>
                <?php endif; ?>

                <div class="bg-base-200 p-4 rounded-box mt-4 text-center">
                    <span class="text-xs uppercase font-bold opacity-50">Total a Pagar</span>
                    <div class="text-3xl font-display font-black text-primary" id="texto_total">$<?php echo number_format($subtotal_productos + $costo_normal, 2); ?></div>
                </div>

                <button type="submit" class="btn btn-primary w-full mt-4 shadow-lg text-lg font-bold">CONFIRMAR PEDIDO</button>
            </div>
        </div>
    </div>
</form>

<script>
    let subtotal = <?php echo $subtotal_productos; ?>;
    let costoNormal = <?php echo $costo_normal; ?>;
    let costoUrgente = <?php echo $costo_urgente; ?>;
    let misPuntos = <?php echo $mis_puntos; ?>;

    function toggleDireccion(tipo) {
        document.getElementById('seccion_escuela').style.display = (tipo === 'escuela') ? 'block' : 'none';
        document.getElementById('seccion_casa').style.display = (tipo === 'casa') ? 'block' : 'none';
        document.getElementById('seccion_tienda').style.display = (tipo === 'tienda') ? 'block' : 'none';
        if(tipo === 'tienda') { calcularTotal(true); } else { calcularTotal(); }
    }

    function toggleInfoPago(metodo) {
        const info = document.getElementById('info_banco');
        if(info) info.style.display = (metodo === 'linea') ? 'block' : 'none';
    }
    // Init
    if(document.querySelector('input[name="tipo_entrega"]:checked')) toggleDireccion(document.querySelector('input[name="tipo_entrega"]:checked').value);
    if(document.getElementById('pago_linea').checked) toggleInfoPago('linea');

    function calcularTotal(forzarTienda = false) {
        let envio = 0;
        if (!forzarTienda && document.getElementById('seccion_tienda').style.display === 'none') {
            envio = document.querySelector('input[name="tipo_envio"][value="urgente"]').checked ? costoUrgente : costoNormal;
        }
        let total = subtotal + envio;
        let checkbox = document.getElementById('switchPuntos');
        if (checkbox && checkbox.checked) {
            if (misPuntos >= total) { total = 0; } else { total = total - misPuntos; }
        }
        document.getElementById('texto_total').innerText = '$' + total.toFixed(2);
    }

    function copiarClabe() {
        navigator.clipboard.writeText("012345678901234567");
        Swal.fire({toast:true, icon:'success', title:'Copiado', position:'top-end', showConfirmButton:false, timer:1500});
    }
</script>

<?php include 'includes/footer.php'; ?>