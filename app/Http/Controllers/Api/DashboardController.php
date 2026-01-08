<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Package;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $totalStudents = Student::count();
        $signedContracts = Contract::whereNotNull('signed_at')->count();

        // Get all packages with their prices
        $packages = Package::all()->keyBy('slug');

        // Count students by package and calculate revenue
        $packageStats = Student::select('package_type', DB::raw('count(*) as count'))
            ->groupBy('package_type')
            ->get()
            ->keyBy('package_type');

        $byPackage = [];
        $revenue = [];
        $totalRevenue = 0;

        foreach ($packages as $slug => $package) {
            $count = $packageStats->get($slug)?->count ?? 0;
            $byPackage[$slug] = $count;
            $packageRevenue = $count * floatval($package->price);
            $revenue[$slug] = $packageRevenue;
            $totalRevenue += $packageRevenue;
        }

        // Ensure backward compatibility with old package names
        $byPackage['pius_plus'] = $byPackage['pius-plus'] ?? 0;
        $byPackage['pius_pro'] = $byPackage['pius-pro'] ?? 0;
        $revenue['pius_plus'] = $revenue['pius-plus'] ?? 0;
        $revenue['pius_pro'] = $revenue['pius-pro'] ?? 0;

        $recentStudents = Student::with('contracts')
            ->orderBy('enrolled_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'total_students' => $totalStudents,
            'signed_contracts' => $signedContracts,
            'by_status' => [
                'enrolled' => Student::where('status', 'enrolled')->count(),
                'contract_signed' => Student::where('status', 'contract_signed')->count(),
                'completed' => Student::where('status', 'completed')->count(),
            ],
            'by_package' => $byPackage,
            'revenue' => array_merge($revenue, ['total' => $totalRevenue]),
            'recent_students' => $recentStudents,
        ]);
    }
}
