<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/navbar.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">Kelola Tenant</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Tenant</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Form Tambah Tenant -->
                <div class="col-12 col-lg-5">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-plus mr-1"></i> Tambah Tenant</h3>
                        </div>
                        <form method="POST" action="<?= BASE_URL ?>tenants/create">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Nama Perusahaan <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required placeholder="Contoh: PT Maju Jaya" maxlength="100">
                                </div>
                                <div class="form-group">
                                    <label>Slug <span class="text-danger">*</span></label>
                                    <input type="text" name="slug" class="form-control" required placeholder="Contoh: maju-jaya" maxlength="50">
                                    <small class="text-muted">Huruf kecil, tanpa spasi. Digunakan sebagai identifier unik.</small>
                                </div>
                                <div class="form-group">
                                    <label>Domain (opsional)</label>
                                    <input type="text" name="domain" class="form-control" placeholder="Contoh: majujaya.com" maxlength="100">
                                </div>
                                <div class="form-group">
                                    <label>Maks User</label>
                                    <input type="number" name="max_users" class="form-control" value="10" min="1" max="1000">
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-1"></i> Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- List Tenant -->
                <div class="col-12 col-lg-7">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-building mr-1"></i> Daftar Tenant</h3>
                            <div class="card-tools">
                                <span class="badge badge-info"><?= count($tenants) ?> tenant</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th width="40">#</th>
                                        <th>Nama</th>
                                        <th>Slug</th>
                                        <th>User</th>
                                        <th>Order</th>
                                        <th>Status</th>
                                        <th width="230">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tenants as $i => $t): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><strong><?= e($t['name']) ?></strong></td>
                                        <td><code><?= e($t['slug']) ?></code></td>
                                        <td><span class="badge badge-info"><?= (int)$t['user_count'] ?> / <?= (int)$t['max_users'] ?></span></td>
                                        <td><span class="badge badge-secondary"><?= (int)$t['order_count'] ?></span></td>
                                        <td>
                                            <?php if ($t['is_active']): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>tenants/impersonate/<?= $t['id'] ?>" class="btn btn-sm btn-info" title="Masuk sebagai tenant ini">
                                                <i class="fas fa-sign-in-alt"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-warning btn-edit-tenant"
                                                data-action="<?= BASE_URL ?>tenants/update/<?= $t['id'] ?>"
                                                data-name="<?= e($t['name']) ?>"
                                                data-slug="<?= e($t['slug']) ?>"
                                                data-domain="<?= e($t['domain'] ?? '') ?>"
                                                data-max-users="<?= (int)$t['max_users'] ?>"
                                                data-is-active="<?= $t['is_active'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ((int)$t['user_count'] === 0 && (int)$t['order_count'] === 0): ?>
                                            <form method="POST" action="<?= BASE_URL ?>tenants/delete/<?= $t['id'] ?>" class="d-inline">
                                                <button type="button" class="btn btn-sm btn-danger btn-delete-tenant"
                                                    data-name="<?= e($t['name']) ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-danger" disabled title="Tidak bisa dihapus, masih ada data">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($tenants)): ?>
                                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada tenant.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Edit Tenant -->
<div class="modal fade" id="editTenantModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <form method="POST" id="editTenantForm">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Edit Tenant</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Perusahaan <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editTenantName" class="form-control" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label>Slug <span class="text-danger">*</span></label>
                        <input type="text" name="slug" id="editTenantSlug" class="form-control" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label>Domain (opsional)</label>
                        <input type="text" name="domain" id="editTenantDomain" class="form-control" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label>Maks User</label>
                        <input type="number" name="max_users" id="editTenantMaxUsers" class="form-control" min="1" max="1000">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="editTenantActive" name="is_active" value="1" checked>
                            <label class="custom-control-label" for="editTenantActive">Tenant Aktif</label>
                        </div>
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

<?php $pageScripts = ['tenants.js']; ?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
