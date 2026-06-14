const express = require('express');
const path = require('path');
const cors = require('cors');

const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors());
app.use(express.json());

// Serve static frontend files
app.use(express.static(path.join(__dirname, 'frontend')));

// Mock Vercel serverless functions locally
app.all('/api/config', (req, res) => require('./api/config')(req, res));
app.all('/api/allocation/run', (req, res) => require('./api/allocation/run')(req, res));
app.all('/api/hostels', (req, res) => require('./api/hostels/index')(req, res));
app.all('/api/rooms', (req, res) => require('./api/rooms/index')(req, res));
app.all('/api/feedback', (req, res) => require('./api/feedback/index')(req, res));
app.all('/api/reports', (req, res) => require('./api/reports/index')(req, res));

// Fallback to index.html for SPA-like behavior
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'frontend', 'index.html'));
});

app.listen(PORT, () => {
  console.log(`Local development server running at http://localhost:${PORT}`);
  console.log('Ensure you have set your Supabase keys in the .env file!');
});
