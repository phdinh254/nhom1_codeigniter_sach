<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Upgrade either branch's users schema without recreating accounts. */
class AlignUsersForAuthentication extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('password', 'users') && ! $this->db->fieldExists('password_hash', 'users')) {
            $this->forge->modifyColumn('users', [
                'password' => ['name' => 'password_hash', 'type' => 'VARCHAR', 'constraint' => 255],
            ]);
        }
        if (! $this->db->fieldExists('is_active', 'users')) {
            $this->forge->addColumn('users', [
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            ]);
        }
        if (! $this->db->fieldExists('last_login_at', 'users')) {
            $this->forge->addColumn('users', [
                'last_login_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
        }
        $this->forge->modifyColumn('users', [
            'full_name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'email' => ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true],
        ]);

        foreach ($this->db->getIndexData('users') as $index) {
            if ($index->fields === ['email'] && ($index->type === 'UNIQUE' || ! empty($index->unique))) {
                return;
            }
        }
        $this->forge->addUniqueKey('email', 'users_email_unique');
        $this->forge->processIndexes('users');
    }

    public function down()
    {
        // Keep the compatible columns and hashes when rolling back this upgrade.
        // Earlier create-table migrations can still remove users on a full rollback.
    }
}
