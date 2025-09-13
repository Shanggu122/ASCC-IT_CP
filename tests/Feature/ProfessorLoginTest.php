<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;

class ProfessorLoginTest extends TestCase
{
    use RefreshDatabase; // assumes sqlite / refresh works; adjust if migrations large

    protected function setUp(): void
    {
        parent::setUp();
        if (DB::getSchemaBuilder()->hasTable('professors')) {
            DB::table('professors')->insert([
                'Prof_ID' => 'P1234',
                'Password' => 'secret123',
                'is_active' => 1,
            ]);
        }
    }

    public function test_login_page_renders_elements()
    {
        $res = $this->get('/login-professor');
        $res->assertStatus(200)
            ->assertSee('Professor ID')
            ->assertSee('Password')
            ->assertSee('Remember me')
            ->assertSee('Forgot Password?');
    }

    public function test_rejects_unknown_professor_id()
    {
        $res = $this->post('/login-professor', [
            'Prof_ID' => 'UNKNOWN',
            'Password' => 'whatever'
        ]);
        $res->assertSessionHasErrors(['Prof_ID']);
    }

    public function test_rejects_wrong_password()
    {
        $res = $this->post('/login-professor', [
            'Prof_ID' => 'P1234',
            'Password' => 'wrong'
        ]);
        $res->assertSessionHasErrors(['Password']);
    }

    public function test_allows_successful_login()
    {
        $res = $this->post('/login-professor', [
            'Prof_ID' => 'P1234',
            'Password' => 'secret123'
        ]);
        $res->assertRedirect();
        $this->assertAuthenticated('professor');
    }

    public function test_rate_limiting_triggers_after_max_attempts()
    {
        for($i=0;$i<5;$i++){
            $this->post('/login-professor', [ 'Prof_ID'=>'P1234', 'Password'=>'badpass' ]);
        }
        $res = $this->post('/login-professor', [ 'Prof_ID'=>'P1234', 'Password'=>'badpass' ]);
        $res->assertSessionHasErrors(['login']);
    }

    public function test_remember_me_sets_cookie()
    {
        if(!Schema::hasTable('professors') || !Schema::hasColumn('professors','remember_token')){
            $this->markTestSkipped('remember_token not present');
        }
        $res = $this->post('/login-professor', [
            'Prof_ID' => 'P1234',
            'Password' => 'secret123',
            'remember' => '1'
        ]);
        $res->assertRedirect();
    $cookie = collect($res->headers->getCookies())->first(fn($c)=> Str::contains($c->getName(), 'remember_professor'));
        $this->assertNotNull($cookie, 'remember cookie not set');
    }
}
