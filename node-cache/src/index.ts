import express from 'express';
import Redis from 'redis';
import axios from 'axios';

const app = express();
const PORT = Number(process.env.PORT || 5000);

// Configuration via environment variables for containerized environments
const REDIS_URL = process.env.REDIS_URL || 'redis://localhost:6379';
const API_BASE_URL = (process.env.API_BASE_URL || 'http://localhost:8000').replace(/\/$/, '');

// Connect to Redis
const redisClient = Redis.createClient({ url: REDIS_URL });

redisClient.on('error', (err) => console.error('Redis error:', err));

// We'll explicitly connect before starting the server. If Redis isn't available
// the server will still start but will log the connection error; route handlers
// will handle Redis errors gracefully.


// Middleware
app.use(express.json());

// Test route
app.get('/', (req, res) => {
    res.send('Node.js Redis Cache is running');
});

// Cached posts route
app.get('/cache/posts', async (req, res) => {
    const cacheKey = 'posts';

    try {
        // Check Redis cache
        const cachedData = await redisClient.get(cacheKey);
        if (cachedData) {
            console.log('Cache hit');
            return res.json(JSON.parse(cachedData));
        }

        // Cache miss — fetch from API
    const response = await axios.get(`${API_BASE_URL}/api/posts`);
        const posts = response.data;

        // Save to Redis for 10 minutes
        await redisClient.setEx(cacheKey, 600, JSON.stringify(posts));

        console.log('Cache miss — data fetched');
        res.json(posts);
    } catch (err) {
        console.error(err);
        res.status(500).json({ error: 'Failed to fetch posts' });
    }
});

app.get('/cache/posts/:id', async (req, res) => {
    const postId = req.params.id;
    const cacheKey = `post:${postId}`;

    try {
        // 1️⃣ Check Redis first
        const cachedPost = await redisClient.get(cacheKey);
        if (cachedPost) {
            console.log(`Cache hit for post ${postId}`);
            return res.json(JSON.parse(cachedPost));
        }

        // 2️⃣ Cache miss — fetch from Lumen API
    const response = await axios.get(`${API_BASE_URL}/api/posts/${postId}`);
        const post = response.data;

        // 3️⃣ Store in Redis for 10 minutes
        await redisClient.setEx(cacheKey, 600, JSON.stringify(post));

        console.log(`Cache miss — fetched post ${postId}`);
        res.json(post);
    } catch (err) {
        console.error(`Error fetching post ${postId}:`, err);
        res.status(500).json({ error: `Failed to fetch post ${postId}` });
    }
});

// Start server after attempting to connect to Redis so we can log the outcome.
(async () => {
    try {
        await redisClient.connect();
        console.log('Connected to Redis');
    } catch (err) {
        console.error('Failed to connect to Redis:', err);
    }

    // Start server
    app.listen(PORT, () => {
        console.log(`Server running on http://0.0.0.0:${PORT}`);
        console.log(`Using Redis at ${REDIS_URL}`);
        console.log(`Proxying API to ${API_BASE_URL}`);
    });
})();
