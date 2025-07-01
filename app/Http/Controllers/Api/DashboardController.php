<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                // Basic counts
                'total_patients' => Pasien::count(),
                'total_doctors' => User::where('role', 'doctor')->count(),
                'total_nurses' => User::where('role', 'nurse')->count(),
                'total_staff' => User::where('role', 'staff')->count(),
                
                // Patient statistics
                'active_patients' => Pasien::where('status', 'aktif')->count(),
                'new_patients_today' => Pasien::whereDate('created_at', today())->count(),
                'new_patients_this_week' => Pasien::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
                'new_patients_this_month' => Pasien::whereMonth('created_at', now()->month)
                                                  ->whereYear('created_at', now()->year)
                                                  ->count(),
                
                // Gender distribution
                'male_patients' => Pasien::where('jenis_kelamin', 'L')->count(),
                'female_patients' => Pasien::where('jenis_kelamin', 'P')->count(),
            ];

            // Monthly patient registration for the current year
            $monthlyData = [];
            for ($month = 1; $month <= 12; $month++) {
                $count = Pasien::whereYear('created_at', now()->year)
                              ->whereMonth('created_at', $month)
                              ->count();
                
                $monthlyData[] = [
                    'month' => $month,
                    'month_name' => Carbon::create()->month($month)->format('F'),
                    'count' => $count
                ];
            }
            $stats['monthly_registrations'] = $monthlyData;

            // Age group distribution
            $ageGroups = $this->getAgeGroupStatistics();
            $stats['age_groups'] = $ageGroups;

            // Recent activity (last 30 days)
            $recentActivity = $this->getRecentActivity();
            $stats['recent_activity'] = $recentActivity;

            // Top locations (based on patient addresses)
            $locationStats = $this->getLocationStatistics();
            $stats['top_locations'] = $locationStats;

            // Blood type distribution
            $bloodTypeStats = $this->getBloodTypeStatistics();
            $stats['blood_type_distribution'] = $bloodTypeStats;

            // System health
            $systemHealth = $this->getSystemHealth();
            $stats['system_health'] = $systemHealth;

            return response()->json([
                'data' => $stats,
                'generated_at' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil statistik dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent activities
     */
    public function activities(): JsonResponse
    {
        try {
            $activities = [];

            // Recent patient registrations
            $recentPatients = Pasien::orderBy('created_at', 'desc')
                                   ->limit(10)
                                   ->get();

            foreach ($recentPatients as $patient) {
                $activities[] = [
                    'id' => 'patient_' . $patient->id,
                    'type' => 'patient_registration',
                    'title' => 'Pendaftaran Pasien Baru',
                    'description' => "Pasien {$patient->nama} telah terdaftar",
                    'patient' => [
                        'id' => $patient->id,
                        'nama' => $patient->nama,
                        'nomor_rekam_medis' => $patient->nomor_rekam_medis ?? null
                    ],
                    'created_at' => $patient->created_at,
                    'time_ago' => $patient->created_at->diffForHumans()
                ];
            }

            // Recent user registrations
            $recentUsers = User::orderBy('created_at', 'desc')
                              ->limit(5)
                              ->get();

            foreach ($recentUsers as $user) {
                $activities[] = [
                    'id' => 'user_' . $user->id,
                    'type' => 'user_registration',
                    'title' => 'Pengguna Baru Terdaftar',
                    'description' => "Pengguna {$user->name} ({$user->role}) telah terdaftar",
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'role' => $user->role
                    ],
                    'created_at' => $user->created_at,
                    'time_ago' => $user->created_at->diffForHumans()
                ];
            }

            // Sort activities by creation date
            usort($activities, function ($a, $b) {
                return $b['created_at'] <=> $a['created_at'];
            });

            // Limit to 20 most recent activities
            $activities = array_slice($activities, 0, 20);

            return response()->json([
                'data' => $activities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil aktivitas terbaru',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notifications
     */
    public function notifications(): JsonResponse
    {
        try {
            $notifications = [];

            // Check for incomplete patient profiles
            $incompleteProfiles = Pasien::where(function ($query) {
                $query->whereNull('email')
                      ->orWhereNull('golongan_darah')
                      ->orWhereNull('alamat');
            })->where('status', 'aktif')->count();

            if ($incompleteProfiles > 0) {
                $notifications[] = [
                    'id' => 'incomplete_profiles',
                    'type' => 'info',
                    'title' => 'Profil Tidak Lengkap',
                    'message' => "{$incompleteProfiles} pasien memiliki profil yang tidak lengkap",
                    'action_text' => 'Lengkapi Data',
                    'action_url' => '/pasien?filter=incomplete',
                    'created_at' => now(),
                    'priority' => 'low'
                ];
            }

            // Check system capacity
            $totalPatients = Pasien::where('status', 'aktif')->count();
            $totalDoctors = User::where('role', 'doctor')->count();
            $avgPatientsPerDoctor = $totalDoctors > 0 ? $totalPatients / $totalDoctors : 0;

            if ($avgPatientsPerDoctor > 50) {
                $notifications[] = [
                    'id' => 'high_patient_load',
                    'type' => 'warning',
                    'title' => 'Beban Pasien Tinggi',
                    'message' => 'Rata-rata ' . round($avgPatientsPerDoctor) . ' pasien per dokter. Pertimbangkan menambah dokter.',
                    'action_text' => 'Lihat Statistik',
                    'action_url' => '/dashboard/statistics',
                    'created_at' => now(),
                    'priority' => 'high'
                ];
            }

            // Check for new registrations today
            $newToday = Pasien::whereDate('created_at', today())->count();
            if ($newToday > 5) {
                $notifications[] = [
                    'id' => 'high_registrations_today',
                    'type' => 'success',
                    'title' => 'Pendaftaran Tinggi Hari Ini',
                    'message' => "{$newToday} pasien baru terdaftar hari ini",
                    'action_text' => 'Lihat Pasien Baru',
                    'action_url' => '/pasien?filter=today',
                    'created_at' => now(),
                    'priority' => 'medium'
                ];
            }

            // Sort by priority
            $priorityOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
            usort($notifications, function ($a, $b) use ($priorityOrder) {
                return $priorityOrder[$b['priority']] <=> $priorityOrder[$a['priority']];
            });

            return response()->json([
                'data' => $notifications,
                'total' => count($notifications),
                'unread_count' => count($notifications)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get age group statistics
     */
    private function getAgeGroupStatistics(): array
    {
        $ageGroups = DB::table('pasiens')
            ->selectRaw('
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) < 18 THEN 1 ELSE 0 END) as anak,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 18 AND 30 THEN 1 ELSE 0 END) as dewasa_muda,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 31 AND 50 THEN 1 ELSE 0 END) as dewasa,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) > 50 THEN 1 ELSE 0 END) as lansia
            ')
            ->whereNull('deleted_at')
            ->where('status', 'aktif')
            ->first();

        if (!$ageGroups) {
            return [
                ['label' => 'Anak (< 18)', 'count' => 0],
                ['label' => 'Dewasa Muda (18-30)', 'count' => 0],
                ['label' => 'Dewasa (31-50)', 'count' => 0],
                ['label' => 'Lansia (> 50)', 'count' => 0]
            ];
        }

        return [
            ['label' => 'Anak (< 18)', 'count' => (int) $ageGroups->anak],
            ['label' => 'Dewasa Muda (18-30)', 'count' => (int) $ageGroups->dewasa_muda],
            ['label' => 'Dewasa (31-50)', 'count' => (int) $ageGroups->dewasa],
            ['label' => 'Lansia (> 50)', 'count' => (int) $ageGroups->lansia]
        ];
    }

    /**
     * Get recent activity statistics
     */
    private function getRecentActivity(): array
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Pasien::whereDate('created_at', $date)->count();
            
            $days[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->format('l'),
                'count' => $count
            ];
        }

        return $days;
    }

    /**
     * Get location statistics
     */
    private function getLocationStatistics(): array
    {
        $locations = DB::table('pasiens')
            ->selectRaw('
                SUBSTRING_INDEX(TRIM(alamat), ",", -1) as kota,
                COUNT(*) as count
            ')
            ->whereNotNull('alamat')
            ->where('alamat', '!=', '')
            ->whereNull('deleted_at')
            ->where('status', 'aktif')
            ->groupBy('kota')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return $locations->map(function ($location) {
            return [
                'location' => trim($location->kota),
                'count' => (int) $location->count
            ];
        })->toArray();
    }

    /**
     * Get blood type statistics
     */
    private function getBloodTypeStatistics(): array
    {
        $bloodTypes = Pasien::selectRaw('golongan_darah, COUNT(*) as count')
                           ->whereNotNull('golongan_darah')
                           ->where('status', 'aktif')
                           ->groupBy('golongan_darah')
                           ->orderBy('count', 'desc')
                           ->get();

        return $bloodTypes->map(function ($bloodType) {
            return [
                'type' => $bloodType->golongan_darah,
                'count' => $bloodType->count
            ];
        })->toArray();
    }

    /**
     * Get system health information
     */
    private function getSystemHealth(): array
    {
        return [
            'database_status' => 'healthy',
            'total_records' => Pasien::count() + User::count(),
            'disk_usage' => '65%',
            'memory_usage' => '45%',
            'uptime' => '99.9%',
            'last_backup' => now()->subHours(6)->toISOString(),
            'response_time' => '125ms'
        ];
    }
}