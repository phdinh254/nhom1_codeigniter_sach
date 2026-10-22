<?php

namespace App\Database\Seeds;

use App\Models\UserModel;
use CodeIgniter\Database\Seeder;
use RuntimeException;

/** Optional local demo data. Never run this seeder on a public installation. */
class DemoSeeder extends Seeder
{
    public function run()
    {
        $this->call(BookSeeder::class);
        $users = new UserModel($this->db);
        if ($users->where('username', 'huy_demo')->first() !== null) {
            return;
        }

        if ($users->insert([
            'username' => 'huy_demo',
            'full_name' => 'Lê Đăng Huy',
            'email' => 'huy.demo@example.com',
            'password' => 'DemoHuy@2026',
        ]) === false) {
            throw new RuntimeException(implode('; ', $users->errors()));
        }
    }
}
