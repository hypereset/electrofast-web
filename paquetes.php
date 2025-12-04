<?php
include 'php/conexion.php';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-12 max-w-6xl">
    
    <div class="text-center mb-12">
        <h1 class="text-4xl font-display font-bold mb-4 text-base-content">
            <span class="text-primary">Kits</span> y Proyectos
        </h1>
        <p class="text-lg opacity-70 max-w-2xl mx-auto">
            Todo lo que necesitas para tu materia en una sola caja. <br>Ahorra tiempo y dinero con nuestros paquetes pre-armados.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <?php
        // --- FILTRO MAESTRO ANTI-BASURA ---
        // Buscamos productos que sean activos Y que su SKU empiece con 'KIT-' o 'PROY-'
        // Así evitamos que salgan resistencias o leds sueltos aunque estén en la categoría equivocada.
        
        $sql = "SELECT p.*, c.nombre as nombre_cat 
                FROM productos p 
                LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                WHERE p.estado = 'activo' 
                AND (p.sku_barras LIKE 'KIT-%' OR p.sku_barras LIKE 'PROY-%')
                ORDER BY p.precio_unitario DESC"; 
        
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // Limpieza de descripción para hacer la lista bonita
                $desc_corta = str_replace(["Incluye:", "Incluye"], "", $row['descripcion']);
                $items = explode(",", $desc_corta);
                
                // Imagen segura
                $ruta_img = "img/" . $row['imagen_url'];
                $img = ($row['imagen_url'] != 'default.jpg' && file_exists($ruta_img)) ? $ruta_img : ((file_exists("img/default.jpg")) ? "img/default.jpg" : "https://via.placeholder.com/200?text=Kit");
                
                // Lógica visual: ¿Es Proyecto o Herramienta? (Basado en el SKU esta vez)
                $es_proyecto = (strpos($row['sku_barras'], 'PROY') !== false);
                $badge_cat = $es_proyecto ? 'badge-secondary' : 'badge-accent';
                $txt_cat = $es_proyecto ? 'PROYECTO' : 'KIT ESCOLAR';
        ?>
            <div class="card card-side bg-base-100 shadow-xl border border-base-200 flex-col sm:flex-row transition-all hover:-translate-y-1 hover:shadow-2xl duration-300 overflow-hidden group">
                
                <figure class="sm:w-1/3 bg-base-200 p-6 flex items-center justify-center relative">
                    <img src="<?php echo $img; ?>" alt="<?php echo $row['nombre']; ?>" class="object-contain h-40 w-40 mix-blend-multiply dark:mix-blend-normal group-hover:scale-110 transition-transform duration-500" loading="lazy" />
                    <div class="absolute top-3 left-3 badge <?php echo $badge_cat; ?> badge-sm text-white font-bold tracking-wider shadow-sm"><?php echo $txt_cat; ?></div>
                </figure>
                
                <div class="card-body sm:w-2/3 p-6">
                    <h2 class="card-title font-display text-primary text-xl leading-tight">
                        <a href="producto.php?id=<?php echo $row['id_producto']; ?>" class="hover:underline">
                            <?php echo $row['nombre']; ?>
                        </a>
                    </h2>
                    
                    <div class="py-2 flex-grow">
                        <ul class="list-disc list-inside text-sm opacity-80 space-y-1">
                            <?php 
                            $count = 0;
                            foreach($items as $item){
                                $item_limpio = trim(str_replace('.', '', $item));
                                if(!empty($item_limpio) && $count < 3) {
                                    echo "<li>".$item_limpio."</li>";
                                    $count++;
                                }
                            }
                            // Si hay más de 3 cosas, poner "...y más"
                            if(count($items) > 3) echo "<li class='list-none text-xs opacity-60 italic mt-1 ml-2'>+ " . (count($items) - 3) . " componentes más...</li>";
                            ?>
                        </ul>
                    </div>
                    
                    <div class="card-actions justify-between items-end mt-4 pt-4 border-t border-base-200">
                        <div class="flex flex-col">
                            <span class="text-xs opacity-50 line-through">Antes: $<?php echo number_format($row['precio_unitario'] * 1.15, 2); ?></span>
                            <div class="flex items-baseline gap-1">
                                <span class="text-2xl font-black font-display text-success">$<?php echo $row['precio_unitario']; ?></span>
                                <span class="text-xs font-bold text-success">MXN</span>
                            </div>
                        </div>
                        
                        <form action="carrito.php" method="POST">
                            <input type="hidden" name="agregar_nuevo" value="true">
                            <input type="hidden" name="id_producto" value="<?php echo $row['id_producto']; ?>">
                            <input type="hidden" name="cantidad" value="1">
                            <input type="hidden" name="return_url" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                            <button class="btn btn-primary btn-sm shadow-md gap-2 font-bold">
                                Agregar <i class="fas fa-cart-plus"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php 
            }
        } else {
            echo "<div class='col-span-1 lg:col-span-2 alert alert-info shadow-lg'>
                    <i class='fas fa-info-circle text-2xl'></i>
                    <div>
                        <h3 class='font-bold'>Aún no hay kits activos</h3>
                        <div class='text-xs'>Estamos armando los mejores paquetes para ti. Vuelve pronto.</div>
                    </div>
                  </div>";
        }
        ?>
    </div>

    <div class="mt-16 mb-8">
        <div class="hero bg-primary text-primary-content rounded-3xl p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full opacity-10" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 20px 20px;"></div>
            
            <div class="hero-content text-center relative z-10">
                <div class="max-w-md">
                    <h2 class="text-3xl font-black font-display mb-4">¿Eres Jefe de Grupo? 🎓</h2>
                    <p class="mb-6 text-lg opacity-90">Armamos el kit exacto para tu materia y les damos precio de mayoreo a todo tu salón.</p>
                    <a href="https://wa.me/5215611676809?text=Hola%20ProtoHub,%20soy%20jefe%20de%20grupo%20y%20quiero%20cotizar..." target="_blank" class="btn btn-white text-primary font-bold shadow-lg border-0 hover:scale-105 transition-transform gap-2">
                        <i class="fab fa-whatsapp text-xl"></i> Cotizar Paquete Grupal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>