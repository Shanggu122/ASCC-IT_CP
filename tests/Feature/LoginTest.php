<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		// Create only the minimal table needed for authentication instead of running full legacy migrations
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
	}

	protected function createStudent(array $overrides = []): User
	{
		$data = array_merge([
			'Stud_ID' => '202400001',
			'Name' => 'Test Student',
			'Dept_ID' => 'CS',
			'Email' => 'student@example.com',
			'Password' => Hash::make('secret123'),
			'profile_picture' => null,
		], $overrides);

		// Direct insert due to legacy primary key setup
		return User::create($data);
	}

	/** @test */
	public function student_can_login_with_valid_credentials_and_redirects_to_dashboard(): void
	{
		$student = $this->createStudent();

		$response = $this->post(route('login.submit'), [
			'Stud_ID' => $student->Stud_ID,
			'Password' => 'secret123',
		]);

		$response->assertRedirect(route('dashboard'));
		$this->assertAuthenticatedAs($student);
	}

	/** @test */
	public function student_cannot_login_with_wrong_password(): void
	{
		$student = $this->createStudent();

		$response = $this->post(route('login.submit'), [
			'Stud_ID' => $student->Stud_ID,
			'Password' => 'wrongpass',
		]);

		$response->assertSessionHasErrors('login');
		$this->assertGuest();
	}

	/** @test */
	public function student_cannot_login_with_invalid_student_id(): void
	{
		$this->createStudent();

		$response = $this->post(route('login.submit'), [
			'Stud_ID' => '999999999',
			'Password' => 'secret123',
		]);

		$response->assertSessionHasErrors('login');
		$this->assertGuest();
	}

	/** @test */
	public function student_cannot_login_with_empty_fields(): void
	{
		$response = $this->post(route('login.submit'), [
			'Stud_ID' => '',
			'Password' => '',
		]);

		$response->assertSessionHasErrors(['Stud_ID', 'Password']);
		$this->assertGuest();
	}

	/** @test */
	public function authenticated_student_can_access_protected_dashboard(): void
	{
		$student = $this->createStudent();

		$this->post(route('login.submit'), [
			'Stud_ID' => $student->Stud_ID,
			'Password' => 'secret123',
		]);

		$this->assertAuthenticatedAs($student);

		$response = $this->get(route('dashboard'));
		$response->assertOk();
	}

	/** @test */
	public function guest_is_redirected_to_login_when_accessing_dashboard(): void
	{
		$response = $this->get(route('dashboard'));
		$response->assertRedirect(route('login'));
		$this->assertGuest();
	}
}

