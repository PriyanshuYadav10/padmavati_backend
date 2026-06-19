const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const morgan = require('morgan');

const env = require('./config/env');
const apiRoutes = require('./routes');
const { notFound, errorHandler } = require('./middleware/error.middleware');

const app = express();

// Security & parsing
app.use(helmet());
app.use(
  cors({
    origin: env.corsOrigin === '*' ? true : env.corsOrigin.split(',').map((o) => o.trim()),
  })
);
app.use(express.json({ limit: '1mb' }));
app.use(express.urlencoded({ extended: true }));

if (env.nodeEnv !== 'test') {
  app.use(morgan(env.nodeEnv === 'development' ? 'dev' : 'combined'));
}

// Root
app.get('/', (req, res) => {
  res.json({ success: true, message: 'Padmavati Bangles Vendor Management API', docs: '/api/health' });
});

// API
app.use('/api', apiRoutes);

// Errors (must be last)
app.use(notFound);
app.use(errorHandler);

module.exports = app;
