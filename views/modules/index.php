<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/navbar.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<?php
$faIcons = [
    'fas fa-tachometer-alt', 'fas fa-plus-circle', 'fas fa-list-alt', 'fas fa-file-export',
    'fas fa-truck', 'fas fa-user-shield', 'fas fa-cubes', 'fas fa-cog', 'fas fa-cogs',
    'fas fa-users', 'fas fa-user', 'fas fa-user-plus', 'fas fa-user-edit',
    'fas fa-shopping-cart', 'fas fa-box', 'fas fa-boxes', 'fas fa-warehouse',
    'fas fa-chart-bar', 'fas fa-chart-line', 'fas fa-chart-pie', 'fas fa-chart-area',
    'fas fa-file', 'fas fa-file-alt', 'fas fa-file-pdf', 'fas fa-file-excel',
    'fas fa-folder', 'fas fa-folder-open', 'fas fa-database', 'fas fa-server',
    'fas fa-envelope', 'fas fa-bell', 'fas fa-comment', 'fas fa-comments',
    'fas fa-home', 'fas fa-building', 'fas fa-map-marker-alt', 'fas fa-globe',
    'fas fa-money-bill-wave', 'fas fa-credit-card', 'fas fa-receipt', 'fas fa-calculator',
    'fas fa-calendar', 'fas fa-clock', 'fas fa-history', 'fas fa-tasks',
    'fas fa-clipboard', 'fas fa-clipboard-list', 'fas fa-clipboard-check',
    'fas fa-shield-alt', 'fas fa-lock', 'fas fa-key', 'fas fa-fingerprint',
    'fas fa-tools', 'fas fa-wrench', 'fas fa-sliders-h', 'fas fa-palette',
    'fas fa-image', 'fas fa-camera', 'fas fa-upload', 'fas fa-download',
    'fas fa-print', 'fas fa-qrcode', 'fas fa-barcode', 'fas fa-tag', 'fas fa-tags',
    'fas fa-star', 'fas fa-heart', 'fas fa-flag', 'fas fa-bookmark',
    'fas fa-circle', 'fas fa-square', 'fas fa-check-circle', 'fas fa-times-circle',
    'fas fa-info-circle', 'fas fa-question-circle', 'fas fa-exclamation-circle',
    'fas fa-arrow-right', 'fas fa-arrow-left', 'fas fa-sync', 'fas fa-redo',
    'fas fa-search', 'fas fa-eye', 'fas fa-eye-slash', 'fas fa-filter',
    'fas fa-sort', 'fas fa-edit', 'fas fa-trash', 'fas fa-save',
    'fas fa-plus', 'fas fa-minus', 'fas fa-times', 'fas fa-check',
    'fas fa-shipping-fast', 'fas fa-dolly', 'fas fa-hand-holding-usd',
    'fas fa-store', 'fas fa-store-alt', 'fas fa-cash-register',
    'fas fa-headset', 'fas fa-phone', 'fas fa-mobile-alt',
    'fas fa-laptop', 'fas fa-desktop', 'fas fa-tablet-alt',
    'fas fa-plug', 'fas fa-bolt', 'fas fa-fire', 'fas fa-magic',
];
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">Kelola Menu / Modul</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Modul</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Form Tambah -->
                <div class="col-md-5">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-plus mr-1"></i> Tambah Modul</h3>
                        </div>
                        <form method="POST" action="<?= BASE_URL ?>modules/create">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Nama Modul <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required placeholder="Contoh: Data Produk">
                                </div>
                                <div class="form-group">
                                    <label>Slug <span class="text-danger">*</span></label>
                                    <input type="text" name="slug" class="form-control" required placeholder="Contoh: products" pattern="[a-z0-9\-]+">
                                    <small class="text-muted">Huruf kecil, angka, dan strip saja</small>
                                </div>
                                <div class="form-group">
                                    <label>URL <span class="text-danger">*</span></label>
                                    <input type="text" name="url" class="form-control" required placeholder="Contoh: products">
                                </div>
                                <div class="form-group">
                                    <label>Icon</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="iconPreview"><i class="fas fa-circle"></i></span>
                                        </div>
                                        <input type="text" name="icon" id="iconInput" class="form-control" value="fas fa-circle" readonly>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#iconPickerModal">
                                                <i class="fas fa-search"></i> Pilih
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Urutan</label>
                                    <input type="number" name="sort_order" class="form-control" value="0" min="0">
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-1"></i> Simpan Modul
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- List -->
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-cubes mr-1"></i> Daftar Modul</h3>
                            <div class="card-tools">
                                <a href="<?= BASE_URL ?>permissions" class="btn btn-sm btn-info">
                                    <i class="fas fa-user-shield mr-1"></i> Kelola Permission
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">Order</th>
                                        <th>Icon</th>
                                        <th>Nama</th>
                                        <th>Slug</th>
                                        <th>URL</th>
                                        <th width="120">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($modules as $mod): ?>
                                    <tr>
                                        <td><?= $mod['sort_order'] ?></td>
                                        <td><i class="<?= e($mod['icon']) ?> fa-lg text-primary"></i></td>
                                        <td><strong><?= e($mod['name']) ?></strong></td>
                                        <td><code><?= e($mod['slug']) ?></code></td>
                                        <td><small><?= e($mod['url']) ?></small></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning btn-edit-module"
                                                data-action="<?= BASE_URL ?>modules/update/<?= $mod['id'] ?>"
                                                data-name="<?= e($mod['name']) ?>"
                                                data-slug="<?= e($mod['slug']) ?>"
                                                data-icon="<?= e($mod['icon']) ?>"
                                                data-url="<?= e($mod['url']) ?>"
                                                data-sort="<?= $mod['sort_order'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="<?= BASE_URL ?>modules/delete/<?= $mod['id'] ?>" class="d-inline">
                                                <button type="button" class="btn btn-sm btn-danger btn-delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Icon Picker Modal -->
<div class="modal fade" id="iconPickerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-icons mr-1"></i> Pilih Icon</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="text" id="iconSearch" class="form-control mb-3" placeholder="Cari icon...">
                <div class="row" id="iconGrid">
                    <?php foreach ($faIcons as $icon): ?>
                    <div class="col-2 col-md-1 text-center mb-2 icon-item" data-icon="<?= $icon ?>">
                        <div class="p-2 border rounded icon-selectable" style="cursor:pointer;" title="<?= $icon ?>">
                            <i class="<?= $icon ?> fa-lg"></i>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Module Modal -->
<div class="modal fade" id="editModuleModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editModuleForm">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Edit Modul</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Modul</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Slug</label>
                        <input type="text" name="slug" id="editSlug" class="form-control" required pattern="[a-z0-9\-]+">
                    </div>
                    <div class="form-group">
                        <label>URL</label>
                        <input type="text" name="url" id="editUrl" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Icon</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="editIconPreview"><i class="fas fa-circle"></i></span>
                            </div>
                            <input type="text" name="icon" id="editIconInput" class="form-control" readonly>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary btn-pick-edit-icon">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Urutan</label>
                        <input type="number" name="sort_order" id="editSort" class="form-control" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php $pageScripts = ['modules.js']; ?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
