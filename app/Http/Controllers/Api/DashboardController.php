<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Student;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $totalStudents = Student::count();
        $signedContracts = Contract::whereNotNull('signed_at')->count();

        $piusPlusCount = Student::where('package_type', 'pius-plus')->count();
        $piusProCount = Student::where('package_type', 'pius-pro')->count();

        $piusPlusRevenue = $piusPlusCount * 1800;
        $piusProRevenue = $piusProCount * 2500;

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
            'by_package' => [
                'pius_plus' => $piusPlusCount,
                'pius_pro' => $piusProCount,
            ],
            'revenue' => [
                'pius_plus' => $piusPlusRevenue,
                'pius_pro' => $piusProRevenue,
                'total' => $piusPlusRevenue + $piusProRevenue,
            ],
            'recent_students' => $recentStudents,
        ]);
    }
}
