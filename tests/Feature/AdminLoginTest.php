<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('t_admin')->insert([
            'Admin_ID' => 'A1234',
            'Password' => Hash::make('secret123'),
            'is_active' => 1,
        ]);
    }

    public function test_login_page_renders_elements()
    {
        $res = $this->get('/login-admin');
        $res->assertStatus(200)
            ->assertSee('Admin ID')
            ->assertSee('Password')
            ->assertSee('Remember me')
            ->assertSee('Forgot Password?');
    }

    public function test_rejects_unknown_admin_id()
    {
        $res = $this->post('/login-admin', [
            'Admin_ID' => 'UNKNOWN',
            'Password' => 'whatever'
        ]);
        $res->assertSessionHasErrors(['Admin_ID']);
    }

    public function test_rejects_wrong_password()
    {
        $res = $this->post('/login-admin', [
            'Admin_ID' => 'A1234',
            'Password' => 'wrong'
        ]);
        $res->assertSessionHasErrors(['Password']);
    }

    public function test_allows_successful_login()
    {
        $res = $this->post('/login-admin', [
            'Admin_ID' => 'A1234',
            'Password' => 'secret123'
        ]);
        $res->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_rate_limiting_triggers_after_max_attempts()
    {
        for($i=0;$i<5;$i++){
            $this->post('/login-admin', [ 'Admin_ID'=>'A1234', 'Password'=>'badpass' ]);
        }
        $res = $this->post('/login-admin', [ 'Admin_ID'=>'A1234', 'Password'=>'badpass' ]);
        $res->assertSessionHasErrors(['login']);
    }

    public function test_remember_me_sets_cookie()
    {
        $res = $this->post('/login-admin', [
            'Admin_ID' => 'A1234',
            'Password' => 'secret123',
            'remember' => '1'
        ]);
        $res->assertRedirect();
        $cookie = collect($res->headers->getCookies())->first(fn($c)=> Str::contains($c->getName(), 'remember_web'));
        $this->assertNotNull($cookie, 'remember cookie not set');
    }
}
