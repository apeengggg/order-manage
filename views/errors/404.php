<?php
$pageTitle = '404 - Halaman Tidak Ditemukan';
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
                <div class="col-sm-6"><h1>404 - Tidak Ditemukan</h1></div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="error-page">
                <h2 class="headline text-warning">404</h2>
                <div class="error-content">
                    <h3><i class="fas fa-exclamation-triangle text-warning"></i> Halaman tidak ditemukan.</h3>
                    <p>Halaman yang Anda cari tidak ditemukan. Silakan kembali ke dashboard.</p>
                    <a href="<?= BASE_URL ?>dashboard" class="btn btn-warning">
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
        <h2 class="headline text-warning">404</h2>
        <div class="error-content">
            <h3><i class="fas fa-exclamation-triangle text-warning"></i> Halaman tidak ditemukan.</h3>
            <p>Halaman yang Anda cari tidak tersedia.</p>
            <a href="<?= BASE_URL ?>auth/login" class="btn btn-primary">
                <i class="fas fa-sign-in-alt mr-1"></i> Login
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
