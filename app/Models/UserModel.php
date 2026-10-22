<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    // role is set by the database, never by registration form input.
    protected $allowedFields = ['username', 'full_name', 'email', 'password'];
    protected $useTimestamps = true;
    protected $validationRules = [
        'id' => 'permit_empty|is_natural_no_zero',
        'username' => 'required|min_length[3]|max_length[50]|alpha_numeric_punct|is_unique[users.username,id,{id}]',
        'full_name' => 'required|max_length[150]',
        'email' => 'required|valid_email|max_length[191]|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[8]|regex_match[/\A[^\x00]{8,72}\z/]',
    ];
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /** Call insert/update with a plain password; only the hash is persisted. */
    protected function hashPassword(array $data): array
    {
        if (array_key_exists('password', $data['data'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
        }

        return $data;
    }
}
