/** @type {import('tailwindcss').Config} */
export default {
  content: [
    // 1. Archivos PHP en la raíz (index.php, catalogo.php, etc.)
    "./*.php",
    
    // 2. Archivos dentro de carpetas específicas
    "./admin/**/*.php",
    "./includes/**/*.php",
    "./php/**/*.php",
    
    // 3. Scripts JS si tienes lógica que agrega clases dinámicamente
    "./js/**/*.js",
    "./*.js"
  ],
  theme: {
    extend: {
      fontFamily: { 
        sans: ['"Inter"', 'sans-serif'], 
        display: ['"Sora"', 'sans-serif'] 
      }
    },
  },
  // Cargamos DaisyUI
  plugins: [require("daisyui")],
  
  // Configuración de temas de DaisyUI
  daisyui: { 
    themes: ["corporate", "night"]
  },
}