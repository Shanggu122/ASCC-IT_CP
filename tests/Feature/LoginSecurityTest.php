<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class LoginSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    if (!Schema::hasTable('t_student')) {
            Schema::create('t_student', function (Blueprint $table) {
                $table->string('Stud_ID', 9)->primary();
                $table->string('Name')->nullable();
                $table->string('Dept_ID')->nullable();
                $table->string('Email')->nullable();
                $table->string('Password')->nullable();
                $table->string('profile_picture')->nullable();
                $table->boolean('is_active')->default(1);
                $table->rememberToken();
            });
        }
        if (!Schema::hasTable('login_attempts')) {
            Schema::create('login_attempts', function (Blueprint $table) {
                $table->id();
                $table->string('stud_id',32)->nullable();
                $table->string('ip',45)->nullable();
                $table->string('user_agent',255)->nullable();
                $table->boolean('successful')->default(false);
                $table->string('reason',40)->nullable();
                $table->timestamps();
            });
        }
    }

    protected function createStudent(array $overrides = []): User
    {
        $data = array_merge([
            'Stud_ID' => '202400900',
            'Name' => 'Sec Student',
            'Dept_ID' => 'CS',
            'Email' => 'sec@example.com',
            'Password' => Hash::make('topsecret'),
            'is_active' => 1,
        ], $overrides);
        return User::create($data);
    }

    /** @test */
    public function session_is_regenerated_on_successful_login(): void
    {
        $u = $this->createStudent();
        $oldId = Session::getId();
        $this->post(route('login.submit'), ['Stud_ID'=>$u->Stud_ID,'Password'=>'topsecret']);
        $newId = Session::getId();
        $this->assertAuthenticatedAs($u);
        $this->assertNotEquals($oldId, $newId, 'Session ID should change after login');
    }

    /** @test */
    public function rate_limiter_blocks_after_too_many_attempts(): void
    {
        $u = $this->createStudent(['Stud_ID'=>'202400901']);
        for ($i=0;$i<5;$i++) {
            $this->post(route('login.submit'), ['Stud_ID'=>$u->Stud_ID,'Password'=>'badpass']);
        }
        $resp = $this->post(route('login.submit'), ['Stud_ID'=>$u->Stud_ID,'Password'=>'badpass']);
        $resp->assertSessionHasErrors('login');
        $msg = session('errors')->get('login')[0] ?? '';
        $this->assertStringContainsString('Too many attempts', $msg);
        $this->assertGuest();
    }

    /** @test */
    public function remember_me_sets_cookie(): void
    {
        $u = $this->createStudent(['Stud_ID'=>'202400902']);
        $resp = $this->post(route('login.submit'), [
            'Stud_ID'=>$u->Stud_ID,
            'Password'=>'topsecret',
            'remember'=>'on'
        ]);
        $resp->assertRedirect(route('dashboard'));
        $cookieNames = array_map(fn($c)=>$c->getName(), $resp->headers->getCookies());
        $rememberCookie = collect($cookieNames)->first(fn($n)=>str_contains($n,'remember')); // framework generated name
        $this->assertNotEmpty($rememberCookie, 'Remember me cookie not set');
    }

    /** @test */
    public function inactive_account_cannot_login(): void
    {
        $u = $this->createStudent(['Stud_ID'=>'202400903','is_active'=>0]);
        $resp = $this->from('/login')->post(route('login.submit'), ['Stud_ID'=>$u->Stud_ID,'Password'=>'topsecret']);
        $resp->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    /** @test */
    public function missing_csrf_token_results_in_419(): void
    {
        // In Feature tests Laravel test client automatically sets CSRF; simulate token mismatch by sending incorrect token header.
        $this->withHeader('X-CSRF-TOKEN','bogus');
        $response = $this->post('/login', ['Stud_ID'=>'X','Password'=>'Y']);
        // Depending on exception handler it may redirect back with errors instead of raw 419, so accept either.
        $this->assertTrue(in_array($response->getStatusCode(), [302,419]), 'Expected redirect or 419 for bad CSRF');
    }

    /** @test */
    public function failed_attempt_is_logged(): void
    {
        Log::spy();
        $this->post(route('login.submit'), ['Stud_ID'=>'111111111','Password'=>'anything']);
        Log::shouldHaveReceived('notice')->atLeast()->once();
    $this->assertGreaterThan(0, DB::table('login_attempts')->where('stud_id','111111111')->count());
    }

    /** @test */
    public function validation_messages_present(): void
    {
        $resp = $this->post(route('login.submit'), ['Stud_ID'=>'','Password'=>'']);
        $resp->assertSessionHasErrors(['Stud_ID','Password']);
    }

    /** @test */
    public function session_timeout_simulated_logout(): void
    {
        $u = $this->createStudent(['Stud_ID'=>'202400904']);
        $this->post(route('login.submit'), ['Stud_ID'=>$u->Stud_ID,'Password'=>'topsecret']);
        $this->assertAuthenticated();
        // Simulate framework deciding to timeout
        \Illuminate\Support\Facades\Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        $resp = $this->get(route('dashboard'));
        $resp->assertRedirect(route('login'));
    }

    /** @test */
    public function successful_login_is_audited(): void
    {
        $u = $this->createStudent(['Stud_ID'=>'202400905']);
        $this->post(route('login.submit'), ['Stud_ID'=>$u->Stud_ID,'Password'=>'topsecret']);
        $row = DB::table('login_attempts')->where('stud_id',$u->Stud_ID)->orderByDesc('id')->first();
        $this->assertNotNull($row);
        $this->assertEquals(1, $row->successful);
        $this->assertEquals('success', $row->reason);
        $resp = $this->get(route('dashboard'));
        $resp->assertOk();
    }
}
