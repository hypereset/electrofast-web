<?php
include '../php/conexion.php';
include 'header.php';

// --- LÓGICA DE RESET ---
if (isset($_GET['limpiar_cliente'])) { unset($_SESSION['pos_cliente']); echo "<script>window.location='pos.php';</script>"; exit; }
if (isset($_GET['limpiar_todo'])) { unset($_SESSION['pos_carrito']); unset($_SESSION['pos_cliente']); echo "<script>window.location='pos.php';</script>"; exit; }

if (isset($_GET['eliminar_item'])) {
    $id_del = $_GET['eliminar_item'];
    unset($_SESSION['pos_carrito'][$id_del]);
    echo "<script>window.location='pos.php';</script>";
    exit;
}

// Inicializar
if (!isset($_SESSION['pos_carrito'])) { $_SESSION['pos_carrito'] = []; }
if (!isset($_SESSION['pos_cliente'])) { $_SESSION['pos_cliente'] = null; }

// --- AGREGAR PRODUCTO ---
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
            // Búsqueda laxa
            $sql_like = "SELECT * FROM productos WHERE nombre LIKE '%$sku%' LIMIT 1";
            $res_like = $conn->query($sql_like);
            if ($res_like->num_rows > 0) {
                 $prod = $res_like->fetch_assoc();
                 $_SESSION['pos_carrito'][$prod['id_producto']] = ($_SESSION['pos_carrito'][$prod['id_producto']] ?? 0) + 1;
            } else {
                 echo "<script>Swal.fire('Error', 'Producto no encontrado', 'error');</script>";
            }
        }
    }
    elseif ($accion == 'sumar' && $id) { $_SESSION['pos_carrito'][$id]++; }
    elseif ($accion == 'restar' && $id) { 
        if ($_SESSION['pos_carrito'][$id] > 1) $_SESSION['pos_carrito'][$id]--; 
        else unset($_SESSION['pos_carrito'][$id]); 
    }
    elseif ($accion == 'fijar' && $id) {
        $cant = intval($_POST['cantidad_manual']);
        if ($cant > 0) $_SESSION['pos_carrito'][$id] = $cant;
        else unset($_SESSION['pos_carrito'][$id]);
    }
    
    if($accion != 'agregar_sku') { echo "<script>window.location='pos.php';</script>"; exit; }
}

// --- SELECCIONAR CLIENTE ---
if (isset($_POST['seleccionar_cliente_id'])) {
    $id_c = $_POST['seleccionar_cliente_id'];
    $sql = "SELECT * FROM usuarios WHERE id_usuario = $id_c";
    $res = $conn->query($sql);
    if ($res->num_rows > 0) { $_SESSION['pos_cliente'] = $res->fetch_assoc(); }
}
?>

<div class="container-fluid pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark"><i class="fas fa-cash-register text-primary"></i> Punto de Venta</h2>
        <a href="pos.php?limpiar_todo=true" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i> Nueva Venta</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <form action="" method="POST" class="d-flex gap-2 position-relative" id="formProducto">
                        <input type="hidden" name="accion_producto" value="agregar_sku">
                        <div class="w-100">
                            <input type="text" name="sku" id="inputProducto" class="form-control form-control-lg" 
                                   placeholder="Escanear SKU o escribir nombre..." autofocus autocomplete="off" onkeyup="buscarProducto()">
                            <div id="listaProductos" class="list-group position-absolute shadow w-100 mt-1" 
                                 style="z-index: 2000; display:none; max-height: 300px; overflow-y: auto; left: 0;">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-plus"></i></button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-end">Importe</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total = 0;
                            if (!empty($_SESSION['pos_carrito'])) {
                                foreach ($_SESSION['pos_carrito'] as $id => $cant) {
                                    $prod = $conn->query("SELECT * FROM productos WHERE id_producto=$id")->fetch_assoc();
                                    $precio = ($cant >= 5) ? $prod['precio_mayoreo'] : $prod['precio_unitario'];
                                    $importe = $precio * $cant;
                                    $total += $importe;
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo $prod['nombre']; ?></div>
                                    <small class="text-muted badge bg-light text-secondary border"><?php echo $prod['sku_barras']; ?></small>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <form action="" method="POST">
                                            <input type="hidden" name="accion_producto" value="restar">
                                            <input type="hidden" name="id_producto" value="<?php echo $id; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary fw-bold" style="width: 30px;">-</button>
                                        </form>
                                        <form action="" method="POST">
                                            <input type="hidden" name="accion_producto" value="fijar">
                                            <input type="hidden" name="id_producto" value="<?php echo $id; ?>">
                                            <input type="number" name="cantidad_manual" value="<?php echo $cant; ?>" class="form-control form-control-sm text-center fw-bold border-secondary mx-1" onchange="this.form.submit()" style="width: 50px;">
                                        </form>
                                        <form action="" method="POST">
                                            <input type="hidden" name="accion_producto" value="sumar">
                                            <input type="hidden" name="id_producto" value="<?php echo $id; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary fw-bold" style="width: 30px;">+</button>
                                        </form>
                                    </div>
                                </td>
                                <td class="text-end fw-bold text-dark">$<?php echo number_format($importe, 2); ?></td>
                                <td class="text-end pe-3">
                                    <a href="pos.php?eliminar_item=<?php echo $id; ?>" class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                            <?php } } else { echo "<tr><td colspan='4' class='text-center text-muted py-4'>Escanea un producto.</td></tr>"; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-dark text-white fw-bold"><i class="fas fa-user"></i> Cliente</div>
                <div class="card-body position-relative">
                    <?php if ($_SESSION['pos_cliente']): ?>
                        <div class="alert alert-success mb-2 border-success">
                            <h6 class="fw-bold mb-0 text-dark"><?php echo $_SESSION['pos_cliente']['nombre_completo']; ?></h6>
                            <small class="text-muted"><?php echo $_SESSION['pos_cliente']['email']; ?></small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Puntos:</span>
                            <span class="fw-bold text-success">$<?php echo number_format($_SESSION['pos_cliente']['puntos'], 2); ?></span>
                        </div>
                        <a href="pos.php?limpiar_cliente=true" class="btn btn-sm btn-outline-danger w-100 mt-3">Cambiar cliente</a>
                    <?php else: ?>
                        <div class="position-relative">
                            <input type="text" id="inputCliente" class="form-control" placeholder="Buscar cliente..." autocomplete="off" onkeyup="buscarCliente()">
                            <div id="listaResultados" class="list-group position-absolute w-100 shadow" style="z-index: 1000; display:none;"></div>
                        </div>
                        <form id="formSeleccion" method="POST" style="display:none;"><input type="hidden" name="seleccionar_cliente_id" id="clienteIdInput"></form>
                        <div class="mt-3 text-center pt-2 border-top">
                            <a href="agregar_usuario.php?origen=pos&tipo=cliente" class="btn btn-sm btn-success fw-bold mt-1"><i class="fas fa-user-plus"></i> Registrar Nuevo</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fs-5">Total:</span>
                        <span class="fs-3 fw-bold text-primary">$<?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <form action="procesar_pos.php" method="POST" id="formCobro">
                        <input type="hidden" name="total_venta" id="totalVentaHidden" value="<?php echo $total; ?>">
                        
                        <input type="hidden" name="pago" id="input_metodo_hidden" value="tarjeta">
                        <input type="hidden" name="monto_recibido" id="input_recibido_hidden" value="0">

                        <?php if ($_SESSION['pos_cliente'] && $_SESSION['pos_cliente']['puntos'] > 0): ?>
                            <div class="form-check form-switch mb-3 bg-warning bg-opacity-25 p-2 rounded">
                                <input class="form-check-input ms-1" type="checkbox" name="usar_puntos" id="checkPuntos">
                                <label class="form-check-label fw-bold small ms-2" for="checkPuntos">Usar Puntos (-$<?php echo min($total, $_SESSION['pos_cliente']['puntos']); ?>)</label>
                            </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2">
                            <button type="button" onclick="cobrarEfectivo()" class="btn btn-success btn-lg fw-bold">
                                <i class="fas fa-money-bill-wave"></i> Cobrar Efectivo
                            </button>
                            
                            <button type="submit" onclick="document.getElementById('input_metodo_hidden').value='tarjeta'" class="btn btn-info text-white fw-bold">
                                <i class="fas fa-credit-card"></i> Cobrar Tarjeta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 1. CALCULADORA DE CAMBIO (SweetAlert2)
function cobrarEfectivo() {
    let total = parseFloat(document.getElementById('totalVentaHidden').value);
    
    if(total <= 0) { Swal.fire('Error', 'El carrito está vacío', 'error'); return; }

    Swal.fire({
        title: 'Cobro en Efectivo',
        html: `
            <div class="fs-4 mb-3">Total a Pagar: <b class="text-primary">$${total.toFixed(2)}</b></div>
            <label class="fw-bold">Dinero Recibido:</label>
            <input type="number" id="monto_recibido" class="swal2-input" placeholder="0.00" style="font-size: 1.5rem; text-align: center;">
            <div id="cambio_text" class="mt-3 fs-5 fw-bold text-muted">Cambio: $0.00</div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Confirmar y Cobrar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754',
        
        // Lógica de Validación
        preConfirm: () => {
            let recibido = parseFloat(document.getElementById('monto_recibido').value);
            if (!recibido || recibido < total) {
                Swal.showValidationMessage('El monto recibido debe ser mayor o igual al total');
                return false;
            }
            return recibido;
        },
        
        // Lógica de Cálculo en tiempo real
        didOpen: () => {
            const input = document.getElementById('monto_recibido');
            const cambioTxt = document.getElementById('cambio_text');
            
            // Enfocar input automáticamente
            input.focus();
            
            input.addEventListener('input', () => {
                 let val = parseFloat(input.value);
                 if(isNaN(val)) val = 0;
                 
                 let cambio = val - total;
                 
                 if(val >= total) {
                     cambioTxt.innerHTML = `Cambio: <span class="text-success">$${cambio.toFixed(2)}</span>`;
                 } else {
                     cambioTxt.innerHTML = `Faltan: <span class="text-danger">$${Math.abs(cambio).toFixed(2)}</span>`;
                 }
            });
            
            // Permitir Enter para confirmar
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') Swal.clickConfirm();
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Si confirmó, llenamos los inputs ocultos y enviamos
            document.getElementById('input_recibido_hidden').value = result.value;
            document.getElementById('input_metodo_hidden').value = 'efectivo';
            document.getElementById('formCobro').submit();
        }
    });
}

// 2. Búsquedas (Igual que antes)
function buscarCliente() { /* ... Código de búsqueda de cliente que ya tenías ... */ 
    let query = document.getElementById('inputCliente').value;
    let lista = document.getElementById('listaResultados');
    if (query.length < 2) { lista.style.display = 'none'; return; }
    fetch('buscar_cliente_ajax.php?q=' + query).then(res => res.json()).then(data => {
        lista.innerHTML = '';
        if (data.length > 0) {
            lista.style.display = 'block';
            data.forEach(user => {
                let item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action text-start';
                item.innerHTML = `<strong>${user.nombre_completo}</strong><br><small class='text-muted'>${user.email}</small>`;
                item.onclick = function() { document.getElementById('clienteIdInput').value = user.id_usuario; document.getElementById('formSeleccion').submit(); };
                lista.appendChild(item);
            });
        } else { lista.style.display = 'none'; }
    });
}
function buscarProducto() { /* ... Código de búsqueda de producto que ya tenías ... */
    let query = document.getElementById('inputProducto').value;
    let lista = document.getElementById('listaProductos');
    if (query.length < 2) { lista.style.display = 'none'; return; }
    fetch('buscar_producto_ajax.php?q=' + query).then(res => res.json()).then(data => {
        lista.innerHTML = '';
        if (data.length > 0) {
            lista.style.display = 'block';
            data.forEach(prod => {
                let item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action text-start d-flex justify-content-between align-items-center';
                item.innerHTML = `<div><strong>${prod.nombre}</strong><br><small class='text-muted'>SKU: ${prod.sku_barras}</small></div><div class='text-end'><span class='badge bg-success'>$${prod.precio_unitario}</span><br><small class='text-muted'>Stock: ${prod.stock_actual}</small></div>`;
                item.onclick = function() { document.getElementById('inputProducto').value = prod.sku_barras; document.getElementById('formProducto').submit(); };
                lista.appendChild(item);
            });
        } else { lista.style.display = 'none'; }
    });
}
</script>
</div></body></html>