<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        $this->ensureLegacyTableCoverage();
    }

    protected function tearDown(): void
    {
        Schema::enableForeignKeyConstraints();

        parent::tearDown();
    }

    /**
     * Many legacy tables in the consultation schema have missing columns when running tests locally.
     * Ensure the minimal columns that the feature tests expect are always available.
     */
    protected function ensureLegacyTableCoverage(): void
    {
        $this->ensureStudentTable();
        $this->ensureProfessorTable();
        $this->ensureConsultationBookingTable();
        $this->ensureLoginAttemptsTable();
        $this->ensureChatMessagesTable();
        $this->ensureNotificationTable();
    }

    protected function ensureStudentTable(): void
    {
        if (!Schema::hasTable("t_student")) {
            Schema::create("t_student", function (Blueprint $table) {
                $table->string("Stud_ID", 12)->primary();
                $table->string("Name", 100)->nullable();
                $table->string("Dept_ID", 32)->nullable();
                $table->string("Email", 150)->nullable();
                $table->string("Password", 255)->nullable();
                $table->string("profile_picture", 255)->nullable();
                $table->boolean("is_active")->default(true);
                $table->rememberToken();
                $table->timestamps();
            });
            return;
        }

        Schema::table("t_student", function (Blueprint $table) {
            if (!Schema::hasColumn("t_student", "Dept_ID")) {
                $table->string("Dept_ID", 32)->nullable()->after("Name");
            }
            if (!Schema::hasColumn("t_student", "profile_picture")) {
                $table->string("profile_picture", 255)->nullable()->after("Password");
            }
            if (!Schema::hasColumn("t_student", "is_active")) {
                $table->boolean("is_active")->default(true)->after("profile_picture");
            }
            if (!Schema::hasColumn("t_student", "remember_token")) {
                $table->rememberToken();
            }
        });
    }

    protected function ensureProfessorTable(): void
    {
        if (!Schema::hasTable("professors")) {
            Schema::create("professors", function (Blueprint $table) {
                $table->string("Prof_ID", 12)->primary();
                $table->string("Name", 100)->nullable();
                $table->string("Dept_ID", 32)->nullable();
                $table->string("Email", 150)->nullable();
                $table->string("Password", 255);
                $table->text("Schedule")->nullable();
                $table->string("profile_picture", 255)->nullable();
                $table->boolean("is_active")->default(true);
                $table->rememberToken();
            });
            return;
        }

        Schema::table("professors", function (Blueprint $table) {
            if (!Schema::hasColumn("professors", "Dept_ID")) {
                $table->string("Dept_ID", 32)->nullable()->after("Name");
            }
            if (!Schema::hasColumn("professors", "profile_picture")) {
                $table->string("profile_picture", 255)->nullable()->after("Schedule");
            }
            if (!Schema::hasColumn("professors", "is_active")) {
                $table->boolean("is_active")->default(true)->after("profile_picture");
            }
            if (!Schema::hasColumn("professors", "remember_token")) {
                $table->rememberToken();
            }
        });
    }

    protected function ensureConsultationBookingTable(): void
    {
        if (!Schema::hasTable("t_consultation_bookings")) {
            Schema::create("t_consultation_bookings", function (Blueprint $table) {
                $table->bigIncrements("Booking_ID");
                $table->string("Prof_ID", 12);
                $table->string("Stud_ID", 12)->nullable();
                $table->string("Booking_Date", 32);
                $table->string("Mode", 32)->nullable();
                $table->string("Status", 32)->default("pending");
                $table->unsignedInteger("Subject_ID")->nullable();
                $table->unsignedBigInteger("term_id")->nullable();
                $table->unsignedInteger("Consult_type_ID")->nullable();
                $table->string("Custom_Type", 150)->nullable();
                $table->timestamp("Created_At")->nullable();
                $table->timestamp("Updated_At")->nullable();
                $table->timestamp("one_hour_reminder_sent_at")->nullable();
                $table->text("reschedule_reason")->nullable();
            });
            return;
        }

        Schema::table("t_consultation_bookings", function (Blueprint $table) {
            if (!Schema::hasColumn("t_consultation_bookings", "Consult_type_ID")) {
                $table->unsignedInteger("Consult_type_ID")->nullable()->after("Subject_ID");
            }
            if (!Schema::hasColumn("t_consultation_bookings", "term_id")) {
                $table->unsignedBigInteger("term_id")->nullable()->after("Subject_ID");
            }
            if (!Schema::hasColumn("t_consultation_bookings", "Custom_Type")) {
                $table->string("Custom_Type", 150)->nullable()->after("Consult_type_ID");
            }
            if (!Schema::hasColumn("t_consultation_bookings", "one_hour_reminder_sent_at")) {
                $table->timestamp("one_hour_reminder_sent_at")->nullable()->after("Updated_At");
            }
            if (!Schema::hasColumn("t_consultation_bookings", "reschedule_reason")) {
                $table->text("reschedule_reason")->nullable()->after("one_hour_reminder_sent_at");
            }
            if (!Schema::hasColumn("t_consultation_bookings", "Booking_Time")) {
                $table->string("Booking_Time", 32)->nullable()->after("Booking_Date");
            }

            // Normalize Booking_Date to varchar to match legacy format if it is still a DATE column.
            $column = DB::selectOne("SHOW COLUMNS FROM t_consultation_bookings LIKE 'Booking_Date'");
            $type = strtolower((string) ($column->Type ?? ""));
            if (!str_contains($type, "varchar")) {
                DB::statement("ALTER TABLE `t_consultation_bookings` MODIFY `Booking_Date` VARCHAR(32) NOT NULL");
            }
        });
    }

    protected function ensureLoginAttemptsTable(): void
    {
        if (!Schema::hasTable("login_attempts")) {
            Schema::create("login_attempts", function (Blueprint $table) {
                $table->bigIncrements("id");
                $table->string("stud_id", 32)->nullable();
                $table->string("prof_id", 32)->nullable();
                $table->string("ip", 45)->nullable();
                $table->string("user_agent", 255)->nullable();
                $table->boolean("successful")->default(false);
                $table->string("reason", 64)->nullable();
                $table->timestamps();
            });
            return;
        }

        Schema::table("login_attempts", function (Blueprint $table) {
            if (!Schema::hasColumn("login_attempts", "prof_id")) {
                $table->string("prof_id", 32)->nullable()->after("stud_id");
            }
        });
    }

    protected function ensureNotificationTable(): void
    {
        if (!Schema::hasTable("notifications")) {
            Schema::create("notifications", function (Blueprint $table) {
                $table->bigIncrements("id");
                $table->unsignedBigInteger("user_id");
                $table->unsignedBigInteger("booking_id")->nullable();
                $table->string("type", 64);
                $table->string("title");
                $table->text("message")->nullable();
                $table->boolean("is_read")->default(false);
                $table->timestamps();
            });
            return;
        }

        if (!Schema::hasColumn("notifications", "booking_id")) {
            Schema::table("notifications", function (Blueprint $table) {
                $table->unsignedBigInteger("booking_id")->nullable()->after("user_id");
            });
        } else {
            $column = DB::selectOne("SHOW COLUMNS FROM notifications LIKE 'booking_id'");
            $isNullable = strtolower((string) ($column->Null ?? "yes")) === "yes";
            if (!$isNullable) {
                $typeDefinition = strtoupper((string) ($column->Type ?? "INT"));
                DB::statement("ALTER TABLE `notifications` MODIFY `booking_id` {$typeDefinition} NULL");
            }
        }

        Schema::table("notifications", function (Blueprint $table) {
            if (!Schema::hasColumn("notifications", "message")) {
                $table->text("message")->nullable()->after("title");
            }
            if (!Schema::hasColumn("notifications", "is_read")) {
                $table->boolean("is_read")->default(false)->after("message");
            }
            if (!Schema::hasColumn("notifications", "created_at")) {
                $table->timestamp("created_at")->nullable();
            }
            if (!Schema::hasColumn("notifications", "updated_at")) {
                $table->timestamp("updated_at")->nullable();
            }
        });
    }

    protected function ensureChatMessagesTable(): void
    {
        if (!Schema::hasTable("t_chat_messages")) {
            Schema::create("t_chat_messages", function (Blueprint $table) {
                $table->bigIncrements("id");
                $table->unsignedBigInteger("Booking_ID")->nullable();
                $table->string("Stud_ID", 12)->nullable();
                $table->string("Prof_ID", 12)->nullable();
                $table->string("Sender", 50)->nullable();
                $table->string("Recipient", 50)->nullable();
                $table->text("Message")->nullable();
                $table->timestamp("Created_At")->nullable();
                $table->string("status", 32)->nullable();
                $table->boolean("is_read")->default(false);
                $table->string("file_path", 255)->nullable();
                $table->string("file_type", 255)->nullable();
                $table->string("original_name", 255)->nullable();
            });
            return;
        }

        Schema::table("t_chat_messages", function (Blueprint $table) {
            if (!Schema::hasColumn("t_chat_messages", "file_path")) {
                $table->string("file_path", 255)->nullable()->after("is_read");
            }
            if (!Schema::hasColumn("t_chat_messages", "file_type")) {
                $table->string("file_type", 255)->nullable()->after("file_path");
            }
            if (!Schema::hasColumn("t_chat_messages", "original_name")) {
                $table->string("original_name", 255)->nullable()->after("file_type");
            }
            if (!Schema::hasColumn("t_chat_messages", "status")) {
                $table->string("status", 32)->nullable()->after("Message");
            }
        });

        $column = DB::selectOne("SHOW COLUMNS FROM t_chat_messages LIKE 'file_type'");
        if ($column) {
            $type = strtolower((string) ($column->Type ?? ""));
            if (!str_contains($type, "255")) {
                DB::statement("ALTER TABLE `t_chat_messages` MODIFY `file_type` VARCHAR(255) NULL");
            }
        }
    }
}
