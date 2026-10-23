<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class Auth extends BaseController
{
    protected $helpers = ['form', 'url'];

    /**
     * Hiển thị form đăng nhập.
     */
    public function login(): RedirectResponse|string
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/books');
        }

        return view('auth/login');
    }

    /**
     * Xử lý form đăng nhập.
     */
    public function attemptLogin(): RedirectResponse
    {
        $rules = [
            'username' => [
                'label' => 'Tên đăng nhập',
                'rules' => 'required|max_length[50]',
            ],
            'password' => [
                'label' => 'Mật khẩu',
                'rules' => 'required',
            ],
        ];

        $messages = [
            'username' => [
                'required'   => 'Vui lòng nhập tên đăng nhập.',
                'max_length' => 'Tên đăng nhập tối đa 50 ký tự.',
            ],
            'password' => [
                'required' => 'Vui lòng nhập mật khẩu.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');

        $userModel = new UserModel();
        $user      = $userModel->verifyCredentials($username, $password);

        if ($user === null) {
            return redirect()->back()->withInput()->with('error', 'Tên đăng nhập hoặc mật khẩu không đúng.');
        }

        if (! (int) $user['is_active']) {
            return redirect()->back()->withInput()->with('error', 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.');
        }

        $userModel->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        // Trang người dùng định vào trước khi bị yêu cầu đăng nhập (AuthFilter lưu), mặc định là danh sách sách
        $session     = session();
        $redirectUrl = $session->get('redirect_url') ?? 'books';
        $session->remove('redirect_url');

        // Đổi session ID sau khi đăng nhập để chống session fixation
        $session->regenerate(true);
        $session->set([
            'isLoggedIn' => true,
            'user_id'    => $user['id'],
            'username'   => $user['username'],
            'full_name'  => $user['full_name'],
            'role'       => $user['role'],
        ]);

        // site_url() luôn giữ chuyển hướng trong website, không cho đi sang domain khác
        return redirect()->to(site_url($redirectUrl))->with('success', 'Đăng nhập thành công. Xin chào ' . $user['full_name'] . '!');
    }

    /**
     * Đăng xuất: xóa thông tin đăng nhập khỏi Session.
     */
    public function logout(): RedirectResponse
    {
        $session = session();
        $session->remove(['isLoggedIn', 'user_id', 'username', 'full_name', 'role']);
        // Hủy session cũ, cấp session ID mới (vẫn giữ được flash message bên dưới)
        $session->regenerate(true);

        return redirect()->to('/login')->with('success', 'Bạn đã đăng xuất thành công.');
    }
}
