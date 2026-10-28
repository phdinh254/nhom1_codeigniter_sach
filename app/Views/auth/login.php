<?php $errors = session('errors') ?? []; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập - Nhà sách</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3a5f 0%, #2f6f8f 100%);
        }
        .login-card {
            max-width: 420px;
            border: 0;
            border-radius: 1rem;
        }
        .login-icon {
            width: 64px;
            height: 64px;
            font-size: 2rem;
        }
    </style>
</head>
<body class="d-flex align-items-center py-4">
<main class="container">
    <div class="card login-card shadow-lg mx-auto">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="login-icon rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3">
                    <i class="bi bi-book-half"></i>
                </div>
                <h1 class="h4 fw-bold mb-1">Đăng nhập hệ thống</h1>
                <p class="text-muted mb-0">Quản lý nhà sách</p>
            </div>

            <?php if (session('error')): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= esc(session('error')) ?></div>
                </div>
            <?php endif ?>

            <?php if (session('success')): ?>
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div><?= esc(session('success')) ?></div>
                </div>
            <?php endif ?>

            <form action="<?= site_url('login') ?>" method="post" novalidate>
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="username" class="form-label">Tên đăng nhập</label>
                    <div class="input-group has-validation">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" id="username" name="username"
                               class="form-control<?= isset($errors['username']) ? ' is-invalid' : '' ?>"
                               value="<?= esc(old('username')) ?>" placeholder="Nhập tên đăng nhập" autofocus>
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['username']) ?></div>
                        <?php endif ?>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <div class="input-group has-validation">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" id="password" name="password"
                               class="form-control<?= isset($errors['password']) ? ' is-invalid' : '' ?>"
                               placeholder="Nhập mật khẩu">
                        <button type="button" class="btn btn-outline-secondary" id="togglePassword" title="Hiện/ẩn mật khẩu">
                            <i class="bi bi-eye"></i>
                        </button>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['password']) ?></div>
                        <?php endif ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
                </button>
            </form>

            <p class="text-center mt-3 mb-0">
                Chưa có tài khoản? <a href="<?= site_url('register') ?>">Đăng ký</a>
            </p>

        </div>
    </div>
</main>

<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon = this.querySelector('i');
        const isHidden = input.type === 'password';

        input.type = isHidden ? 'text' : 'password';
        icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
</script>
</body>
</html>
