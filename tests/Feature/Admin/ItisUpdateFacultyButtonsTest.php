<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Admin;

class ItisUpdateFacultyButtonsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureProfessorsTable();
    }

    private function ensureProfessorsTable(): void
    {
        if (Schema::hasTable('professors')) {
            $needsReset = !Schema::hasColumn('professors', 'Dept_ID') ||
                !Schema::hasColumn('professors', 'is_active') ||
                !Schema::hasColumn('professors', 'profile_picture');

            if ($needsReset) {
                Schema::drop('professors');
            }
        }

        if (!Schema::hasTable('professors')) {
            Schema::create('professors', function (Blueprint $table) {
                $table->string('Prof_ID', 12)->primary();
                $table->string('Name')->nullable();
                $table->string('Dept_ID', 50)->nullable();
                $table->string('Email')->nullable();
                $table->string('Password')->nullable();
                $table->string('profile_picture')->nullable();
                $table->text('Schedule')->nullable();
                $table->string('remember_token', 100)->nullable();
                $table->boolean('is_active')->default(1);
            });
        }

        DB::statement('DELETE FROM professors');
    }

    private function actingAsAdmin(): Admin
    {
        // We don't need a real DB row for session guard auth in tests
        $admin = new Admin([
            "Admin_ID" => "A0001",
            "Name" => "Test Admin",
            "Email" => "admin@example.com",
        ]);
        $this->actingAs($admin, "admin");
        // Disable CSRF for form submissions in feature tests
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        return $admin;
    }

    /**
     * Sanity: the admin ITIS page renders and includes the lower-right action buttons
     * inside the Edit Faculty panel (Cancel, Delete, Update Faculty).
     */
    public function test_edit_panel_buttons_are_present(): void
    {
        $this->actingAsAdmin();

        $this->ensureSubjectTables();

        $resp = $this->get("/admin-itis");
        $resp->assertStatus(200);

        // Extract the action bar HTML segment and assert the exact buttons exist inside it
        $html = $resp->getContent();
        // Anchor to the edit panel section specifically (the panel is a div wrapper)
        $editAnchor = strpos($html, '<div id="editFacultyPanel"');
        $this->assertNotFalse($editAnchor, "Edit Faculty panel form not found");
        $start = strpos($html, '<div class="panel-actions">', $editAnchor);
        $this->assertNotFalse($start, "Edit panel action bar not found");
        $snippet = substr($html, $start, 1200); // window should include the three buttons

        // Cancel button
        $this->assertStringContainsString('button" class="btn-secondary"', $snippet);
        $this->assertStringContainsString(
            'onclick="ModalManager.close(\'editFaculty\')"',
            $snippet,
        );
        $this->assertStringContainsString(">Cancel<", $snippet);

        // Delete button
        $this->assertStringContainsString('class="delete-prof-btn-modal btn-danger"', $snippet);
        $this->assertStringContainsString(">Delete<", $snippet);

        // Update button
        $this->assertStringContainsString('type="submit" class="btn-primary"', $snippet);
        $this->assertStringContainsString(">Update Faculty<", $snippet);
    }

    /**
     * Update button flow: posting to the update endpoint updates a professor's basic fields.
     */
    public function test_update_professor_success(): void
    {
        $this->actingAsAdmin();

        // seed a professor in ITIS (Dept_ID = 1)
        DB::table("professors")->insert([
            "Prof_ID" => "20200001",
            "Name" => "Prof Alpha",
            "Dept_ID" => "1",
            "Email" => "alpha@example.com",
            "Password" => "x",
            "Schedule" => null,
            "is_active" => 1,
        ]);

        $payload = [
            "Name" => "Prof Alpha Updated",
            "Schedule" => "Monday 09:00 AM to 11:00 AM",
            // subjects omitted in minimal schema
        ];

        $resp = $this->postJson("/admin-itis/update-professor/20200001", $payload);
        $resp->assertStatus(200)->assertJson([
            "success" => true,
        ]);

        $this->assertDatabaseHas("professors", [
            "Prof_ID" => "20200001",
            "Name" => "Prof Alpha Updated",
            "Schedule" => "Monday 09:00 AM to 11:00 AM",
        ]);
    }

    /**
     * Delete button flow: deleting via the ITIS route removes the professor.
     */
    public function test_delete_professor_success(): void
    {
        $this->actingAsAdmin();

        DB::table("professors")->insert([
            "Prof_ID" => "20200002",
            "Name" => "Prof Beta",
            "Dept_ID" => "1",
            "Email" => "beta@example.com",
            "Password" => "x",
            "Schedule" => null,
            "is_active" => 1,
        ]);

        $resp = $this->deleteJson("/admin-itis/delete-professor/20200002");
        $resp->assertStatus(200)->assertJson(["success" => true]);

        $this->assertDatabaseMissing("professors", [
            "Prof_ID" => "20200002",
        ]);
    }

    /**
     * Update button: sending no actual changes should respond with a friendly
     * message and not mutate the database.
     */
    public function test_update_professor_no_changes_detected(): void
    {
        $this->actingAsAdmin();

        DB::table("professors")->insert([
            "Prof_ID" => "20200003",
            "Name" => "Prof Gamma",
            "Dept_ID" => "1",
            "Email" => "gamma@example.com",
            "Password" => "x",
            "Schedule" => "Tuesday 10:00 AM to 12:00 PM",
            "is_active" => 1,
        ]);

        // Submit the exact same data (no real change)
        $resp = $this->postJson("/admin-itis/update-professor/20200003", [
            "Name" => "Prof Gamma",
            "Schedule" => "Tuesday 10:00 AM to 12:00 PM",
        ]);

        $resp->assertStatus(200)->assertJson([
            "success" => false,
            "message" => "No changes detected",
        ]);

        // Ensure database remains unchanged
        $this->assertDatabaseHas("professors", [
            "Prof_ID" => "20200003",
            "Name" => "Prof Gamma",
            "Schedule" => "Tuesday 10:00 AM to 12:00 PM",
        ]);
    }

    /**
     * Update button: validation guard — missing Name should return 422 with
     * structured error payload and keep the current DB values.
     */
    public function test_update_professor_validation_error_on_missing_name(): void
    {
        $this->actingAsAdmin();

        DB::table("professors")->insert([
            "Prof_ID" => "20200004",
            "Name" => "Prof Delta",
            "Dept_ID" => "1",
            "Email" => "delta@example.com",
            "Password" => "x",
            "Schedule" => null,
            "is_active" => 1,
        ]);

        $resp = $this->postJson("/admin-itis/update-professor/20200004", [
            // 'Name' omitted intentionally
            "Schedule" => "Wednesday 01:00 PM to 03:00 PM",
        ]);

        $resp
            ->assertStatus(422)
            ->assertJson([
                "success" => false,
                "message" => "Validation error",
            ])
            ->assertJsonStructure(["errors" => ["Name"]]);

        // Ensure DB not changed
        $this->assertDatabaseHas("professors", [
            "Prof_ID" => "20200004",
            "Name" => "Prof Delta",
            "Schedule" => null,
        ]);
    }

    private function ensureSubjectTables(): void
    {
        if (!Schema::hasTable('t_subject')) {
            Schema::create('t_subject', function (Blueprint $table) {
                $table->increments('Subject_ID');
                $table->string('Subject_Name')->nullable();
            });
        }

        if (!Schema::hasTable('professor_subject')) {
            Schema::create('professor_subject', function (Blueprint $table) {
                $table->id();
                $table->string('Prof_ID', 12);
                $table->unsignedInteger('Subject_ID');
            });
        }

        DB::statement('DELETE FROM t_subject');
        DB::statement('DELETE FROM professor_subject');
    }
}
