<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Pemilik Kaya Kayu',
                'username' => 'owner',
                'email' => 'owner@kayakayu.test',
                'password' => 'password',
                'role' => 'owner',
                'phone' => '081200000001',
                'is_active' => true,
            ],
            [
                'name' => 'Admin Sales',
                'username' => 'admin',
                'email' => 'admin@kayakayu.test',
                'password' => 'password',
                'role' => 'admin',
                'phone' => '081200000002',
                'is_active' => true,
            ],
            [
                'name' => 'Pegawai Produksi',
                'username' => 'pegawai',
                'email' => 'pegawai@kayakayu.test',
                'password' => 'password',
                'role' => 'employee',
                'phone' => '081200000003',
                'is_active' => true,
            ],
            [
                'name' => 'Pegawai Finishing',
                'username' => 'finishing',
                'email' => 'finishing@kayakayu.test',
                'password' => 'password',
                'role' => 'employee',
                'phone' => '081200000004',
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['username' => $user['username']], $user);
        }
    }
}
