<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginPageElementsTest extends TestCase
{
    /** @test */
    public function login_page_has_expected_form_elements(): void
    {
        $resp = $this->get(route('login'));
        $resp->assertStatus(200);
        $html = $resp->getContent();
        // Basic structural checks
        $this->assertStringContainsString('id="student-login-form"', $html, 'Form id missing');
        $this->assertStringContainsString('name="Stud_ID"', $html, 'Student ID input missing');
        $this->assertStringContainsString('name="Password"', $html, 'Password input missing');
        $this->assertStringContainsString('type="checkbox" name="remember"', $html, 'Remember checkbox missing');
        $this->assertStringContainsString('id="toggle-password-btn"', $html, 'Password toggle button missing');
        $this->assertStringContainsString('Forgot Password?', $html, 'Forgot password link text missing');
        $this->assertStringContainsString('type="submit"', $html, 'Submit button missing');
        // CSRF token meta
        $this->assertStringContainsString('name="_token"', $html, 'CSRF token input missing');
    }

    /** @test */
    public function forgot_password_route_renders(): void
    {
        $resp = $this->get(route('forgotpassword'));
        $resp->assertStatus(200);
    }
}
