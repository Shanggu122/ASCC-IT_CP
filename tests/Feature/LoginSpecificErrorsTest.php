<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginSpecificErrorsTest extends TestCase
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
    }

    protected function createStudent(array $overrides=[]): User
    {
        $data = array_merge([
            'Stud_ID' => '202470001',
            'Name' => 'Spec Student',
            'Dept_ID' => 'CS',
            'Email' => 'spec@example.com',
            'Password' => Hash::make('specpass123'),
            'is_active' => 1,
        ], $overrides);
        return User::create($data);
    }

    /** @test */
    public function shows_specific_message_when_student_id_not_found(): void
    {
        $resp = $this->post(route('login.submit'), [
            'Stud_ID' => '999999999',
            'Password' => 'anything'
        ]);
        $resp->assertSessionHasErrors('Stud_ID');
        $msg = session('errors')->first('Stud_ID');
        $this->assertSame('Student ID does not exist.', $msg);
        $this->assertGuest();
    }

    /** @test */
    public function shows_specific_message_when_password_is_wrong(): void
    {
        $student = $this->createStudent();
        $resp = $this->post(route('login.submit'), [
            'Stud_ID' => $student->Stud_ID,
            'Password' => 'wrongpw'
        ]);
        $resp->assertSessionHasErrors('Password');
        $msg = session('errors')->first('Password');
        $this->assertSame('Incorrect password.', $msg);
        $this->assertGuest();
    }

    /** @test */
    public function successful_login_still_redirects_and_no_specific_errors(): void
    {
        $student = $this->createStudent(['Stud_ID' => '202470002']);
        $resp = $this->post(route('login.submit'), [
            'Stud_ID' => $student->Stud_ID,
            'Password' => 'specpass123'
        ]);
        $resp->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($student);
    }
}
