<?php

use App\Models\BookModel;
use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/** @internal */
final class LibraryModelsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $refresh = true;

    private function book(): array
    {
        return ['title' => 'Sách thử nghiệm', 'author' => 'Lê Đăng Huy', 'price' => '95000.50', 'quantity' => 3];
    }

    private function user(): array
    {
        return ['username' => 'huy_test', 'full_name' => 'Lê Đăng Huy', 'email' => 'huy@example.com', 'password' => 'TestHuy@2026'];
    }

    public function testBookCrudPreservesVietnameseAndTimestamps(): void
    {
        $model = new BookModel();
        $id = $model->insert($this->book());
        $this->assertNotFalse($id);
        $row = $model->find($id);
        $this->assertSame('Sách thử nghiệm', $row['title']);
        $this->assertNotEmpty($row['created_at']);
        $this->assertTrue($model->update($id, ['quantity' => 2]));
        $this->assertSame(2, (int) $model->find($id)['quantity']);
        $this->assertTrue($model->delete($id));
        $this->assertNull($model->find($id));
    }

    public function testBookRejectsNegativeAndOverflowingValues(): void
    {
        foreach ([['quantity' => -1], ['quantity' => '1.5'], ['quantity' => '4294967296'], ['price' => '-1'], ['price' => '1.001'], ['price' => '100000000.00']] as $invalid) {
            $model = new BookModel();
            $this->assertFalse($model->insert(array_replace($this->book(), $invalid)));
            $this->assertNotEmpty($model->errors());
        }
        $this->assertSame(0, (new BookModel())->countAllResults());
    }

    public function testPasswordHashingAndProfileUpdate(): void
    {
        $model = new UserModel();
        $id = $model->insert($this->user() + ['role' => 'admin']);
        $this->assertNotFalse($id);
        $row = $model->find($id);
        $this->assertTrue(password_verify('TestHuy@2026', $row['password']));
        $this->assertSame('user', $row['role']);
        $hash = $row['password'];
        $this->assertTrue($model->update($id, ['id' => $id, 'email' => 'huy@example.com', 'full_name' => 'Huy cập nhật']));
        $this->assertSame($hash, $model->find($id)['password']);
        $this->assertTrue($model->update($id, ['password' => 'NewHuy@2026']));
        $this->assertTrue(password_verify('NewHuy@2026', $model->find($id)['password']));
        $this->assertTrue($model->delete($id));
        $this->assertNull($model->find($id));
    }

    public function testDuplicateUsernameAndEmailAreRejected(): void
    {
        $model = new UserModel();
        $this->assertNotFalse($model->insert($this->user()));
        $this->assertFalse($model->insert(array_replace($this->user(), ['email' => 'another@example.com'])));
        $this->assertArrayHasKey('username', $model->errors());
        $this->assertFalse($model->insert(array_replace($this->user(), ['username' => 'other_user'])));
        $this->assertArrayHasKey('email', $model->errors());
    }

    public function testPasswordMustFitBcryptByteLimitAndContainNoNullByte(): void
    {
        foreach (['short', str_repeat('a', 73), str_repeat('ệ', 25), "validpass\0hidden"] as $password) {
            $model = new UserModel();
            $this->assertFalse($model->insert(array_replace($this->user(), ['password' => $password])));
            $this->assertArrayHasKey('password', $model->errors());
        }
    }

    public function testDatabaseUniqueIndexProtectsDirectWrites(): void
    {
        $model = new UserModel();
        $id = $model->insert($this->user());
        $this->assertNotFalse($id);
        $row = $model->find($id);
        unset($row['id']);
        $this->expectException(DatabaseException::class);
        $this->db->table('users')->insert($row);
    }
}
