<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Classes;
use App\Models\Cluster;
use App\Models\Environment;
use App\Models\Region;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Regions
        $jabar = Region::create([
            'name' => 'Jawa Barat',
            'code' => 'JB',
            'is_development' => false,
            'is_active' => true,
        ]);

        Region::create([
            'name' => 'Jawa Tengah',
            'code' => 'JT',
            'is_development' => true,
            'is_active' => false,
        ]);

        Region::create([
            'name' => 'Jawa Timur',
            'code' => 'JI',
            'is_development' => true,
            'is_active' => false,
        ]);

        Region::create([
            'name' => 'DKI Jakarta',
            'code' => 'JK',
            'is_development' => true,
            'is_active' => false,
        ]);

        // 2. Clusters
        $cluster = Cluster::create([
            'region_id' => $jabar->id,
            'name' => 'KLASTER PLACEHOLDER (ganti dengan data resmi)',
            'is_active' => true,
        ]);

        // 3. Environments
        $gpi = Environment::create([
            'cluster_id' => $cluster->id,
            'name' => 'GPI',
            'is_active' => true,
        ]);

        $jagabaya = Environment::create([
            'cluster_id' => $cluster->id,
            'name' => 'Jagabaya',
            'is_active' => true,
        ]);

        // 4. Classes
        $classGpi = Classes::create([
            'environment_id' => $gpi->id,
            'name' => 'Kelas Anak GPI',
            'code' => 'KLS-GPI-01',
            'location' => 'Ruang Serbaguna GPI',
            'schedule' => 'Minggu, 09.00 - 11.00 WIB',
            'status' => 'active',
        ]);

        $classJagabaya = Classes::create([
            'environment_id' => $jagabaya->id,
            'name' => 'Kelas Anak Jagabaya',
            'code' => 'KLS-JGB-01',
            'location' => 'Pos Belajar Jagabaya',
            'schedule' => 'Sabtu, 14.00 - 16.00 WIB',
            'status' => 'active',
        ]);

        // 5. Teachers
        $teacherBudi = Teacher::create([
            'full_name' => 'Budi Santoso',
            'nickname' => 'Kak Budi',
            'phone' => '081234567890',
            'status' => 'active',
        ]);

        $teacherSiti = Teacher::create([
            'full_name' => 'Siti Rahma',
            'nickname' => 'Kak Siti',
            'phone' => '081298765432',
            'status' => 'active',
        ]);

        // Assign to classes
        $classGpi->teachers()->attach($teacherBudi->id, ['is_primary' => true]);
        $classJagabaya->teachers()->attach($teacherSiti->id, ['is_primary' => true]);

        // 6. Users
        User::create([
            'name' => 'Budi Santoso',
            'username' => 'guru',
            'email' => 'guru@example.com',
            'password' => Hash::make('Password123'),
            'role' => 'guru',
            'teacher_id' => $teacherBudi->id,
            'status' => 'active',
            'must_change_password' => false,
        ]);

        User::create([
            'name' => 'Siti Rahma',
            'username' => 'gurusiti',
            'email' => 'siti@example.com',
            'password' => Hash::make('Password123'),
            'role' => 'guru',
            'teacher_id' => $teacherSiti->id,
            'status' => 'active',
            'must_change_password' => true,
        ]);

        // 7. Students
        Student::create([
            'class_id' => $classGpi->id,
            'full_name' => 'Ahmad Faiz',
            'nickname' => 'Faiz',
            'gender' => 'L',
            'birth_date' => '2018-05-12',
            'address' => 'Jl. Mawar No. 10',
            'parent_name' => 'Bambang',
            'parent_phone' => '08111222333',
            'status' => 'active',
        ]);

        Student::create([
            'class_id' => $classGpi->id,
            'full_name' => 'Dewi Lestari',
            'nickname' => 'Dewi',
            'gender' => 'P',
            'birth_date' => '2019-02-18',
            'address' => 'Jl. Melati No. 4',
            'parent_name' => 'Lestari',
            'parent_phone' => '08111444555',
            'status' => 'active',
        ]);

        Student::create([
            'class_id' => $classJagabaya->id,
            'full_name' => 'Bima Satria',
            'nickname' => 'Bima',
            'gender' => 'L',
            'birth_date' => '2018-11-20',
            'address' => 'Jl. Kenanga No. 7',
            'parent_name' => 'Satria',
            'parent_phone' => '08111666777',
            'status' => 'active',
        ]);

        // 8. Activities
        Activity::create([
            'title' => 'Mewarnai Bersama di GPI',
            'description' => 'Kegiatan ceria mewarnai buku cerita moral bersama seluruh peserta kelas anak.',
            'activity_date' => now()->subDays(2),
            'scope_type' => 'class',
            'class_id' => $classGpi->id,
            'status' => 'published',
        ]);

        Activity::create([
            'title' => 'Latihan Bernyanyi dan Doa',
            'description' => 'Mengenal lagu-lagu persahabatan dan menghafal doa bersama Kak Siti.',
            'activity_date' => now()->subDay(),
            'scope_type' => 'class',
            'class_id' => $classJagabaya->id,
            'status' => 'published',
        ]);

        Activity::create([
            'title' => 'Piknik Ceria Bersama',
            'description' => 'Kegiatan luar ruangan bersama pengurus dan guru yang sedang ditinjau.',
            'activity_date' => now()->addDays(5),
            'scope_type' => 'class',
            'class_id' => $classGpi->id,
            'status' => 'pending_review',
        ]);
    }
}
