</main> 

<footer class="footer p-10 bg-neutral text-neutral-content mt-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 justify-items-center md:justify-items-start text-center md:text-left">
  
  <aside class="flex flex-col items-center md:items-start w-full">
    <div class="font-display font-bold text-3xl mb-2 flex items-center gap-2 text-primary">
        <span class="text-white">Proto</span>Hub
    </div>
    <p class="font-sans text-sm opacity-80 max-w-xs leading-relaxed">
      Innovación entregada en minutos.<br/>
      Tu aliado tecnológico en Coacalco para salvar el semestre.
    </p>
    <div class="flex gap-4 mt-4">
        <a class="link link-hover opacity-60 hover:opacity-100 transition-opacity p-2"><i class="fab fa-facebook fa-xl"></i></a>
        <a class="link link-hover opacity-60 hover:opacity-100 transition-opacity p-2"><i class="fab fa-instagram fa-xl"></i></a>
        <a class="link link-hover opacity-60 hover:opacity-100 transition-opacity p-2"><i class="fab fa-tiktok fa-xl"></i></a>
    </div>
  </aside> 

  <nav class="flex flex-col gap-2 w-full">
    <h6 class="footer-title opacity-100 text-white uppercase tracking-widest border-b border-white/10 pb-2 mb-2 w-full md:w-auto">Tienda</h6> 
    <a href="catalogo.php" class="link link-hover py-1">Catálogo Completo</a>
    <a href="paquetes.php" class="link link-hover py-1">Kits Escolares</a>
    <a href="#" class="link link-hover py-1">Novedades</a>
    <li><a href="solicitar_producto.php" class="link link-hover text-warning font-bold">Solicitar Producto Nuevo</a></li>
  </nav> 

  <nav class="flex flex-col gap-2 w-full">
    <h6 class="footer-title opacity-100 text-white uppercase tracking-widest border-b border-white/10 pb-2 mb-2 w-full md:w-auto">Ayuda</h6> 
    <a href="faq.php" class="link link-hover py-1">Preguntas Frecuentes</a>
    <a href="nosotros.php" class="link link-hover py-1">¿Quiénes somos?</a>
    <a href="terminos.php" class="link link-hover py-1">Términos y Condiciones</a>
    <a href="#" class="link link-hover py-1">Política de Devoluciones</a>
  </nav>

  <nav class="flex flex-col gap-2 w-full">
    <h6 class="footer-title opacity-100 text-white uppercase tracking-widest border-b border-white/10 pb-2 mb-2 w-full md:w-auto">Contacto</h6> 
    
    <div class="flex flex-col gap- text-sm opacity-70">
        <span class="font-bold text-white">Sucursal Central:</span>
        <span class="text-sm opacity-70 mb-1">Blvd. de las Rosas No.45, Coacalco</span>
        <span class="text-sm opacity-70 mb-1">Lunes a Viernes: 9am - 7pm</span>
        <span class="text-sm opacity-70 mb-1">Sabado a Domingo: 10am - 5pm</span>
   

    </div>
    
    <a href="https://wa.me/5215611676809?text=Hola%20ProtoHub." target="_blank" class="btn btn-success btn-sm text-white shadow-lg w-full mt-2 max-w-[200px] mx-auto md:mx-0">
        <i class="fab fa-whatsapp text-lg mr-1"></i> WhatsApp
    </a>
    <a href="mailto:contact@protohub.com" class="link link-hover text-sm mt-2 py-1 break-all">contacto@protohub.com</a>
  </nav>
</footer>

<script>
// Función Global para que sirva con onsubmit="..."
window.agregarAlCarritoAjax = function(formElement) {
    event.preventDefault(); 

    const btn = formElement.querySelector('button');
    const originalContent = btn.innerHTML;
    
    // Feedback de carga
    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';
    btn.disabled = true;

    const formData = new FormData(formElement);
    formData.append('ajax', 'true'); 

    fetch('carrito.php', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        if (!response.ok) throw new Error("Error de red: " + response.status);
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            
            // 1. Actualizar Badges Rojos
            document.querySelectorAll('.cart-badge-count').forEach(b => {
                b.innerText = data.total_items;
                b.classList.remove('hidden');
                b.classList.add('animate-bounce');
                setTimeout(() => b.classList.remove('animate-bounce'), 1000);
            });

            // 2. Actualizar Texto Grande "Items"
            document.querySelectorAll('.cart-item-count-text').forEach(t => {
                t.innerText = data.total_items + " Items";
            });
            
            // 3. Alerta
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false, 
                timer: 2000, timerProgressBar: false,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            Toast.fire({ icon: 'success', title: '¡Agregado al carrito!' });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({icon: 'error', title: 'Error', text: 'No se pudo agregar.'});
    })
    .finally(() => {
        btn.innerHTML = originalContent;
        btn.disabled = false;
    });
};

// Inicializador de respaldo (por si usas formularios sin onsubmit)
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[action="carrito.php"]');
    forms.forEach(form => {
        if (form.querySelector('input[name="agregar_nuevo"]') && !form.hasAttribute('onsubmit')) {
            form.addEventListener('submit', function(e) {
                agregarAlCarritoAjax(this);
            });
        }
    });
});
</script>

</body>
</html>