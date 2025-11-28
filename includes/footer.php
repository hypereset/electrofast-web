</main> <footer class="footer p-10 bg-neutral text-neutral-content mt-auto">
  <aside>
    <div class="font-display font-bold text-3xl mb-2 flex items-center gap-2 text-primary">
        <span class="text-white">Proto</span>Hub
    </div>
    <p class="font-sans text-sm opacity-80 max-w-xs">
      Innovación entregada en minutos.<br/>
      Tu aliado tecnológico en Coacalco para salvar el semestre.
    </p>
    <div class="flex gap-4 mt-4">
        <a class="link link-hover opacity-60 hover:opacity-100 transition-opacity"><i class="fab fa-facebook fa-xl"></i></a>
        <a class="link link-hover opacity-60 hover:opacity-100 transition-opacity"><i class="fab fa-instagram fa-xl"></i></a>
        <a class="link link-hover opacity-60 hover:opacity-100 transition-opacity"><i class="fab fa-tiktok fa-xl"></i></a>
    </div>
  </aside> 

  <nav>
    <h6 class="footer-title opacity-100 text-white">Tienda</h6> 
    <a href="catalogo.php" class="link link-hover">Catálogo Completo</a>
    <a href="paquetes.php" class="link link-hover ">Kits Escolares</a>
    <a href="#" class="link link-hover">Novedades</a>
  </nav> 

  <nav>
    <h6 class="footer-title opacity-100 text-white">Ayuda</h6> 
    <a href="faq.php" class="link link-hover ">Preguntas Frecuentes</a>
    <a href="nosotros.php" class="link link-hover">¿Quiénes somos?</a>
    <a href="terminos.php" class="link link-hover">Términos y Condiciones</a>
    <a href="#" class="link link-hover">Política de Devoluciones</a>
  </nav>

  <nav>
    <h6 class="footer-title opacity-100 text-white">Contacto</h6>  
    <span class="text-sm opacity-70 mb-2">Dir. Blvd. de las Rosas No.45, Coacalco</span>
    <span class="text-sm opacity-70 mb-1">Lunes a Viernes: 9am - 7pm</span>
    <span class="text-sm opacity-70 mb-1">Sabado a Domingo: 10am - 5pm</span>
   
    <a href="https://wa.me/5215611676809?text=Hola%20ProtoHub." target="_blank" class="btn btn-success btn-sm text-white shadow-lg w-full">
        <i class="fab fa-whatsapp text-lg mr-1"></i> WhatsApp
    </a>
    <a href="mailto:contacto@protohub.com" class="link link-hover text-sm mt-2">contacto@protohub.com</a>
  </nav>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[action="carrito.php"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (form.querySelector('input[name="agregar_nuevo"]')) {
                e.preventDefault();
                const formData = new FormData(form);
                formData.append('ajax', 'true');

                fetch('carrito.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        
                        // 1. Actualizar Badges Rojos (Iconos)
                        const badges = document.querySelectorAll('.cart-badge-count');
                        badges.forEach(b => {
                            b.innerText = data.total_items;
                            b.classList.remove('hidden');
                            b.classList.add('animate-bounce');
                            setTimeout(() => b.classList.remove('animate-bounce'), 1000);
                        });

                        // 2. Actualizar Texto Grande (Dropdown)
                        const textCounts = document.querySelectorAll('.cart-item-count-text');
                        textCounts.forEach(span => {
                            span.innerText = data.total_items + " Items";
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
                .catch(error => console.error('Error:', error));
            }
        });
    });
});
</script>

</body>
</html>