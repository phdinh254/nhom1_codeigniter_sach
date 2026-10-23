<?php

use App\Database\Migrations\ConvertSampleAccountToReader;
use App\Database\Migrations\RenameSampleUserToReader;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;

/** @internal */
final class SampleReaderMigrationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $refresh = true;

    private function legacyUser(array $overrides = []): array
    {
        $model = new UserModel();
        $id = $model->insert(array_replace([
            'username' => 'nhanvien',
            'full_name' => 'Nhân viên bán sách',
            'email' => 'nhanvien@nhasach.local',
            'password' => 'nhanvien123',
            'role' => 'user',
        ], $overrides));
        $this->assertNotFalse($id);

        return $model->find($id);
    }

    private function migration(): ConvertSampleAccountToReader
    {
        return new ConvertSampleAccountToReader(\Config\Database::forge('tests'));
    }

    public static function legacyNames(): array
    {
        return [['Nhân viên bán sách'], ['Độc giả']];
    }

    #[DataProvider('legacyNames')]
    public function testExistingSampleBecomesReaderWithoutRecreatingAccount(string $name): void
    {
        $before = $this->legacyUser([
            'full_name' => $name, 'role' => 'admin', 'is_active' => 0,
            'last_login_at' => '2026-10-22 12:00:00',
        ]);
        // Exercise an upgrade where the published display-name migration ran first.
        (new RenameSampleUserToReader(\Config\Database::forge('tests')))->up();
        $this->migration()->up();

        $model = new UserModel();
        $after = $model->findByUsername('docgia');
        $this->assertSame($before['id'], $after['id']);
        $this->assertSame('Độc giả', $after['full_name']);
        $this->assertSame('docgia@nhasach.local', $after['email']);
        $this->assertSame('user', $after['role']);
        $this->assertSame($before['is_active'], $after['is_active']);
        $this->assertSame($before['created_at'], $after['created_at']);
        $this->assertSame($before['last_login_at'], $after['last_login_at']);
        $this->assertNotSame('docgia123', $after['password_hash']);
        $this->assertTrue(password_verify('docgia123', $after['password_hash']));
        $this->assertFalse(password_verify('nhanvien123', $after['password_hash']));
        $this->assertNotNull($model->verifyCredentials('docgia', 'docgia123'));
        $this->assertNull($model->verifyCredentials('nhanvien', 'nhanvien123'));
        $this->assertSame(1, $model->countAllResults());
    }

    public function testRerunRollbackAndSeederPreserveUpdatedReaderCredentials(): void
    {
        $before = $this->legacyUser();
        $migration = $this->migration();
        $migration->up();
        $model = new UserModel();
        $this->assertTrue($model->update($before['id'], [
            'password' => 'ChangedReader@2026', 'full_name' => 'Độc giả đã cập nhật',
        ]));
        $updated = $model->find($before['id']);
        $migration->up();
        $migration->down();
        $seeder = \Config\Database::seeder('tests');
        $seeder->setSilent(true);
        $seeder->call('UserSeeder');
        $seeder->call('UserSeeder');

        $this->assertSame($updated, $model->find($before['id']));
        $this->assertNotNull($model->verifyCredentials('docgia', 'ChangedReader@2026'));
        $this->assertNull($model->verifyCredentials('docgia', 'docgia123'));
        $this->assertSame(3, $model->countAllResults());
    }

    public function testCustomizedLegacyAccountIsUnchanged(): void
    {
        $before = $this->legacyUser(['full_name' => 'Tên đã tùy chỉnh']);
        $this->migration()->up();
        $this->assertSame($before, (new UserModel())->find($before['id']));
    }

    public static function conflicts(): array
    {
        return [
            [['username' => 'docgia', 'email' => 'other@example.com']],
            [['username' => 'other_reader', 'email' => 'docgia@nhasach.local']],
        ];
    }

    #[DataProvider('conflicts')]
    public function testConflictingAccountStopsUpgradeWithoutChangingUsers(array $conflict): void
    {
        $before = $this->legacyUser();
        $model = new UserModel();
        $otherId = $model->insert($conflict + [
            'full_name' => 'Tài khoản khác', 'password' => 'OtherReader@2026',
        ]);
        $this->assertNotFalse($otherId);
        $other = $model->find($otherId);

        try {
            $this->migration()->up();
            $this->fail('Expected a username/email conflict to stop the migration.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('đã được sử dụng', $exception->getMessage());
        }

        $this->assertSame($before, $model->find($before['id']));
        $this->assertSame($other, $model->find($otherId));
    }
}
