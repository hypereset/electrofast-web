import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    // 1. Le decimos que la salida sea la carpeta 'css' existente
    outDir: 'css',
    
    // 2. ¡IMPORTANTE! Le decimos que NO borre los otros archivos que ya tengas en 'css'
    emptyOutDir: false,
    
    rollupOptions: {
      input: './main.css',
      output: {
        // 3. Forzamos a que el archivo de salida se llame siempre igual (sin códigos raros)
        assetFileNames: 'estilos_final.[ext]', 
      },
    },
  },
  server: {
    host: true,
    port: 5173,
    strictPort: true,
    origin: 'http://localhost:5173',
  },
});