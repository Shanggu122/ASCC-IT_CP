<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure actual 'admin' table (matching Admin model) exists for test isolation
        if (!Schema::hasTable('admin')) {
            Schema::create('admin', function (Blueprint $table) {
                $table->string('Admin_ID', 20)->primary();
                $table->string('Password');
                $table->string('Name')->nullable();
                $table->string('Email')->nullable();
                $table->string('profile_picture')->nullable();
                $table->boolean('is_active')->default(1);
                $table->rememberToken();
            });
        }

        DB::table('admin')->insert([
            'Admin_ID' => 'A1234',
            'Password' => Hash::make('secret123'),
            'is_active' => 1,
        ]);
    }

    public function test_login_page_renders_elements()
    {
    $res = $this->get(route('login.admin'));
        $res->assertStatus(200)
            ->assertSee('Admin ID')
            ->assertSee('Password')
            ->assertSee('Remember me')
            ->assertSee('Forgot Password?');
    }

    public function test_rejects_unknown_admin_id()
    {
        $res = $this->post(route('login.admin.submit'), [
            'Admin_ID' => 'UNKNOWN',
            'Password' => 'whatever'
        ]);
        $res->assertSessionHasErrors(['Admin_ID']);
    }

    public function test_rejects_wrong_password()
    {
        $res = $this->post(route('login.admin.submit'), [
            'Admin_ID' => 'A1234',
            'Password' => 'wrong'
        ]);
        $res->assertSessionHasErrors(['Password']);
    }

    public function test_allows_successful_login()
    {
        $res = $this->post(route('login.admin.submit'), [
            'Admin_ID' => 'A1234',
            'Password' => 'secret123'
        ]);
        $res->assertRedirect();
        $this->assertAuthenticated('admin');
    }

    public function test_rate_limiting_triggers_after_max_attempts()
    {
        for($i=0;$i<5;$i++){
            $this->post(route('login.admin.submit'), [ 'Admin_ID'=>'A1234', 'Password'=>'badpass' ]);
        }
        $res = $this->post(route('login.admin.submit'), [ 'Admin_ID'=>'A1234', 'Password'=>'badpass' ]);
        $res->assertSessionHasErrors(['login']);
    }

    public function test_remember_me_sets_cookie()
    {
        $res = $this->post(route('login.admin.submit'), [
            'Admin_ID' => 'A1234',
            'Password' => 'secret123',
            'remember' => '1'
        ]);
        $res->assertRedirect();
        // Cookie name for non-default guard typically still uses remember_web unless customized; check both
        $cookie = collect($res->headers->getCookies())->first(function($c){
            return Str::contains($c->getName(), 'remember_web') || Str::contains($c->getName(), 'remember_admin');
        });
        $this->assertNotNull($cookie, 'remember cookie not set');
    }
}
