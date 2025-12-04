<?php
include '../php/conexion.php';
include 'header.php';

// --- LÓGICA PHP (Misma de siempre) ---
if (isset($_GET['limpiar_cliente'])) { unset($_SESSION['pos_cliente']); echo "<script>window.location='pos.php';</script>"; exit; }
if (isset($_GET['limpiar_todo'])) { unset($_SESSION['pos_carrito']); unset($_SESSION['pos_cliente']); echo "<script>window.location='pos.php';</script>"; exit; }
if (isset($_GET['eliminar_item'])) {
    $id_del = $_GET['eliminar_item'];
    unset($_SESSION['pos_carrito'][$id_del]);
    echo "<script>window.location='pos.php';</script>"; exit;
}
if (!isset($_SESSION['pos_carrito'])) { $_SESSION['pos_carrito'] = []; }
if (!isset($_SESSION['pos_cliente'])) { $_SESSION['pos_cliente'] = null; }
if (isset($_POST['accion_producto'])) {
    $accion = $_POST['accion_producto'];
    $id = isset($_POST['id_producto']) ? $_POST['id_producto'] : null;
    if ($accion == 'agregar_sku') {
        $sku = $conn->real_escape_string($_POST['sku']);
        $sql = "SELECT * FROM productos WHERE sku_barras = '$sku' OR nombre = '$sku' LIMIT 1";
        $res = $conn->query($sql);
        if ($res->num_rows > 0) {
            $prod = $res->fetch_assoc();
            $_SESSION['pos_carrito'][$prod['id_producto']] = ($_SESSION['pos_carrito'][$prod['id_producto']] ?? 0) + 1;
        } else {
            $sql_like = "SELECT * FROM productos WHERE nombre LIKE '%$sku%' LIMIT 1";
            $res_like = $conn->query($sql_like);
            if ($res_like->num_rows > 0) {
                 $prod = $res_like->fetch_assoc();
                 $_SESSION['pos_carrito'][$prod['id_producto']] = ($_SESSION['pos_carrito'][$prod['id_producto']] ?? 0) + 1;
            } else {
                 echo "<script>Swal.fire('Error', 'Producto no encontrado', 'error');</script>";
            }
        }
    } elseif ($accion == 'sumar' && $id) { $_SESSION['pos_carrito'][$id]++; }
    elseif ($accion == 'restar' && $id) { 
        if ($_SESSION['pos_carrito'][$id] > 1) $_SESSION['pos_carrito'][$id]--; 
        else unset($_SESSION['pos_carrito'][$id]); 
    } elseif ($accion == 'fijar' && $id) {
        $cant = intval($_POST['cantidad_manual']);
        if ($cant > 0) $_SESSION['pos_carrito'][$id] = $cant; else unset($_SESSION['pos_carrito'][$id]);
    }
    if($accion != 'agregar_sku') { echo "<script>window.location='pos.php';</script>"; exit; }
}
if (isset($_POST['seleccionar_cliente_id'])) {
    $id_c = $_POST['seleccionar_cliente_id'];
    $sql = "SELECT * FROM usuarios WHERE id_usuario = $id_c";
    $res = $conn->query($sql);
    if ($res->num_rows > 0) { $_SESSION['pos_cliente'] = $res->fetch_assoc(); }
}
?>

<style>
    /* Quitamos el 'overflow: hidden' y altura fija para permitir scroll natural */
    .main-content {
        padding: 0 !important;
        width: 100% !important;
        max-width: none !important;
        min-height: 100vh !important; /* Mínimo pantalla completa, pero puede crecer */
        height: auto !important;
        overflow-y: auto !important; /* Activar scroll vertical */
    }
    body { overflow-y: auto !important; } /* Permitir scroll en el cuerpo */
</style>

<div class="flex flex-col w-full min-h-screen bg-base-200 p-2 gap-4 pb-20"> <div class="navbar bg-base-100 rounded-lg shadow-sm border border-base-300 px-4 shrink-0">
        <div class="flex-1">
            <h1 class="text-lg font-bold text-primary flex items-center gap-2">
                <i class="fas fa-desktop"></i> TERMINAL POS
            </h1>
        </div>
        <div class="flex-none">
            <a href="pos.php?limpiar_todo=true" class="btn btn-error btn-outline btn-sm font-bold">REINICIAR VENTA</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 w-full h-auto">
        
        <div class="flex flex-col gap-4">
            
            <div class="card bg-base-100 shadow-sm border border-base-300 shrink-0 z-50 overflow-visible sticky top-2">
                <div class="p-4">
                    <form action="" method="POST" class="flex gap-2 relative w-full" id="formProducto">
                        <input type="hidden" name="accion_producto" value="agregar_sku">
                        <div class="relative w-full">
                            <i class="fas fa-barcode absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-2xl"></i>
                            <input type="text" name="sku" id="inputProducto" 
                                   class="input input-bordered w-full h-16 pl-12 text-2xl font-mono font-bold focus:input-primary bg-base-200 focus:bg-base-100 transition-all" 
                                   placeholder="Escanear SKU..." 
                                   autofocus autocomplete="off" onkeyup="buscarProducto()">
                            <div id="listaProductos" class="absolute top-full left-0 w-full bg-base-100 shadow-2xl rounded-b-lg border border-base-200 mt-1 overflow-y-auto hidden max-h-[50vh] z-[100]"></div>
                        </div>
                        <button type="submit" class="btn btn-primary w-20 h-16 text-2xl shadow-md"><i class="fas fa-level-down-alt rotate-90"></i></button>
                    </form>
                </div>
            </div>

            <div class="card bg-base-100 shadow-md border border-base-200 flex-grow">
                <div class="bg-base-200 p-3 grid grid-cols-12 gap-2 font-bold text-xs uppercase text-base-content/60 border-b border-base-300">
                    <div class="col-span-6 pl-2">Descripción</div>
                    <div class="col-span-3 text-center">Cant.</div>
                    <div class="col-span-3 text-right pr-4">Total</div>
                </div>
                
                <div class="p-0">
                    <?php if (!empty($_SESSION['pos_carrito'])): $total = 0; ?>
                        <?php foreach ($_SESSION['pos_carrito'] as $id => $cant): 
                            $prod = $conn->query("SELECT * FROM productos WHERE id_producto=$id")->fetch_assoc();
                            $precio = ($cant >= 5) ? $prod['precio_mayoreo'] : $prod['precio_unitario'];
                            $importe = $precio * $cant;
                            $total += $importe;
                        ?>
                        <div class="grid grid-cols-12 gap-2 items-center p-3 border-b border-base-200 hover:bg-base-100 transition-colors group">
                            <div class="col-span-6 pl-2 leading-tight">
                                <div class="font-bold text-base"><?php echo $prod['nombre']; ?></div>
                                <div class="text-xs opacity-50 font-mono"><?php echo $prod['sku_barras']; ?></div>
                            </div>
                            <div class="col-span-3 flex justify-center">
                                <div class="join border border-base-300 h-8 shadow-sm">
                                    <form method="POST" class="inline"><input type="hidden" name="accion_producto" value="restar"><input type="hidden" name="id_producto" value="<?php echo $id; ?>"><button class="btn btn-sm join-item btn-ghost w-8 hover:bg-error/20 text-lg font-bold">-</button></form>
                                    <form method="POST" class="inline"><input type="hidden" name="accion_producto" value="fijar"><input type="hidden" name="id_producto" value="<?php echo $id; ?>"><input type="number" name="cantidad_manual" value="<?php echo $cant; ?>" class="input input-sm join-item w-12 text-center font-bold p-0 border-x border-base-200 focus:outline-none appearance-none" onchange="this.form.submit()"></form>
                                    <form method="POST" class="inline"><input type="hidden" name="accion_producto" value="sumar"><input type="hidden" name="id_producto" value="<?php echo $id; ?>"><button class="btn btn-sm join-item btn-ghost w-8 hover:bg-success/20 text-lg font-bold">+</button></form>
                                </div>
                            </div>
                            <div class="col-span-3 text-right flex items-center justify-end gap-3 pr-2">
                                <div class="font-mono font-bold text-lg">$<?php echo number_format($importe, 2); ?></div>
                                <a href="pos.php?eliminar_item=<?php echo $id; ?>" class="btn btn-square btn-xs btn-ghost text-error"><i class="fas fa-times"></i></a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: $total = 0; ?>
                        <div class="py-20 flex flex-col items-center justify-center opacity-30">
                            <i class="fas fa-barcode text-8xl mb-4"></i>
                            <span class="text-2xl font-bold">Escanea un producto</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            
            <div class="card bg-base-100 shadow-sm border border-base-200 z-40 overflow-visible sticky top-2">
                <div class="card-body p-4">
                    <div class="flex justify-between items-center text-xs opacity-50 uppercase font-bold mb-2">
                        <span><i class="fas fa-user"></i> Datos del Cliente</span>
                        <?php if ($_SESSION['pos_cliente']): ?><a href="pos.php?limpiar_cliente=true" class="text-error hover:underline">Cambiar</a><?php endif; ?>
                    </div>
                    
                    <?php if ($_SESSION['pos_cliente']): ?>
                        <div class="bg-base-200 p-3 rounded-xl border border-base-300 flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div class="bg-neutral text-neutral-content rounded-full w-12"><span class="text-xl"><?php echo strtoupper(substr($_SESSION['pos_cliente']['nombre_completo'], 0, 1)); ?></span></div>
                            </div>
                            <div class="overflow-hidden w-full">
                                <div class="font-bold text-lg truncate"><?php echo $_SESSION['pos_cliente']['nombre_completo']; ?></div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs opacity-70 truncate"><?php echo $_SESSION['pos_cliente']['email']; ?></span>
                                    <span class="badge badge-success font-bold text-white">$<?php echo number_format($_SESSION['pos_cliente']['puntos'], 2); ?> pts</span>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="relative w-full">
                            <div class="join w-full relative">
                                <div class="relative w-full">
                                    <input type="text" id="inputCliente" class="input input-bordered w-full join-item" placeholder="Buscar Cliente..." autocomplete="off" onkeyup="buscarCliente()">
                                    <div id="listaResultados" class="absolute top-full left-0 w-full bg-base-100 shadow-xl rounded-b-lg border border-base-200 mt-1 hidden z-50 max-h-48 overflow-y-auto"></div>
                                </div>
                                <a href="agregar_usuario.php?origen=pos&tipo=cliente" class="btn btn-primary join-item"><i class="fas fa-plus"></i></a>
                            </div>
                        </div>
                        <form id="formSeleccion" method="POST" class="hidden"><input type="hidden" name="seleccionar_cliente_id" id="clienteIdInput"></form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card bg-white shadow-xl border-t-8 border-primary sticky top-40">
                <div class="card-body p-6 flex flex-col justify-between">
                    
                    <div class="space-y-4">
                        <h3 class="text-center font-bold opacity-40 uppercase tracking-widest text-sm">Resumen de Venta</h3>
                        
                        <div class="flex justify-between text-base text-gray-500">
                            <span>Subtotal</span>
                            <span class="font-mono">$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="flex justify-between text-base text-gray-500">
                            <span>Impuestos</span>
                            <span class="font-mono">$0.00</span>
                        </div>
                        
                        <div class="divider my-2"></div>
                        
                        <div class="text-center bg-base-200 py-6 rounded-xl border border-base-300">
                            <span class="block text-sm uppercase font-bold opacity-50 mb-1">Total a Pagar</span>
                            <span class="block text-6xl font-black text-primary tracking-tighter" id="displayTotal">$<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>

                    <form action="procesar_pos.php" method="POST" id="formCobro" class="flex flex-col gap-3 mt-4">
                        <input type="hidden" name="total_venta" id="totalVentaHidden" value="<?php echo $total; ?>">
                        <input type="hidden" name="pago" id="input_metodo_hidden" value="tarjeta">
                        <input type="hidden" name="monto_recibido" id="input_recibido_hidden" value="0">

                        <?php if ($_SESSION['pos_cliente'] && $_SESSION['pos_cliente']['puntos'] > 0): ?>
                            <div class="form-control">
                                <label class="label cursor-pointer bg-warning/10 rounded-lg p-3 border border-warning/20">
                                    <span class="label-text font-bold text-warning-content flex items-center gap-2">
                                        <i class="fas fa-gift text-xl"></i> Usar Puntos (-$<?php echo min($total, $_SESSION['pos_cliente']['puntos']); ?>)
                                    </span> 
                                    <input type="checkbox" name="usar_puntos" id="checkPuntos" class="checkbox checkbox-warning" />
                                </label>
                            </div>
                        <?php endif; ?>

                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" onclick="cobrarEfectivo()" class="btn btn-success text-white h-24 text-xl shadow-lg hover:scale-[1.02] transition-transform col-span-2">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-money-bill-wave text-4xl mb-1"></i>
                                    <span class="font-black">EFECTIVO</span>
                                </div>
                            </button>
                            
                            <button type="submit" onclick="document.getElementById('input_metodo_hidden').value='tarjeta'" class="btn btn-outline btn-info h-16 font-bold text-lg">
                                <i class="fas fa-credit-card mr-2"></i> Tarjeta
                            </button>
                            
                            <button type="submit" onclick="document.getElementById('input_metodo_hidden').value='transferencia'" class="btn btn-outline btn-warning h-16 font-bold text-lg">
                                <i class="fas fa-qrcode mr-2"></i> Transfer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function cobrarEfectivo() {
    let total = parseFloat(document.getElementById('totalVentaHidden').value);
    if(total <= 0) { Swal.fire({icon:'warning', title:'Carrito vacío', showConfirmButton:false, timer:1500}); return; }

    Swal.fire({
        title: 'COBRO EN EFECTIVO',
        html: `
            <div class="text-4xl mb-4 font-black text-center">$${total.toFixed(2)}</div>
            <input type="number" id="monto_recibido" class="input input-bordered input-lg w-full text-center text-3xl font-bold text-success" placeholder="0.00">
            <div id="cambio_text" class="mt-4 text-2xl font-bold text-center opacity-50">Cambio: $0.00</div>
        `,
        showCancelButton: true, confirmButtonText: 'CONFIRMAR PAGO', confirmButtonColor: '#22c55e', cancelButtonText: 'Cancelar', width: '400px',
        didOpen: () => {
            const i = document.getElementById('monto_recibido');
            const c = document.getElementById('cambio_text');
            setTimeout(()=>i.focus(), 100);
            i.addEventListener('input', () => {
                 let v = parseFloat(i.value)||0, diff = v - total;
                 c.innerHTML = (v >= total) ? `<span class="text-success">Cambio: $${diff.toFixed(2)}</span>` : `<span class="text-error">Falta: $${Math.abs(diff).toFixed(2)}</span>`;
            });
            i.addEventListener('keypress', (e) => { if (e.key === 'Enter') Swal.clickConfirm(); });
        },
        preConfirm: () => {
            let v = parseFloat(document.getElementById('monto_recibido').value);
            if (!v || v < total) { Swal.showValidationMessage('Monto insuficiente'); return false; }
            return v;
        }
    }).then((r) => {
        if (r.isConfirmed) {
            document.getElementById('input_recibido_hidden').value = r.value;
            document.getElementById('input_metodo_hidden').value = 'efectivo';
            document.getElementById('formCobro').submit();
        }
    });
}

function buscarCliente() {
    let q = document.getElementById('inputCliente').value, l = document.getElementById('listaResultados');
    if (q.length < 2) { l.classList.add('hidden'); return; }
    fetch('buscar_cliente_ajax.php?q=' + q).then(r=>r.json()).then(d => {
        l.innerHTML = '';
        if (d.length > 0) {
            l.classList.remove('hidden');
            d.forEach(u => {
                let div = document.createElement('div');
                div.className = 'p-3 hover:bg-base-200 cursor-pointer border-b text-sm flex justify-between';
                div.innerHTML = `<div><b>${u.nombre_completo}</b><br><span class="opacity-50 text-xs">${u.email}</span></div>`;
                div.onmousedown = () => { 
                    document.getElementById('clienteIdInput').value = u.id_usuario; 
                    document.getElementById('formSeleccion').submit(); 
                };
                l.appendChild(div);
            });
        } else { l.classList.add('hidden'); }
    });
}

function buscarProducto() {
    let q = document.getElementById('inputProducto').value, l = document.getElementById('listaProductos');
    if (q.length < 2) { l.classList.add('hidden'); return; }
    fetch('buscar_producto_ajax.php?q=' + q).then(r=>r.json()).then(d => {
        l.innerHTML = '';
        if (d.length > 0) {
            l.classList.remove('hidden');
            d.forEach(p => {
                let div = document.createElement('div');
                div.className = 'p-4 hover:bg-base-100 cursor-pointer border-b flex justify-between items-center transition-colors';
                div.innerHTML = `<div><div class="font-bold text-lg">${p.nombre}</div><div class="text-sm opacity-50">${p.sku_barras}</div></div><div class="badge badge-success font-bold text-lg p-3">$${p.precio_unitario}</div>`;
                div.onmousedown = () => { 
                    document.getElementById('inputProducto').value = p.sku_barras; 
                    document.getElementById('formProducto').submit(); 
                };
                l.appendChild(div);
            });
        } else { l.classList.add('hidden'); }
    });
}

document.addEventListener('click', (e) => {
    if(!e.target.closest('#inputCliente') && !e.target.closest('#listaResultados')) {
        document.getElementById('listaResultados')?.classList.add('hidden');
    }
    if(!e.target.closest('#inputProducto') && !e.target.closest('#listaProductos')) {
        document.getElementById('listaProductos')?.classList.add('hidden');
    }
});
</script>