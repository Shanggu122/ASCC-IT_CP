<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AdminLogoutTest extends TestCase
{
    protected function ensureAdmin(): Admin
    {
        if(!Schema::hasTable('admin')){
            Schema::create('admin', function(Blueprint $table){
                $table->string('Admin_ID')->primary();
                $table->string('Name')->nullable();
                $table->string('Email')->nullable();
                $table->string('Password');
                $table->string('remember_token')->nullable();
            });
        }
        $admin = Admin::first();
        if(!$admin){
            $admin = Admin::create([
                'Admin_ID' => 'A0000001',
                'Name' => 'Admin Test',
                'Email' => 'admin@example.com',
                'Password' => 'secret123',
            ]);
        }
        return $admin;
    }

    public function test_admin_logout_redirects_and_blocks_dashboard()
    {
        $admin = $this->ensureAdmin();
        auth()->guard('admin')->login($admin);

        $dash = $this->get('/admin/dashboard');
        $dash->assertStatus(200);

        $logout = $this->post('/admin/logout');
        $logout->assertStatus(302);
        $this->assertSame('/login/admin', parse_url($logout->headers->get('Location'), PHP_URL_PATH));

        $after = $this->get('/admin/dashboard');
        $after->assertRedirect(route('login.admin'));
    }
}
