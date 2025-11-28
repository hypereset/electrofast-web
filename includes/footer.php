</main> 

<footer class="footer p-10 bg-neutral text-neutral-content mt-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 justify-items-center md:justify-items-start text-center md:text-left">
  <aside class="w-full"><p class="font-bold text-xl">ProtoHub</p><p>Innovación entregada.</p></aside> 
  <nav><h6 class="footer-title">Legal</h6><a href="terminos.php" class="link link-hover">Términos</a></nav>
  <nav><h6 class="footer-title">Contacto</h6><a href="#" class="link link-hover">Soporte</a></nav>
</footer>

<script>
window.agregarAlCarritoAjax = function(formElement) {
    event.preventDefault(); 

    const btn = formElement.querySelector('button');
    const originalContent = btn.innerHTML;
    
    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';
    btn.disabled = true;

    const formData = new FormData(formElement);
    formData.append('ajax', 'true'); 

    fetch('carrito.php', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        if (!response.ok) throw new Error("Error de red");
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            // Actualizar Badges
            document.querySelectorAll('.cart-badge-count').forEach(b => {
                b.innerText = data.total_items;
                b.classList.remove('hidden');
                b.classList.add('animate-bounce');
                setTimeout(() => b.classList.remove('animate-bounce'), 1000);
            });
            // Actualizar Texto del Menú
            document.querySelectorAll('.cart-item-count-text').forEach(t => {
                t.innerText = data.total_items + " Items";
            });

            Swal.fire({
                toast: true, position: 'top-end', icon: 'success', title: '¡Agregado!',
                showConfirmButton: false, timer: 2000
            });
        }
    })
    .catch(error => {
        console.error('Error AJAX:', error);
        Swal.fire({icon: 'error', title: 'Error', text: 'No se pudo agregar.'});
    })
    .finally(() => {
        btn.innerHTML = originalContent;
        btn.disabled = false;
    });
};
</script>

</body>
</html>