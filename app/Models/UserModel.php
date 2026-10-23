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
        'username' => 'required|min_length[3]|max_length[50]|alpha_numeric_punct|is_unique[users.username,id,{id}]',
        'full_name' => 'required|max_length[150]',
        'email' => 'permit_empty|valid_email|max_length[191]|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[8]|regex_match[/\A[^\x00]{8,72}\z/]',
        'role' => 'permit_empty|in_list[user,admin]',
        'is_active' => 'permit_empty|in_list[0,1]',
        'last_login_at' => 'permit_empty|valid_date[Y-m-d H:i:s]',
    ];
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

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
