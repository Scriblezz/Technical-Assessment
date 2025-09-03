import React, { useState, useEffect } from 'react';
import axios from 'axios';

const LUMEN_API = 'http://localhost:8000/api';

function App() {
  const [token, setToken] = useState(localStorage.getItem('token') || '');
  const [posts, setPosts] = useState([]);

  // auth form (register/login)
  const [authMode, setAuthMode] = useState('login'); // 'login' | 'register'
  const [authForm, setAuthForm] = useState({ name: '', email: '', password: '' });

  // new post form
  const [postForm, setPostForm] = useState({ title: '', content: '' });
  const [error, setError] = useState('');

  // when the app mounts, fetch posts and apply stored token to axios if present
  useEffect(() => {
    if (token) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    } else {
      delete axios.defaults.headers.common['Authorization'];
    }
    fetchPosts();
  }, []); // run once

  // keep axios default auth header in sync with token state
  useEffect(() => {
    if (token) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    } else {
      delete axios.defaults.headers.common['Authorization'];
    }
  }, [token]);

  const fetchPosts = async () => {
    try {
  const res = await axios.get(`${LUMEN_API}/posts`);
  // API returns array of posts
  setPosts(res.data || []);
      setError('');
    } catch (err) {
      console.error('Fetch posts error:', err);
      setError('Failed to fetch posts (is backend running?)');
    }
  };

  const handleRegister = async () => {
    try {
      await axios.post(`${LUMEN_API}/register`, {
        name: authForm.name,
        email: authForm.email,
        password: authForm.password,
      });

      // After successful register, switch to login and auto-login
      await handleLogin();
      } catch (err) {
        console.error('Register error:', err);
        const msg = err.response && err.response.data ? JSON.stringify(err.response.data) : err.message;
        setError('Registration failed: ' + msg);
      }
  };

  const handleLogin = async () => {
    try {
      const res = await axios.post(`${LUMEN_API}/login`, {
        email: authForm.email,
        password: authForm.password,
      });

      const t = res.data.token;
  setToken(t);
  localStorage.setItem('token', t);
  axios.defaults.headers.common['Authorization'] = `Bearer ${t}`;
  setError('');
  // refresh posts now that we're authenticated
  await fetchPosts();
    } catch (err) {
      console.error('Login error:', err);
      const msg = err.response && err.response.data ? JSON.stringify(err.response.data) : err.message;
      setError('Login failed: ' + msg);
    }
  };

  const handleLogout = () => {
    setToken('');
    localStorage.removeItem('token');
  delete axios.defaults.headers.common['Authorization'];
  };

  const handleAddPost = async () => {
    if (!token) {
      setError('You must be logged in to add a post');
      return;
    }

    try {
      await axios.post(`${LUMEN_API}/posts`, { title: postForm.title, content: postForm.content });

      setPostForm({ title: '', content: '' });
      await fetchPosts();
    } catch (err) {
      console.error('Add post error:', err);
      // if token invalid/expired, remove it
      if (err.response && err.response.status === 401) {
        handleLogout();
        setError('Session expired, please log in again');
        return;
      }
      setError('Failed to add post');
    }
  };

  return (
    <div style={{ padding: 20, maxWidth: 800, margin: '0 auto' }}>
      <h1>Simple Posts UI</h1>

      <section style={{ marginBottom: 20 }}>
        {!token ? (
          <div style={{ border: '1px solid #ddd', padding: 12, borderRadius: 6 }}>
            <h3>{authMode === 'login' ? 'Login' : 'Register'}</h3>

            {authMode === 'register' && (
              <input
                placeholder="Name"
                value={authForm.name}
                onChange={e => setAuthForm({ ...authForm, name: e.target.value })}
                style={{ display: 'block', marginBottom: 8 }}
              />
            )}

            <input
              placeholder="Email"
              value={authForm.email}
              onChange={e => setAuthForm({ ...authForm, email: e.target.value })}
              style={{ display: 'block', marginBottom: 8 }}
            />

            <input
              placeholder="Password"
              type="password"
              value={authForm.password}
              onChange={e => setAuthForm({ ...authForm, password: e.target.value })}
              style={{ display: 'block', marginBottom: 8 }}
            />

            {authMode === 'login' ? (
              <button onClick={handleLogin}>Login</button>
            ) : (
              <button onClick={handleRegister}>Register</button>
            )}

            <div style={{ marginTop: 8 }}>
              <button onClick={() => setAuthMode(authMode === 'login' ? 'register' : 'login')}>
                Switch to {authMode === 'login' ? 'Register' : 'Login'}
              </button>
            </div>
          </div>
        ) : (
          <div style={{ marginBottom: 12 }}>
            <strong>Logged in</strong>
            <button onClick={handleLogout} style={{ marginLeft: 12 }}>
              Logout
            </button>
          </div>
        )}
      </section>

      <section style={{ marginBottom: 20 }}>
        <h3>Add Post</h3>
        <input
          placeholder="Title"
          value={postForm.title}
          onChange={e => setPostForm({ ...postForm, title: e.target.value })}
          style={{ display: 'block', marginBottom: 8 }}
        />
        <textarea
          placeholder="Content"
          value={postForm.content}
          onChange={e => setPostForm({ ...postForm, content: e.target.value })}
          rows={4}
          style={{ display: 'block', marginBottom: 8, width: '100%' }}
        />
        <button onClick={handleAddPost}>Add Post</button>
      </section>

      <section>
        <h2>Posts</h2>
        {error && <div style={{ color: 'red' }}>{error}</div>}
        <ul>
          {posts.map(p => (
            <li key={p.id} style={{ marginBottom: 10 }}>
              <strong>{p.title}</strong>
              <div>{p.content}</div>
            </li>
          ))}
        </ul>
      </section>
    </div>
  );
}

export default App;
