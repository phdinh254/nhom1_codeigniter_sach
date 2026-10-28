<?php

use App\Models\BookModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class LibraryWorkflowFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';
    protected $refresh = true;

    protected function setUp(): void
    {
        parent::setUp();

        // Feature tests use the isolated SQLite "tests" connection. No
        // application MySQL data is read or written by these requests.
        $seeder = \Config\Database::seeder('tests');
        $seeder->setSilent(true);
        $seeder->call('UserSeeder');
    }

    public function testGuestIsRedirectedToLoginForBookList(): void
    {
        $result = $this->get('books');

        $result->assertRedirectTo('/login');
    }

    public function testReaderCanViewBooksButCannotOpenAdminCreatePage(): void
    {
        $reader = $this->readerSession();

        $this->withSession($reader)->get('books')->assertOK();
        $this->withSession($reader)->get('books/create')->assertRedirectTo('/no-access');
    }

    public function testAdminCanCreateUpdateAndDeleteBookThroughProtectedRoutes(): void
    {
        $admin = $this->adminSession();

        $this->withSession($admin)->get('books/create')->assertOK();

        $this->withSession($admin)
            ->post('books/store', $this->bookPayload())
            ->assertRedirectTo('/books');

        $model = new BookModel();
        $created = $model->where('title', 'Sách feature test')->first();
        $this->assertNotNull($created);

        $this->withSession($admin)
            ->post('books/update/' . $created['id'], $this->bookPayload([
                'title'    => 'Sách feature test đã sửa',
                'quantity' => '8',
            ]))
            ->assertRedirectTo('/books');

        $updated = $model->find($created['id']);
        $this->assertSame('Sách feature test đã sửa', $updated['title']);
        $this->assertSame(8, (int) $updated['quantity']);

        $this->withSession($admin)
            ->post('books/delete/' . $created['id'], $this->csrfPayload())
            ->assertRedirectTo('/books');

        $this->assertNull($model->find($created['id']));
    }

    public function testAdminCannotPersistInvalidBookInput(): void
    {
        $result = $this->withSession($this->adminSession())->post('books/store', $this->bookPayload([
            'title'          => '   ',
            'published_year' => (string) (\App\Validation\LibraryRules::currentYear() + 1),
            'price'          => '-1',
            'quantity'       => '1.5',
        ]));

        $result->assertRedirect();
        $this->assertSame(0, (new BookModel())->countAllResults());
    }

    public function testValidLoginCreatesAuthenticatedAdminSession(): void
    {
        $result = $this->post('login', $this->csrfPayload([
            'username' => 'admin',
            'password' => 'admin123',
        ]));

        $result->assertRedirectTo('/books');
        $result->assertSessionHas('isLoggedIn', true);
        $result->assertSessionHas('username', 'admin');
        $result->assertSessionHas('role', 'admin');
    }

    private function adminSession(): array
    {
        return [
            'isLoggedIn' => true,
            'user_id'    => 1,
            'username'   => 'admin',
            'role'       => 'admin',
        ];
    }

    private function readerSession(): array
    {
        return [
            'isLoggedIn' => true,
            'user_id'    => 2,
            'username'   => 'nhanvien',
            'role'       => 'user',
        ];
    }

    private function bookPayload(array $overrides = []): array
    {
        return $this->csrfPayload(array_replace([
            'title'          => 'Sách feature test',
            'author'         => 'Tác giả kiểm thử',
            'publisher'      => 'NXB kiểm thử',
            'published_year' => (string) \App\Validation\LibraryRules::currentYear(),
            'price'          => '25000.00',
            'quantity'       => '4',
            'description'    => 'Dữ liệu chỉ tồn tại trong SQLite in-memory của PHPUnit.',
        ], $overrides));
    }

    private function csrfPayload(array $payload = []): array
    {
        return array_merge($payload, [csrf_token() => csrf_hash()]);
    }
}
