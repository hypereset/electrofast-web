<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/../php/conexion.php'; 

// 1. Contar Carrito
$num_items = 0;
if(isset($_SESSION['carrito'])){ foreach($_SESSION['carrito'] as $cant){ $num_items += $cant; } }

// 2. Puntos
$puntos_usuario = 0;
if(isset($_SESSION['id_usuario']) && isset($conn)){
    $id_user = $_SESSION['id_usuario'];
    $res_pts = $conn->query("SELECT puntos FROM usuarios WHERE id_usuario = $id_user");
    if($res_pts && $res_pts->num_rows > 0){ $puntos_usuario = $res_pts->fetch_assoc()['puntos']; }
}

// 3. Widget Pedido
$pedido_activo = null;
$id_pedido_activo = 0;
if(isset($_SESSION['id_usuario']) && isset($conn)){
    $sql_activo = "SELECT * FROM pedidos WHERE id_usuario = {$_SESSION['id_usuario']} AND estatus_pedido IN ('pendiente', 'en_preparacion', 'en_camino') ORDER BY fecha_pedido DESC LIMIT 1";
    $res_activo = $conn->query($sql_activo);
    if($res_activo && $res_activo->num_rows > 0){
        $pedido_activo = $res_activo->fetch_assoc();
        $id_pedido_activo = $pedido_activo['id_pedido'];
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="corporate">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ProtoHub</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    
<link rel="stylesheet" href="css/estilos_final.css?v=<?php echo time(); ?>">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script>
        const savedTheme = localStorage.getItem('tema') || 'corporate';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        .bell-ringing { animation: bellShake 2s infinite; color: #ff6f00 !important; }
        @keyframes bellShake { 0% { transform: rotate(0); } 10% { transform: rotate(10deg); } 20% { transform: rotate(-10deg); } 30% { transform: rotate(6deg); } 40% { transform: rotate(-6deg); } 50% { transform: rotate(0); } }
        .swal2-container { z-index: 99999 !important; }
        #widgetDiDi { z-index: 99990 !important; }
    </style>
</head>
<body class="bg-base-200 min-h-screen flex flex-col font-sans text-base-content">

<?php if($pedido_activo): ?>
    <?php
        $es_tienda = ($pedido_activo['tipo_entrega'] == 'tienda');
        $txt_status = ucfirst(str_replace('_', ' ', $pedido_activo['estatus_pedido']));
        $badge_cls = "badge-warning";
        if ($es_tienda && $pedido_activo['estatus_pedido'] == 'en_camino') {
            $txt_status = '¡Listo para recoger!';
            $badge_cls = 'badge-success text-white animate-pulse';
        }
    ?>
    <div id="widgetDiDi" class="fixed bottom-5 right-5 z-[9999] card w-80 bg-base-100 shadow-2xl border-l-8 border-primary transform hover:-translate-y-1 transition-all duration-300">
        <div class="card-body p-4">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="font-bold text-primary flex items-center gap-2">
                        <i class="fas <?php echo $es_tienda ? 'fa-store' : 'fa-motorcycle'; ?>"></i>
                        <?php echo $es_tienda ? 'Recolección' : 'En Curso'; ?>
                    </h3>
                    <p class="text-xs opacity-60">Orden #<?php echo str_pad($pedido_activo['id_pedido'], 4, "0", STR_PAD_LEFT); ?></p>
                </div>
                <div class="badge <?php echo $badge_cls; ?> font-bold text-xs"><?php echo $txt_status; ?></div>
            </div>
            <progress class="progress progress-primary w-full mt-2" value="70" max="100"></progress>
            <div class="card-actions justify-end mt-2"><a href="mis_pedidos.php" class="btn btn-xs btn-outline btn-primary w-full">Ver Detalles</a></div>
        </div>
    </div>
<?php endif; ?>

<div class="navbar bg-base-100 shadow-md sticky top-0 z-50 px-4 border-b border-base-300 h-16">
  <div class="navbar-start w-auto lg:w-1/2">
    <div class="dropdown">
      <div tabindex="0" role="button" class="btn btn-ghost lg:hidden"><i class="fas fa-bars text-lg"></i></div>
      <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
        <li><a href="index.php">Inicio</a></li>
        <li><a>Categorías</a><ul class="p-2"><li><a href="catalogo.php">Ver Todo</a></li></ul></li>
      </ul>
    </div>
    <a href="index.php" class="btn btn-ghost text-xl font-display font-bold tracking-tight px-2">
        <?php if(file_exists("img/logo.png")) { 
            // AQUI ESTÁ EL ARREGLO: style="max-height: 40px;"
            echo '<img src="img/logo.png" style="max-height: 40px; width: auto;" class="mr-1 object-contain" alt="Logo">'; 
        } else { 
            echo '<i class="fas fa-microchip text-primary text-2xl mr-2"></i>'; 
        } ?>
        <span class="text-primary">Proto</span>Hub
    </a>
  </div>

  <div class="navbar-center hidden lg:flex">
    <form action="index.php" method="GET" class="join">
        <input type="text" name="busqueda" placeholder="Buscar..." class="input input-bordered input-sm w-80 focus:outline-none join-item" />
        <button class="btn btn-primary btn-sm join-item"><i class="fas fa-search"></i></button>
    </form>
  </div>

  <div class="navbar-end gap-2 w-auto lg:w-1/2">
    <label class="swap swap-rotate btn btn-ghost btn-circle btn-sm text-primary">
      <input type="checkbox" id="themeToggle" />
      <i class="swap-on fas fa-sun text-lg"></i>
      <i class="swap-off fas fa-moon text-lg"></i>
    </label>

    <?php if(isset($_SESSION['id_usuario'])): ?>
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost btn-circle" id="bellIcon">
                <div class="indicator">
                    <i class="fas fa-bell text-lg"></i>
                    <span id="notifBadge" class="badge badge-xs badge-error indicator-item hidden"></span>
                </div>
            </div>
            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-80" id="listaNotificaciones"><li><a class="text-center opacity-50">Cargando...</a></li></ul>
        </div>

        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost rounded-btn px-2 flex items-center gap-2">
                <div class="hidden md:flex flex-col items-end mr-1 text-right">
                    <span class="text-xs font-bold text-success">$<?php echo number_format($puntos_usuario, 2); ?> Pts</span>
                    <span class="text-xs opacity-60"><?php echo explode(' ', $_SESSION['nombre_usuario'])[0]; ?></span>
                </div>
                <div class="avatar placeholder">
                    <div class="bg-neutral text-neutral-content rounded-full w-8"><span class="text-xs"><?php echo strtoupper(substr($_SESSION['nombre_usuario'], 0, 2)); ?></span></div>
                </div>
            </div>
            <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                <li class="md:hidden text-success font-bold px-4 py-2">$<?php echo number_format($puntos_usuario, 2); ?> Pts</li>
                <?php if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2): ?><li><a href="admin/index.php" class="text-warning font-bold">Panel Admin</a></li><?php endif; ?>
                <li><a href="mi_perfil.php">Mi Perfil</a></li>
                <li><a href="mis_pedidos.php">Mis Pedidos</a></li>
                <li><a href="logout.php" class="text-error">Cerrar Sesión</a></li>
            </ul>
        </div>
    <?php else: ?>
        <a href="login.php" class="btn btn-primary btn-sm font-display">Ingresar</a>
    <?php endif; ?>

    <div class="dropdown dropdown-end">
        <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
            <div class="indicator">
                <i class="fas fa-shopping-cart text-lg"></i>
                <span class="badge badge-sm badge-secondary indicator-item cart-badge-count <?php echo ($num_items > 0) ? '' : 'hidden'; ?>"><?php echo $num_items; ?></span>
            </div>
        </div>
        <div tabindex="0" class="mt-3 z-[1] card card-compact dropdown-content w-52 bg-base-100 shadow-xl">
            <div class="card-body">
                <span class="font-bold text-lg cart-item-count-text"><?php echo $num_items; ?> Items</span>
                <div class="card-actions"><a href="carrito.php" class="btn btn-primary btn-block btn-sm">Ver carrito</a></div>
            </div>
        </div>
    </div>
  </div>
</div>

<script>
    const toggle = document.getElementById('themeToggle');
    const html = document.querySelector('html');
    const currentTheme = localStorage.getItem('tema') || 'corporate';
    toggle.checked = currentTheme === 'night';
    html.setAttribute('data-theme', currentTheme);
    toggle.addEventListener('change', function() {
        const newTheme = this.checked ? 'night' : 'corporate';
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('tema', newTheme);
    });

    <?php if(isset($_SESSION['id_usuario'])): ?>
    let historialNotis = new Set();
    let ordenActivaID = <?php echo $id_pedido_activo; ?>;
    function consultarNotis() {
        fetch('api_notificaciones.php').then(r=>r.json()).then(data => {
            const badge = document.getElementById('notifBadge');
            const lista = document.getElementById('listaNotificaciones');
            const bell = document.getElementById('bellIcon');
            const widget = document.getElementById('widgetDiDi');
            if(data.cantidad > 0) {
                badge.classList.remove('hidden');
                if(data.cantidad > historialNotis.size) bell.classList.add('bell-ringing');
                let html = `<li class="menu-title flex justify-between"><span>Notificaciones</span><a onclick="borrarNotis()" class="link link-hover text-error text-xs cursor-pointer">Borrar</a></li>`;
                data.notificaciones.forEach(n => {
                    html += `<li><a href="mis_pedidos.php" class="text-xs"><i class="${n.icono} ${n.color}"></i> ${n.texto}</a></li>`;
                    if(!historialNotis.has(n.id + n.estatus)) {
                        historialNotis.add(n.id + n.estatus);
                        if(n.estatus === 'entregado') {
                            Swal.fire({toast:true, position:'top-end', icon:'success', title:'¡Pedido Entregado!', showConfirmButton:false, timer:4000});
                            if(widget && n.id == ordenActivaID) widget.style.display = 'none';
                        } else if(n.estatus === 'cancelado') {
                            Swal.fire({toast:true, position:'top-end', icon:'error', title:'Pedido Cancelado', showConfirmButton:false, timer:4000});
                            if(widget && n.id == ordenActivaID) widget.style.display = 'none';
                        }
                    }
                });
                lista.innerHTML = html;
            } else {
                badge.classList.add('hidden');
                bell.classList.remove('bell-ringing');
                lista.innerHTML = `<li><span class="text-center opacity-50 py-4">Sin novedades</span></li>`;
            }
        });
    }
    function borrarNotis() { fetch('api_notificaciones.php?accion=borrar').then(() => { consultarNotis(); historialNotis.clear(); }); }
    setInterval(consultarNotis, 5000); consultarNotis();
    <?php endif; ?>
</script>

<main class="flex-grow container mx-auto px-4 py-8 max-w-7xl">