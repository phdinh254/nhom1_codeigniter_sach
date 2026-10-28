<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    // password is virtual input; callbacks persist only password_hash.
    // Controllers must whitelist request fields before allowing role/status edits.
    protected $allowedFields = ['username', 'full_name', 'email', 'password', 'role', 'is_active', 'last_login_at'];
    protected $useTimestamps = true;
    protected $validationRules = [
        'id' => 'permit_empty|is_natural_no_zero',
        'username' => 'required|regex_match[/\A[A-Za-z0-9][A-Za-z0-9_.-]{2,49}\z/]|is_unique[users.username,id,{id}]',
        'full_name' => 'library_text[150]',
        'email' => 'permit_empty|valid_email|max_length[191]|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[8]|regex_match[/\A[^\x00]{8,72}\z/]',
        'role' => 'if_exist|in_list[user,admin]',
        'is_active' => 'if_exist|library_status',
        'last_login_at' => 'permit_empty|valid_date[Y-m-d H:i:s]',
    ];
    protected $validationMessages = [
        'username' => ['regex_match' => 'Tên đăng nhập dài 3–50 ký tự, bắt đầu bằng chữ/số và chỉ chứa chữ, số, dấu gạch dưới, dấu chấm hoặc gạch ngang.'],
        'full_name' => ['library_text' => 'Họ tên không được trống, chỉ có khoảng trắng, chứa ký tự điều khiển hoặc dài quá 150 ký tự.'],
        'role' => ['in_list' => 'Vai trò chỉ nhận user hoặc admin.'],
    ];
    protected $beforeInsert = ['normalizeUserData', 'hashPassword'];
    protected $beforeUpdate = ['normalizeUserData', 'hashPassword'];

    protected function normalizeUserData(array $data): array
    {
        if (isset($data['data']['full_name']) && is_string($data['data']['full_name'])) {
            $data['data']['full_name'] = trim($data['data']['full_name']);
        }
        if (array_key_exists('email', $data['data']) && $data['data']['email'] === '') {
            $data['data']['email'] = null;
        }

        return $data;
    }

    /** Call insert/update with a plain password; only the hash is persisted. */
    protected function hashPassword(array $data): array
    {
        if (array_key_exists('password', $data['data'])) {
            $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
            unset($data['data']['password']);
        }

        return $data;
    }

    public function findByUsername(string $username): ?array
    {
        return $this->where('username', $username)->first();
    }

    /** Account status is checked by Auth::attemptLogin after verification. */
    public function verifyCredentials(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);
        if ($user === null || ! password_verify($password, $user['password_hash'])) {
            return null;
        }
        // A failed rehash (e.g. legacy password shorter than the current rules)
        // must not block a login whose password was already verified.
        if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT)
            && $this->update($user['id'], ['password' => $password])) {
            $user = $this->find($user['id']);
        }

        return $user;
    }
}
