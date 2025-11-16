<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Professor;

class AdminNotificationOnProfessorLeaveTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureProfessorsTable();
        $this->ensureCalendarOverridesTable();
        $this->ensureNotificationsTable();
    }

    protected function createProfessor(array $overrides = []): Professor
    {
        $data = array_merge(
            [
                "Prof_ID" => 20256661,
                "Name" => "Prof Tester",
                "Email" => "leave@test.local",
                "Password" => Hash::make("secret"),
            ],
            $overrides,
        );
        return Professor::create($data);
    }

    public function test_professor_leave_creates_admin_notification(): void
    {
        $prof = $this->createProfessor();
        $this->actingAs($prof, "professor");

        $date = now()->toDateString();
        $resp = $this->postJson("/api/professor/calendar/leave/apply", ["start_date" => $date]);
        $resp->assertStatus(200)->assertJson(["success" => true]);

        $notif = DB::table("notifications")->where("type", "professor_leave")->first();
        $this->assertNotNull($notif, "Admin notification for professor leave should be created");
        $this->assertEquals($prof->Prof_ID, $notif->user_id);
        $this->assertEquals(0, $notif->booking_id);
        $this->assertStringContainsString($prof->Name, $notif->message);
        $this->assertStringContainsString($date, $notif->message);
    }

        private function ensureProfessorsTable(): void
        {
            Schema::disableForeignKeyConstraints();
            Schema::dropIfExists('professors');
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
            Schema::enableForeignKeyConstraints();
        }

        private function ensureCalendarOverridesTable(): void
        {
            Schema::dropIfExists('calendar_overrides');
            Schema::create('calendar_overrides', function (Blueprint $table) {
                $table->id();
                $table->date('start_date');
                $table->date('end_date');
                $table->enum('scope_type', ['all', 'department', 'subject', 'professor'])->default('all');
                $table->unsignedBigInteger('scope_id')->nullable();
                $table->foreignId('term_id')->nullable();
                $table->enum('effect', ['force_mode', 'block_all', 'holiday']);
                $table->enum('allowed_mode', ['online', 'onsite'])->nullable();
                $table->string('reason_key')->nullable();
                $table->text('reason_text')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        private function ensureNotificationsTable(): void
        {
            Schema::dropIfExists('notifications');
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id');
                $table->integer('booking_id');
                $table->foreignId('term_id')->nullable();
                $table->string('type');
                $table->string('title');
                $table->text('message');
                $table->boolean('is_read')->default(false);
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            });
        }
}
