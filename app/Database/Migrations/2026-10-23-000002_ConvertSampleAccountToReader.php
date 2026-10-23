<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

/** Complete the sample account conversion after the display-name migration. */
class ConvertSampleAccountToReader extends Migration
{
    public function up()
    {
        $sample = $this->db->table('users')
            ->where('username', 'nhanvien')
            ->whereIn('full_name', ['Nhân viên bán sách', 'Độc giả'])
            ->get()->getRowArray();

        if ($sample === null) {
            return;
        }

        // Stop before changing credentials if another account owns either value.
        $conflict = $this->db->table('users')
            ->where('id !=', $sample['id'])
            ->groupStart()
            ->where('username', 'docgia')
            ->orWhere('email', 'docgia@nhasach.local')
            ->groupEnd()
            ->countAllResults();

        if ($conflict > 0) {
            throw new RuntimeException('Không thể đổi tài khoản mẫu: username docgia hoặc email docgia@nhasach.local đã được sử dụng.');
        }

        if (! $this->db->table('users')->where('id', $sample['id'])->update([
            'username' => 'docgia',
            'full_name' => 'Độc giả',
            'email' => 'docgia@nhasach.local',
            'password_hash' => password_hash('docgia123', PASSWORD_BCRYPT),
            'role' => 'user',
        ])) {
            throw new RuntimeException('Không thể cập nhật tài khoản mẫu thành Độc giả.');
        }
    }

    public function down()
    {
        // Credentials cannot be reconstructed. Keep the reader account on rollback,
        // including any password/profile changes made after this migration.
    }
}
