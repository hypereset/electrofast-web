<?php
include '../php/conexion.php';
include 'header.php';

// Seguridad: Solo Admin
if($_SESSION['rol'] != 1){
    echo "<script>window.location='index.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sku = $_POST['sku'];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $mayoreo = $_POST['mayoreo'];
    $stock = $_POST['stock'];
    $desc = $_POST['descripcion'];
    $categoria = $_POST['categoria'];
    
    $nombre_imagen = 'default.jpg'; 

    // --- LÓGICA INTELIGENTE DE IMAGEN ---
    
    // OPCIÓN A: ¿Subieron un archivo?
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $archivo = $_FILES['foto'];
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombre_imagen = "prod_" . time() . "." . $extension;
        move_uploaded_file($archivo['tmp_name'], "../img/" . $nombre_imagen);
    } 
    // OPCIÓN B: ¿Pegaron una URL de internet?
    elseif (!empty($_POST['url_imagen'])) {
        $url = $_POST['url_imagen'];
        // Intentamos adivinar la extensión (jpg, png) o ponemos jpg por defecto
        $ext = pathinfo($url, PATHINFO_EXTENSION);
        if(empty($ext) || strlen($ext) > 4) $ext = 'jpg'; 
        
        $nombre_imagen = "prod_web_" . time() . "." . $ext;
        $ruta_destino = "../img/" . $nombre_imagen;

        // TRUCO: Descargamos la imagen de internet al servidor
        $imagen_descargada = file_get_contents($url);
        if($imagen_descargada !== false){
            file_put_contents($ruta_destino, $imagen_descargada);
        } else {
            $nombre_imagen = 'default.jpg'; // Falló la descarga
        }
    }

    $sql = "INSERT INTO productos (sku_barras, nombre, descripcion, precio_unitario, precio_mayoreo, stock_actual, id_categoria, imagen_url) 
            VALUES ('$sku', '$nombre', '$desc', $precio, $mayoreo, $stock, $categoria, '$nombre_imagen')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
            Swal.fire('¡Éxito!', 'Producto agregado correctamente', 'success').then(() => {
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
        <h2 class="fw-bold">✨ Nuevo Producto</h2>
        <a href="productos.php" class="btn btn-outline-secondary">Cancelar</a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-4">
            <form action="agregar_producto.php" method="POST" enctype="multipart/form-data">
                
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nombre del Producto</label>
                                <input type="text" name="nombre" id="inputNombre" class="form-control" placeholder="Ej: Arduino Uno" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Código SKU</label>
                                <input type="text" name="sku" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción Detallada</label>
                            <textarea name="descripcion" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bg-light h-100 border-dashed">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <i class="fas fa-camera fa-2x text-secondary mb-2"></i>
                                <label class="fw-bold mb-2">Imagen del Producto</label>
                                
                                <input type="file" name="foto" class="form-control form-control-sm mb-2" accept="image/*">
                                
                                <div class="text-muted small mb-2">- O -</div>
                                
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text"><i class="fas fa-link"></i></span>
                                    <input type="text" name="url_imagen" class="form-control" placeholder="Pegar link de imagen...">
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
                        <input type="number" step="0.50" name="precio" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-warning">Precio Mayoreo ($)</label>
                        <input type="number" step="0.50" name="mayoreo" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Stock Inicial</label>
                        <input type="number" name="stock" class="form-control" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Categoría</label>
                    <select name="categoria" class="form-select" required>
                        <?php
                        $cats = $conn->query("SELECT * FROM categorias");
                        while($c = $cats->fetch_assoc()){
                            echo "<option value='".$c['id_categoria']."'>".$c['nombre']."</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-proto btn-lg fw-bold">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function buscarEnGoogle() {
    let nombre = document.getElementById('inputNombre').value;
    if (nombre.trim() === "") {
        Swal.fire('Espera', 'Escribe primero el nombre del producto', 'warning');
        return;
    }
    // Abre una pestaña nueva buscando imágenes de ese producto
    let url = "https://www.google.com/search?tbm=isch&q=" + encodeURIComponent(nombre + " componente electronico");
    window.open(url, '_blank');
}
</script>

</div>
</body>
</html>