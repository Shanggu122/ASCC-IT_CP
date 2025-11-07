<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Professor;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class ConsultationLogTest extends TestCase
{
    use RefreshDatabase;

    protected $professor;
    protected $student;
    protected $subject;
    protected $consultation;

    protected function setUp(): void
    {
        parent::setUp();

        // Create consultation types
        DB::table('t_consultation_types')->insert([
            'Consult_type_ID' => 'TYPE1',
            'Consult_Type' => 'tutoring',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create test data
        DB::table('professors')->insert([
            'Prof_ID' => 'P12345',
            'Name' => 'Test Professor',
            'Email' => 'professor@test.com',
            'Password' => bcrypt('password123'),
            'Schedule' => 'MWF 9:00-11:00'
        ]);
        $this->professor = 'P12345';

        DB::table('t_student')->insert([
            'Stud_ID' => '2022-12345',
            'Name' => 'Test Student',
            'Email' => 'student@test.com',
            'Password' => bcrypt('password123'),
            'Dept_ID' => 'IT'
        ]);
        $this->student = '2022-12345';

        DB::table('t_subject')->insert([
            'Subject_ID' => '101',
            'subject_code' => 'TEST101',
            'subject_name' => 'Test Subject',
            'Units' => 3,
            'Dept_ID' => 'IT'
        ]);
        $this->subject = 'TEST101';

        // Create a test consultation booking
        DB::table('t_consultation_bookings')->insert([
            'Booking_ID' => 'TEST123',
            'Student_ID' => $this->student,
            'Prof_ID' => $this->professor,
            'Subject_ID' => '101', // Using the Subject_ID we created earlier
            'Consult_type_ID' => 'TYPE1',
            'Booking_Date' => now()->addDays(2)->format('D M d Y'),
            'Mode' => 'online',
            'Status' => 'pending',
            'Created_At' => now(),
            'Updated_At' => now()
        ]);
        $this->consultation = 'TEST123';
    }

    /** @test */
    public function consultation_logs_are_displayed_properly()
    {
        // Create and login as professor
        $professor = Professor::where('Prof_ID', 'P12345')->first();
        $this->actingAs($professor, 'professor');

        $response = $this->get('/conlog-professor');

        $response->assertStatus(200)
            ->assertViewIs('conlog-professor')
            ->assertSee('Test Student')
            ->assertSee('Test Subject') // Using subject_name instead of subject_code
            ->assertSee('tutoring')
            ->assertSee('online');
    }

    /** @test */
    public function search_bar_allows_filtering_consultation_logs()
    {
        // Create another consultation for testing search
        DB::table('t_consultation_bookings')->insert([
            'Booking_ID' => 'TEST456',
            'Student_ID' => $this->student,
            'Prof_ID' => $this->professor,
            'Subject_ID' => '101', // Using the same Subject_ID since we're testing search
            'Booking_Date' => now()->addDays(3)->format('D M d Y'),
            'Consult_type_ID' => 'TYPE1',
            'Mode' => 'onsite',
            'Status' => 'pending',
            'Created_At' => now(),
            'Updated_At' => now()
        ]);

        // Login as professor
        $professor = Professor::where('Prof_ID', 'P12345')->first();
        $this->actingAs($professor, 'professor');

        // Test search functionality
        $response = $this->get('/conlog-professor');
        $response->assertStatus(200);

        // Assert both consultations are visible
        $response->assertSee('Test Subject')
                ->assertSee('tutoring');

        // Test search filter via JavaScript (verify the data attributes are present)
        $response->assertSee('data-label="Student"')
                ->assertSee('data-label="Subject"')
                ->assertSee('data-label="Type"');
    }

    /** @test */
    public function type_filter_dropdown_works()
    {
        // Create consultations with different types
        DB::table('t_consultation_bookings')->insert([
            'Booking_ID' => 'TEST789',
            'Student_ID' => $this->student,
            'Prof_ID' => $this->professor,
            'Subject_ID' => '101',
            'Booking_Date' => now()->addDays(4)->format('D M d Y'),
            'Consult_type_ID' => 'TYPE1',
            'Mode' => 'online',
            'Status' => 'pending',
            'Created_At' => now(),
            'Updated_At' => now()
        ]);

        // Login as professor
        $professor = Professor::where('Prof_ID', 'P12345')->first();
        $this->actingAs($professor, 'professor');

        $response = $this->get('/conlog-professor');
        
        // Verify filter options are present
        $response->assertSee('All Types')
                ->assertSee('Tutoring')
                ->assertSee('Grade Consultation')
                ->assertSee('Others');

        // Verify data attributes for filtering
        $response->assertSee('data-label="Type"');
    }

    /** @test */
    public function shows_no_consultations_message_when_empty()
    {
        // Clear all consultations
        DB::table('t_consultation_bookings')->delete();

        // Login as professor
        $professor = Professor::where('Prof_ID', 'P12345')->first();
        $this->actingAs($professor, 'professor');

        $response = $this->get('/conlog-professor');

        $response->assertStatus(200)
            ->assertSee('No consultations found');
    }

    /** @test */
    public function professor_can_accept_requested_consultation()
    {
        // Login as professor
        $professor = Professor::where('Prof_ID', 'P12345')->first();
        $this->actingAs($professor, 'professor');

        // Try to approve the consultation
        $response = $this->post('/api/consultations/update-status', [
            'id' => 'TEST123',
            'status' => 'approved'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Status updated to approved.'
            ]);

        // Verify database was updated
        $this->assertDatabaseHas('t_consultation_bookings', [
            'Booking_ID' => 'TEST123',
            'Status' => 'approved'
        ]);
    }

    /** @test */
    public function professor_cannot_access_consultations_when_not_authenticated()
    {
        $response = $this->get('/conlog-professor');
        $response->assertRedirect('/login-professor');
    }
}