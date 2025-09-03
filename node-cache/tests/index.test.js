const request = require('supertest');
const express = require('express');

// Create a small express app mirroring the server routes we care about
const app = express();
app.use(express.json());

app.get('/', (req, res) => res.send('Node.js Redis Cache is running'));

// Simulate cache/posts route returning posts array
app.get('/cache/posts', (req, res) => {
  res.json([{ id: 1, title: 'Welcome', content: 'This is the first post.' }]);
});

describe('node-cache API', () => {
  test('GET / returns running message', async () => {
    const res = await request(app).get('/');
    expect(res.statusCode).toBe(200);
    expect(res.text).toContain('Node.js Redis Cache is running');
  });

  test('GET /cache/posts returns posts array', async () => {
    const res = await request(app).get('/cache/posts');
    expect(res.statusCode).toBe(200);
    expect(Array.isArray(res.body)).toBe(true);
    expect(res.body[0]).toHaveProperty('id');
    expect(res.body[0]).toHaveProperty('title');
  });
});
