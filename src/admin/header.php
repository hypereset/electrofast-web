<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] == 3) {
    header("Location: ../login.php");
    exit;
}
$es_admin = ($_SESSION['rol'] == 1);
?>
<!DOCTYPE html>
<html lang="es" data-theme="corporate">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Panel Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    
       <link rel="stylesheet" href="../css/estilos_final.css?v=<?php echo time(); ?>">
  
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { background-color: #f0f2f5; min-height: 100vh; display: flex; overflow-x: hidden; }
        .sidebar { height: 100vh; position: sticky; top: 0; width: 260px; background-color: var(--proto-azul) !important; color: white; transition: width 0.3s ease; z-index: 1000; display: flex; flex-direction: column; flex-shrink: 0; overflow-y: auto; -ms-overflow-style: none; scrollbar-width: none; }
        .sidebar::-webkit-scrollbar { display: none; }
        .main-content { flex-grow: 1; padding: 2rem; transition: all 0.3s ease; min-width: 0; }
        .sidebar.collapsed { width: 80px; }
        .sidebar.collapsed .logo-text, .sidebar.collapsed .user-info-text, .sidebar.collapsed .nav-link span { display: none !important; }
        .sidebar.collapsed .nav-link { justify-content: center; padding: 15px 0; }
        .sidebar.collapsed .nav-link i { margin: 0 !important; font-size: 1.4rem; }
        .sidebar.collapsed .logo-container { justify-content: center; padding: 10px 0; }
        .sidebar.collapsed .user-info { justify-content: center; padding: 5px; background: transparent; }
        .sidebar.collapsed .user-avatar { margin: 0; }
        .sidebar .expand-btn { display: none; }
        .sidebar.collapsed .expand-btn { display: block; text-align: center; margin-top: 10px; cursor: pointer; color: rgba(255,255,255,0.5); }
        .nav-link { color: rgba(255,255,255,0.8); margin-bottom: 5px; font-weight: 500; white-space: nowrap; display: flex; align-items: center; padding: 10px 15px; border-radius: 8px; }
        .nav-link:hover, .nav-link.active { color: white !important; background-color: rgba(255,255,255,0.15); }
        .nav-link.active { border-left: 4px solid var(--proto-naranja); border-radius: 4px 8px 8px 4px; }
    </style>
</head>
<body>

<div class="sidebar p-3" id="sidebarMenu">
    <div class="d-flex align-items-center justify-content-between mb-4 logo-container">
        <a href="index.php" class="d-flex align-items-center text-white text-decoration-none overflow-hidden">
            <?php 
                $logo = (file_exists("../img/logo.png")) ? "../img/logo.png" : ((file_exists("../img/logo.jpg")) ? "../img/logo.jpg" : "");
                if($logo) echo '<img src="'.$logo.'" alt="Logo" height="35" style="min-width: 35px; object-fit:contain;">';
                else echo '<i class="fas fa-microchip fa-2x text-warning" style="min-width: 35px;"></i>';
            ?>
            <span class="fs-5 fw-bold ms-2 logo-text">ProtoHub</span>
        </a>
        <i class="fas fa-bars cursor-pointer text-white-50 hover-white logo-text" onclick="toggleSidebar()"></i>
    </div>
    
    <div class="d-flex align-items-center mb-4 p-2 rounded user-info" style="background: rgba(0,0,0,0.2); min-height: 50px;">
        <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold user-avatar flex-shrink-0" style="width: 35px; height: 35px; min-width: 35px;">
            <?php echo strtoupper(substr($_SESSION['nombre_usuario'], 0, 1)); ?>
        </div>
        <div class="ms-2 small text-white overflow-hidden user-info-text">
            <div class="fw-bold text-truncate"><?php echo explode(' ', $_SESSION['nombre_usuario'])[0]; ?></div>
            <div class="opacity-75 text-uppercase" style="font-size: 0.7rem;"><?php echo ($es_admin) ? 'Admin' : 'Staff'; ?></div>
        </div>
    </div>
    
    <ul class="nav nav-pills flex-column mb-auto mt-2">
        <li><a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt" style="min-width: 25px;"></i> <span class="ms-2">Dashboard</span></a></li>
        <li><a href="pos.php" class="nav-link text-warning"><i class="fas fa-cash-register" style="min-width: 25px;"></i> <span class="ms-2">Punto de Venta</span></a></li>
        <li><a href="pedidos.php" class="nav-link"><i class="fas fa-box-open" style="min-width: 25px;"></i> <span class="ms-2">Pedidos</span></a></li>
        <li><a href="productos.php" class="nav-link"><i class="fas fa-microchip" style="min-width: 25px;"></i> <span class="ms-2">Productos</span></a></li>
        <li><a href="reportes.php" class="nav-link"><i class="fas fa-chart-line" style="min-width: 25px;"></i> <span class="ms-2">Reportes</span></a></li>
        <?php if($es_admin): ?>
            <li><a href="finanzas.php" class="nav-link"><i class="fas fa-wallet" style="min-width: 25px;"></i> <span class="ms-2">Finanzas</span></a></li>
            <li><a href="usuarios.php" class="nav-link"><i class="fas fa-users-cog" style="min-width: 25px;"></i> <span class="ms-2">Personal</span></a></li>
        <?php endif; ?>
    </ul>
    
    <div class="expand-btn text-white-50 hover-white" onclick="toggleSidebar()"><i class="fas fa-chevron-right fa-lg"></i></div>

    <div class="mt-auto">
        <a href="../logout.php" class="nav-link text-danger bg-danger bg-opacity-10 mt-4"><i class="fas fa-sign-out-alt" style="min-width: 25px;"></i> <span class="ms-2">Cerrar Sesión</span></a>
    </div>
</div>

<div class="main-content">
<script>
    const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
    if(isCollapsed) document.getElementById('sidebarMenu').classList.add('collapsed');
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebarMenu');
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
    }
</script>