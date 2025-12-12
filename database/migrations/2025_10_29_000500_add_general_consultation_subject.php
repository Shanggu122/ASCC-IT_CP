<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable("t_subject")) {
            return;
        }

        // Insert "General Consultation" subject if it doesn't exist yet
        $exists = DB::table("t_subject")
            ->whereRaw("LOWER(TRIM(Subject_Name)) = ?", ["general consultation"])
            ->exists();
        if (!$exists) {
            DB::table("t_subject")->insert([
                "Subject_Name" => "General Consultation",
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable("t_subject")) {
            return;
        }

        // Remove the subject by name (id can vary across environments)
        DB::table("t_subject")
            ->whereRaw("LOWER(TRIM(Subject_Name)) = ?", ["general consultation"])
            ->delete();
    }
};
