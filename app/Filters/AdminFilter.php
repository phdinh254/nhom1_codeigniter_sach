<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AdminFilter - Kiểm tra phân quyền Quản trị viên (Authorization)
 * Chặn người dùng thường (User) truy cập trái phép vào các URL dành cho Admin.
 */
class AdminFilter implements FilterInterface
{
    /**
     * Kiểm tra quyền Admin trước khi cho phép truy cập vào Controller.
     *
     * @param list<string>|null $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        $isLoggedIn = $session->get('isLoggedIn')
            || $session->get('logged_in')
            || $session->has('user_id')
            || $session->has('user');

        if (! $isLoggedIn) {
            return redirect()->to('/login')->with('error', 'Vui lòng đăng nhập để tiếp tục!');
        }

        $userSession = $session->get('user');
        $role        = $session->get('role') ?? (is_array($userSession) ? ($userSession['role'] ?? null) : null);

        if (strtolower((string) $role) !== 'admin') {
            return redirect()->to('/no-access')->with('error', 'Từ chối truy cập! Bạn không có quyền truy cập vào chức năng dành cho Admin.');
        }
    }

    /**
     * Xử lý sau khi Controller thực thi xong (không sử dụng).
     *
     * @param list<string>|null $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Không thực hiện thao tác sau request
    }
}

