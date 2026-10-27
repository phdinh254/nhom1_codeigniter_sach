<?php
$errors = session('errors') ?? [];

// Ô nhập có lỗi thì tô đỏ và hiện thông báo ngay bên dưới
$invalid = static fn (string $field): string => isset($errors[$field]) ? ' is-invalid' : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký - Nhà sách</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3a5f 0%, #2f6f8f 100%);
        }
        .register-card {
            max-width: 480px;
            border: 0;
            border-radius: 1rem;
        }
        .register-icon {
            width: 64px;
            height: 64px;
            font-size: 2rem;
        }
    </style>
</head>
<body class="d-flex align-items-center py-4">
<main class="container">
    <div class="card register-card shadow-lg mx-auto">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="register-icon rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3">
                    <i class="bi bi-person-plus"></i>
                </div>
                <h1 class="h4 fw-bold mb-1">Đăng ký tài khoản</h1>
                <p class="text-muted mb-0">Quản lý nhà sách</p>
            </div>

            <?php if (session('error')): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= esc(session('error')) ?></div>
                </div>
            <?php endif ?>

            <form action="<?= site_url('register') ?>" method="post" novalidate>
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="full_name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                    <input type="text" id="full_name" name="full_name"
                           class="form-control<?= $invalid('full_name') ?>"
                           value="<?= esc(old('full_name')) ?>" placeholder="Nhập họ tên" autofocus>
                    <?php if (isset($errors['full_name'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['full_name']) ?></div>
                    <?php endif ?>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" id="username" name="username"
                               class="form-control<?= $invalid('username') ?>"
                               value="<?= esc(old('username')) ?>" placeholder="3–50 ký tự, không dấu, không khoảng trắng">
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['username']) ?></div>
                        <?php endif ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group has-validation">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" id="email" name="email"
                               class="form-control<?= $invalid('email') ?>"
                               value="<?= esc(old('email')) ?>" placeholder="Không bắt buộc">
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['email']) ?></div>
                        <?php endif ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" id="password" name="password"
                               class="form-control<?= $invalid('password') ?>" placeholder="Ít nhất 8 ký tự">
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['password']) ?></div>
                        <?php endif ?>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password_confirm" class="form-label">Nhập lại mật khẩu <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" id="password_confirm" name="password_confirm"
                               class="form-control<?= $invalid('password_confirm') ?>" placeholder="Nhập lại mật khẩu">
                        <?php if (isset($errors['password_confirm'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['password_confirm']) ?></div>
                        <?php endif ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-person-plus me-1"></i> Đăng ký
                </button>
            </form>

            <p class="text-center mt-3 mb-0">
                Đã có tài khoản? <a href="<?= site_url('login') ?>">Đăng nhập</a>
            </p>
        </div>
    </div>
</main>
</body>
</html>
