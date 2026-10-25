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
     * Hiển thị form đăng ký.
     */
    public function register(): RedirectResponse|string
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/books');
        }

        return view('auth/register');
    }

    /**
     * Xử lý form đăng ký: tạo tài khoản mới với vai trò user.
     */
    public function attemptRegister(): RedirectResponse
    {
        $data = [
            'full_name'        => trim((string) $this->request->getPost('full_name')),
            'username'         => trim((string) $this->request->getPost('username')),
            'email'            => trim((string) $this->request->getPost('email')),
            'password'         => (string) $this->request->getPost('password'),
            'password_confirm' => (string) $this->request->getPost('password_confirm'),
        ];

        // Cùng điều kiện với UserModel, thêm nhập lại mật khẩu và thông báo tiếng Việt
        $rules = [
            'full_name'        => 'required|library_text[150]',
            'username'         => 'required|regex_match[/\A[A-Za-z0-9][A-Za-z0-9_.-]{2,49}\z/]|is_unique[users.username]',
            'email'            => 'permit_empty|valid_email|max_length[191]|is_unique[users.email]',
            'password'         => 'required|min_length[8]|max_length[72]',
            'password_confirm' => 'required|matches[password]',
        ];

        $messages = [
            'full_name' => [
                'required'     => 'Vui lòng nhập họ tên.',
                'library_text' => 'Họ tên không hợp lệ hoặc dài quá 150 ký tự.',
            ],
            'username' => [
                'required'    => 'Vui lòng nhập tên đăng nhập.',
                'regex_match' => 'Tên đăng nhập dài 3–50 ký tự, bắt đầu bằng chữ/số, chỉ gồm chữ, số, dấu gạch dưới, dấu chấm hoặc gạch ngang.',
                'is_unique'   => 'Tên đăng nhập đã có người sử dụng.',
            ],
            'email' => [
                'valid_email' => 'Email không đúng định dạng.',
                'max_length'  => 'Email tối đa 191 ký tự.',
                'is_unique'   => 'Email đã được dùng cho tài khoản khác.',
            ],
            'password' => [
                'required'   => 'Vui lòng nhập mật khẩu.',
                'min_length' => 'Mật khẩu phải có ít nhất 8 ký tự.',
                'max_length' => 'Mật khẩu tối đa 72 ký tự.',
            ],
            'password_confirm' => [
                'required' => 'Vui lòng nhập lại mật khẩu.',
                'matches'  => 'Mật khẩu nhập lại không khớp.',
            ],
        ];

        if (! $this->validateData($data, $rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Chỉ lấy các trường cho phép; vai trò và trạng thái do hệ thống đặt, không lấy từ form
        $userModel = new UserModel();
        $saved     = $userModel->insert([
            'full_name' => $data['full_name'],
            'username'  => $data['username'],
            'email'     => $data['email'],
            'password'  => $data['password'],
            'role'      => 'user',
            'is_active' => 1,
        ]);

        if ($saved === false) {
            return redirect()->back()->withInput()->with('errors', $userModel->errors());
        }

        return redirect()->to('/login')->with('success', 'Đăng ký thành công. Vui lòng đăng nhập bằng tài khoản ' . $data['username'] . '.');
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
