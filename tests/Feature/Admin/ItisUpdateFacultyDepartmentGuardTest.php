<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
    use RefreshDatabase;

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
}
