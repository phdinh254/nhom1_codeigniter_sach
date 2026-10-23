<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthFilter - Kiểm tra xác thực người dùng (Authentication)
 * Chặn người dùng chưa đăng nhập truy cập vào các route được bảo vệ.
 */
class AuthFilter implements FilterInterface
{
    /**
     * Kiểm tra trạng thái đăng nhập trước khi cho phép truy cập vào Controller.
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
            // Nhớ trang đang mở để đăng nhập xong quay lại đúng trang đó
            if ($request->getMethod() === 'GET') {
                $query = $request->getUri()->getQuery();
                $session->set('redirect_url', uri_string() . ($query !== '' ? '?' . $query : ''));
            }

            return redirect()->to('/login')->with('error', 'Vui lòng đăng nhập để tiếp tục!');
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

