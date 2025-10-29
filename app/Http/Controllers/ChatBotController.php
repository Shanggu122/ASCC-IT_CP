<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Services\DialogflowService;
// Conversation persistence removed (FAQ only)

class ChatBotController extends Controller
{
    public function chat(Request $request, DialogflowService $dialogflow)
    {
        $text = (string) $request->input("message");
        $sessionId = session()->getId();

        // 1) Try lightweight DB-backed intents for student dashboard (English responses only)
        //    a) "<professor> schedule"
        //    b) "available slots ... <professor> ... <date>"
        // These replies only include non-sensitive info.
        // 1) Try lightweight DB-backed intents for student dashboard (English responses only)
        //    a) "<professor> schedule" including "this week" / "next week" summary
        //    b) "available slots ... <professor> ... <date>"
        // These replies only include non-sensitive info.
        $reply = $this->handleDbIntents($text);
        if ($reply !== null) {
            return response()->json(["reply" => $reply]);
        }

        try {
            $reply = $dialogflow->detectIntent($text, $sessionId);
            return response()->json(["reply" => $reply]);
        } catch (\Throwable $e) {
            Log::error("Dialogflow Error: " . $e->getMessage());
            $examples = [
                "Professor Abaleta schedule this week",
                "Are there available slots for Professor Benito on Friday?",
                "My pending schedules this week",
                "Do I have any consultation today?",
                "Which professors handle Data Structures?",
            ];
            $help =
                "Sorry, the chatbot is currently unavailable. You can try again later.\n" .
                "Examples I can answer:\n- " .
                implode("\n- ", $examples);
            return response()->json(["reply" => $help], 500);
        }
    }

    // --- Private helpers ---
    private function handleDbIntents(string $message): ?string
    {
        $m = mb_strtolower(trim($message));
        if ($m === "") {
            return null;
        }

        // Early guard: non-scheduling FAQ like "How do I contact my professor after booking?"
        // Route these to short guidance instead of misfiring the student-status flow.
        if (
            (str_contains($m, "contact") ||
                str_contains($m, "message") ||
                str_contains($m, "chat") ||
                str_contains($m, "email") ||
                str_contains($m, "reach out") ||
                str_contains($m, "pm") ||
                str_contains($m, "dm") ||
                // Common Tagalog phrasing
                str_contains($m, "paano") ||
                str_contains($m, "makontak") ||
                str_contains($m, "i-message") ||
                str_contains($m, "imessage") ||
                str_contains($m, "mag message")) &&
            (str_contains($m, "professor") ||
                str_contains($m, " prof ") ||
                str_contains($m, " sir ") ||
                str_contains($m, " ma'am ") ||
                str_contains($m, " maam "))
        ) {
            return "You can message your professor anytime from the Messages tab on your dashboard — no approved booking is required. " .
                "Open Messages, click New Message, search/select your professor, and send your message. " .
                "If you already have a booking, you can also reply in that existing conversation.";
        }
        // to steer them to check availability instead of misfiring into student-status.
        // Early guard: cancellation policy (should run BEFORE generic booking checker)
        if (
            (preg_match("/\b(cancel|cancellation)\b/i", $m) ||
                preg_match("/i-?cancel|icancel|kansel(?:ahin)?|kansel/i", $m) ||
                preg_match("/tanggalin|alisin/i", $m)) &&
            (str_contains($m, "booking") ||
                str_contains($m, "consultation") ||
                str_contains($m, "schedule"))
        ) {
            return "You can cancel a booking within 1 hour from the time you created it, as long as it’s still Pending. After 1 hour or once it’s Approved you can’t cancel in the system. If you need to change the time, message your professor to request a reschedule.";
        }

        // If it's a pure how-to booking question with no professor and no date, let Dialogflow handle it
        $isHowToBookCore = (bool) preg_match("/\bhow\s+(do\s+i|to)\s+(book|schedule)\b/i", $m);
        $isHowToBookTagalog = (bool) preg_match(
            "/\bpaano\s+(ako\s+)?mag[\s-]?(book|schedule)\b/i",
            $m,
        );
        if ($isHowToBookCore || $isHowToBookTagalog) {
            $profProbe = $this->matchProfessorFromMessage($m);
            $dateProbe = $this->extractDate($m);
            if (!$profProbe && !$dateProbe) {
                // Return null so chat() falls back to Dialogflow's richer how-to response
                return null;
            }
        }

        $asksToBook =
            (preg_match("/\b(can|pwede|puwede)\b/i", $m) &&
                // Exclude resched* so we don't hijack reschedule questions
                !preg_match("/resched/i", $m) &&
                (str_contains($m, " schedule ") ||
                    str_contains($m, " book ") ||
                    str_contains($m, " booking "))) ||
            (str_contains($m, "can i schedule") && !preg_match("/resched/i", $m)) ||
            str_contains($m, "can i book") ||
            // How-to phrasing (English)
            ((bool) preg_match("/\bhow\s+(do\s+i|to)\s+(book|schedule)\b/i", $m) &&
                !preg_match("/resched/i", $m)) ||
            // Tagalog: paano mag-book/schedule (+ common variations)
            ((bool) preg_match("/\bpaano\s+(ako\s+)?mag[\s-]?(book|schedule)\b/i", $m) &&
                !preg_match("/resched/i", $m));

        $negAvailability =
            // English
            str_contains($m, "unavailable") ||
            str_contains($m, "not available") ||
            str_contains($m, "no schedule") ||
            str_contains($m, "no schedules") ||
            str_contains($m, "not yet available") ||
            // Tagalog variants
            str_contains($m, "walang schedule") ||
            str_contains($m, "wala pang schedule") ||
            str_contains($m, "hindi available") ||
            str_contains($m, "di available") ||
            str_contains($m, "ndi available");

        if ($asksToBook || $negAvailability) {
            $prof = $this->matchProfessorFromMessage($m);
            if ($prof) {
                return "You can only book on dates that are in " .
                    $prof->Name .
                    "'s schedule and still have open slots. " .
                    "To check availability, ask: 'Are there available slots for " .
                    $prof->Name .
                    " on Friday?' or view their 'this/next week' schedule.";
            }
            return "You can only book on dates that are in the professor's schedule and still have open slots. " .
                "Specify the professor and date, e.g.: 'Are there available slots for Prof Benito on 2025-11-03?'";
        }

        // Early guard: rescheduling guidance (avoid matching generic schedule intent)
        if (
            str_contains($m, "resched") ||
            str_contains($m, "reschedule") ||
            preg_match("/\bresched(?:ule|uling)?\b/i", $m)
        ) {
            return "You can’t directly reschedule a booking. If it’s still Pending and within 1 hour of creation, cancel it and book a new date/time. If it’s already Approved or past 1 hour, message your professor from the Messages tab to request a new time they may reschedule it or ask you to cancel and rebook.";
        }

        // Early guard: "Can I see/meet <prof> now?" or "Is <prof> in the office?" -> steer to Messages page and department office
        $asksSeeNow =
            (str_contains($m, " now") ||
                str_contains($m, "right now") ||
                str_contains($m, "ngayon")) &&
            (str_contains($m, "see") ||
                str_contains($m, "meet") ||
                str_contains($m, "talk") ||
                str_contains($m, "kita") ||
                str_contains($m, "makita") ||
                str_contains($m, "makausap") ||
                str_contains($m, "puntahan") ||
                str_contains($m, "available"));
        $asksInOffice =
            (bool) preg_match("/\b(in\s+the\s+office|nasa\s+office)\b/i", $m) ||
            ((bool) preg_match("/\boffice\b/i", $m) && (bool) preg_match("/\b(is|ba)\b/i", $m));
        if ($asksSeeNow || $asksInOffice) {
            $prof = $this->matchProfessorFromMessage($m);
            if ($prof) {
                $deptName = "the department office";
                try {
                    if (Schema::hasColumn("professors", "Dept_ID")) {
                        $deptId =
                            $prof->Dept_ID ??
                            DB::table("professors")
                                ->where("Prof_ID", $prof->Prof_ID)
                                ->value("Dept_ID");
                        if ((int) $deptId === 1) {
                            $deptName = "the IT&IS Department office";
                        } elseif ((int) $deptId === 2) {
                            $deptName = "the Computer Science Department office";
                        } else {
                            $deptName = "the department office";
                        }
                    }
                } catch (\Throwable $e) {
                }
                return sprintf(
                    "Walk-ins aren't guaranteed. Please send a quick message to %s via the Messages page on your dashboard. If it's urgent, you may also visit %s to check if they can accommodate you.",
                    (string) $prof->Name,
                    $deptName,
                );
            }
            return "Walk-ins aren't guaranteed. Please message your professor from the Messages page on your dashboard. If it's urgent, you may visit the IT&IS or Computer Science department office to check if they can accommodate you.";
        }

        // Early intent: List professors by department (names only)
        // Examples: "List all the professors in IT&IS", "Professors in ComSci",
        //           "Who teaches in the Computer Science department?"
        $mentionsDeptItis = (bool) preg_match(
            "/\b(
                it&?is
                |it\s*&\s*is
                |it\s*is
                |itis
                |it\s*department
                |information\s*technology(?:\s*department)?(?:\s*(?:&|and)\s*information\s*systems)?
                |info\s*tech
                |infotech
            )\b/ix",
            $m,
        );
        $mentionsDeptCs = (bool) preg_match("/\b(comsci|computer\s*science|comp\s*sci|cs)\b/i", $m);
        $asksListProfs =
            (str_contains($m, "list") || str_contains($m, "sino") || str_contains($m, "who")) &&
            (bool) preg_match("/\b(professor(s)?|faculty|faculty\s+members)\b/i", $m);
        // Also treat teacher/instructor phrasing as the same intent when a department is mentioned
        $asksListTeachers =
            (str_contains($m, "who") || str_contains($m, "sino")) &&
            ((bool) preg_match(
                "/\b(teach|teaches|teacher|teachers|instructor|instructors)\b/i",
                $m,
            ) ||
                // Common Tagalog phrasing
                str_contains($m, "nagtuturo") ||
                str_contains($m, "nag turo") ||
                str_contains($m, "teacher") ||
                str_contains($m, "guro"));
        $mentionsScheduleOrAvailability =
            str_contains($m, "schedule") ||
            (bool) preg_match("/\b(consultation|consult|office)\s*hour(s)?\b/i", $m) ||
            (bool) preg_match("/\bconsultation\s*(time|times)\b/i", $m) ||
            str_contains($m, "availability") ||
            str_contains($m, "available");
        if (
            ($mentionsDeptItis || $mentionsDeptCs) &&
            ($asksListProfs || $asksListTeachers) &&
            !$mentionsScheduleOrAvailability
        ) {
            if (!Schema::hasColumn("professors", "Dept_ID")) {
                return "Department information is not available.";
            }
            $deptId = $mentionsDeptItis ? 1 : 2;
            $deptLabel = $mentionsDeptItis ? "IT&IS" : "Computer Science";
            $names = DB::table("professors")
                ->where("Dept_ID", $deptId)
                ->orderBy("Name")
                ->pluck("Name")
                ->toArray();
            if (empty($names)) {
                return "There are no professors listed under " . $deptLabel . ".";
            }
            $out = [$deptLabel . " Professors:"];
            foreach ($names as $nm) {
                $out[] = "- " . (string) $nm;
            }
            return implode("\n", $out);
        }

        // Early intent: Full faculty schedule (list all professors and their consultation schedule)
        // Detect group-level schedule queries (faculty/professors), even without explicit "all"
        $containsScheduleKeyword =
            str_contains($m, "schedule") ||
            (bool) preg_match("/\b(consultation|consult|office)\s*hour(s)?\b/i", $m) ||
            (bool) preg_match("/\bconsultation\s*(time|times)\b/i", $m);
        $mentionsFacultyWord = str_contains($m, "faculty") || str_contains($m, "faculty members");
        $mentionsPluralProfessors = (bool) preg_match("/\bprofessors\b/i", $m);
        $mentionsAllModifier =
            str_contains($m, "all ") ||
            str_contains($m, "full ") ||
            str_contains($m, "entire ") ||
            str_contains($m, "whole ") ||
            // Tagalog
            str_contains($m, "lahat ") ||
            preg_match("/\blahat\s+ng\b/i", $m);
        $asksFullFacultySchedule =
            $containsScheduleKeyword &&
            ($mentionsFacultyWord ||
                $mentionsPluralProfessors ||
                (bool) preg_match(
                    "/\ball\s+(professor|professors|faculty)(?:\s+members)?\b/i",
                    $m,
                ) ||
                str_contains($m, "all faculty") ||
                str_contains($m, "full faculty") ||
                str_contains($m, "entire faculty") ||
                str_contains($m, "whole faculty"));
        if ($asksFullFacultySchedule) {
            // Select available columns; include Dept_ID if present for grouping
            $selects = ["Name", "Schedule"];
            $hasDept = Schema::hasColumn("professors", "Dept_ID");
            if ($hasDept) {
                $selects[] = "Dept_ID";
            }
            $rows = DB::table("professors")->select($selects)->orderBy("Name")->get();
            if ($rows->isEmpty()) {
                return "There are no professors in the directory yet.";
            }

            // Optional department filter: IT&IS-only or ComSci-only
            $deptFilter = null; // 1 = IT&IS, 2 = CS (when schema supports Dept_ID)
            $mentionsItis = (bool) preg_match(
                "/\b(
                    it&?is
                    |it\s*&\s*is
                    |it\s*is
                    |itis
                    |it\s*department
                    |information\s*technology(?:\s*department)?(?:\s*(?:&|and)\s*information\s*systems)?
                    |info\s*tech
                    |infotech
                )\b/ix",
                $m,
            );
            $mentionsCs = (bool) preg_match("/\b(comsci|computer\s*science|comp\s*sci|cs)\b/i", $m);
            if ($hasDept) {
                if ($mentionsItis && !$mentionsCs) {
                    $deptFilter = 1;
                } elseif ($mentionsCs && !$mentionsItis) {
                    $deptFilter = 2;
                }
            }

            if ($hasDept && $deptFilter !== null) {
                $rows = $rows
                    ->filter(function ($r) use ($deptFilter) {
                        return (int) ($r->Dept_ID ?? 0) === (int) $deptFilter;
                    })
                    ->values();
                if ($rows->isEmpty()) {
                    $deptName =
                        $deptFilter === 1 ? "IT&IS Department" : "Computer Science Department";
                    return "There are no professors listed under " . $deptName . ".";
                }
            }

            // Group by department when possible
            $groups = [
                "IT&IS Department" => [],
                "Computer Science Department" => [],
                "Other Departments" => [],
            ];
            foreach ($rows as $r) {
                $deptName = "Other Departments";
                if ($hasDept) {
                    $dept = (int) ($r->Dept_ID ?? 0);
                    if ($dept === 1) {
                        $deptName = "IT&IS Department";
                    } elseif ($dept === 2) {
                        $deptName = "Computer Science Department";
                    }
                }
                $groups[$deptName][] = $r;
            }

            // If the user asked for all/full/complete/show all, do not cap the list
            $noLimit =
                $mentionsAllModifier ||
                str_contains($m, "show all") ||
                str_contains($m, "complete list") ||
                str_contains($m, "full list") ||
                (bool) preg_match("/\ball\b/i", $m) ||
                (bool) preg_match("/\blah(?:at)?\b/i", $m); // Tagalog 'lahat'

            $limit = $noLimit ? null : 25; // soft cap overall for readability
            $count = 0;
            $out = ["Full faculty consultation schedules:"]; // header
            foreach ($groups as $title => $items) {
                if (empty($items)) {
                    continue;
                }
                $out[] = ""; // blank line for spacing
                $out[] = $title;
                foreach ($items as $r) {
                    $count++;
                    if ($limit !== null && $count > $limit) {
                        break 2; // stop across groups
                    }
                    $name = (string) ($r->Name ?? "Professor");
                    $sched = trim((string) ($r->Schedule ?? ""));
                    if ($sched === "") {
                        $sched = "No schedule set";
                    }
                    // Indent schedule on its own line to reduce visual clutter in chat bubbles
                    $out[] = "- " . $name;
                    $out[] = "  Schedule: " . $sched;
                    $out[] = ""; // blank line between professors
                }
            }
            $total = (int) $rows->count();
            if ($limit !== null && $total > $limit) {
                $out[] = sprintf("...and %d more.", $total - $limit);
            }
            // Trim potential trailing blank line
            while (!empty($out) && trim(end($out)) === "") {
                array_pop($out);
            }
            return implode("\n", $out);
        }

        // Early intent: All professor availability (defaults to today if date not provided)
        $asksAllAvailability =
            // Treat generic group words as global
            ($mentionsFacultyWord ||
                $mentionsPluralProfessors ||
                str_contains($m, "all professors") ||
                str_contains($m, "all professor") ||
                str_contains($m, "all faculty") ||
                str_contains($m, "full faculty") ||
                str_contains($m, "entire faculty") ||
                str_contains($m, "whole faculty") ||
                (bool) preg_match(
                    "/\ball\s+(professor|professors|faculty)(?:\s+members)?\b/i",
                    $m,
                )) &&
            (str_contains($m, "availability") || str_contains($m, "available"));
        if ($asksAllAvailability) {
            $date = $this->extractDate($m);
            $tz = "Asia/Manila";
            if (!$date) {
                $date = Carbon::now($tz)->startOfDay();
                $assumed = true;
            } else {
                $assumed = false;
            }

            $dateKey = $date->copy()->timezone($tz)->startOfDay()->format("D M d Y");
            $capacity = 5;
            $capacityStatuses = ["approved", "rescheduled"]; // counts as booked

            // We also need Schedule to determine who actually has a schedule for the requested day
            $profs = DB::table("professors")
                ->select("Prof_ID", "Name", "Schedule")
                ->orderBy("Name")
                ->get();
            if ($profs->isEmpty()) {
                return "There are no professors in the directory yet.";
            }

            $bookedMap = DB::table("t_consultation_bookings")
                ->select("Prof_ID", DB::raw("COUNT(*) as cnt"))
                ->where("Booking_Date", $dateKey)
                ->whereIn(DB::raw("LOWER(Status)"), $capacityStatuses)
                ->groupBy("Prof_ID")
                ->pluck("cnt", "Prof_ID");

            $lines = [];
            $header = $assumed
                ? sprintf(
                    "All professors availability (assuming today %s):",
                    $date->format("D, M d Y"),
                )
                : sprintf("All professors availability for %s:", $date->format("D, M d Y"));
            $lines[] = $header;

            // Only include professors that actually have a schedule block on the requested day
            $dow = strtolower($date->format("l")); // e.g., wednesday
            $listed = 0;
            foreach ($profs as $p) {
                $parsed = $this->parseProfessorSchedule((string) ($p->Schedule ?? ""));
                $times = $parsed[$dow] ?? [];
                if (empty($times)) {
                    // No schedule for this day -> skip
                    continue;
                }

                $booked = (int) ($bookedMap[$p->Prof_ID] ?? 0);
                $remaining = max($capacity - $booked, 0);
                $note = "";
                try {
                    $ov = app(\App\Services\CalendarOverrideService::class)->evaluate(
                        (int) $p->Prof_ID,
                        $dateKey,
                    );
                    if (($ov["blocked"] ?? false) === true) {
                        $note = " [blocked]";
                    } elseif (!empty($ov["forced_mode"])) {
                        $note = " [mode: " . ucfirst((string) $ov["forced_mode"]) . "]";
                    }
                } catch (\Throwable $e) {
                }

                // Put the time window on its own line for readability
                $lines[] = sprintf("- %s", (string) $p->Name);
                $lines[] = sprintf("  Slots: %d/%d available%s", $remaining, $capacity, $note);
                $listed++;
            }

            if ($listed === 0) {
                $lines[] = "No professors have a consultation schedule on this date.";
            }

            // Trim possible trailing blank lines
            while (!empty($lines) && trim(end($lines)) === "") {
                array_pop($lines);
            }
            return implode("\n", $lines);
        }

        // Generic statuses intent: "What are the consultation statuses?"
        // When user asks about consultation statuses without specifying a professor,
        // show their own Pending + Approved/Accepted/Rescheduled bookings.
        if (
            // mentions status or statuses
            ((bool) preg_match("/\bstatus(?:es)?\b/i", $m)) &&
            // and mentions consultation/schedule/booking generically
            ((bool) preg_match(
                "/\b(consultation|consultations|schedule|schedules|booking|bookings|appointment|appointments)\b/i",
                $m,
            ))
        ) {
            // If the message is clearly about a specific professor, let other branches handle it
            $mentionsProfessorExplicit =
                str_contains($m, " with ") ||
                (bool) preg_match("/\bprof(essor)?\b/i", $m) ||
                str_contains($m, " sir ") ||
                str_contains($m, " maam ") ||
                str_contains($m, " ma'am ");
            if (!$mentionsProfessorExplicit) {
                $user = Auth::user();
                $studId = $user->Stud_ID ?? null;
                if (!$studId) {
                    return "Please sign in to check your bookings.";
                }
                $statuses = ["pending", "approved", "accepted", "rescheduled"];
                return $this->summarizeStudentByStatuses(
                    (int) $studId,
                    $statuses,
                    "Your pending and approved consultations:",
                );
            }
        }

        // Student-status intents (require logged-in student)
        // Examples:
        //  - "Do I have a schedule this week/next week?"
        //  - "Do I have any consultation today/tomorrow/Monday?"
        //  - "Was my consultation with <Professor> accepted?"
        $mentionsMe = (bool) preg_match("/\b(i|my|me|mine|ko|akin|sakin)\b/i", $m);
        $talksSchedule =
            str_contains($m, "schedule") ||
            str_contains($m, "consultation") ||
            str_contains($m, "booking") ||
            str_contains($m, "appointment");
        $mentionsStatus =
            str_contains($m, "status") ||
            str_contains($m, "accepted") ||
            str_contains($m, "approved") ||
            str_contains($m, "confirmed");
        $mentionsStatusish =
            $mentionsStatus ||
            str_contains($m, "pending") ||
            str_contains($m, "completed") ||
            str_contains($m, "complete") ||
            str_contains($m, "done") ||
            str_contains($m, "finished");
        // Determine if the user is asking about their booking status/timeline (not "can I schedule")
        $mentionsWeek =
            str_contains($m, "this week") ||
            str_contains($m, "this wk") ||
            str_contains($m, "next week") ||
            str_contains($m, "next wk");
        $date = $this->extractDate($m);
        $statusQuestion = (bool) preg_match(
            "/\b(do\s+i\s+have|what\s+(is|are).*my|when\s+is\s+my|show\s+me\s+my|status|summary)\b/i",
            $m,
        );
        // Treat 'booked'/'bookings' as asking for accepted/approved items
        $mentionsBooked =
            str_contains($m, "booked") ||
            str_contains($m, "my bookings") ||
            str_contains($m, "bookings ko") ||
            str_contains($m, "naka book") ||
            str_contains($m, "na-book") ||
            str_contains($m, "nabook");
        $isCanISchedule =
            (bool) (preg_match("/\bcan\s+i\s+(schedule|book)\b/i", $m) ||
                preg_match("/\b(pwede|puwede)\b.*(mag\s*book|mag\s*schedule|schedule|book)/i", $m));

        if (
            $mentionsMe &&
            !$isCanISchedule &&
            ($mentionsStatusish || $statusQuestion || $mentionsWeek || $date || $mentionsBooked)
        ) {
            $user = Auth::user();
            $studId = $user->Stud_ID ?? null;
            if (!$studId) {
                return "Please sign in to check your bookings.";
            }

            // Accepted-only filter if explicitly asked
            $acceptedOnly =
                str_contains($m, "accepted") ||
                str_contains($m, "approved") ||
                str_contains($m, "confirmed");
            // Date already computed above

            // Special: Latest/Next consultation (normalize WHAT/WHEN to the same answer)
            $mentionsLatest =
                (bool) preg_match(
                    "/\b(latest|upcoming|soonest|pinaka\s*bago|pinakabago|huling)\b/i",
                    $m,
                ) ||
                (bool) preg_match(
                    "/\bnext\s+(consultation|booking|appointment|sched|schedule)\b/i",
                    $m,
                ) ||
                (bool) preg_match("/\bsusunod\s+(na\s+)?(konsulta|schedule)\b/i", $m);
            if (
                $mentionsLatest &&
                (str_contains($m, "consultation") || str_contains($m, "schedule"))
            ) {
                // Prefer approved/rescheduled over pending
                $prefStatuses = ["approved", "accepted", "rescheduled"];
                $best = $this->findEarliestByStatuses((int) $studId, $prefStatuses);

                if ($best === null) {
                    return "You don't have any approved/rescheduled consultations scheduled yet.";
                }

                // Build enriched response similar to the generic branch
                $mode = null;
                if (isset($best->Mode) && $best->Mode !== null && $best->Mode !== "") {
                    $mode = ucfirst((string) $best->Mode);
                } else {
                    try {
                        $dateKey = (string) ($best->Booking_Date ?? "");
                        if ($dateKey !== "" && isset($best->Prof_ID)) {
                            $ov = app(\App\Services\CalendarOverrideService::class)->evaluate(
                                (int) $best->Prof_ID,
                                $dateKey,
                            );
                            if (!empty($ov["forced_mode"])) {
                                $mode = ucfirst((string) $ov["forced_mode"]);
                            }
                        }
                    } catch (\Throwable $e) {
                    }
                }

                $timeStr = null;
                try {
                    $dateKey = (string) ($best->Booking_Date ?? "");
                    if ($dateKey !== "" && !empty($best->Schedule ?? "")) {
                        $dateObj = Carbon::parse($dateKey, "Asia/Manila");
                        $dow = strtolower($dateObj->format("l"));
                        $parsed = $this->parseProfessorSchedule((string) $best->Schedule);
                        $times = $parsed[$dow] ?? [];
                        if (!empty($times)) {
                            $timeStr = $times[0];
                        }
                    }
                } catch (\Throwable $e) {
                }

                $parts = [];
                $parts[] = ucfirst((string) ($best->Status ?? "Unknown"));
                if ($mode) {
                    $parts[] = $mode;
                }
                $statusMode = "(" . implode(", ", $parts) . ")";
                $dateDisp = (string) ($best->Booking_Date ?? "(date not set)");
                if ($timeStr) {
                    return sprintf(
                        "Your latest consultation is with %s on %s at %s %s.",
                        (string) ($best->Professor_Name ?? "Professor"),
                        $dateDisp,
                        $timeStr,
                        $statusMode,
                    );
                }
                return sprintf(
                    "Your latest consultation is with %s on %s %s.",
                    (string) ($best->Professor_Name ?? "Professor"),
                    $dateDisp,
                    $statusMode,
                );
            }

            // Short: "when/what is my consultation" -> treat as latest approved/rescheduled only
            $asksWhenWhatMyConsult = (bool) preg_match(
                "/\b(when|what)\s+is\s+my\s+(consultation|schedule|appointment|booking|sched)\b/i",
                $m,
            );
            if ($asksWhenWhatMyConsult) {
                $prefStatuses = ["approved", "accepted", "rescheduled"];
                $best = $this->findEarliestByStatuses((int) $studId, $prefStatuses);
                if ($best === null) {
                    return "You don't have any approved/rescheduled consultations scheduled yet.";
                }

                $mode = null;
                if (isset($best->Mode) && $best->Mode !== null && $best->Mode !== "") {
                    $mode = ucfirst((string) $best->Mode);
                } else {
                    try {
                        $dateKey = (string) ($best->Booking_Date ?? "");
                        if ($dateKey !== "" && isset($best->Prof_ID)) {
                            $ov = app(\App\Services\CalendarOverrideService::class)->evaluate(
                                (int) $best->Prof_ID,
                                $dateKey,
                            );
                            if (!empty($ov["forced_mode"])) {
                                $mode = ucfirst((string) $ov["forced_mode"]);
                            }
                        }
                    } catch (\Throwable $e) {
                    }
                }
                $timeStr = null;
                try {
                    $dateKey = (string) ($best->Booking_Date ?? "");
                    if ($dateKey !== "" && !empty($best->Schedule ?? "")) {
                        $dateObj = Carbon::parse($dateKey, "Asia/Manila");
                        $dow = strtolower($dateObj->format("l"));
                        $parsed = $this->parseProfessorSchedule((string) $best->Schedule);
                        $times = $parsed[$dow] ?? [];
                        if (!empty($times)) {
                            $timeStr = $times[0];
                        }
                    }
                } catch (\Throwable $e) {
                }
                $parts = [];
                $parts[] = ucfirst((string) ($best->Status ?? "Approved"));
                if ($mode) {
                    $parts[] = $mode;
                }
                $statusMode = "(" . implode(", ", $parts) . ")";
                $dateDisp = (string) ($best->Booking_Date ?? "(date not set)");
                if ($timeStr) {
                    return sprintf(
                        "Your latest consultation is with %s on %s at %s %s.",
                        (string) ($best->Professor_Name ?? "Professor"),
                        $dateDisp,
                        $timeStr,
                        $statusMode,
                    );
                }
                return sprintf(
                    "Your latest consultation is with %s on %s %s.",
                    (string) ($best->Professor_Name ?? "Professor"),
                    $dateDisp,
                    $statusMode,
                );
            }

            // Initialize status-interest flags
            $wantsPending = false;
            $wantsApproved = false;
            $wantsCompleted = false;
            $wantsRescheduled = false;

            // Summary by specific status keywords e.g.,
            //  - "my pending schedules"
            //  - "my approved/accepted schedules"
            //  - "my completed schedules"
            // Special case: short one-liner for yes/no style pending check
            $wantsPending = str_contains($m, "pending");
            $wantsApproved =
                str_contains($m, "approved") ||
                str_contains($m, "accepted") ||
                str_contains($m, "confirmed");
            if ($mentionsBooked) {
                $wantsApproved = true; // interpret 'booked' as approved/accepted
            }
            $wantsCompleted =
                str_contains($m, "completed") ||
                str_contains($m, "complete") ||
                str_contains($m, "done") ||
                str_contains($m, "finished");
            $wantsRescheduled = str_contains($m, "rescheduled") || str_contains($m, "reschedule");
            // One-liner: "do i have a pending consultation" / "may pending ba" / "meron pa ba pending"
            if (
                $wantsPending &&
                !$mentionsWeek &&
                !$date &&
                (bool) preg_match("/(do\s+i\s+have|may|meron|meron ba|may ba)/i", $m)
            ) {
                return $this->shortPendingOneLiner((int) $studId);
            }
            // One-liner: "do i have a rescheduled consultation" variants
            if (
                $wantsRescheduled &&
                !$mentionsWeek &&
                !$date &&
                (bool) preg_match("/(do\s+i\s+have|may|meron|meron ba|may ba)/i", $m)
            ) {
                return $this->shortRescheduledOneLiner((int) $studId);
            }
            // Skip this immediate summary if the query also specifies a week range;
            // the weekly branch below will handle status+week together.
            $mentionsWeek =
                str_contains($m, "this week") ||
                str_contains($m, "this wk") ||
                str_contains($m, "next week") ||
                str_contains($m, "next wk");
            if (
                !$mentionsWeek &&
                !$date &&
                ($wantsPending || $wantsApproved || $wantsCompleted || $wantsRescheduled)
            ) {
                $statuses = [];
                $titleParts = [];
                if ($wantsPending) {
                    $statuses[] = "pending";
                    $titleParts[] = "pending";
                }
                if ($wantsApproved) {
                    array_push($statuses, "approved", "accepted", "rescheduled");
                    $titleParts[] = "approved/accepted";
                }
                if ($wantsCompleted) {
                    // Include common synonyms if ever used
                    array_push($statuses, "completed", "done", "finished");
                    $titleParts[] = "completed";
                }
                if ($wantsRescheduled) {
                    $statuses[] = "rescheduled";
                    $titleParts[] = "rescheduled";
                }
                // De-duplicate
                $statuses = array_values(array_unique(array_map("strtolower", $statuses)));
                if (!empty($statuses)) {
                    $title = "Your " . implode(" + ", $titleParts) . " consultations:";
                    return $this->summarizeStudentByStatuses((int) $studId, $statuses, $title);
                }
            }

            // With professor focus: status inquiry like "Was my consultation with <prof> accepted?"
            if (
                (str_contains($m, "with") ||
                    str_contains($m, "prof") ||
                    str_contains($m, " kay ") ||
                    str_contains($m, "sir ") ||
                    str_contains($m, " maam ") ||
                    str_contains($m, " ma'am ")) &&
                (str_contains($m, "accepted") ||
                    str_contains($m, "approved") ||
                    str_contains($m, "status"))
            ) {
                $prof = $this->matchProfessorFromMessage($m);
                if (!$prof) {
                    return "I could not find that professor.";
                }

                $row = DB::table("t_consultation_bookings as b")
                    ->leftJoin("professors as p", "p.Prof_ID", "=", "b.Prof_ID")
                    ->where("b.Stud_ID", (int) $studId)
                    ->where("b.Prof_ID", (int) $prof->Prof_ID)
                    ->orderByDesc("b.created_at")
                    ->select("b.Booking_Date", "b.Status", "p.Name as Professor_Name")
                    ->first();

                if (!$row) {
                    return "You don't have any booking with Professor " . $prof->Name . ".";
                }
                $status = strtolower((string) ($row->Status ?? ""));
                $acceptedStatuses = ["approved", "accepted", "rescheduled"];
                $isAccepted = in_array($status, $acceptedStatuses, true);
                $dateText = (string) ($row->Booking_Date ?? "(date not set)");
                if ($isAccepted) {
                    return sprintf(
                        "Yes. Your consultation with %s is %s (%s).",
                        (string) ($row->Professor_Name ?? $prof->Name),
                        ucfirst($status),
                        $dateText,
                    );
                }
                if ($status === "pending") {
                    return sprintf(
                        "Not yet. Your consultation with %s is still Pending (%s).",
                        (string) ($row->Professor_Name ?? $prof->Name),
                        $dateText,
                    );
                }
                if (
                    $status === "declined" ||
                    $status === "rejected" ||
                    $status === "cancelled" ||
                    $status === "canceled"
                ) {
                    return sprintf(
                        "No. Your consultation with %s is %s (%s).",
                        (string) ($row->Professor_Name ?? $prof->Name),
                        ucfirst($status),
                        $dateText,
                    );
                }
                return sprintf(
                    "Latest status with %s is %s (%s).",
                    (string) ($row->Professor_Name ?? $prof->Name),
                    ucfirst($status ?: "Unknown"),
                    $dateText,
                );
            }

            // Time range: this week / next week
            if (
                str_contains($m, "this week") ||
                str_contains($m, "this wk") ||
                str_contains($m, "next week") ||
                str_contains($m, "next wk")
            ) {
                $which =
                    str_contains($m, "next week") || str_contains($m, "next wk") ? "next" : "this";
                // If specific status keywords are present, use filtered weekly summary
                if (
                    isset($wantsPending, $wantsApproved, $wantsCompleted, $wantsRescheduled) &&
                    ($wantsPending || $wantsApproved || $wantsCompleted || $wantsRescheduled)
                ) {
                    $statuses = [];
                    $titleParts = [];
                    if ($wantsPending) {
                        $statuses[] = "pending";
                        $titleParts[] = "pending";
                    }
                    if ($wantsApproved) {
                        array_push($statuses, "approved", "accepted", "rescheduled");
                        $titleParts[] = "approved/accepted";
                    }
                    if ($wantsCompleted) {
                        array_push($statuses, "completed", "done", "finished");
                        $titleParts[] = "completed";
                    }
                    if ($wantsRescheduled) {
                        $statuses[] = "rescheduled";
                        $titleParts[] = "rescheduled";
                    }
                    $statuses = array_values(array_unique(array_map("strtolower", $statuses)));
                    if (!empty($statuses)) {
                        $label = implode(" + ", $titleParts);
                        return $this->summarizeStudentWeekFiltered(
                            (int) $studId,
                            $which,
                            $statuses,
                            $label,
                        );
                    }
                }
                // Generic week question: show only approved/accepted (no pending), concise style
                return $this->summarizeStudentWeekFiltered(
                    (int) $studId,
                    $which,
                    ["approved", "accepted"],
                    "approved",
                );
            }

            // Specific day/date: today/tomorrow/weekday/explicit date
            if ($date) {
                $tz = "Asia/Manila";
                $key = $date->copy()->timezone($tz)->startOfDay()->format("D M d Y");
                $statuses = $acceptedOnly
                    ? ["approved", "accepted", "rescheduled"]
                    : ["approved", "accepted", "rescheduled", "pending"];
                $rows = DB::table("t_consultation_bookings as b")
                    ->leftJoin("professors as p", "p.Prof_ID", "=", "b.Prof_ID")
                    ->where("b.Stud_ID", (int) $studId)
                    ->where("b.Booking_Date", $key)
                    ->whereIn(DB::raw("LOWER(b.Status)"), $statuses)
                    ->orderByDesc("b.created_at")
                    ->select("b.Booking_Date", "b.Status", "p.Name as Professor_Name")
                    ->get();

                if ($rows->isEmpty()) {
                    return sprintf(
                        "You have no %sconsultations on %s.",
                        $acceptedOnly ? "accepted " : "",
                        $date->format("D, M d Y"),
                    );
                }
                $list = [];
                foreach ($rows as $r) {
                    $list[] = sprintf(
                        "%s — %s",
                        (string) ($r->Professor_Name ?? "Professor"),
                        ucfirst((string) $r->Status),
                    );
                }
                return sprintf(
                    "You have %d %sconsultation(s) on %s: %s.",
                    (int) $rows->count(),
                    $acceptedOnly ? "accepted " : "",
                    $date->format("D, M d Y"),
                    implode(", ", $list),
                );
            }

            // Generic: "Do I have a schedule?" -> Show next upcoming
            $statuses = $acceptedOnly
                ? ["approved", "accepted", "rescheduled"]
                : ["approved", "accepted", "rescheduled", "pending"];
            $upcomingQuery = DB::table("t_consultation_bookings as b")
                ->leftJoin("professors as p", "p.Prof_ID", "=", "b.Prof_ID")
                ->where("b.Stud_ID", (int) $studId)
                ->whereIn(DB::raw("LOWER(b.Status)"), $statuses)
                ->orderByDesc("b.created_at")
                ->select(
                    "b.Booking_Date",
                    "b.Status",
                    "b.Prof_ID",
                    "p.Name as Professor_Name",
                    "p.Schedule",
                );
            if (Schema::hasColumn("t_consultation_bookings", "Mode")) {
                $upcomingQuery->addSelect("b.Mode");
            }
            $upcoming = $upcomingQuery->first();

            if (!$upcoming) {
                return "You don't have any consultations scheduled.";
            }
            // Enrich with mode and an indicative time window (from professor schedule for that day)
            $mode = null;
            if (isset($upcoming->Mode) && $upcoming->Mode !== null && $upcoming->Mode !== "") {
                $mode = ucfirst((string) $upcoming->Mode);
            } else {
                // Fallback to forced mode if any
                try {
                    $dateKey = (string) ($upcoming->Booking_Date ?? "");
                    if ($dateKey !== "" && isset($upcoming->Prof_ID)) {
                        $ov = app(\App\Services\CalendarOverrideService::class)->evaluate(
                            (int) $upcoming->Prof_ID,
                            $dateKey,
                        );
                        if (!empty($ov["forced_mode"])) {
                            $mode = ucfirst((string) $ov["forced_mode"]);
                        }
                    }
                } catch (\Throwable $e) {
                }
            }
            $timeStr = null;
            try {
                $dateKey = (string) ($upcoming->Booking_Date ?? "");
                if ($dateKey !== "" && !empty($upcoming->Schedule ?? "")) {
                    $dateObj = Carbon::parse($dateKey, "Asia/Manila");
                    $dow = strtolower($dateObj->format("l"));
                    $parsed = $this->parseProfessorSchedule((string) $upcoming->Schedule);
                    $times = $parsed[$dow] ?? [];
                    if (!empty($times)) {
                        $timeStr = $times[0];
                    }
                }
            } catch (\Throwable $e) {
            }

            $parts = [];
            $parts[] = ucfirst((string) ($upcoming->Status ?? "Unknown"));
            if ($mode) {
                $parts[] = $mode;
            }
            $statusMode = "(" . implode(", ", $parts) . ")";
            $dateDisp = (string) ($upcoming->Booking_Date ?? "(date not set)");
            if ($timeStr) {
                return sprintf(
                    "Your latest %sconsultation is with %s on %s at %s %s.",
                    $acceptedOnly ? "accepted " : "",
                    (string) ($upcoming->Professor_Name ?? "Professor"),
                    $dateDisp,
                    $timeStr,
                    $statusMode,
                );
            }
            return sprintf(
                "Your latest %sconsultation is with %s on %s %s.",
                $acceptedOnly ? "accepted " : "",
                (string) ($upcoming->Professor_Name ?? "Professor"),
                $dateDisp,
                $statusMode,
            );
        }

        // 0) Subject -> Professors intent
        // Examples:
        //  - "Who are the professors that has Database subjects"
        //  - "Which professor teaches Data Structures?"
        //  - "Professors handling Web Development subject"
        if (
            (str_contains($m, "subject") ||
                str_contains($m, "subjects") ||
                str_contains($m, "course") ||
                str_contains($m, "courses")) &&
            (str_contains($m, "who") ||
                str_contains($m, "which") ||
                str_contains($m, "professor") ||
                str_contains($m, "professors") ||
                str_contains($m, "teach") ||
                str_contains($m, "teaches") ||
                str_contains($m, "handle") ||
                str_contains($m, "handles"))
        ) {
            $subject = $this->matchSubjectFromMessage($m);
            if (!$subject) {
                $sugs = $this->suggestSubjects($m);
                if (!empty($sugs)) {
                    return "I could not find that subject. Did you mean: " .
                        implode(", ", $sugs) .
                        "?";
                }
                return "I could not find any matching subject.";
            }

            // Find distinct professors teaching the matched subject
            $names = DB::table("professor_subject as ps")
                ->join("professors as p", "p.Prof_ID", "=", "ps.Prof_ID")
                ->join("t_subject as s", "s.Subject_ID", "=", "ps.Subject_ID")
                ->where("s.Subject_ID", $subject->Subject_ID)
                ->distinct()
                ->orderBy("p.Name")
                ->pluck("p.Name")
                ->toArray();

            if (empty($names)) {
                return "There are no assigned professors for '" .
                    $subject->Subject_Name .
                    "' right now.";
            }
            $list = implode(", ", $names);
            return sprintf("Professors who handle %s: %s.", $subject->Subject_Name, $list);
        }

        // 0b) Professor -> Subjects intent
        // Examples:
        //  - "what subjects <prof> has"
        //  - "subjects of <prof>"
        //  - "what does <prof> teach" (interpreted as subjects list)
        if (str_contains($m, "subject") || str_contains($m, "subjects")) {
            $prof = $this->matchProfessorFromMessage($m);
            if ($prof) {
                $subs = DB::table("professor_subject as ps")
                    ->join("t_subject as s", "s.Subject_ID", "=", "ps.Subject_ID")
                    ->where("ps.Prof_ID", $prof->Prof_ID)
                    ->distinct()
                    ->orderBy("s.Subject_Name")
                    ->pluck("s.Subject_Name")
                    ->toArray();

                if (empty($subs)) {
                    return "Professor " . $prof->Name . " currently has no assigned subjects.";
                }
                return "Subjects of " . $prof->Name . ": " . implode(", ", $subs) . ".";
            } else {
                return "I could not find that professor.";
            }
        }

        // A0) "Free" phrasing as availability/schedule intent
        // Handles grammar-poor queries like "when professor <name> free".
        // If a date is provided, treat as availability; otherwise show the professor's schedule with a tip.
        if ((bool) preg_match("/\bfree\b/i", $m)) {
            $prof = $this->matchProfessorFromMessage($m);
            if ($prof) {
                $date = $this->extractDate($m);
                if ($date) {
                    // Mirror Availability intent logic for a specific date
                    $dateKey = $date
                        ->copy()
                        ->timezone("Asia/Manila")
                        ->startOfDay()
                        ->format("D M d Y");

                    // Ensure professor has schedule on that day
                    $parsedSched = $this->parseProfessorSchedule((string) ($prof->Schedule ?? ""));
                    $dow = strtolower($date->format("l"));
                    $timesForDay = $parsedSched[$dow] ?? [];
                    if (empty($timesForDay)) {
                        return sprintf(
                            "%s has no consultation schedule on %s.",
                            (string) $prof->Name,
                            $date->format("D, M d Y"),
                        );
                    }

                    $capacityStatuses = ["approved", "rescheduled"]; // counts as booked
                    $booked = DB::table("t_consultation_bookings")
                        ->where("Prof_ID", $prof->Prof_ID)
                        ->where("Booking_Date", $dateKey)
                        ->whereIn("Status", $capacityStatuses)
                        ->count();
                    $capacity = 5;
                    $remaining = max($capacity - $booked, 0);

                    $modeNote = "";
                    try {
                        $ov = app(\App\Services\CalendarOverrideService::class)->evaluate(
                            (int) $prof->Prof_ID,
                            $dateKey,
                        );
                        if (($ov["blocked"] ?? false) === true) {
                            $modeNote = " (Note: date is blocked)";
                        } elseif (!empty($ov["forced_mode"])) {
                            $modeNote =
                                " (Mode lock: " . ucfirst((string) $ov["forced_mode"]) . ")";
                        }
                    } catch (\Throwable $e) {
                    }

                    return sprintf(
                        "%s, %s: %d/%d slots available%s.",
                        $prof->Name,
                        $date->format("D, M d Y"),
                        $remaining,
                        $capacity,
                        $modeNote,
                    );
                }

                // No date -> provide schedule overview with a helpful tip
                $sched = (string) ($prof->Schedule ?? "");
                if (trim($sched) === "") {
                    return "Professor " . $prof->Name . " has no schedule set.";
                }
                return "Schedule of " . $prof->Name . ": " . $sched;
            } else {
                return "I could not find that professor.";
            }
        }

        // A) Schedule intent (supports "this week" / "next week")
        // Exclude reschedule/rescheduled/rescheduling so 'how to reschedule' doesn't fall into this block
        // Also treat "consultation hours" / "office hours" as schedule synonyms
        $isSchedule = (bool) (preg_match("/\bschedule\b/i", $m) && !preg_match("/\bresched/i", $m));
        $isHoursSynonym =
            (bool) preg_match("/\b(consultation|consult|office)\s*hour(s)?\b/i", $m) ||
            (bool) preg_match("/\bconsultation\s*(time|times)\b/i", $m) ||
            // Loose phrasing like "what professor X consultations are" -> treat as schedule when a professor is mentioned
            (bool) preg_match("/\bconsultations?\b/i", $m) ||
            str_contains($m, "oras ng konsultasyon") ||
            str_contains($m, "oras ng consultation") ||
            str_contains($m, "office hours");
        if ($isSchedule || $isHoursSynonym) {
            $prof = $this->matchProfessorFromMessage($m);
            // If no strong match, suggest closest names
            if (!$prof) {
                return "I could not find that professor.";
            }

            if (str_contains($m, "this week") || str_contains($m, "this wk")) {
                return $this->summarizeWeek($prof, "this");
            }
            if (str_contains($m, "next week") || str_contains($m, "next wk")) {
                return $this->summarizeWeek($prof, "next");
            }

            $sched = (string) ($prof->Schedule ?? "");
            if (trim($sched) === "") {
                return "Professor " . $prof->Name . " has no schedule set.";
            }
            return "Schedule of " . $prof->Name . ": " . $sched;
        }

        // B) Availability intent (available slots)
        if (
            str_contains($m, "available") ||
            str_contains($m, "slot") ||
            str_contains($m, "slots") ||
            str_contains($m, "open")
        ) {
            $prof = $this->matchProfessorFromMessage($m);
            if (!$prof) {
                return 'Please specify the professor. Example: "Are there available slots for Professor Benito tomorrow?"';
            }

            $date = $this->extractDate($m);
            if (!$date) {
                return 'Please specify a date (e.g., "tomorrow", "Monday", or "2025-09-15").';
            }
            $dateKey = $date->copy()->timezone("Asia/Manila")->startOfDay()->format("D M d Y");

            // First, ensure the professor actually has a schedule on that day-of-week
            $parsedSched = $this->parseProfessorSchedule((string) ($prof->Schedule ?? ""));
            $dow = strtolower($date->format("l"));
            $timesForDay = $parsedSched[$dow] ?? [];
            if (empty($timesForDay)) {
                // No schedule for this date -> do not show slots; give a clear reply
                return sprintf(
                    "%s has no consultation schedule on %s.",
                    (string) $prof->Name,
                    $date->format("D, M d Y"),
                );
            }

            // Count approved/rescheduled bookings for that professor on that date
            $capacityStatuses = ["approved", "rescheduled"];
            $booked = DB::table("t_consultation_bookings")
                ->where("Prof_ID", $prof->Prof_ID)
                ->where("Booking_Date", $dateKey)
                ->whereIn("Status", $capacityStatuses)
                ->count();
            $capacity = 5;
            $remaining = max($capacity - $booked, 0);

            // Try to fetch per-day forced mode or block via CalendarOverrideService (best effort)
            $modeNote = "";
            try {
                $ov = app(\App\Services\CalendarOverrideService::class)->evaluate(
                    (int) $prof->Prof_ID,
                    $dateKey,
                );
                if (($ov["blocked"] ?? false) === true) {
                    $modeNote = " (Note: date is blocked)";
                } elseif (!empty($ov["forced_mode"])) {
                    $modeNote = " (Mode lock: " . ucfirst((string) $ov["forced_mode"]) . ")";
                }
            } catch (\Throwable $e) {
                // ignore override issues in chatbot reply
            }

            return sprintf(
                "%s, %s: %d/%d slots available%s.",
                $prof->Name,
                $date->format("D, M d Y"),
                $remaining,
                $capacity,
                $modeNote,
            );
        }

        return null; // not handled
    }

    private function summarizeStudentByStatuses(int $studId, array $statuses, string $title): string
    {
        $rows = DB::table("t_consultation_bookings as b")
            ->leftJoin("professors as p", "p.Prof_ID", "=", "b.Prof_ID")
            ->where("b.Stud_ID", $studId)
            ->whereIn(DB::raw("LOWER(b.Status)"), $statuses)
            ->orderByDesc("b.created_at")
            ->limit(20)
            ->select("b.Booking_Date", "b.Status", "p.Name as Professor_Name")
            ->get();

        if ($rows->isEmpty()) {
            // Build friendly label; collapse synonyms to a single canonical word where helpful
            $lower = array_map("strtolower", $statuses);
            if (count(array_intersect($lower, ["completed", "done", "finished"])) > 0) {
                $label = "completed";
            } else {
                $label = implode(" or ", array_unique($lower));
            }
            return "You have no " . $label . " consultations.";
        }

        $lines = [$title];
        foreach ($rows as $r) {
            $dateStr = (string) ($r->Booking_Date ?? "");
            $pretty = $dateStr;
            try {
                if ($dateStr !== "") {
                    $c = Carbon::parse($dateStr, "Asia/Manila");
                    $pretty = $c->format("D, M d Y");
                }
            } catch (\Throwable $e) {
                // keep original
            }
            $lines[] = sprintf(
                "- %s — %s (%s)",
                $pretty,
                (string) ($r->Professor_Name ?? "Professor"),
                ucfirst((string) ($r->Status ?? "")),
            );
        }
        return implode("\n", $lines);
    }

    /**
     * Find the earliest upcoming booking for the given student filtered by statuses.
     * Returns a stdClass row containing: Booking_Date, Status, Prof_ID, Professor_Name, Schedule, Mode (if exists).
     * If no future bookings exist, returns null.
     */
    private function findEarliestByStatuses(int $studId, array $statuses)
    {
        $rows = DB::table("t_consultation_bookings as b")
            ->leftJoin("professors as p", "p.Prof_ID", "=", "b.Prof_ID")
            ->where("b.Stud_ID", $studId)
            ->whereIn(DB::raw("LOWER(b.Status)"), array_map("strtolower", $statuses))
            ->select(
                "b.Booking_Date",
                "b.Status",
                "b.Prof_ID",
                "p.Name as Professor_Name",
                "p.Schedule",
            );
        if (Schema::hasColumn("t_consultation_bookings", "Mode")) {
            $rows->addSelect("b.Mode");
        }
        $list = $rows->get();

        if ($list->isEmpty()) {
            return null;
        }
        $tz = "Asia/Manila";
        $now = Carbon::now($tz)->startOfDay();
        $best = null;
        foreach ($list as $r) {
            $dateStr = (string) ($r->Booking_Date ?? "");
            if ($dateStr === "") {
                continue;
            }
            try {
                $d = Carbon::parse($dateStr, $tz)->startOfDay();
            } catch (\Throwable $e) {
                continue;
            }
            if ($d->lessThan($now)) {
                continue; // only future/today
            }
            if ($best === null) {
                $best = [$d, $r];
            } else {
                if ($d->lessThan($best[0])) {
                    $best = [$d, $r];
                }
            }
        }
        return $best ? $best[1] : null;
    }

    private function shortPendingOneLiner(int $studId): string
    {
        $tz = "Asia/Manila";
        $now = Carbon::now($tz)->startOfDay();
        $pendingQuery = DB::table("t_consultation_bookings as b")
            ->leftJoin("professors as p", "p.Prof_ID", "=", "b.Prof_ID")
            ->where("b.Stud_ID", $studId)
            ->whereRaw("LOWER(b.Status) = 'pending'")
            ->orderByDesc("b.created_at")
            ->limit(100)
            ->select("b.Booking_Date", "b.Prof_ID", "p.Name as Professor_Name", "p.Schedule");
        if (Schema::hasColumn("t_consultation_bookings", "Mode")) {
            $pendingQuery->addSelect("b.Mode");
        }
        $rows = $pendingQuery->get();

        if ($rows->isEmpty()) {
            return "You have no pending consultations.";
        }

        $bestUpcoming = null; // [Carbon $date, $row]
        foreach ($rows as $r) {
            $dateStr = (string) ($r->Booking_Date ?? "");
            if ($dateStr === "") {
                continue;
            }
            try {
                $d = Carbon::parse($dateStr, $tz)->startOfDay();
            } catch (\Throwable $e) {
                continue;
            }
            if ($d->lessThan($now)) {
                continue;
            }
            if ($bestUpcoming === null || $d->lessThan($bestUpcoming[0])) {
                $bestUpcoming = [$d, $r];
            }
        }

        if ($bestUpcoming !== null) {
            [$d, $r] = $bestUpcoming;
            // Try to include indicative time window and mode
            $timeStr = null;
            $mode = null;
            try {
                if (!empty($r->Schedule ?? "")) {
                    $dow = strtolower($d->format("l"));
                    $parsed = $this->parseProfessorSchedule((string) $r->Schedule);
                    $times = $parsed[$dow] ?? [];
                    if (!empty($times)) {
                        $timeStr = $times[0];
                    }
                }
            } catch (\Throwable $e) {
            }
            if (isset($r->Mode) && $r->Mode !== null && $r->Mode !== "") {
                $mode = ucfirst((string) $r->Mode);
            } else {
                try {
                    $dateKey = $d->format("D M d Y");
                    if (isset($r->Prof_ID)) {
                        $ov = app(\App\Services\CalendarOverrideService::class)->evaluate(
                            (int) $r->Prof_ID,
                            $dateKey,
                        );
                        if (!empty($ov["forced_mode"])) {
                            $mode = ucfirst((string) $ov["forced_mode"]);
                        }
                    }
                } catch (\Throwable $e) {
                }
            }
            $suffix = "";
            if ($timeStr && $mode) {
                $suffix = sprintf(" at %s (%s)", $timeStr, $mode);
            } elseif ($timeStr) {
                $suffix = sprintf(" at %s", $timeStr);
            } elseif ($mode) {
                $suffix = sprintf(" (%s)", $mode);
            }

            return sprintf(
                "Pending on %s — %s%s.",
                $d->format("D, M d Y"),
                (string) ($r->Professor_Name ?? "Professor"),
                $suffix,
            );
        }

        // No future date; fall back to the most recent pending record
        $r = $rows->first();
        $dateStr = (string) ($r->Booking_Date ?? "");
        $pretty = $dateStr;
        try {
            if ($dateStr !== "") {
                $pretty = Carbon::parse($dateStr, $tz)->format("D, M d Y");
            }
        } catch (\Throwable $e) {
        }
        // Fallback: enrich if possible
        $timeStr = null;
        $mode = null;
        try {
            if (!empty($r->Schedule ?? "") && $dateStr !== "") {
                $d2 = Carbon::parse($dateStr, $tz)->startOfDay();
                $dow = strtolower($d2->format("l"));
                $parsed = $this->parseProfessorSchedule((string) $r->Schedule);
                $times = $parsed[$dow] ?? [];
                if (!empty($times)) {
                    $timeStr = $times[0];
                }
            }
        } catch (\Throwable $e) {
        }
        if (isset($r->Mode) && $r->Mode !== null && $r->Mode !== "") {
            $mode = ucfirst((string) $r->Mode);
        }
        $suffix = "";
        if ($timeStr && $mode) {
            $suffix = sprintf(" at %s (%s)", $timeStr, $mode);
        } elseif ($timeStr) {
            $suffix = sprintf(" at %s", $timeStr);
        } elseif ($mode) {
            $suffix = sprintf(" (%s)", $mode);
        }
        return sprintf(
            "Pending on %s — %s%s.",
            $pretty,
            (string) ($r->Professor_Name ?? "Professor"),
            $suffix,
        );
    }

    private function shortRescheduledOneLiner(int $studId): string
    {
        $tz = "Asia/Manila";
        $now = Carbon::now($tz)->startOfDay();
        $query = DB::table("t_consultation_bookings as b")
            ->leftJoin("professors as p", "p.Prof_ID", "=", "b.Prof_ID")
            ->where("b.Stud_ID", $studId)
            ->whereRaw("LOWER(b.Status) = 'rescheduled'")
            ->orderByDesc("b.created_at")
            ->limit(100)
            ->select("b.Booking_Date", "b.Prof_ID", "p.Name as Professor_Name", "p.Schedule");
        if (Schema::hasColumn("t_consultation_bookings", "Mode")) {
            $query->addSelect("b.Mode");
        }
        // Try to include a reschedule reason if the column exists (support common variants)
        $reasonAlias = null;
        $reasonCandidates = [
            "Reschedule_Reason",
            "Rescheduled_Reason",
            "Resched_Reason",
            "ReschedReason",
            "RescheduleReason",
            "Reason",
            "Remarks",
            "Notes",
            "Note",
        ];
        foreach ($reasonCandidates as $cand) {
            if (Schema::hasColumn("t_consultation_bookings", $cand)) {
                $query->addSelect(DB::raw("b." . $cand . " as ReasonText"));
                $reasonAlias = "ReasonText";
                break;
            }
        }
        $rows = $query->get();

        if ($rows->isEmpty()) {
            return "You have no rescheduled consultations.";
        }

        $bestUpcoming = null; // [Carbon $date, $row]
        foreach ($rows as $r) {
            $dateStr = (string) ($r->Booking_Date ?? "");
            if ($dateStr === "") {
                continue;
            }
            try {
                $d = Carbon::parse($dateStr, $tz)->startOfDay();
            } catch (\Throwable $e) {
                continue;
            }
            if ($d->lessThan($now)) {
                continue;
            }
            if ($bestUpcoming === null || $d->lessThan($bestUpcoming[0])) {
                $bestUpcoming = [$d, $r];
            }
        }

        $formatLine = function ($d, $r) use ($tz, $reasonAlias) {
            // Include indicative time window and mode
            $timeStr = null;
            $mode = null;
            try {
                if (!empty($r->Schedule ?? "")) {
                    $dow = strtolower($d->format("l"));
                    $parsed = $this->parseProfessorSchedule((string) $r->Schedule);
                    $times = $parsed[$dow] ?? [];
                    if (!empty($times)) {
                        $timeStr = $times[0];
                    }
                }
            } catch (\Throwable $e) {
            }
            if (isset($r->Mode) && $r->Mode !== null && $r->Mode !== "") {
                $mode = ucfirst((string) $r->Mode);
            } else {
                try {
                    $dateKey = $d->format("D M d Y");
                    if (isset($r->Prof_ID)) {
                        $ov = app(\App\Services\CalendarOverrideService::class)->evaluate(
                            (int) $r->Prof_ID,
                            $dateKey,
                        );
                        if (!empty($ov["forced_mode"])) {
                            $mode = ucfirst((string) $ov["forced_mode"]);
                        }
                    }
                } catch (\Throwable $e) {
                }
            }
            $suffix = "";
            if ($timeStr && $mode) {
                $suffix = sprintf(" at %s (%s)", $timeStr, $mode);
            } elseif ($timeStr) {
                $suffix = sprintf(" at %s", $timeStr);
            } elseif ($mode) {
                $suffix = sprintf(" (%s)", $mode);
            }
            $reasonStr = "";
            if ($reasonAlias && isset($r->$reasonAlias)) {
                $val = trim((string) $r->$reasonAlias);
                if ($val !== "") {
                    $reasonStr = ". Reason: " . $val;
                }
            }

            return sprintf(
                "Rescheduled on %s — %s%s%s.",
                $d->format("D, M d Y"),
                (string) ($r->Professor_Name ?? "Professor"),
                $suffix,
                $reasonStr,
            );
        };

        if ($bestUpcoming !== null) {
            [$d, $r] = $bestUpcoming;
            return $formatLine($d, $r);
        }

        // No future rescheduled date; fall back to most recent rescheduled record
        $r = $rows->first();
        $dateStr = (string) ($r->Booking_Date ?? "");
        try {
            $d = $dateStr !== "" ? Carbon::parse($dateStr, $tz)->startOfDay() : Carbon::now($tz);
        } catch (\Throwable $e) {
            $d = Carbon::now($tz);
        }
        return $formatLine($d, $r);
    }

    private function summarizeStudentWeek(int $studId, string $which, bool $acceptedOnly): string
    {
        $tz = "Asia/Manila";
        $start = Carbon::now($tz)->startOfWeek(Carbon::MONDAY);
        if ($which === "next") {
            $start = $start->copy()->addWeek();
        }
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = $start->copy()->addDays($i);
        }
        $dateKeys = array_map(fn($d) => $d->format("D M d Y"), $days);
        $statuses = $acceptedOnly
            ? ["approved", "accepted", "rescheduled"]
            : ["approved", "accepted", "rescheduled", "pending"];

        $rows = DB::table("t_consultation_bookings as b")
            ->leftJoin("professors as p", "p.Prof_ID", "=", "b.Prof_ID")
            ->where("b.Stud_ID", $studId)
            ->whereIn("b.Booking_Date", $dateKeys)
            ->whereIn(DB::raw("LOWER(b.Status)"), $statuses)
            ->select("b.Booking_Date", "b.Status", "p.Name as Professor_Name")
            ->get();

        // If there are no items at all for the requested week, return a concise fallback
        if ($rows->isEmpty()) {
            $range = $which === "next" ? "next week" : "this week";
            $baseMsg = $acceptedOnly
                ? "You have no accepted consultations " . $range . "."
                : "You have no consultations " . $range . ".";

            // Helpful add-on: if the student has an upcoming consultation outside this week,
            // mention the next one to reduce confusion between "this week" and future bookings.
            try {
                $now = Carbon::now($tz)->startOfDay();
                $statusScope = $acceptedOnly
                    ? ["approved", "accepted", "rescheduled"]
                    : ["approved", "accepted", "rescheduled", "pending"];
                $candidates = DB::table("t_consultation_bookings as b")
                    ->leftJoin("professors as p", "p.Prof_ID", "=", "b.Prof_ID")
                    ->where("b.Stud_ID", $studId)
                    ->whereIn(DB::raw("LOWER(b.Status)"), $statusScope)
                    ->orderByDesc("b.created_at")
                    ->limit(100)
                    ->select("b.Booking_Date", "b.Status", "p.Name as Professor_Name")
                    ->get();

                $next = null; // [Carbon $date, $row]
                foreach ($candidates as $r) {
                    $dateStr = (string) ($r->Booking_Date ?? "");
                    if ($dateStr === "") {
                        continue;
                    }
                    try {
                        $d = Carbon::parse($dateStr, $tz)->startOfDay();
                    } catch (\Throwable $e) {
                        continue;
                    }
                    if ($d->lessThan($now)) {
                        continue;
                    }
                    if ($next === null || $d->lessThan($next[0])) {
                        $next = [$d, $r];
                    }
                }

                if ($next !== null) {
                    [$d, $r] = $next;
                    $addon = sprintf(
                        " Next %sconsultation: %s — %s (%s).",
                        $acceptedOnly ? "accepted " : "",
                        $d->format("D, M d Y"),
                        (string) ($r->Professor_Name ?? "Professor"),
                        ucfirst((string) ($r->Status ?? "")),
                    );
                    return $baseMsg . $addon;
                }
            } catch (\Throwable $e) {
                // ignore add-on errors and keep base message
            }

            return $baseMsg;
        }

        // Group by date key
        $byDate = [];
        foreach ($rows as $r) {
            $k = (string) $r->Booking_Date;
            $byDate[$k] = $byDate[$k] ?? [];
            $byDate[$k][] = $r;
        }

        $lines = [];
        $lines[] = sprintf("Your %s week consultations:", $which);
        foreach ($days as $d) {
            $k = $d->format("D M d Y");
            $items = $byDate[$k] ?? [];
            if (empty($items)) {
                $lines[] = sprintf("- %s (%s): none", $d->format("D"), $d->format("M d"));
            } else {
                $parts = [];
                foreach ($items as $it) {
                    $parts[] = sprintf(
                        "%s (%s)",
                        (string) ($it->Professor_Name ?? "Professor"),
                        ucfirst((string) $it->Status),
                    );
                }
                $lines[] = sprintf(
                    "- %s (%s): %s",
                    $d->format("D"),
                    $d->format("M d"),
                    implode(", ", $parts),
                );
            }
        }
        return implode("\n", $lines);
    }

    private function summarizeStudentWeekFiltered(
        int $studId,
        string $which,
        array $statuses,
        string $label,
    ): string {
        $tz = "Asia/Manila";
        $start = Carbon::now($tz)->startOfWeek(Carbon::MONDAY);
        if ($which === "next") {
            $start = $start->copy()->addWeek();
        }
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = $start->copy()->addDays($i);
        }
        $dateKeys = array_map(fn($d) => $d->format("D M d Y"), $days);
        $statuses = array_values(array_unique(array_map("strtolower", $statuses)));

        $rows = DB::table("t_consultation_bookings as b")
            ->leftJoin("professors as p", "p.Prof_ID", "=", "b.Prof_ID")
            ->where("b.Stud_ID", $studId)
            ->whereIn("b.Booking_Date", $dateKeys)
            ->whereIn(DB::raw("LOWER(b.Status)"), $statuses)
            ->select("b.Booking_Date", "b.Status", "p.Name as Professor_Name")
            ->get();

        if ($rows->isEmpty()) {
            $range = $which === "next" ? "next week" : "this week";
            return "You have no " . $label . " consultations " . $range . ".";
        }

        $byDate = [];
        foreach ($rows as $r) {
            $k = (string) $r->Booking_Date;
            $byDate[$k] = $byDate[$k] ?? [];
            $byDate[$k][] = $r;
        }

        $lines = [];
        $lines[] = sprintf("Your %s %s week consultations:", $label, $which);
        foreach ($days as $d) {
            $k = $d->format("D M d Y");
            $items = $byDate[$k] ?? [];
            if (empty($items)) {
                continue; // skip days without items
            }
            $parts = [];
            foreach ($items as $it) {
                $parts[] = sprintf(
                    "%s (%s)",
                    (string) ($it->Professor_Name ?? "Professor"),
                    ucfirst((string) $it->Status),
                );
            }
            $lines[] = sprintf(
                "- %s (%s): %s",
                $d->format("D"),
                $d->format("M d"),
                implode(", ", $parts),
            );
        }
        if (count($lines) === 1) {
            $range = $which === "next" ? "next week" : "this week";
            return "You have no " . $label . " consultations " . $range . ".";
        }
        return implode("\n", $lines);
    }

    private function matchProfessorFromMessage(string $m)
    {
        // Score-based matching against real professor names from DB to avoid false positives like "week".
        $text = " " . preg_replace("/[^a-z0-9\s]/", " ", $m) . " ";
        $text = preg_replace("/\s+/", " ", $text);
        $stopwords = [
            " schedule ",
            " available ",
            " slots ",
            " slot ",
            " this ",
            " next ",
            " week ",
            " today ",
            " tomorrow ",
            " of ",
            " is ",
            " the ",
            " for ",
            " on ",
            " at ",
            " are ",
            " any ",
            " sir ",
            " maam ",
            " ma'am ",
            " mr ",
            " mrs ",
            " ms ",
            " professor ",
            " prof ",
            " dr ",
        ];
        $needleText = $text;
        foreach ($stopwords as $sw) {
            $needleText = str_replace($sw, " ", $needleText);
        }

        $best = null;
        $bestScore = 0;
        $rows = DB::table("professors")->select("Prof_ID", "Name", "Schedule")->get();
        foreach ($rows as $row) {
            $nameLower = mb_strtolower($row->Name);
            $score = 0;
            $full = " " . preg_replace("/\s+/", " ", $nameLower) . " ";
            if (str_contains($needleText, $full)) {
                $score += 100; // strong full-name hit
            }
            $tokens = preg_split("/\s+|,|\./", $nameLower);
            foreach ($tokens as $t) {
                $t = trim($t);
                if (mb_strlen($t) < 3) {
                    continue;
                }
                if (str_contains($needleText, " " . $t . " ")) {
                    $score += mb_strlen($t);
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $row;
            }
        }
        return $bestScore > 0 ? $best : null;
    }

    private function extractProfessorName(string $m): ?string
    {
        // Remove honorifics/prefixes
        $clean = str_replace(
            [
                "ma'am",
                "maam",
                "mam",
                "sir",
                "mr.",
                "mr",
                "mrs.",
                "mrs",
                "ms.",
                "ms",
                "prof.",
                "prof",
                "professor",
                "dr.",
                "dr",
            ],
            "",
            $m,
        );
        $clean = preg_replace("/\s+ni\s+|\s+kay\s+/u", " ", $clean);
        $clean = preg_replace("/\s+si\s+/u", " ", $clean);

        // Try to locate a token that matches any professor last name fragment
        // Heuristic: take last word with letters only
        if (preg_match('/([a-zñ]+)$/u', trim($clean), $mm)) {
            $candidate = $mm[1] ?? "";
            if ($candidate !== "") {
                return $candidate;
            }
        }
        return null;
    }

    private function findProfessorByNameLike(string $needle)
    {
        $needle = mb_strtolower($needle);
        return DB::table("professors")
            ->whereRaw("LOWER(Name) LIKE ?", ["%" . $needle . "%"])
            ->select("Prof_ID", "Name", "Schedule")
            ->orderBy("Prof_ID", "asc")
            ->first();
    }

    private function extractDate(string $m): ?Carbon
    {
        $tz = "Asia/Manila";
        $now = Carbon::now($tz);

        // English+common shorthands
        if (str_contains($m, "today")) {
            return $now->copy()->startOfDay();
        }
        if (str_contains($m, "tomorrow")) {
            return $now->copy()->addDay()->startOfDay();
        }

        // In X days
        if (preg_match("/in\s+(\d+)\s+day(s)?/i", $m, $mm)) {
            $n = (int) ($mm[1] ?? 0);
            if ($n >= 0 && $n <= 365) {
                return $now->copy()->addDays($n)->startOfDay();
            }
        }

        // Day-of-week (English)
        $dowMap = [
            "monday" => Carbon::MONDAY,
            "tuesday" => Carbon::TUESDAY,
            "wednesday" => Carbon::WEDNESDAY,
            "thursday" => Carbon::THURSDAY,
            "friday" => Carbon::FRIDAY,
            "saturday" => Carbon::SATURDAY,
            "sunday" => Carbon::SUNDAY,
            // Tagalog
            "lunes" => Carbon::MONDAY,
            "martes" => Carbon::TUESDAY,
            "miyerkules" => Carbon::WEDNESDAY,
            "huwebes" => Carbon::THURSDAY,
            "biyernes" => Carbon::FRIDAY,
            "sabado" => Carbon::SATURDAY,
            "linggo" => Carbon::SUNDAY,
        ];
        // next <weekday>
        foreach ($dowMap as $key => $const) {
            if (preg_match("/next\s+" . $key . "/i", $m)) {
                return Carbon::now($tz)->next($const)->startOfDay();
            }
        }
        // plain <weekday> (could mean this week's or next occurrence)
        foreach ($dowMap as $key => $const) {
            if (str_contains($m, $key)) {
                $target = Carbon::now($tz)->next($const)->startOfDay();
                // If it's the same weekday mentioned and today hasn't passed end of day, allow today
                if ((int) $now->dayOfWeek === $const) {
                    $target = $now->copy()->startOfDay();
                }
                return $target;
            }
        }
        // Try generic date parse from message
        try {
            $parsed = Carbon::parse($m, $tz);
            if ($parsed) {
                return $parsed->startOfDay();
            }
        } catch (\Throwable $e) {
        }

        // Try to extract explicit YYYY-MM-DD
        if (preg_match("/(\d{4}-\d{2}-\d{2})/u", $m, $mm)) {
            try {
                return Carbon::createFromFormat("Y-m-d", $mm[1], $tz)->startOfDay();
            } catch (\Throwable $e) {
            }
        }

        return null;
    }
    private function suggestProfessors(string $m): array
    {
        $text = " " . preg_replace("/[^a-z0-9\s]/", " ", mb_strtolower($m)) . " ";
        $text = preg_replace("/\s+/", " ", $text);
        $rows = DB::table("professors")->select("Name")->get();
        $scores = [];
        foreach ($rows as $row) {
            $name = mb_strtolower($row->Name);
            $score = 0;
            if (str_contains($text, " " . $name . " ")) {
                $score += 100;
            }
            $tokens = preg_split("/\s+/", $name);
            foreach ($tokens as $t) {
                if (mb_strlen($t) < 3) {
                    continue;
                }
                if (str_contains($text, " " . $t . " ")) {
                    $score += mb_strlen($t);
                }
            }
            $scores[] = [$row->Name, $score];
        }
        usort($scores, function ($a, $b) {
            return $b[1] <=> $a[1];
        });
        $suggestions = [];
        foreach ($scores as $pair) {
            if ($pair[1] <= 0) {
                break;
            }
            $suggestions[] = $pair[0];
            if (count($suggestions) >= 3) {
                break;
            }
        }
        return $suggestions;
    }

    private function summarizeWeek($prof, string $which): string
    {
        $tz = "Asia/Manila";
        $start = Carbon::now($tz)->startOfWeek(Carbon::MONDAY);
        if ($which === "next") {
            $start = $start->copy()->addWeek();
        }
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = $start->copy()->addDays($i);
        }

        // Preload bookings counts for the week
        $dateKeys = array_map(fn($d) => $d->format("D M d Y"), $days);
        $capacityStatuses = ["approved", "rescheduled"];
        $rows = DB::table("t_consultation_bookings")
            ->select("Booking_Date", DB::raw("COUNT(*) as cnt"))
            ->where("Prof_ID", $prof->Prof_ID)
            ->whereIn("Status", $capacityStatuses)
            ->whereIn("Booking_Date", $dateKeys)
            ->groupBy("Booking_Date")
            ->get()
            ->pluck("cnt", "Booking_Date");

        // Parse schedule into day -> times
        $parsed = $this->parseProfessorSchedule((string) ($prof->Schedule ?? ""));

        $capacity = 5;
        $lines = [];
        $title = ucfirst($which) . " week schedule for " . $prof->Name . ":";
        $lines[] = $title;
        foreach ($days as $d) {
            $key = $d->format("D M d Y");
            $dow = strtolower($d->format("l")); // monday..sunday
            $times = $parsed[$dow] ?? [];
            $booked = (int) ($rows[$key] ?? 0);
            $remaining = max($capacity - $booked, 0);
            $note = "";
            try {
                $ov = app(\App\Services\CalendarOverrideService::class)->evaluate(
                    (int) $prof->Prof_ID,
                    $key,
                );
                if (($ov["blocked"] ?? false) === true) {
                    $note = " [blocked]";
                } elseif (!empty($ov["forced_mode"])) {
                    $note = " [mode: " . ucfirst((string) $ov["forced_mode"]) . "]";
                }
            } catch (\Throwable $e) {
            }

            if (empty($times)) {
                $lines[] = sprintf(
                    "- %s (%s): No schedule set. Remaining %d/%d%s",
                    $d->format("D"),
                    $d->format("M d"),
                    $remaining,
                    $capacity,
                    $note,
                );
            } else {
                $lines[] = sprintf(
                    "- %s (%s): %s. Remaining %d/%d%s",
                    $d->format("D"),
                    $d->format("M d"),
                    implode(" | ", $times),
                    $remaining,
                    $capacity,
                    $note,
                );
            }
        }
        return implode("\n", $lines);
    }

    private function parseProfessorSchedule(string $text): array
    {
        $result = [
            "monday" => [],
            "tuesday" => [],
            "wednesday" => [],
            "thursday" => [],
            "friday" => [],
            "saturday" => [],
            "sunday" => [],
        ];
        if (trim($text) === "") {
            return $result;
        }

        $dayMap = [
            "monday" => ["monday", "mon", "m"],
            "tuesday" => ["tuesday", "tue", "t", "tu"],
            "wednesday" => ["wednesday", "wed", "w"],
            "thursday" => ["thursday", "thu", "thur", "th"],
            "friday" => ["friday", "fri", "f"],
            "saturday" => ["saturday", "sat", "sa"],
            "sunday" => ["sunday", "sun", "su"],
        ];
        // Special combos like MWF, TTh
        $comboMap = [
            "mwf" => ["monday", "wednesday", "friday"],
            "tth" => ["tuesday", "thursday"],
        ];

        $normalized = preg_replace("/<br\s*\/>/i", "\n", $text);
        $normalized = str_replace([";", "|"], "\n", $normalized);
        $lines = preg_split('/\n+/', $normalized);
        foreach ($lines as $line) {
            $lower = mb_strtolower(trim($line));
            if ($lower === "") {
                continue;
            }

            // Extract time range if any
            $time = null;
            if (
                preg_match(
                    "/(\d{1,2}:\d{2}\s*(?:am|pm))\s*(?:\-|to|–|—)\s*(\d{1,2}:\d{2}\s*(?:am|pm))/i",
                    $lower,
                    $mm,
                )
            ) {
                $time = strtoupper(trim($mm[1] . " - " . $mm[2]));
            } elseif (
                preg_match(
                    "/(\d{1,2}\s*(?:am|pm))\s*(?:\-|to|–|—)\s*(\d{1,2}\s*(?:am|pm))/i",
                    $lower,
                    $mm,
                )
            ) {
                $time = strtoupper(trim($mm[1] . " - " . $mm[2]));
            }
            if (!$time) {
                $time = "Available";
            }

            $matchedAny = false;
            // Combo patterns
            foreach ($comboMap as $key => $days) {
                if (str_contains($lower, $key)) {
                    foreach ($days as $d) {
                        $result[$d][] = $time;
                    }
                    $matchedAny = true;
                }
            }
            if ($matchedAny) {
                continue;
            }

            // Individual days
            foreach ($dayMap as $day => $aliases) {
                foreach ($aliases as $alias) {
                    if (preg_match("/\b" . preg_quote($alias, "/") . "\b/i", $lower)) {
                        $result[$day][] = $time;
                        $matchedAny = true;
                        break;
                    }
                }
            }
            // If nothing matched but line contains days separated by commas (e.g., Monday, Wednesday)
            if (
                !$matchedAny &&
                preg_match_all("/\b(mon|tue|wed|thu|thur|fri|sat|sun)\b/i", $lower, $list)
            ) {
                foreach ($list[1] as $abbr) {
                    $abbr = strtolower($abbr);
                    foreach ($dayMap as $day => $aliases) {
                        if (in_array($abbr, $aliases, true)) {
                            $result[$day][] = $time;
                            break;
                        }
                    }
                }
            }
        }
        return $result;
    }

    // --- Subject helpers ---
    private function matchSubjectFromMessage(string $m)
    {
        $text = " " . preg_replace("/[^a-z0-9\s]/", " ", $m) . " ";
        $text = preg_replace("/\s+/", " ", $text);
        $rows = DB::table("t_subject")->select("Subject_ID", "Subject_Name")->get();
        $best = null;
        $bestScore = 0;
        foreach ($rows as $row) {
            $nameLower = mb_strtolower($row->Subject_Name ?? "");
            if ($nameLower === "") {
                continue;
            }
            $score = 0;
            $full = " " . preg_replace("/\s+/", " ", $nameLower) . " ";
            if (str_contains($text, $full)) {
                $score += 100; // exact phrase hit
            }
            $tokens = preg_split("/\s+|,|\./", $nameLower);
            foreach ($tokens as $t) {
                $t = trim($t);
                if (mb_strlen($t) < 3) {
                    continue;
                }
                if (str_contains($text, " " . $t . " ")) {
                    $score += mb_strlen($t);
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $row;
            }
        }
        return $bestScore > 0 ? $best : null;
    }

    private function suggestSubjects(string $m): array
    {
        $text = " " . preg_replace("/[^a-z0-9\s]/", " ", mb_strtolower($m)) . " ";
        $text = preg_replace("/\s+/", " ", $text);
        $rows = DB::table("t_subject")->select("Subject_Name")->get();
        $scores = [];
        foreach ($rows as $row) {
            $name = mb_strtolower($row->Subject_Name ?? "");
            if ($name === "") {
                continue;
            }
            $score = 0;
            if (str_contains($text, " " . $name . " ")) {
                $score += 100;
            }
            $tokens = preg_split("/\s+|,|\./", $name);
            foreach ($tokens as $t) {
                if (mb_strlen($t) < 3) {
                    continue;
                }
                if (str_contains($text, " " . $t . " ")) {
                    $score += mb_strlen($t);
                }
            }
            $scores[] = [$row->Subject_Name, $score];
        }
        usort($scores, fn($a, $b) => $b[1] <=> $a[1]);
        $out = [];
        foreach ($scores as $pair) {
            if ($pair[1] <= 0) {
                break;
            }
            $out[] = $pair[0];
            if (count($out) >= 3) {
                break;
            }
        }
        return $out;
    }
}
