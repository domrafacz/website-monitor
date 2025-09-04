/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.ts",
    "./templates/**/*.html.twig",
  ],
  darkMode: 'class',
  safelist: [
    'bg-red-500',
    'bg-red-600',
    'bg-red-700',
    'hover:bg-red-700',
    'focus:ring-red-300',
    'dark:bg-red-600',
    'dark:hover:bg-red-700',
    'dark:focus:ring-red-800',
    // Bootstrap 4 equivalents
    'bg-red-600', // btn-danger background
    'hover:bg-red-700', // btn-danger hover
    'text-red-600', // text-danger
    'text-red-700', // text-danger darker
    'border-red-600', // btn-danger border
    'hover:border-red-700', // btn-danger border hover
    'focus:outline-none',
    'focus:ring-2',
    'focus:ring-red-500',
    'focus:ring-offset-2',
    'text-white',
    'font-medium',
    'px-4',
    'py-2',
    'rounded',
    'transition-colors',
    'duration-200',
    'text-red-500'
  ],
  theme: {
    extend: {
      fontFamily: {
        'sans': ['Montserrat', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'Noto Sans', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
