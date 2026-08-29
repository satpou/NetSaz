module.exports = {
  apps: [
    {
      name: 'primabill-whatsapp',
      script: 'server.js',
      cwd: './whatsapp-bridge',
      watch: false,
      env: {
        NODE_ENV: 'production',
        PORT: 3001,
        PUPPETEER_EXECUTABLE_PATH: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
      },
    },
  ],
};
