<?php
include '../php/conexion.php';
include 'header.php';

if (!isset($_GET['id'])) {
    echo "<script>window.location='productos.php';</script>";
    exit;
}
$id = $_GET['id'];

// Obtener datos actuales
$sql_get = "SELECT * FROM productos WHERE id_producto = $id";
$res = $conn->query($sql_get);
if ($res->num_rows == 0) exit;
$prod = $res->fetch_assoc();

// PROCESAR FORMULARIO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $sku = $_POST['sku'];
    $precio = $_POST['precio'];
    $mayoreo = $_POST['mayoreo'];
    $stock = $_POST['stock'];
    $desc = $_POST['descripcion'];
    $categoria = $_POST['categoria'];

    // LÓGICA DE IMAGEN
    $nombre_imagen = $prod['imagen_url']; // Por defecto conservamos la actual

    // OPCIÓN A: ¿Subieron archivo nuevo?
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $archivo = $_FILES['foto'];
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombre_imagen = "prod_" . time() . "." . $extension;
        move_uploaded_file($archivo['tmp_name'], "../img/" . $nombre_imagen);
    }
    // OPCIÓN B: ¿Pegaron URL nueva?
    elseif (!empty($_POST['url_imagen'])) {
        $url = $_POST['url_imagen'];
        $ext = pathinfo($url, PATHINFO_EXTENSION);
        if(empty($ext) || strlen($ext) > 4) $ext = 'jpg'; 
        
        $nombre_imagen = "prod_web_" . time() . "." . $ext;
        $ruta_destino = "../img/" . $nombre_imagen;

        $imagen_descargada = file_get_contents($url);
        if($imagen_descargada !== false){
            file_put_contents($ruta_destino, $imagen_descargada);
        }
    }

    $sql = "UPDATE productos SET 
            nombre='$nombre', 
            sku_barras='$sku', 
            precio_unitario=$precio, 
            precio_mayoreo=$mayoreo, 
            stock_actual=$stock, 
            descripcion='$desc', 
            id_categoria=$categoria,
            imagen_url='$nombre_imagen'  
            WHERE id_producto=$id";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
            Swal.fire('¡Actualizado!', 'Producto modificado correctamente.', 'success').then(() => {
                window.location = 'productos.php';
            });
        </script>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}
?>

<div class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">✏️ Editar Producto</h2>
        <a href="productos.php" class="btn btn-outline-secondary">Cancelar</a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-4">
            <form action="editar_producto.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nombre</label>
                                <input type="text" name="nombre" id="inputNombre" class="form-control" value="<?php echo $prod['nombre']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">SKU / Código</label>
                                <input type="text" name="sku" class="form-control" value="<?php echo $prod['sku_barras']; ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3" required><?php echo $prod['descripcion']; ?></textarea>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bg-light h-100 border-dashed">
                            <div class="card-body text-center">
                                <label class="form-label fw-bold mb-2">Imagen Actual</label>
                                <div class="mb-3 d-flex justify-content-center align-items-center bg-white border" style="height: 100px;">
                                    <?php if($prod['imagen_url'] != 'default.jpg' && file_exists("../img/".$prod['imagen_url'])): ?>
                                        <img src="../img/<?php echo $prod['imagen_url']; ?>" style="max-height: 100%; max-width: 100%;">
                                    <?php else: ?>
                                        <i class="fas fa-image fa-3x text-secondary opacity-25"></i>
                                    <?php endif; ?>
                                </div>
                                
                                <hr>
                                <label class="form-label small fw-bold text-primary">¿Cambiar Imagen?</label>
                                
                                <input type="file" name="foto" class="form-control form-control-sm mb-2" accept="image/*">
                                
                                <div class="text-muted small mb-2">- O Pegar URL -</div>
                                
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text"><i class="fas fa-link"></i></span>
                                    <input type="text" name="url_imagen" class="form-control" placeholder="http://...">
                                </div>

                                <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="buscarEnGoogle()">
                                    <i class="fab fa-google"></i> Buscar Referencia
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-success">Precio Unitario ($)</label>
                        <input type="number" step="0.50" name="precio" class="form-control" value="<?php echo $prod['precio_unitario']; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-warning">Precio Mayoreo ($)</label>
                        <input type="number" step="0.50" name="mayoreo" class="form-control" value="<?php echo $prod['precio_mayoreo']; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Stock</label>
                        <input type="number" name="stock" class="form-control" value="<?php echo $prod['stock_actual']; ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Categoría</label>
                    <select name="categoria" class="form-select" required>
                        <?php
                        $cats = $conn->query("SELECT * FROM categorias");
                        while($c = $cats->fetch_assoc()){
                            $selected = ($c['id_categoria'] == $prod['id_categoria']) ? 'selected' : '';
                            echo "<option value='".$c['id_categoria']."' $selected>".$c['nombre']."</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function buscarEnGoogle() {
    let nombre = document.getElementById('inputNombre').value;
    if (nombre.trim() === "") {
        Swal.fire('Espera', 'El nombre no puede estar vacío', 'warning');
        return;
    }
    let url = "https://www.google.com/search?tbm=isch&q=" + encodeURIComponent(nombre + " componente electronico");
    window.open(url, '_blank');
}
</script>

</div>
</body>
</html>