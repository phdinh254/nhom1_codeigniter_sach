<?php

use App\Filters\AdminFilter;
use App\Filters\AuthFilter;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Filters;
use Config\Services;

/**
 * Kiểm thử phân quyền AuthFilter, AdminFilter và truy cập trái quyền
 *
 * @internal
 */
final class AuthorizationFilterTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        session()->destroy();
    }

    public function testFilterAliasesAreRegistered(): void
    {
        $filtersConfig = new Filters();

        $this->assertArrayHasKey('auth', $filtersConfig->aliases);
        $this->assertArrayHasKey('admin', $filtersConfig->aliases);
        $this->assertSame(AuthFilter::class, $filtersConfig->aliases['auth']);
        $this->assertSame(AdminFilter::class, $filtersConfig->aliases['admin']);
    }

    public function testAuthFilterRedirectsGuestToLogin(): void
    {
        $filter   = new AuthFilter();
        $request  = Services::request();
        $response = $filter->before($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));
    }

    public function testAuthFilterAllowsLoggedInUser(): void
    {
        session()->set([
            'isLoggedIn' => true,
            'role'       => 'user',
        ]);

        $filter   = new AuthFilter();
        $request  = Services::request();
        $response = $filter->before($request);

        $this->assertNull($response);
    }

    public function testAdminFilterBlocksNormalUserFromAdminRoutes(): void
    {
        session()->set([
            'isLoggedIn' => true,
            'role'       => 'user',
        ]);

        $filter   = new AdminFilter();
        $request  = Services::request();
        $response = $filter->before($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/no-access', $response->getHeaderLine('Location'));
    }

    public function testAdminFilterAllowsAdminRole(): void
    {
        session()->set([
            'isLoggedIn' => true,
            'role'       => 'admin',
        ]);

        $filter   = new AdminFilter();
        $request  = Services::request();
        $response = $filter->before($request);

        $this->assertNull($response);
    }
}

