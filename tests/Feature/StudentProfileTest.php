<?php

namespace Tests\Feature;

use Tests\TestCase;

class StudentProfileTest extends TestCase
{
    /**
     * Profile page route should be registered (auth-protected is fine).
     */
    public function test_profile_route_exists(): void
    {
        $response = $this->get(route('profile.show'));
        // Accept common outcomes for unauthenticated requests
        $this->assertContains($response->status(), [200, 302, 401]);
    }

    /**
     * Password change endpoint should be registered as POST.
     */
    public function test_password_change_route_exists(): void
    {
        // Use correct field names expected by the controller (oldPassword/newPassword)
        $response = $this->post(route('changePassword'), [
            'oldPassword' => 'test',
            'newPassword' => 'newtest',
            'newPassword_confirmation' => 'newtest',
        ]);
        // Any status other than 404 means route is wired
        $this->assertNotEquals(404, $response->status());
    }

    /**
     * Profile picture upload route exists (may redirect/validate when unauthenticated/no file).
     */
    public function test_profile_picture_upload_route_exists(): void
    {
        $response = $this->post(route('profile.uploadPicture'));
        $this->assertNotEquals(404, $response->status());
    }

    /**
     * Profile picture delete route exists.
     */
    public function test_profile_picture_delete_route_exists(): void
    {
        $response = $this->post(route('profile.deletePicture'));
        $this->assertNotEquals(404, $response->status());
    }

    /**
     * Chat route exists (POST is defined; GET should be 405 or similar, not 404).
     */
    public function test_chat_route_exists_via_get_method_check(): void
    {
        $response = $this->get('/chat');
        $this->assertNotEquals(404, $response->status());
        // Often 405 for method not allowed, or 302/401 if middleware involved
        $this->assertContains($response->status(), [302, 401, 405]);
    }

    /**
     * POST endpoints typically require CSRF; verify we don't get 404 when missing it.
     */
    public function test_post_routes_have_csrf_or_validation_guards(): void
    {
        $this->assertContains($this->post(route('changePassword'))->status(), [419, 422, 302, 401, 500]);
        $this->assertContains($this->post(route('profile.uploadPicture'))->status(), [419, 422, 302, 401, 500]);
        $this->assertContains($this->post(route('profile.deletePicture'))->status(), [419, 422, 302, 401, 500]);
    }

    /**
     * Check common HTTP method expectations for routes.
     */
    public function test_profile_route_http_methods(): void
    {
        $getResponse = $this->get(route('profile.show'));
        $this->assertNotEquals(405, $getResponse->status(), 'GET should be allowed for profile.show');

        $postResponse = $this->post('/profile');
        $this->assertEquals(405, $postResponse->status(), 'POST should not be allowed for /profile');
    }

    public function test_password_change_http_methods(): void
    {
        $postResponse = $this->post(route('changePassword'));
        $this->assertNotEquals(405, $postResponse->status(), 'POST should be allowed for changePassword');

        $getResponse = $this->get('/change-password');
        $this->assertEquals(405, $getResponse->status(), 'GET should not be allowed for /change-password');
    }

    /**
     * Sanity check: all relevant routes are registered.
     */
    public function test_all_profile_routes_registered(): void
    {
        $routes = [
            ['GET', route('profile.show')],
            ['POST', route('changePassword')],
            ['POST', route('profile.uploadPicture')],
            ['POST', route('profile.deletePicture')],
            // Chat is POST-defined; use GET to assert 405 but not 404
            ['GET', '/chat'],
        ];

        foreach ($routes as [$method, $uri]) {
            $response = $method === 'GET' ? $this->get($uri) : $this->post($uri);
            $this->assertNotEquals(404, $response->status(), "Route {$method} {$uri} should exist (got {$response->status()})");
        }
    }
}