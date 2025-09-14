<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Professor;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ProfessorDashboardNoCacheTest extends TestCase
{
    protected function ensureProfessor(): Professor
    {
        if(!Schema::hasTable('professors')){
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
                'Prof_ID' => 'P0000002',
                'Name' => 'Cache Test Prof',
                'Email' => 'cacheprof@example.com',
                'Password' => 'secret123',
            ]);
        }
        return $prof;
    }

    public function test_dashboard_professor_has_no_cache_headers()
    {
        $prof = $this->ensureProfessor();
        auth()->guard('professor')->login($prof);

        $resp = $this->get('/dashboard-professor');
        $resp->assertStatus(200);
        $resp->assertHeader('Cache-Control');
        $cache = $resp->headers->get('Cache-Control');
        $this->assertStringContainsString('no-store', $cache);
        $this->assertStringContainsString('no-cache', $cache);
        $this->assertStringContainsString('must-revalidate', $cache);
        $resp->assertHeader('Pragma', 'no-cache');
        $resp->assertHeader('Expires');
    }
}
