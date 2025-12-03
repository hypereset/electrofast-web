<?php
session_start();
include 'php/conexion.php';
if (!isset($_SESSION['id_usuario'])) { header("Location: login.php"); exit; }
include 'includes/header.php';

$id_pedido = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verificamos que el pedido exista y sea del usuario
$sql = "SELECT total_final FROM pedidos WHERE id_pedido = $id_pedido AND id_usuario = {$_SESSION['id_usuario']}";
$res = $conn->query($sql);

// Si no encuentra el pedido o no es suyo, lo regresa
if(!$res || $res->num_rows == 0) { echo "<script>window.location='mis_pedidos.php';</script>"; exit; }

$pedido = $res->fetch_assoc();
?>

<div class="container mx-auto px-4 py-12 max-w-2xl">
    <div class="card bg-base-100 shadow-xl border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-2xl font-display font-bold text-primary mb-2">
                <i class="fas fa-file-invoice-dollar"></i> Generar Factura
            </h2>
            <p class="text-sm opacity-70 mb-6">Completa tus datos fiscales. (Simulación para fines académicos)</p>
            
            <div class="alert alert-info shadow-sm mb-6 text-xs">
                <i class="fas fa-info-circle"></i> 
                <span>Estás facturando el pedido <strong>#<?php echo str_pad($id_pedido, 6, "0", STR_PAD_LEFT); ?></strong> por un total de <strong>$<?php echo number_format($pedido['total_final'], 2); ?></strong></span>
            </div>

            <form action="imprimir_factura.php" method="POST" target="_blank">
                <input type="hidden" name="id_pedido" value="<?php echo $id_pedido; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">RFC</span></label>
                        <input type="text" name="rfc" placeholder="XAXX010101000" class="input input-bordered uppercase" required />
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">Razón Social</span></label>
                        <input type="text" name="razon_social" placeholder="Nombre o Empresa" value="<?php echo $_SESSION['nombre_usuario']; ?>" class="input input-bordered uppercase" required />
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">Régimen Fiscal</span></label>
                        <select name="regimen" class="select select-bordered w-full">
                            <option value="601">601 - General de Ley Personas Morales</option>
                            <option value="605">605 - Sueldos y Salarios e Ingresos Asimilados</option>
                            <option value="608">608 - Demás ingresos</option>
                            <option value="612">612 - Personas Físicas con Actividades Empresariales</option>
                            <option value="626" selected>626 - Régimen Simplificado de Confianza</option>
                        </select>
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">Uso de CFDI</span></label>
                        <select name="uso_cfdi" class="select select-bordered w-full">
                            <option value="G03" selected>G03 - Gastos en general</option>
                            <option value="D01">D01 - Honorarios médicos</option>
                            <option value="D02">D02 - Gastos médicos por incapacidad</option>
                            <option value="I01">I01 - Construcciones</option>
                            <option value="S01">S01 - Sin efectos fiscales</option>
                        </select>
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">Código Postal</span></label>
                        <input type="number" name="cp" placeholder="55700" class="input input-bordered" required />
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">Correo Electrónico</span></label>
                        <input type="email" name="email" value="<?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?>" class="input input-bordered" required />
                    </div>
                </div>

                <div class="card-actions justify-end mt-8">
                    <a href="mis_pedidos.php" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" class="btn btn-primary font-bold">
                        <i class="fas fa-check"></i> Generar Factura PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>