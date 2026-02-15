<?php
$pageTitle = 'Pengaturan';
$pageScripts = ['settings.js'];
require ROOT_PATH . '/views/layouts/header.php';
require ROOT_PATH . '/views/layouts/navbar.php';
require ROOT_PATH . '/views/layouts/sidebar.php';

$appName = $settings['app_name'] ?? 'Order Management System';
$primaryColor = $settings['primary_color'] ?? '#007bff';
$loginBgColor = $settings['login_bg_color'] ?? '#667eea';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-cog mr-2"></i>Pengaturan</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form method="POST" action="<?= BASE_URL ?>settings/update" id="settingsForm">
                <div class="row">
                    <!-- App Name & Dark Mode -->
                    <div class="col-lg-6">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-edit mr-1"></i> Umum</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="app_name">Nama Aplikasi</label>
                                    <input type="text" class="form-control" id="app_name" name="app_name"
                                           value="<?= e($appName) ?>" placeholder="Order Management System">
                                    <small class="text-muted">Ditampilkan di sidebar, title, dan footer</small>
                                </div>
                                <div class="form-group mb-0">
                                    <small class="text-muted"><i class="fas fa-info-circle mr-1"></i> Dark mode dapat diaktifkan masing-masing user melalui tombol <i class="fas fa-moon"></i> di navbar.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Colors -->
                    <div class="col-lg-6">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-palette mr-1"></i> Warna</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="primary_color">Warna Utama (Primary)</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="primary_color"
                                               name="primary_color" value="<?= e($primaryColor) ?>" style="max-width:60px; padding:3px;">
                                        <input type="text" class="form-control" id="primary_color_text"
                                               value="<?= e($primaryColor) ?>" maxlength="7" style="max-width:120px;">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary btn-reset-color" data-default="#007bff" data-target="primary_color">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="login_bg_color">Warna Background Login</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="login_bg_color"
                                               name="login_bg_color" value="<?= e($loginBgColor) ?>" style="max-width:60px; padding:3px;">
                                        <input type="text" class="form-control" id="login_bg_color_text"
                                               value="<?= e($loginBgColor) ?>" maxlength="7" style="max-width:120px;">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary btn-reset-color" data-default="#667eea" data-target="login_bg_color">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Logo Upload -->
                    <div class="col-lg-6">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-image mr-1"></i> Logo Aplikasi</h3>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3" id="logoPreviewContainer">
                                    <?php if ($logoUrl): ?>
                                        <img src="<?= e($logoUrl) ?>" alt="Logo" id="logoPreview" class="img-fluid" style="max-height:120px;">
                                    <?php else: ?>
                                        <div id="logoPlaceholder" class="border rounded p-4 text-muted">
                                            <i class="fas fa-image fa-3x mb-2 d-block"></i>
                                            Belum ada logo
                                        </div>
                                        <img src="" alt="Logo" id="logoPreview" class="img-fluid d-none" style="max-height:120px;">
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <label class="btn btn-outline-primary btn-sm mr-2 mb-0">
                                        <i class="fas fa-upload mr-1"></i> Upload Logo
                                        <input type="file" id="logoFile" accept="image/*" class="d-none">
                                    </label>
                                    <button type="button" class="btn btn-outline-danger btn-sm" id="btnRemoveLogo"
                                            <?= $logoUrl ? '' : 'style="display:none"' ?>>
                                        <i class="fas fa-trash mr-1"></i> Hapus
                                    </button>
                                </div>
                                <div id="logoUploadProgress" class="progress mt-2 d-none" style="height:4px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:100%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Login Background Upload -->
                    <div class="col-lg-6">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-desktop mr-1"></i> Background Login</h3>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3" id="loginBgPreviewContainer">
                                    <?php if ($loginBgUrl): ?>
                                        <img src="<?= e($loginBgUrl) ?>" alt="Login BG" id="loginBgPreview" class="img-fluid rounded" style="max-height:120px;">
                                    <?php else: ?>
                                        <div id="loginBgPlaceholder" class="border rounded p-4 text-muted">
                                            <i class="fas fa-desktop fa-3x mb-2 d-block"></i>
                                            Belum ada background (gunakan warna)
                                        </div>
                                        <img src="" alt="Login BG" id="loginBgPreview" class="img-fluid rounded d-none" style="max-height:120px;">
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <label class="btn btn-outline-primary btn-sm mr-2 mb-0">
                                        <i class="fas fa-upload mr-1"></i> Upload Background
                                        <input type="file" id="loginBgFile" accept="image/*" class="d-none">
                                    </label>
                                    <button type="button" class="btn btn-outline-danger btn-sm" id="btnRemoveLoginBg"
                                            <?= $loginBgUrl ? '' : 'style="display:none"' ?>>
                                        <i class="fas fa-trash mr-1"></i> Hapus
                                    </button>
                                </div>
                                <div id="loginBgUploadProgress" class="progress mt-2 d-none" style="height:4px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:100%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save mr-1"></i> Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<?php require ROOT_PATH . '/views/layouts/footer.php'; ?>
