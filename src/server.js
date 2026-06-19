const app = require('./app');
const env = require('./config/env');
const connectDB = require('./config/db');

let server;

(async () => {
  try {
    await connectDB();
    server = app.listen(env.port, () => {
      console.log(`🚀 Server running in ${env.nodeEnv} mode on http://localhost:${env.port}`);
      console.log(`   Auth enabled: ${env.authEnabled}`);
    });
  } catch (err) {
    console.error('Failed to start server:', err.message);
    process.exit(1);
  }
})();

// Graceful shutdown
const shutdown = (signal) => {
  console.log(`\n${signal} received. Shutting down...`);
  if (server) server.close(() => process.exit(0));
  else process.exit(0);
};

process.on('SIGINT', () => shutdown('SIGINT'));
process.on('SIGTERM', () => shutdown('SIGTERM'));
process.on('unhandledRejection', (reason) => {
  console.error('Unhandled Rejection:', reason);
});
