</main> </div> </div> <script>
    const sidebar = document.getElementById('sidebar');
    const texts = document.querySelectorAll('.sidebar-text');
    const icon = document.getElementById('toggleIcon');
    
    // Recuperar estado
    const isCollapsed = localStorage.getItem('admin-sidebar-collapsed') === 'true';
    if (isCollapsed) aplicarColapso(true);

    function toggleSidebar() {
        const collapsed = sidebar.classList.contains('w-20');
        aplicarColapso(!collapsed);
    }

    function aplicarColapso(colapsar) {
        if (colapsar) {
            sidebar.classList.replace('w-64', 'w-20');
            texts.forEach(el => el.classList.add('hidden'));
            if(icon) icon.classList.replace('fa-chevron-left', 'fa-chevron-right');
            localStorage.setItem('admin-sidebar-collapsed', 'true');
        } else {
            sidebar.classList.replace('w-20', 'w-64');
            texts.forEach(el => el.classList.remove('hidden'));
            if(icon) icon.classList.replace('fa-chevron-right', 'fa-chevron-left');
            localStorage.setItem('admin-sidebar-collapsed', 'false');
        }
    }

    function toggleMobileSidebar() {
        sidebar.classList.toggle('-translate-x-full');
        sidebar.classList.toggle('absolute');
        sidebar.classList.toggle('h-full');
    }
</script>

</body>
</html>