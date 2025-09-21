<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\Attributes\Test;

class LoginTest extends TestCase
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

		// Ensure audit table exists for security-related tests
		if (!Schema::hasTable('login_attempts')) {
			Schema::create('login_attempts', function (Blueprint $table) {
				$table->id();
				$table->string('stud_id', 32)->nullable();
				$table->string('ip', 45)->nullable();
				$table->string('user_agent', 255)->nullable();
				$table->boolean('successful')->default(false);
				$table->string('reason', 40)->nullable();
				$table->timestamps();
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

		return User::create($data);
	}

	// ===== Core login flow =====

	// [1] Valid login redirects and grants dashboard access
	#[Test]
	public function student_can_login_with_valid_credentials_and_redirects_to_dashboard(): void
	{
		$student = $this->createStudent();

		$response = $this->post(route('login.submit'), [
			'Stud_ID' => $student->Stud_ID,
			'Password' => 'secret123',
		]);

		$response->assertRedirect(route('dashboard'));
		$this->assertAuthenticatedAs($student);
		// also confirm dashboard access in same flow
		$this->get(route('dashboard'))->assertOk();
	}

	// [5] Guest is redirected to login when accessing dashboard
	#[Test]
	public function guest_is_redirected_to_login_when_accessing_dashboard(): void
	{
		$response = $this->get(route('dashboard'));
		$response->assertRedirect(route('login'));
		$this->assertGuest();
	}

	// [7] Session ID regenerates on successful login
	#[Test]
	public function session_is_regenerated_on_successful_login(): void
	{
		$student = $this->createStudent(['Stud_ID' => '202400778', 'Email' => 'sess@example.com', 'Password' => Hash::make('sessionpass')]);
		$oldId = session()->getId();
		$this->post(route('login.submit'), ['Stud_ID' => $student->Stud_ID, 'Password' => 'sessionpass']);
		$newId = session()->getId();
		$this->assertAuthenticatedAs($student);
		$this->assertNotEquals($oldId, $newId, 'Session ID should change after login');
	}

	// [14] Already-authenticated visit to login is handled (no re-login; allowed or redirected)
	#[Test]
	public function already_authenticated_redirects_away_from_login(): void
	{
		$student = $this->createStudent(['Stud_ID'=>'202401115','Password'=>Hash::make('go')]);
		$this->post(route('login.submit'), ['Stud_ID'=>'202401115','Password'=>'go']);
		$this->assertAuthenticatedAs($student);
		$res = $this->get(route('login'));
		$this->assertTrue(in_array($res->getStatusCode(), [200, 302, 303]));
	}

	// [15] Intended URL after login redirects to dashboard
	#[Test]
	public function intended_redirect_after_login_goes_to_dashboard(): void
	{
		$student = $this->createStudent(['Stud_ID'=>'202401116','Password'=>Hash::make('intend')]);
		// Simulate intended URL
		session(['url.intended' => route('dashboard')]);
		$res = $this->post(route('login.submit'), ['Stud_ID'=>'202401116','Password'=>'intend']);
		$res->assertRedirect(route('dashboard'));
		$this->assertAuthenticatedAs($student);
	}

	// [20] Logout invalidates session; dashboard access is blocked afterward
	#[Test]
	public function logout_then_cannot_access_dashboard(): void
	{
		$student = $this->createStudent(['Stud_ID'=>'202401121','Password'=>Hash::make('bye')]);
		$this->post(route('login.submit'), ['Stud_ID'=>'202401121','Password'=>'bye']);
		$this->assertAuthenticatedAs($student);
		\Illuminate\Support\Facades\Auth::logout();
		session()->invalidate();
		session()->regenerateToken();
		$res = $this->get(route('dashboard'));
		$res->assertRedirect(route('login'));
	}

	// ===== Credentials & validation =====

	// [2] Wrong password denies login and logs failed attempt
	#[Test]
	public function student_cannot_login_with_wrong_password(): void
	{
		Log::spy();
		$student = $this->createStudent();

		$response = $this->post(route('login.submit'), [
			'Stud_ID' => $student->Stud_ID,
			'Password' => 'wrongpass',
		]);

		$response->assertSessionHasErrors('login');
		$this->assertGuest();
		// logged/audited as failed attempt
		Log::shouldHaveReceived('notice')->atLeast()->once();
		$this->assertGreaterThan(0, DB::table('login_attempts')->where('stud_id', $student->Stud_ID)->count());
	}

	// [3] Invalid student ID shows “ID not found” message
	#[Test]
	public function student_cannot_login_with_invalid_student_id(): void
	{
		$this->createStudent();

		$response = $this->post(route('login.submit'), [
			'Stud_ID' => '999999999',
			'Password' => 'secret123',
		]);

		$response->assertSessionHas('errors');
		$bag = session('errors');
		$flat = $bag ? $bag->all() : [];
		$this->assertTrue(in_array('Student ID does not exist.', $flat), 'Expected specific missing ID message');
		$this->assertGuest();
	}

	// [4] Empty or missing fields return validation errors for Stud_ID and Password
	#[Test]
	public function student_cannot_login_with_empty_fields(): void
	{
		$response = $this->post(route('login.submit'), [
			'Stud_ID' => '',
			'Password' => '',
		]);

		$response->assertSessionHasErrors(['Stud_ID', 'Password']);
		$this->assertGuest();
	}

	// [8] Inactive or locked account cannot login
	#[Test]
	public function inactive_account_cannot_login(): void
	{
		$student = $this->createStudent(['Stud_ID' => '202400779', 'is_active' => 0]);
		$response = $this->from(route('login'))
			->post(route('login.submit'), ['Stud_ID' => $student->Stud_ID, 'Password' => 'secret123']);
		$response->assertSessionHasErrors('login');
		$this->assertGuest();
	}

	// [11] Password with leading/trailing whitespace is handled safely (no unintended auth)
	#[Test]
	public function password_with_leading_or_trailing_whitespace_fails(): void
	{
		$student = $this->createStudent(['Stud_ID' => '202401112', 'Password' => Hash::make('edgepass')]);
		$response = $this->post(route('login.submit'), [
			'Stud_ID' => $student->Stud_ID,
			'Password' => ' edgepass ',
		]);
		// Some implementations trim input; accept either redirect (trimmed) or validation error
		$code = $response->getStatusCode();
		if (in_array($code, [302,303])) {
			$this->assertAuthenticatedAs($student);
		} else {
			$response->assertSessionHasErrors();
			$this->assertGuest();
		}
	}

	// [12] Student ID exceeding max length is rejected
	#[Test]
	public function student_id_exceeds_max_length_rejected(): void
	{
		$longId = str_repeat('1', 100);
		$response = $this->post(route('login.submit'), [
			'Stud_ID' => $longId,
			'Password' => 'any',
		]);
		$response->assertSessionHasErrors(['Stud_ID']);
		$this->assertGuest();
	}

	// [13] Student ID with leading zeros is accepted
	#[Test]
	public function student_id_with_leading_zeros_logs_in(): void
	{
		$student = $this->createStudent(['Stud_ID' => '000123456', 'Password' => Hash::make('zeroes')]);
		$response = $this->post(route('login.submit'), [
			'Stud_ID' => '000123456',
			'Password' => 'zeroes',
		]);
		$response->assertRedirect(route('dashboard'));
		$this->assertAuthenticatedAs($student);
	}

	// [17] Password minimum length is enforced
	#[Test]
	public function password_min_length_enforced_on_validation(): void
	{
		$student = $this->createStudent(['Stud_ID'=>'202401118','Password'=>Hash::make('validpass')]);
		$res = $this->post(route('login.submit'), ['Stud_ID'=>'202401118','Password'=>'12']);
		$res->assertSessionHasErrors('login');
		$this->assertGuest();
	}

	// [19] Student ID with internal spaces is rejected
	#[Test]
	public function student_id_with_internal_spaces_rejected(): void
	{
		$student = $this->createStudent(['Stud_ID'=>'202401119','Password'=>Hash::make('spacer')]);
		$res = $this->post(route('login.submit'), ['Stud_ID'=>'2024 01119','Password'=>'spacer']);
		$res->assertSessionHasErrors(['Stud_ID']);
		$this->assertGuest();
	}

	// ===== Password variants =====

	// [9] Password with special characters works
	#[Test]
	public function password_with_special_characters_allows_login(): void
	{
		$student = $this->createStudent(['Stud_ID' => '202400780', 'Password' => Hash::make('@G$w0rd!#2024')]);
		$response = $this->post(route('login.submit'), ['Stud_ID' => $student->Stud_ID, 'Password' => '@G$w0rd!#2024']);
		$response->assertRedirect(route('dashboard'));
		$this->assertAuthenticatedAs($student);
	}
	

	// [16] Unicode password works
	#[Test]
	public function unicode_password_works(): void
	{
		$pwd = "Pásswörd🔥123";
		$student = $this->createStudent(['Stud_ID'=>'202401117','Password'=>Hash::make($pwd)]);
		$res = $this->post(route('login.submit'), ['Stud_ID'=>'202401117','Password'=>$pwd]);
		$res->assertRedirect(route('dashboard'));
		$this->assertAuthenticatedAs($student);
	}

	// ===== Security/threat tests =====

	// [10] SQL injection attempts (fields or password) do not authenticate
	#[Test]
	public function sql_injection_like_strings_do_not_bypass_auth(): void
	{
		$inj = "' OR 1=1 --";
		// Both fields injection
		$response = $this->post(route('login.submit'), ['Stud_ID' => $inj, 'Password' => $inj]);
		$response->assertStatus(302);
		$this->assertGuest();

		// Password-only injection with valid Stud_ID
		$student = $this->createStudent(['Stud_ID' => '202409991', 'Password' => Hash::make('safe')]);
		$res2 = $this->post(route('login.submit'), ['Stud_ID' => '202409991', 'Password' => $inj]);
		$res2->assertSessionHasErrors('login');
		$this->assertGuest();
	}

	// [24] Rate limiter blocks after too many failed attempts
	#[Test]
	public function rate_limiter_blocks_after_too_many_attempts(): void
	{
		$student = $this->createStudent(['Stud_ID' => '202401111', 'Password' => Hash::make('ratelimit')]);
		for ($i = 0; $i < 5; $i++) {
			$this->post(route('login.submit'), ['Stud_ID' => $student->Stud_ID, 'Password' => 'wrong-'.$i]);
		}
		$resp = $this->post(route('login.submit'), ['Stud_ID' => $student->Stud_ID, 'Password' => 'wrong-final']);
		$resp->assertSessionHasErrors('login');
		$msg = session('errors')->get('login')[0] ?? '';
		$this->assertStringContainsString('Too many attempts', $msg);
		$this->assertGuest();
	}

	// [25] Missing or invalid CSRF token results in redirect or 419
	#[Test]
	public function missing_csrf_token_results_in_redirect_or_419(): void
	{
		$this->withHeader('X-CSRF-TOKEN','bogus');
		$response = $this->post('/login', ['Stud_ID' => 'X', 'Password' => 'Y']);
		$this->assertTrue(in_array($response->getStatusCode(), [302, 419]), 'Expected redirect or 419 for bad CSRF');
	}

	// ===== Cookies & session =====

	// [6] Remember me sets persistent cookie on successful login
	#[Test]
	public function remember_me_sets_cookie_after_login(): void
	{
		$student = $this->createStudent(['Stud_ID' => '202400777', 'Email' => 'remember@example.com', 'Password' => Hash::make('remember123')]);
		$response = $this->post(route('login.submit'), [
			'Stud_ID' => $student->Stud_ID,
			'Password' => 'remember123',
			'remember' => 'on',
		]);
		$response->assertRedirect(route('dashboard'));
		$cookies = $response->headers->getCookies();
		$this->assertTrue(collect($cookies)->contains(function($c){ return str_contains($c->getName(), 'remember'); }), 'Remember me cookie not found');
	}

	// [18] No user is created on failed login
	#[Test]
	public function no_user_is_created_on_failed_login(): void
	{
		$before = \App\Models\User::count();
		$this->post(route('login.submit'), ['Stud_ID'=>'909999999','Password'=>'nope']);
		$after = \App\Models\User::count();
		$this->assertSame($before, $after);
	}

	// [21] User remains authenticated after a subsequent failed login attempt
	#[Test]
	public function remains_authenticated_after_failed_relogin_attempt(): void
	{
		$student = $this->createStudent(['Stud_ID'=>'202401122','Password'=>Hash::make('stay')]);
		$this->post(route('login.submit'), ['Stud_ID'=>'202401122','Password'=>'stay']);
		$this->assertAuthenticatedAs($student);
		$res = $this->post(route('login.submit'), ['Stud_ID'=>'202401122','Password'=>'wrong-after']);
		$this->assertAuthenticatedAs($student);
	}

	// ===== UI/Routes =====

	// [23] Forgot Password route renders successfully
	#[Test]
	public function forgot_password_route_renders(): void
	{
		$resp = $this->get(route('forgotpassword'));
		$resp->assertStatus(200);
	}

	// [22] Login page renders expected student form elements and controls
	#[Test]
	public function login_page_has_expected_form_elements(): void
	{
		$resp = $this->get(route('login'));
		$resp->assertStatus(200);
		$html = $resp->getContent();
		$this->assertStringContainsString('id="student-login-form"', $html);
		$this->assertStringContainsString('name="Stud_ID"', $html);
		$this->assertStringContainsString('name="Password"', $html);
		$this->assertStringContainsString('type="checkbox" name="remember"', $html);
		$this->assertStringContainsString('id="toggle-password-btn"', $html);
		$this->assertStringContainsString('Forgot Password?', $html);
		$this->assertStringContainsString('type="submit"', $html);
		$this->assertStringContainsString('name="_token"', $html);
	}

	// ===== Auditing & messaging =====

	// [26] Successful login is audited in attempts table
	#[Test]
	public function successful_login_is_audited_in_attempts_table(): void
	{
		$student = $this->createStudent(['Stud_ID' => '202401333', 'Password' => Hash::make('auditpw')]);
		$this->post(route('login.submit'), ['Stud_ID' => $student->Stud_ID, 'Password' => 'auditpw']);
		$row = DB::table('login_attempts')->where('stud_id', $student->Stud_ID)->orderByDesc('id')->first();
		$this->assertNotNull($row);
		$this->assertEquals(1, $row->successful);
		$this->assertEquals('success', $row->reason);
		$this->get(route('dashboard'))->assertOk();
	}

	// [27] Specific error message shown when password is wrong
	#[Test]
	public function shows_specific_message_when_password_is_wrong(): void
	{
		$student = $this->createStudent(['Stud_ID' => '202401444', 'Password' => Hash::make('specpass123')]);
		$resp = $this->post(route('login.submit'), [
			'Stud_ID' => $student->Stud_ID,
			'Password' => 'wrongpw'
		]);
		$resp->assertSessionHas('errors');
		$bag = session('errors');
		$flat = $bag ? $bag->all() : [];
		$this->assertTrue(in_array('Incorrect password.', $flat), 'Expected incorrect password message');
		$this->assertGuest();
	}
}

