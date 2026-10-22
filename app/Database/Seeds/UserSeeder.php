<?php

namespace App\Database\Seeds;

use App\Models\UserModel;
use CodeIgniter\Database\Seeder;

/**
 * Tạo sẵn tài khoản để kiểm thử đăng nhập.
 * Mật khẩu được UserModel băm bằng password_hash() trước khi lưu.
 */
class UserSeeder extends Seeder
{
    public function run()
    {
        $userModel = new UserModel();

        $users = [
            [
                'username'  => 'admin',
                'password'  => 'admin123',
                'full_name' => 'Quản trị viên',
                'email'     => 'admin@nhasach.local',
                'role'      => 'admin',
                'is_active' => 1,
            ],
            [
                'username'  => 'nhanvien',
                'password'  => 'nhanvien123',
                'full_name' => 'Nhân viên bán sách',
                'email'     => 'nhanvien@nhasach.local',
                'role'      => 'user',
                'is_active' => 1,
            ],
            [
                'username'  => 'taikhoankhoa',
                'password'  => 'khoa123456',
                'full_name' => 'Tài khoản bị khóa',
                'email'     => null,
                'role'      => 'user',
                'is_active' => 0,
            ],
        ];

        foreach ($users as $user) {
            // Chạy seeder nhiều lần không bị trùng username
            if ($userModel->findByUsername($user['username']) === null) {
                $userModel->insert($user);
            }
        }
    }
}
