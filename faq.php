<?php include 'php/conexion.php'; include 'includes/header.php'; ?>

<div class="container mx-auto px-4 py-12 max-w-4xl">
    
    <div class="text-center mb-12">
        <h1 class="text-4xl font-display font-bold mb-4">Preguntas Frecuentes</h1>
        <p class="opacity-70">Resolvemos tus dudas para que tu proyecto salga perfecto.</p>
    </div>

    <div class="flex flex-col gap-4">
        <div class="collapse collapse-plus bg-base-100 border border-base-200 shadow-sm rounded-box">
            <input type="radio" name="my-accordion-3" checked="checked" /> 
            <div class="collapse-title text-xl font-medium font-display">
                🚚 ¿Hacen envíos fuera de Coacalco?
            </div>
            <div class="collapse-content opacity-80"> 
                <p><strong>Nuestra prioridad es Coacalco.</strong> Garantizamos entregas rápidas en zonas como Villa de las Flores, San Rafael y Parque Residencial. También cubrimos zonas cercanas de Tultitlán y Ecatepec con un tiempo ligeramente mayor.</p>
            </div>
        </div>

        <div class="collapse collapse-plus bg-base-100 border border-base-200 shadow-sm rounded-box">
            <input type="radio" name="my-accordion-3" /> 
            <div class="collapse-title text-xl font-medium font-display">
                🏪 ¿Puedo recoger mi pedido personalmente?
            </div>
            <div class="collapse-content opacity-80"> 
                <p>¡Sí! Puedes recoger sin costo en nuestra <strong>Sucursal Central</strong> (Blvd de las Rosas 45).<br>Horarios: L-V 8am-7pm. <span class="text-error font-bold">Importante:</span> Tienes 3 días para recoger tu pedido.</p>
            </div>
        </div>

        <div class="collapse collapse-plus bg-base-100 border border-base-200 shadow-sm rounded-box">
            <input type="radio" name="my-accordion-3" /> 
            <div class="collapse-title text-xl font-medium font-display">
                💵 ¿Por qué no puedo pagar en efectivo?
            </div>
            <div class="collapse-content opacity-80"> 
                <p>Por seguridad de nuestros repartidores, los pedidos mayores a <strong>$1,000.00 MXN</strong> deben pagarse mediante Transferencia o Depósito previo.</p>
            </div>
        </div>

        <div class="collapse collapse-plus bg-base-100 border border-base-200 shadow-sm rounded-box">
            <input type="radio" name="my-accordion-3" /> 
            <div class="collapse-title text-xl font-medium font-display">
                🔧 ¿Tienen garantía los componentes?
            </div>
            <div class="collapse-content opacity-80"> 
                <p>Sí. Tienes <strong>48 horas</strong> para reportar defectos de fábrica. No aplica en componentes quemados por mala conexión.</p>
            </div>
        </div>
    </div>

    <div class="mt-12 text-center bg-base-200 p-8 rounded-2xl">
        <h3 class="font-bold text-lg mb-2">¿Aún tienes dudas?</h3>
        <p class="text-sm opacity-70 mb-4">Escríbenos directamente y te respondemos en minutos.</p>
        <a href="https://wa.me/5215611676809?text=Hola%20ProtoHub,%20tengo%20una%20duda%20." target="_blank" class="btn btn-success text-white gap-2">
            <i class="fab fa-whatsapp text-lg"></i> WhatsApp Soporte
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>