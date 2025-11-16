<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Admin;

/**
 * This test is designed to reveal a real system issue: the ITIS update route
 * currently allows updating a ComSci (Dept_ID=2) professor. We expect ITIS
 * to only update ITIS (Dept_ID=1) professors.
 *
 * NOTE: This will fail until the controller enforces department checks.
 */
class ItisUpdateFacultyDepartmentGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureProfessorsTable();
    }

    private function actingAsAdmin(): void
    {
        $admin = new Admin([
            "Admin_ID" => "A0001",
            "Name" => "Admin",
            "Email" => "admin@example.com",
        ]);
        $this->actingAs($admin, "admin");
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_itis_route_should_not_update_comsci_professors(): void
    {
        $this->actingAsAdmin();

        // Seed a ComSci professor (Dept_ID = 2)
        DB::table("professors")->insert([
            "Prof_ID" => "C2001",
            "Name" => "ComSci Prof",
            "Dept_ID" => "2",
            "Email" => "c2001@example.com",
            "Password" => "x",
            "Schedule" => null,
            "is_active" => 1,
        ]);

        // Attempt to update via the ITIS route
        $resp = $this->postJson("/admin-itis/update-professor/C2001", [
            "Name" => "ComSci Prof Updated From ITIS",
        ]);

        // EXPECTED: forbidden or explicit failure (policy)
        $resp->assertStatus(403)->assertJson(["success" => false]);

        // Also ensure DB not changed (defense-in-depth)
        $this->assertDatabaseHas("professors", [
            "Prof_ID" => "C2001",
            "Name" => "ComSci Prof",
        ]);
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
}
