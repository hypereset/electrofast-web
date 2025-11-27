<?php
include 'php/conexion.php';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-12 max-w-6xl">
    
    <div class="text-center mb-12">
        <h1 class="text-4xl font-display font-bold mb-4 text-base-content">
            <span class="text-primary">Kits</span> Escolares
        </h1>
        <p class="text-lg opacity-70 max-w-2xl mx-auto">
            Todo lo que necesitas para tu proyecto en una sola caja. Ahorra tiempo y dinero con nuestros paquetes pre-armados.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <?php
        // Buscar categoría Kits
        $sql_cat = "SELECT id_categoria FROM categorias WHERE nombre = 'Kits Escolares' LIMIT 1";
        $res_cat = $conn->query($sql_cat);
        
        if($res_cat->num_rows > 0){
            $id_kit_cat = $res_cat->fetch_assoc()['id_categoria'];
            $sql = "SELECT * FROM productos WHERE id_categoria = $id_kit_cat AND estado = 'activo'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $desc_corta = str_replace("Incluye:", "", $row['descripcion']);
                    $items = explode(",", $desc_corta);
                    
                    $ruta_img = "img/" . $row['imagen_url'];
                    $img = ($row['imagen_url'] != 'default.jpg' && file_exists($ruta_img)) ? $ruta_img : ((file_exists("img/default.jpg")) ? "img/default.jpg" : "https://via.placeholder.com/200?text=Kit");
        ?>
            <div class="card card-side bg-base-100 shadow-xl border border-base-200 flex-col sm:flex-row transition-transform hover:-translate-y-1 duration-300">
                <figure class="sm:w-1/3 bg-base-200 p-6 flex items-center justify-center">
                    <img src="<?php echo $img; ?>" alt="<?php echo $row['nombre']; ?>" class="object-contain h-40 w-40 mix-blend-multiply dark:mix-blend-normal" />
                </figure>
                <div class="card-body sm:w-2/3">
                    <h2 class="card-title font-display text-primary">
                        <?php echo $row['nombre']; ?>
                        <div class="badge badge-secondary badge-sm">PROYECTO</div>
                    </h2>
                    
                    <div class="py-2">
                        <ul class="list-disc list-inside text-sm opacity-80 space-y-1">
                            <?php 
                            $count = 0;
                            foreach($items as $item){
                                if($count < 4) echo "<li>".trim($item)."</li>";
                                $count++;
                            }
                            if(count($items) > 4) echo "<li class='italic text-xs'>... y más componentes.</li>";
                            ?>
                        </ul>
                    </div>
                    
                    <div class="card-actions justify-between items-end mt-auto">
                        <div>
                            <span class="line-through text-xs opacity-50 block">$<?php echo number_format($row['precio_unitario'] * 1.15, 2); ?></span>
                            <span class="text-2xl font-bold font-display text-success">$<?php echo $row['precio_unitario']; ?></span>
                        </div>
                        <form action="carrito.php" method="POST">
                            <input type="hidden" name="agregar_nuevo" value="true">
                            <input type="hidden" name="id_producto" value="<?php echo $row['id_producto']; ?>">
                            <input type="hidden" name="cantidad" value="1">
                            <input type="hidden" name="return_url" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                            <button class="btn btn-primary btn-sm shadow-lg">
                                Lo quiero <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php 
                }
            } else {
                // CORRECCIÓN AQUÍ: Usamos comillas simples ' en los atributos del SVG
                echo "<div class='col-span-2 alert alert-info'><svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' class='stroke-current shrink-0 w-6 h-6'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg><span>Próximamente agregaremos kits escolares.</span></div>";
            }
        } else {
            echo "<div class='col-span-2 alert alert-warning'>Categoría de Kits no encontrada.</div>";
        }
        ?>
    </div>

    <div class="mt-16">
        <div class="hero bg-base-200 rounded-box p-8">
            <div class="hero-content text-center">
                <div class="max-w-md">
                    <h2 class="text-2xl font-bold font-display">¿Kits personalizados para tu grupo?</h2>
                    <p class="py-6 opacity-70">Si eres jefe de grupo o profesor, contáctanos. Armamos el paquete exacto de tu materia con descuento por volumen.</p>
                    <a href="https://wa.me/5215611676809?text=Hola%20ProtoHub,%20quisiera%20cotizar%20kits%20para%20mi%20grupo." target="_blank" class="btn btn-success text-white shadow-lg gap-2">
                        <i class="fab fa-whatsapp text-xl"></i> Cotizar Grupo
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>