<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminAnalyticsController extends Controller
{
    private const TZ = 'Asia/Manila';
    /**
     * Show analytics page.
     */
    public function index()
    {
        return view('admin-analytics');
    }

    /**
     * Return aggregated analytics data for charts.
     */
    public function data(Request $request)
    {
        // Fetch all bookings with needed joins.
        $bookings = DB::table('t_consultation_bookings as b')
            ->join('professors as p', 'p.Prof_ID', '=', 'b.Prof_ID')
            ->join('t_consultation_types as ct', 'ct.Consult_type_ID', '=', 'b.Consult_type_ID')
            ->select([
                'b.Booking_ID',
                'b.Booking_Date',
                DB::raw("COALESCE(b.Custom_Type, ct.Consult_Type) as Type"),
                'p.Dept_ID'
            ])
            // Only include consultations that are fully completed. Exclude pending/approved/rescheduled.
            ->where('b.Status', 'completed')
            ->get();

        // If no bookings, return zeroed structure.
        if ($bookings->isEmpty()) {
            return response()->json($this->emptyPayload());
        }

        // Department mapping (adjust if different in DB)
        $deptMap = [
            1 => 'IT & IS',
            2 => 'CompSci'
        ];

        // --- Aggregate Topics per Department (stacked bar) ---
        $topicCounts = [];
        foreach ($bookings as $b) {
            $dept = $deptMap[$b->Dept_ID] ?? 'Other';
            $topic = $b->Type ?: 'Unknown';
            $topicCounts[$dept][$topic] = ($topicCounts[$dept][$topic] ?? 0) + 1;
        }

        // Determine top 3 topics overall (fallback to fixed list if fewer)
        $overallTopicCounts = [];
        foreach ($topicCounts as $dept => $topics) {
            foreach ($topics as $t => $c) {
                $overallTopicCounts[$t] = ($overallTopicCounts[$t] ?? 0) + $c;
            }
        }
        arsort($overallTopicCounts);
        $topTopics = array_slice(array_keys($overallTopicCounts), 0, 3);
        // If less than 3, pad with placeholders
        while (count($topTopics) < 3) {
            $topTopics[] = 'Topic '.(count($topTopics)+1);
        }

        // Build per-dept topic data ensuring zero fill
        $departments = array_values(array_unique(array_map(function($d){return $d;}, array_keys($topicCounts))));
        sort($departments);
        $topicsByDept = [];
        foreach ($departments as $dept) {
            foreach ($topTopics as $topic) {
                $topicsByDept[$dept][$topic] = $topicCounts[$dept][$topic] ?? 0;
            }
        }

    // Pre-init weekday counts (Mon-Fri only) using ISO weekday numbers to avoid locale name issues.
    $dayCounts = ['Monday'=>0,'Tuesday'=>0,'Wednesday'=>0,'Thursday'=>0,'Friday'=>0];
    $weekendCounts = ['Saturday'=>0,'Sunday'=>0];
    $isoToName = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'];

        // --- Monthly Consultation Activity (line chart) ---
        $monthOrder = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        // Determine range (current year) or from earliest booking year if different
        $monthsUsed = [];
        $monthlyCounts = [];
    $debugRaw = [];
    foreach ($bookings as $b) {
            // Booking_Date stored like 'Mon Aug 26 2025'
            $carbon = $this->parseBookingDate($b->Booking_Date);
            if (!$carbon) continue;
            $monthLabel = $carbon->format('M');
            $dept = $deptMap[$b->Dept_ID] ?? 'Other';
            $monthlyCounts[$dept][$monthLabel] = ($monthlyCounts[$dept][$monthLabel] ?? 0) + 1;
            $monthsUsed[$monthLabel] = true;

            // Weekday counts (locale-agnostic)
            $dow = $carbon->dayOfWeekIso; // 1=Mon ... 7=Sun
            if ($dow >=1 && $dow <=5) {
                $dayCounts[$isoToName[$dow]]++;
            } elseif ($dow == 6 || $dow == 7) {
                $weekendCounts[$isoToName[$dow]]++;
            }

            if ($request->boolean('include_raw')) {
                $debugRaw[] = [
                    'booking_date_raw' => $b->Booking_Date,
                    'parsed_weekday_iso' => $dow,
                    'parsed_weekday_name' => $dow >=1 && $dow <=5 ? $isoToName[$dow] : $carbon->format('l'),
                    'dept' => $dept,
                    'parsed_date' => $carbon->toDateString(),
                ];
            }
        }
        // Use only months that appear OR last 6 months if prefer; here we order by monthOrder filter to those used
        $months = array_values(array_filter($monthOrder, fn($m)=>isset($monthsUsed[$m])));
        if (empty($months)) {
            $months = array_slice($monthOrder, 0, 5); // default first five months
        }
        $activitySeries = [];
        foreach ($departments as $dept) {
            $activitySeries[$dept] = [];
            foreach ($months as $m) {
                $activitySeries[$dept][] = $monthlyCounts[$dept][$m] ?? 0;
            }
        }

    // Peak consultation days already built in $dayCounts

        $payload = [
            'topics' => [
                'departments' => $departments,
                'topics' => $topTopics,
                'data' => $topicsByDept,
            ],
            'activity' => [
                'months' => $months,
                'series' => $activitySeries,
            ],
            'peak_days' => $dayCounts,
            'weekend_days' => $weekendCounts,
            'debug' => $request->boolean('include_raw') ? $debugRaw : null,
            'server_time' => [
                'manila_now' => Carbon::now(self::TZ)->format('Y-m-d H:i:s'),
                'utc_now' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
            ],
        ];

        return response()->json($payload);
    }

    private function parseBookingDate($dateString): ?Carbon
    {
        if (!$dateString) return null;
        $clean = trim(preg_replace('/\s+/', ' ', str_replace([','], '', $dateString)));

        // 1. Direct pattern with weekday at start e.g. Fri Aug 29 2025 ...
        if (preg_match('/^(Mon|Tue|Wed|Thu|Fri|Sat|Sun)\s+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+(\d{1,2})\s+(\d{4})/i', $clean, $m)) {
            return $this->buildCarbon($m[2], $m[3], $m[4]);
        }
        // 2. Any Month Day Year sequence anywhere in the string
        if (preg_match('/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+(\d{1,2})\s+(\d{4})/i', $clean, $m2)) {
            return $this->buildCarbon($m2[1], $m2[2], $m2[3]);
        }
        // 3. ISO / SQL date formats YYYY-MM-DD or with time
        if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $clean, $m3)) {
            try { return Carbon::createFromDate((int)$m3[1], (int)$m3[2], (int)$m3[3], self::TZ)->startOfDay(); } catch (\Exception $e) {}
        }
        // 4. Fallback generic parse
        try { return Carbon::parse($clean, self::TZ)->startOfDay(); } catch (\Exception $e) { return null; }
    }

    private function buildCarbon(string $monTxt, string $dayTxt, string $yearTxt): ?Carbon
    {
        $monthMap = [
            'Jan'=>1,'Feb'=>2,'Mar'=>3,'Apr'=>4,'May'=>5,'Jun'=>6,
            'Jul'=>7,'Aug'=>8,'Sep'=>9,'Oct'=>10,'Nov'=>11,'Dec'=>12
        ];
        $monKey = ucfirst(strtolower(substr($monTxt,0,3))); // normalize e.g. AUG -> Aug
        if (!isset($monthMap[$monKey])) return null;
        if (!ctype_digit($dayTxt) || !ctype_digit($yearTxt)) return null;
        try {
            return Carbon::createFromDate((int)$yearTxt, $monthMap[$monKey], (int)$dayTxt, self::TZ)->startOfDay();
        } catch (\Exception $e) { return null; }
    }

    private function emptyPayload(): array
    {
        return [
            'topics' => [
                'departments' => ['IT & IS','CompSci'],
                'topics' => ['Programming','Networking','Capstone'],
                'data' => [
                    'IT & IS' => ['Programming'=>0,'Networking'=>0,'Capstone'=>0],
                    'CompSci' => ['Programming'=>0,'Networking'=>0,'Capstone'=>0],
                ],
            ],
            'activity' => [
                'months' => ['Jan','Feb','Mar','Apr','May'],
                'series' => [
                    'IT & IS' => [0,0,0,0,0],
                    'CompSci' => [0,0,0,0,0]
                ],
            ],
            'peak_days' => ['Monday'=>0,'Tuesday'=>0,'Wednesday'=>0,'Thursday'=>0,'Friday'=>0],
        ];
    }
}
