<?php include 'php/conexion.php'; include 'includes/header.php'; ?>

<div class="hero h-[400px]" style="background-image: url('img/nosotros.jpg');">
  <div class="hero-overlay bg-opacity-80 bg-primary mix-blend-multiply"></div>
  <div class="hero-content text-center text-neutral-content">
    <div class="max-w-md">
      <h1 class="mb-5 text-5xl font-display font-bold text-white">Nuestra Historia</h1>
      <p class="mb-5 font-bold text-white text-lg">Innovación entregada en minutos.</p>
    </div>
  </div>
</div>

<div class="container mx-auto px-4 py-16 max-w-5xl">
    
    <div class="flex flex-col md:flex-row gap-12 items-center mb-16">
        <div class="md:w-1/2 prose lg:prose-xl text-base-content">
            <h2 class="font-display font-bold text-3xl text-primary mb-4">De estudiantes, para estudiantes.</h2>
            <p class="opacity-80 leading-relaxed">
                ProtoHub nació en los pasillos del <strong>Tecnológico de Estudios Superiores de Coacalco (TESCO)</strong>. 
                Todo comenzó una tarde de entrega de proyectos finales de la carrera de Ingeniería en TICS.
            </p>
            <p class="opacity-80 leading-relaxed">
                Nos dimos cuenta de un problema recurrente: perder horas valiosas viajando a otros municipios vecinos solo para conseguir un sensor o algún componente específico.
            </p>
            <div class="border-l-4 border-warning pl-4 italic opacity-70 my-4">
                "¿Por qué no crear un sistema local, rápido y tecnológico que lleve los componentes directamente a la puerta de la escuela?"
            </div>
        </div>
        
        <div class="md:w-1/2">
            <div class="mockup-window border bg-base-300 border-base-300 shadow-2xl">
                <div class="flex justify-center bg-base-200 h-64 items-center">
                    <?php if(file_exists('img/nosotros.jpg')): ?>
                        <img src="img/nosotros.jpg" class="w-full h-full object-cover" alt="Equipo">
                    <?php else: ?>
                        <i class="fas fa-university text-6xl opacity-20"></i>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="divider"></div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-12">
        <div class="card bg-base-100 shadow-xl border border-base-200 hover:border-primary transition-colors">
            <div class="card-body items-center text-center">
                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-primary mb-4">
                    <i class="fas fa-rocket text-3xl"></i>
                </div>
                <h2 class="card-title font-display">Nuestra Misión</h2>
                <p class="opacity-70">Facilitar el acceso a tecnología eliminando barreras de tiempo y distancia mediante un servicio de entrega ultrarrápido.</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-xl border border-base-200 hover:border-secondary transition-colors">
            <div class="card-body items-center text-center">
                <div class="w-16 h-16 rounded-full bg-secondary/10 flex items-center justify-center text-secondary mb-4">
                    <i class="fas fa-eye text-3xl"></i>
                </div>
                <h2 class="card-title font-display">Nuestra Visión</h2>
                <p class="opacity-70">Ser la plataforma líder de suministros tecnológicos en el Estado de México, fomentando la innovación académica.</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>