<?php
$_loginAppName = appSetting('app_name', APP_NAME);
$_loginBgColor = appSetting('login_bg_color', '#667eea');
$_loginPrimaryColor = appSetting('primary_color', '#007bff');

// Check for login background image
$_loginBgUrl = null;
$_loginBgFileId = appSetting('login_bg_file_id');
if ($_loginBgFileId) {
    $fs = new \App\Services\FileService();
    $f = $fs->getFile((int)$_loginBgFileId);
    if ($f) $_loginBgUrl = $fs->getFileUrl($f);
}

// Check for logo
$_loginLogoUrl = null;
$_loginLogoFileId = appSetting('logo_file_id');
if ($_loginLogoFileId) {
    $fs = $fs ?? new \App\Services\FileService();
    $f = $fs->getFile((int)$_loginLogoFileId);
    if ($f) $_loginLogoUrl = $fs->getThumbnailUrl($f) ?: $fs->getFileUrl($f);
}

$_loginBgStyle = $_loginBgUrl
    ? "background: url('" . e($_loginBgUrl) . "') center/cover no-repeat fixed;"
    : "background: linear-gradient(135deg, " . e($_loginBgColor) . " 0%, #764ba2 100%);";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | <?= e($_loginAppName) ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        .btn-primary { background-color: <?= e($_loginPrimaryColor) ?>; border-color: <?= e($_loginPrimaryColor) ?>; }
        .btn-primary:hover { filter: brightness(0.9); background-color: <?= e($_loginPrimaryColor) ?>; border-color: <?= e($_loginPrimaryColor) ?>; }
        .card-primary.card-outline { border-top-color: <?= e($_loginPrimaryColor) ?>; }
        @media (max-width: 575.98px) {
            .login-box { width: 95%; margin: 1rem auto; }
        }
    </style>
</head>
<body class="hold-transition login-page" style="<?= $_loginBgStyle ?>">
<div class="login-box">
    <div class="card card-outline card-primary elevation-3">
        <div class="card-header text-center">
            <?php if ($_loginLogoUrl): ?>
                <img src="<?= e($_loginLogoUrl) ?>" alt="Logo" style="max-height:60px;" class="mb-2"><br>
            <?php endif; ?>
            <a href="#" class="h1"><?= e($_loginAppName) ?></a>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Silakan login untuk memulai</p>

            <?php if ($err = flash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-exclamation-triangle mr-1"></i> <?= e($err) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>auth/login">
                <div class="input-group mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username"
                           value="<?= e(old('username')) ?>" required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-user"></span></div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-lock"></span></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                        </button>
                    </div>
                </div>
            </form>

            <div class="mt-3 text-center">
                <small class="text-muted">
                    Default: <code>admin</code> / <code>cs1</code> / <code>cs2</code> &mdash; Password: <code>admin123</code>
                </small>
            </div>
        </div>
        <div class="card-footer text-center py-2">
            <small class="text-muted">Licensed by <strong>Mohamad Irfan Manaf</strong></small>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
