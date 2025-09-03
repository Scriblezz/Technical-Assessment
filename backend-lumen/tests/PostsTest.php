<?php

namespace Tests;

class PostsTest extends TestCase
{
    public function test_get_posts_returns_ok_and_array()
    {
        $this->get('/api/posts');

        $this->assertEquals(200, $this->response->getStatusCode());

        $content = json_decode($this->response->getContent(), true);
        $this->assertIsArray($content);
        // At least one post exists in the file-backed store
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey('id', $content[0]);
        $this->assertArrayHasKey('title', $content[0]);
    }

    public function test_add_post_requires_auth()
    {
        $this->post('/api/posts', ['title' => 'Test', 'content' => 'Body']);
        $this->assertEquals(401, $this->response->getStatusCode());
    }

    public function test_add_post_with_jwt_succeeds()
    {
        // Login using seeded user
        $this->post('/api/login', ['email' => 'test@example.com', 'password' => 'password']);
        $this->assertEquals(200, $this->response->getStatusCode());
        $data = json_decode($this->response->getContent(), true);
        $token = $data['token'] ?? '';

        $this->post('/api/posts', ['title' => 'From Test', 'content' => 'Hello'], ['Authorization' => 'Bearer '.$token]);
        $this->assertEquals(201, $this->response->getStatusCode());
    }
}
