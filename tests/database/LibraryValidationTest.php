<?php

use App\Database\Migrations\RenameSampleUserToReader;
use App\Models\BookModel;
use App\Models\UserModel;
use App\Validation\LibraryRules;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/** @internal */
final class LibraryValidationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $refresh = true;

    private function book(): array
    {
        return ['title' => 'Sách hợp lệ', 'author' => 'Tác giả', 'price' => '10000.00', 'quantity' => 1];
    }

    private function user(): array
    {
        return ['username' => 'valid_reader', 'full_name' => 'Độc giả', 'email' => 'reader@example.com', 'password' => 'Reader@2026'];
    }

    public function testFuturePublicationYearIsRejectedOnInsertAndUpdate(): void
    {
        $currentYear = LibraryRules::currentYear();
        $model = new BookModel();
        $id = $model->insert($this->book() + ['published_year' => $currentYear]);
        $this->assertNotFalse($id);
        $this->assertFalse($model->insert($this->book() + ['published_year' => $currentYear + 1]));
        $this->assertArrayHasKey('published_year', $model->errors());
        $this->assertFalse($model->update($id, ['published_year' => $currentYear + 1]));
        $this->assertSame($currentYear, (int) $model->find($id)['published_year']);
    }

    public function testYearLimitFollowsVietnameseNewYearWithoutCodeChanges(): void
    {
        $rules = new LibraryRules();
        try {
            Time::setTestNow('2026-12-31 23:59:59', 'Asia/Ho_Chi_Minh');
            $this->assertSame(2026, LibraryRules::currentYear());
            $this->assertFalse($rules->publication_year('2027'));
            Time::setTestNow('2027-01-01 00:00:00', 'Asia/Ho_Chi_Minh');
            $this->assertSame(2027, LibraryRules::currentYear());
            $this->assertTrue($rules->publication_year('2027'));
        } finally {
            Time::setTestNow();
        }
    }

    public function testBookRejectsMalformedValuesAndWhitespaceOnlyNames(): void
    {
        $invalidRows = [
            ['title' => '   '], ['title' => "\u{00A0}"], ['title' => ['array']],
            ['author' => "\t\n"], ['author' => false],
            ['title' => str_repeat('a', 256)], ['author' => str_repeat('a', 151)],
            ['publisher' => str_repeat('a', 151)],
            ['published_year' => 999], ['published_year' => '2020.0'],
            ['published_year' => '2e3'], ['published_year' => false], ['published_year' => [2020]],
            ['published_year' => "2020\n"],
            ['price' => '-1'], ['price' => '100000000.00'], ['price' => '1.001'],
            ['price' => '1e3'], ['price' => "10\n"], ['price' => false], ['price' => ['10']],
            ['quantity' => -1], ['quantity' => '1.5'], ['quantity' => '4294967296'],
            ['quantity' => "1\n"], ['quantity' => false], ['quantity' => [1]],
            ['description' => str_repeat('a', 10001)], ['description' => "text\0hidden"],
        ];
        foreach ($invalidRows as $invalid) {
            $model = new BookModel();
            $this->assertFalse($model->insert(array_replace($this->book(), $invalid)), json_encode($invalid));
            $this->assertNotEmpty($model->errors());
        }
        $this->assertSame(0, (new BookModel())->countAllResults());
    }

    public function testOptionalYearNormalizesToNullAndNumericLimitsAreAccepted(): void
    {
        $model = new BookModel();
        $id = $model->insert(array_replace($this->book(), [
            'title' => '  Sách hợp lệ  ', 'published_year' => '',
            'publisher' => '', 'price' => '99999999.99', 'quantity' => '4294967295',
            'description' => "Dòng một\nDòng hai",
        ]));
        $this->assertNotFalse($id);
        $row = $model->find($id);
        $this->assertSame('Sách hợp lệ', $row['title']);
        $this->assertNull($row['published_year']);
        $this->assertNull($row['publisher']);
        $this->assertSame('4294967295', (string) $row['quantity']);
        $this->assertTrue($model->update($id, ['price' => '0', 'quantity' => 0]));
    }

    public function testRequiredBookFieldsCannotBeOmittedOnInsert(): void
    {
        foreach (['title', 'author', 'price', 'quantity'] as $field) {
            $row = $this->book();
            unset($row[$field]);
            $model = new BookModel();
            $this->assertFalse($model->insert($row));
            $this->assertArrayHasKey($field, $model->errors());
        }
    }

    public function testUserNamesRolesAndStatusesAreStrict(): void
    {
        foreach ([
            ['username' => 'bad user'], ['username' => 'bad,user'], ['username' => '../name'],
            ['full_name' => '   '], ['full_name' => ['name']],
            ['role' => 'owner'], ['role' => ''], ['is_active' => ''], ['is_active' => false],
            ['is_active' => 2],
        ] as $invalid) {
            $model = new UserModel();
            $this->assertFalse($model->insert(array_replace($this->user(), $invalid)));
            $this->assertNotEmpty($model->errors());
        }
    }

    public function testMultipleUsersCanOmitEmailWithoutViolatingUniqueIndex(): void
    {
        $model = new UserModel();
        $first = $model->insert(array_replace($this->user(), ['email' => '']));
        $second = $model->insert(array_replace($this->user(), ['username' => 'another_reader', 'email' => '']));
        $this->assertNotFalse($first);
        $this->assertNotFalse($second);
        $this->assertNull($model->find($first)['email']);
        $this->assertNull($model->find($second)['email']);
    }

    public function testReaderRenamePreservesIdentityPasswordAndCustomNames(): void
    {
        $model = new UserModel();
        $id = $model->insert(array_replace($this->user(), [
            'username' => 'nhanvien', 'full_name' => 'Nhân viên bán sách',
        ]));
        $this->assertNotFalse($id);
        $before = $model->find($id);
        $migration = new RenameSampleUserToReader(\Config\Database::forge('tests'));
        $migration->up();
        $after = $model->find($id);
        $this->assertSame('Độc giả', $after['full_name']);
        $this->assertSame($before['password_hash'], $after['password_hash']);
        $this->assertSame($before['username'], $after['username']);
        $this->assertSame($before['role'], $after['role']);
        $this->assertNotNull($model->verifyCredentials('nhanvien', 'Reader@2026'));
        $this->assertTrue($model->update($id, ['full_name' => 'Tên đã tùy chỉnh']));
        $migration->up();
        $this->assertSame('Tên đã tùy chỉnh', $model->find($id)['full_name']);
    }

    public function testSeederCreatesReaderDisplayNameAndIsRepeatable(): void
    {
        $seeder = \Config\Database::seeder('tests');
        $seeder->setSilent(true);
        $seeder->call('UserSeeder');
        $seeder->call('UserSeeder');
        $model = new UserModel();
        $reader = $model->findByUsername('docgia');
        $this->assertSame('Độc giả', $reader['full_name']);
        $this->assertSame('user', $reader['role']);
        $this->assertSame('docgia@nhasach.local', $reader['email']);
        $this->assertNotNull($model->verifyCredentials('docgia', 'docgia123'));
        $this->assertNull($model->findByUsername('nhanvien'));
        $this->assertSame(3, $model->countAllResults());
    }
}
