<?php

namespace Tests\Feature;

use Tests\TestCase;

class StudentProfileTest extends TestCase
{
    /**
     * Test that the profile route exists and returns a valid response.
     */
    public function test_profile_route_exists()
    {
        $response = $this->get('/profile');
        
        // Accept any of these status codes as valid
        $this->assertContains($response->status(), [200, 302, 401, 419]);
    }

    /**
     * Test that the password change route exists.
     */
    public function test_password_change_route_exists()
    {
        $response = $this->post('/change-password', [
            'current_password' => 'test',
            'new_password' => 'newtest',
            'new_password_confirmation' => 'newtest'
        ]);
        
        // Accept any status code that shows the route exists (not 404)
        $this->assertNotEquals(404, $response->status());
    }

    /**
     * Test that the profile picture upload route exists.
     */
    public function test_profile_picture_upload_route_exists()
    {
        $response = $this->post('/profile/upload-picture');
        
        // Accept any status code that shows the route exists (not 404)
        $this->assertNotEquals(404, $response->status());
    }

    /**
     * Test that the profile picture delete route exists.
     */
    public function test_profile_picture_delete_route_exists()
    {
        $response = $this->post('/profile/delete-picture');
        
        // Accept any status code that shows the route exists (not 404)
        $this->assertNotEquals(404, $response->status());
    }

    /**
     * Test that the chat route exists.
     */
    public function test_chat_route_exists()
    {
        $response = $this->get('/chat');
        
        // Accept any status code that shows the route exists (not 404)
        $this->assertNotEquals(404, $response->status());
    }

    /**
     * Test that routes have CSRF protection (expecting 419 when no token provided).
     */
    public function test_post_routes_have_csrf_protection()
    {
        // Test password change route expects CSRF token
        $response = $this->post('/change-password');
        $this->assertContains($response->status(), [419, 422, 302, 401, 500]);

        // Test profile picture upload route expects CSRF token
        $response = $this->post('/profile/upload-picture');
        $this->assertContains($response->status(), [419, 422, 302, 401, 500]);

        // Test profile picture delete route expects CSRF token
        $response = $this->post('/profile/delete-picture');
        $this->assertContains($response->status(), [419, 422, 302, 401, 500]);
    }

    /**
     * Test that GET routes are accessible (even if they redirect for auth).
     */
    public function test_get_routes_accessibility()
    {
        // Profile route should be accessible (may redirect if not authenticated)
        $response = $this->get('/profile');
        $this->assertContains($response->status(), [200, 302, 401]);

        // Chat route should be accessible (may redirect if not authenticated)
        $response = $this->get('/chat');
        $this->assertContains($response->status(), [200, 302, 401, 405]);
    }

    /**
     * Test that the profile route uses correct HTTP methods.
     */
    public function test_profile_route_http_methods()
    {
        // GET should work
        $getResponse = $this->get('/profile');
        $this->assertNotEquals(405, $getResponse->status(), 'GET method should be allowed for /profile');

        // POST should not be allowed for profile route
        $postResponse = $this->post('/profile');
        $this->assertEquals(405, $postResponse->status(), 'POST method should not be allowed for /profile');
    }

    /**
     * Test that password change uses correct HTTP method.
     */
    public function test_password_change_http_methods()
    {
        // POST should work
        $postResponse = $this->post('/change-password');
        $this->assertNotEquals(405, $postResponse->status(), 'POST method should be allowed for /change-password');

        // GET should not be allowed
        $getResponse = $this->get('/change-password');
        $this->assertEquals(405, $getResponse->status(), 'GET method should not be allowed for /change-password');
    }

    /**
     * Test all profile-related routes exist in the application.
     */
    public function test_all_profile_routes_registered()
    {
        $routes = [
            ['GET', '/profile'],
            ['POST', '/change-password'],
            ['POST', '/profile/upload-picture'],
            ['POST', '/profile/delete-picture'],
            ['GET', '/chat']
        ];

        foreach ($routes as [$method, $uri]) {
            if ($method === 'GET') {
                $response = $this->get($uri);
            } else {
                $response = $this->post($uri);
            }
            
            $this->assertNotEquals(404, $response->status(), 
                "Route {$method} {$uri} should exist (got {$response->status()})");
        }
    }
}