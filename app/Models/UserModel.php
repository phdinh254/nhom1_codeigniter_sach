<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    // 'password' là trường tạm (mật khẩu gốc), được hashPassword() đổi thành 'password_hash'
    protected $allowedFields = [
        'username',
        'password',
        'password_hash',
        'full_name',
        'email',
        'role',
        'is_active',
        'last_login_at',
    ];

    // Băm mật khẩu tự động khi thêm/sửa tài khoản có trường 'password'
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data): array
    {
        if (! isset($data['data']['password'])) {
            return $data;
        }

        $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        unset($data['data']['password']);

        return $data;
    }

    public function findByUsername(string $username): ?array
    {
        return $this->where('username', $username)->first();
    }

    /**
     * Kiểm tra username/password.
     * Trả về thông tin user nếu đúng, null nếu sai username hoặc mật khẩu.
     */
    public function verifyCredentials(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);

        if ($user === null || ! password_verify($password, $user['password_hash'])) {
            return null;
        }

        // Cập nhật lại hash nếu PHP đổi thuật toán mặc định
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $this->update($user['id'], ['password' => $password]);
        }

        return $user;
    }
}
