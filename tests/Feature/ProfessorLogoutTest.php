<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Professor;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ProfessorLogoutTest extends TestCase
{
    // If database refresh is heavy you can comment this out; kept for isolation
    // use RefreshDatabase;

    protected function makeProfessor(): Professor
    {
        if (!Schema::hasTable('professors')) {
            Schema::create('professors', function(Blueprint $table){
                $table->string('Prof_ID')->primary();
                $table->string('Name')->nullable();
                $table->string('Email')->nullable();
                $table->string('Password');
                $table->string('remember_token')->nullable();
            });
        }
        $prof = Professor::first();
        if(!$prof){
            $prof = Professor::create([
                'Prof_ID' => 'P0000001',
                'Name' => 'Test Prof',
                'Email' => 'prof@example.com',
                'Password' => 'secret123',
            ]);
        }
        return $prof;
    }

    public function test_professor_logout_redirects_to_professor_login_and_blocks_dashboard()
    {
        $prof = $this->makeProfessor();

        // Simulate login via guard
    auth()->guard('professor')->login($prof);

        $resp = $this->get('/dashboard-professor');
        $resp->assertStatus(200);

    $logout = $this->get('/logout-professor');
    // Assert redirect (302) to login-professor
    $logout->assertRedirect('/login-professor');

        // After logout, accessing dashboard should redirect to professor login
        $after = $this->get('/dashboard-professor');
        $after->assertRedirect('/login-professor');
    }
}
