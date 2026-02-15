<?php $_globalView = \App\TenantContext::isSuperAdmin(); ?>
<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/navbar.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">Kelola User</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">User</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Form Tambah User -->
                <div class="col-12 col-lg-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-plus mr-1"></i> Tambah User</h3>
                        </div>
                        <form method="POST" action="<?= BASE_URL ?>users/create">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Username <span class="text-danger">*</span></label>
                                    <input type="text" name="username" class="form-control" required placeholder="Username login" maxlength="50">
                                </div>
                                <div class="form-group">
                                    <label>Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required placeholder="Nama lengkap" maxlength="100">
                                </div>
                                <div class="form-group">
                                    <label>Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter" minlength="6">
                                </div>
                                <div class="form-group">
                                    <label>Role <span class="text-danger">*</span></label>
                                    <select name="role_id" class="form-control" required>
                                        <option value="">-- Pilih Role --</option>
                                        <?php foreach ($roles as $role): ?>
                                        <option value="<?= $role['id'] ?>"><?= e($role['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
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

                <!-- List User -->
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-users mr-1"></i> Daftar User</h3>
                            <div class="card-tools">
                                <span class="badge badge-info"><?= count($users) ?> user</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th width="40">#</th>
                                        <?php if ($_globalView): ?><th>Tenant</th><?php endif; ?>
                                        <th>Username</th>
                                        <th>Nama</th>
                                        <th>Role</th>
                                        <th>Dibuat</th>
                                        <th width="180">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $i => $user): ?>
                                    <?php $isSelf = (int)$user['id'] === (int)auth('user_id'); ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <?php if ($_globalView): ?>
                                        <td><span class="badge badge-secondary"><?= e($user['tenant_name'] ?? 'Super Admin') ?></span></td>
                                        <?php endif; ?>
                                        <td>
                                            <code><?= e($user['username']) ?></code>
                                            <?php if ($isSelf): ?>
                                                <span class="badge badge-success ml-1">Anda</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= e($user['name']) ?></strong></td>
                                        <td>
                                            <span class="badge badge-<?= ($user['role_slug'] ?? '') === 'admin' ? 'danger' : 'info' ?>">
                                                <?= e($user['role_name'] ?? '-') ?>
                                            </span>
                                        </td>
                                        <td><small><?= date('d/m/Y', strtotime($user['created_at'])) ?></small></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning btn-edit-user"
                                                data-action="<?= BASE_URL ?>users/update/<?= $user['id'] ?>"
                                                data-username="<?= e($user['username']) ?>"
                                                data-name="<?= e($user['name']) ?>"
                                                data-role-id="<?= $user['role_id'] ?>"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-info btn-change-pwd"
                                                data-action="<?= BASE_URL ?>users/changePassword/<?= $user['id'] ?>"
                                                data-name="<?= e($user['name']) ?>"
                                                title="Ganti Password">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            <?php if (!$isSelf): ?>
                                            <form method="POST" action="<?= BASE_URL ?>users/delete/<?= $user['id'] ?>" class="d-inline">
                                                <button type="button" class="btn btn-sm btn-danger btn-delete-user"
                                                    data-name="<?= e($user['name']) ?>"
                                                    title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-danger" disabled title="Tidak bisa hapus akun sendiri">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($users)): ?>
                                    <tr><td colspan="<?= $_globalView ? 7 : 6 ?>" class="text-center text-muted py-4">Belum ada user.</td></tr>
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

<!-- Modal Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <form method="POST" id="editUserForm">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Edit User</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="editUserUsername" class="form-control" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editUserName" class="form-control" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label>Role <span class="text-danger">*</span></label>
                        <select name="role_id" id="editUserRole" class="form-control" required>
                            <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id'] ?>"><?= e($role['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
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

<!-- Modal Change Password -->
<div class="modal fade" id="changePwdModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <form method="POST" id="changePwdForm">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title"><i class="fas fa-key mr-1"></i> Ganti Password - <span id="changePwdName"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Password Baru <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="newPassword" class="form-control" required placeholder="Minimal 6 karakter" minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" id="confirmPassword" class="form-control" required placeholder="Ulangi password" minlength="6">
                        <div class="invalid-feedback">Password tidak cocok.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">Simpan Password</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php $pageScripts = ['users.js']; ?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
