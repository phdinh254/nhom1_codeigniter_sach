<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>403 - Từ chối truy cập</title>

    <style>
        body {
            height: 100%;
            background: #fafafa;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: #555;
            font-weight: 300;
            margin: 0;
        }
        h1 {
            font-weight: lighter;
            letter-spacing: normal;
            font-size: 3rem;
            margin-top: 0;
            margin-bottom: 0.5rem;
            color: #d9534f;
        }
        .wrap {
            max-width: 720px;
            margin: 5rem auto;
            padding: 2.5rem;
            background: #fff;
            text-align: center;
            border: 1px solid #efefef;
            border-top: 4px solid #d9534f;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .badge {
            display: inline-block;
            background: #fdf2f2;
            color: #c9302c;
            border: 1px solid #f5c6cb;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            margin-top: 1rem;
            font-weight: 500;
        }
        p {
            margin-top: 1.25rem;
            line-height: 1.6;
        }
        .actions {
            margin-top: 2rem;
        }
        a.btn {
            display: inline-block;
            padding: 0.6rem 1.25rem;
            background: #dd4814;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
        }
        a.btn:hover {
            background: #c13c0e;
        }
    </style>
</head>
<body>
<div class="wrap">
    <h1>403 - Forbidden</h1>
    <div class="badge">
        <?= esc(session()->getFlashdata('error') ?? ($message ?? 'Từ chối truy cập! Bạn không có quyền truy cập vào chức năng dành cho Admin.')) ?>
    </div>
    <p>
        Tài khoản hiện tại của bạn không có quyền <strong>Admin</strong> để truy cập vào đường dẫn này.<br>
        Yêu cầu đã bị chặn bởi bộ lọc phân quyền <code>AdminFilter</code>.
    </p>
    <div class="actions">
        <a class="btn" href="<?= site_url('/') ?>">Quay về Trang chủ</a>
    </div>
</div>
</body>
</html>

