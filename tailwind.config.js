module.exports = {
    content: [
      './resources/views/**/*.blade.php',
      './app/Filament/**/*.php',
      './vendor/filament/**/*.blade.php',
    ],
    theme: {
      extend: {
        spacing: {
          '128': '32rem',
        },
        colors: {
          'maintenance-blue': '#1e3a8a',
          'status-green': '#059669',
          'status-red': '#dc2626',
        }
      }
    }
  }
