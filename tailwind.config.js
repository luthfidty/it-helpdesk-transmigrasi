/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./node_modules/flowbite/**/*.js" // pastikan ini ada
  ],
  theme: {
    extend: {
      colors: {
        blue: {
          50: "#f0f9ff",
          100: "#e0f2fe",
          600: "#2563eb",
          700: "#1d4ed8",
        },
        gray: {
          600: "#4b5563",
          800: "#1f2937",
        },
      },
    },
  },
  plugins: [
    require('flowbite/plugin') // pastikan ini ada
  ],
}
