<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/navbar.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">Kelola Role</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Role</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Form Tambah Role -->
                <div class="col-12 col-lg-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-plus mr-1"></i> Tambah Role</h3>
                        </div>
                        <form method="POST" action="<?= BASE_URL ?>roles/create">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Nama Role <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required placeholder="Contoh: Manager">
                                </div>
                                <div class="form-group">
                                    <label>Slug <span class="text-danger">*</span></label>
                                    <input type="text" name="slug" class="form-control" required placeholder="Contoh: manager" maxlength="50">
                                    <small class="text-muted">Huruf kecil, tanpa spasi. Digunakan sebagai identifier.</small>
                                </div>
                                <div class="form-group">
                                    <label>Deskripsi</label>
                                    <input type="text" name="description" class="form-control" placeholder="Deskripsi singkat role" maxlength="200">
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

                <!-- List Role -->
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-tag mr-1"></i> Daftar Role</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th width="40">#</th>
                                        <th>Nama</th>
                                        <th>Slug</th>
                                        <th>Deskripsi</th>
                                        <th width="80">User</th>
                                        <th width="150">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roles as $i => $role): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><strong><?= e($role['name']) ?></strong></td>
                                        <td><code><?= e($role['slug']) ?></code></td>
                                        <td><?= e($role['description'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge badge-info"><?= (int)$role['user_count'] ?> user</span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning btn-edit-role"
                                                data-action="<?= BASE_URL ?>roles/update/<?= $role['id'] ?>"
                                                data-name="<?= e($role['name']) ?>"
                                                data-slug="<?= e($role['slug']) ?>"
                                                data-description="<?= e($role['description'] ?? '') ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ((int)$role['user_count'] === 0): ?>
                                            <form method="POST" action="<?= BASE_URL ?>roles/delete/<?= $role['id'] ?>" class="d-inline">
                                                <button type="button" class="btn btn-sm btn-danger btn-delete-role"
                                                    data-name="<?= e($role['name']) ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-danger" disabled title="Tidak bisa dihapus, masih ada user">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($roles)): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada role.</td></tr>
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

<!-- Modal Edit Role -->
<div class="modal fade" id="editRoleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <form method="POST" id="editRoleForm">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Edit Role</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Role <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editRoleName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Slug <span class="text-danger">*</span></label>
                        <input type="text" name="slug" id="editRoleSlug" class="form-control" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <input type="text" name="description" id="editRoleDesc" class="form-control" maxlength="200">
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

<?php $pageScripts = ['roles.js']; ?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
