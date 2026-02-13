<?php
$pageTitle = '403 - Akses Ditolak';
require __DIR__ . '/../layouts/header.php';
if (isLoggedIn()):
    require __DIR__ . '/../layouts/navbar.php';
    require __DIR__ . '/../layouts/sidebar.php';
endif;
?>

<?php if (isLoggedIn()): ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>403 - Akses Ditolak</h1></div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="error-page">
                <h2 class="headline text-danger">403</h2>
                <div class="error-content">
                    <h3><i class="fas fa-ban text-danger"></i> Akses Ditolak!</h3>
                    <p>Anda tidak memiliki izin untuk mengakses halaman ini. Hubungi administrator untuk mendapatkan akses.</p>
                    <a href="<?= BASE_URL ?>dashboard" class="btn btn-danger">
                        <i class="fas fa-home mr-1"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
<?php else: ?>
<div class="login-page" style="min-height:100vh;">
    <div class="error-page" style="padding-top:20vh;">
        <h2 class="headline text-danger">403</h2>
        <div class="error-content">
            <h3><i class="fas fa-ban text-danger"></i> Akses Ditolak!</h3>
            <p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>
            <a href="<?= BASE_URL ?>auth/login" class="btn btn-primary">
                <i class="fas fa-sign-in-alt mr-1"></i> Login
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
