<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Services\AcademicTermService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function __invoke(AcademicTermService $service)
    {
        $active = $service->getActiveTerm();
        $terms = collect();

        if (Schema::hasTable('terms')) {
            try {
                $terms = Term::query()->with('academicYear')->orderByDesc('start_at')->limit(20)->get();
            } catch (QueryException $e) {
                $terms = collect();
            }
        }

        return view("admin-dashboard", [
            "activeTerm" => $active,
            "termOptions" => $terms,
        ]);
    }
}
