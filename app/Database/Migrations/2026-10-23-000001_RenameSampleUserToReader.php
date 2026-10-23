<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Change only the original sample name, preserving identity and custom names. */
class RenameSampleUserToReader extends Migration
{
    public function up()
    {
        $this->db->table('users')
            ->where('username', 'nhanvien')
            ->where('full_name', 'Nhân viên bán sách')
            ->update(['full_name' => 'Độc giả']);
    }

    public function down()
    {
        $this->db->table('users')
            ->where('username', 'nhanvien')
            ->where('full_name', 'Độc giả')
            ->update(['full_name' => 'Nhân viên bán sách']);
    }
}
