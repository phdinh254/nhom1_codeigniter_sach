<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Dashboard extends BaseController
{
    /**
     * Trang sau khi đăng nhập. Chưa đăng nhập thì quay về form login.
     */
    public function index(): RedirectResponse|string
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        return view('dashboard', [
            'title'    => 'Tài khoản',
            'fullName' => session()->get('full_name'),
            'username' => session()->get('username'),
            'role'     => session()->get('role'),
        ]);
    }
}
