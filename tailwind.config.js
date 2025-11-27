/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./src/**/*.php",      // <--- ESTO ES LO IMPORTANTE
    "./src/**/*.html",
    "./src/**/*.js"
  ],
  theme: {
    extend: {
      fontFamily: { 
        sans: ['"Inter"', 'sans-serif'], 
        display: ['"Sora"', 'sans-serif'] 
      }
    },
  },
  plugins: [require("daisyui")],
  daisyui: { 
    themes: ["corporate", "night"]
  },
}