/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./public/**/*.php', './src/**/*.php'],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f0f7ff',
          100: '#e0effe',
          200: '#bae0fd',
          300: '#7cc7fb',
          400: '#38aaf7',
          500: '#0e90e9',
          600: '#0070c7',
          700: '#0058a3',
          800: '#004a87',
          900: '#063f6f',
          950: '#04284d'
        },
        dswd: {
          blue: '#003087',
          gold: '#ffc72c',
          red: '#c8102e',
          dark: '#001a4d',
          slate: '#1e293b'
        }
      },
      fontFamily: {
        sans: ['Inter', 'Outfit', 'system-ui', 'sans-serif'],
        display: ['Outfit', 'Inter', 'sans-serif']
      },
      boxShadow: {
        premium:
          '0 10px 30px -5px rgba(0, 48, 135, 0.1), 0 4px 10px -5px rgba(0, 48, 135, 0.05)',
        glass: '0 8px 32px 0 rgba(31, 38, 135, 0.07)'
      }
    }
  },
  plugins: []
};

