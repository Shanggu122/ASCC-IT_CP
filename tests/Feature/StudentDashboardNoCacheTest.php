<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentDashboardNoCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!Schema::hasTable('t_student')) {
            Schema::create('t_student', function (Blueprint $table) {
                $table->string('Stud_ID',9)->primary();
                $table->string('Name')->nullable();
                $table->string('Dept_ID')->nullable();
                $table->string('Email')->nullable();
                $table->string('Password')->nullable();
                $table->boolean('is_active')->default(1);
                $table->rememberToken();
            });
        }

        // Insert a test student
    DB::table('t_student')->insert([
            'Stud_ID' => '202470010',
            'Name' => 'Cache Test',
            'Dept_ID' => 'CS',
            'Email' => 'cachetest@example.com',
            'Password' => Hash::make('studentpass'),
            'is_active' => 1,
        ]);
    }

    protected function loginStudent(): void
    {
        $this->post(route('login.submit'), [
            'Stud_ID' => '202470010',
            'Password' => 'studentpass'
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_dashboard_sets_no_cache_headers(): void
    {
        $this->loginStudent();
        $resp = $this->get(route('dashboard'));
        $resp->assertStatus(200);
        $cache = $resp->headers->get('Cache-Control');
        $this->assertNotNull($cache, 'Cache-Control header missing');
        // Must contain all critical directives regardless of order; allow additional like 'private'
        foreach (['no-cache','no-store','max-age=0','must-revalidate'] as $required) {
            $this->assertStringContainsString($required, $cache, "Cache-Control missing {$required}");
        }
        $resp->assertHeader('Pragma', 'no-cache');
    $expires = $resp->headers->get('Expires');
    $this->assertNotNull($expires, 'Expires header missing');
    $this->assertStringContainsString('01 Jan 1990', $expires, 'Expires header not using past sentinel date');
    }

    public function test_cannot_return_to_dashboard_after_logout_using_back_button(): void
    {
        $this->loginStudent();
    $this->get(route('logout')); // student logout is GET route
    $this->assertGuest();
        // Simulate browser back to cached dashboard
        $resp = $this->get(route('dashboard'));
        $resp->assertRedirect(route('login'));
    }
}
