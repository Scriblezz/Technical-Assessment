<?php

namespace Tests;

class AuthTest extends TestCase
{
    public function test_login_with_invalid_creds_returns_401()
    {
        $this->post('/api/login', ['email' => 'nope@example.com', 'password' => 'wrong']);

        $this->assertEquals(401, $this->response->getStatusCode());
    }

    public function test_register_validation()
    {
        // Missing fields should return 422 validation error
        $this->post('/api/register', []);
        $this->assertEquals(422, $this->response->getStatusCode());
    }
}
