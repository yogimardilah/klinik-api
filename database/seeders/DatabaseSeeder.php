<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pasien;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@klinik.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'email_verified_at' => now()
        ]);

        // Create doctor users
        User::create([
            'name' => 'Dr. John Smith',
            'email' => 'doctor@klinik.com',
            'password' => Hash::make('doctor123'),
            'role' => 'doctor',
            'email_verified_at' => now()
        ]);

        User::create([
            'name' => 'Dr. Sarah Johnson',
            'email' => 'sarah.doctor@klinik.com',
            'password' => Hash::make('doctor123'),
            'role' => 'doctor',
            'email_verified_at' => now()
        ]);

        // Create nurse users
        User::create([
            'name' => 'Nurse Mary',
            'email' => 'nurse@klinik.com',
            'password' => Hash::make('nurse123'),
            'role' => 'nurse',
            'email_verified_at' => now()
        ]);

        User::create([
            'name' => 'Nurse Lisa',
            'email' => 'lisa.nurse@klinik.com',
            'password' => Hash::make('nurse123'),
            'role' => 'nurse',
            'email_verified_at' => now()
        ]);

        // Create staff users
        User::create([
            'name' => 'Staff Member',
            'email' => 'staff@klinik.com',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
            'email_verified_at' => now()
        ]);

        User::create([
            'name' => 'Reception Staff',
            'email' => 'reception@klinik.com',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
            'email_verified_at' => now()
        ]);

        // Create sample patients
        Pasien::create([
            'nama' => 'Ahmad Susanto',
            'tanggal_lahir' => '1985-05-15',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Merdeka No. 123, Jakarta',
            'nomor_telepon' => '081234567890',
            'email' => 'ahmad.susanto@email.com',
            'golongan_darah' => 'A',
            'status' => 'aktif',
            'nomor_rekam_medis' => 'RM-2024-001'
        ]);

        Pasien::create([
            'nama' => 'Siti Rahmawati',
            'tanggal_lahir' => '1992-08-22',
            'jenis_kelamin' => 'P',
            'alamat' => 'Jl. Sudirman No. 456, Bandung',
            'nomor_telepon' => '081987654321',
            'email' => 'siti.rahmawati@email.com',
            'golongan_darah' => 'B',
            'status' => 'aktif',
            'nomor_rekam_medis' => 'RM-2024-002'
        ]);

        Pasien::create([
            'nama' => 'Budi Hartono',
            'tanggal_lahir' => '1978-12-10',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Gatot Subroto No. 789, Surabaya',
            'nomor_telepon' => '081122334455',
            'email' => 'budi.hartono@email.com',
            'golongan_darah' => 'O',
            'status' => 'aktif',
            'nomor_rekam_medis' => 'RM-2024-003'
        ]);

        Pasien::create([
            'nama' => 'Dewi Lestari',
            'tanggal_lahir' => '1995-03-07',
            'jenis_kelamin' => 'P',
            'alamat' => 'Jl. Diponegoro No. 321, Yogyakarta',
            'nomor_telepon' => '081556677889',
            'email' => 'dewi.lestari@email.com',
            'golongan_darah' => 'AB',
            'status' => 'aktif',
            'nomor_rekam_medis' => 'RM-2024-004'
        ]);

        Pasien::create([
            'nama' => 'Eko Prasetyo',
            'tanggal_lahir' => '1988-11-25',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Ahmad Yani No. 654, Medan',
            'nomor_telepon' => '081998877665',
            'email' => 'eko.prasetyo@email.com',
            'golongan_darah' => 'A',
            'status' => 'aktif',
            'nomor_rekam_medis' => 'RM-2024-005'
        ]);

        // Display seeded credentials for testing
        $this->command->info('Sample users created:');
        $this->command->info('Admin: admin@klinik.com / admin123');
        $this->command->info('Doctor: doctor@klinik.com / doctor123');
        $this->command->info('Nurse: nurse@klinik.com / nurse123');
        $this->command->info('Staff: staff@klinik.com / staff123');
        $this->command->info('Sample patients: 5 patients created');
    }
}
