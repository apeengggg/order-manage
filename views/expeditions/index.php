<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/navbar.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">Kelola Ekspedisi</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Ekspedisi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Form Tambah -->
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-plus mr-1"></i> Tambah Ekspedisi</h3>
                        </div>
                        <form method="POST" action="<?= BASE_URL ?>expeditions/create" enctype="multipart/form-data">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Nama Ekspedisi</label>
                                    <input type="text" name="name" class="form-control" required placeholder="Contoh: JNE">
                                </div>
                                <div class="form-group">
                                    <label>Kode</label>
                                    <input type="text" name="code" class="form-control" required placeholder="Contoh: JNE" maxlength="20">
                                </div>
                                <div class="form-group">
                                    <label>Foto / Logo</label>
                                    <div class="text-center mb-2">
                                        <img id="createPhotoPreview" src="" alt="" style="max-width:100%;max-height:150px;display:none;border-radius:4px;">
                                    </div>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="createPhotoInput" name="photo" accept="image/*">
                                        <label class="custom-file-label" for="createPhotoInput">Pilih foto...</label>
                                    </div>
                                    <small class="text-muted">Opsional. Maks 5MB. Format: jpg, png, gif, webp</small>
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

                <!-- List -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-truck mr-1"></i> Daftar Ekspedisi</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th width="40">#</th>
                                        <th width="60">Foto</th>
                                        <th>Nama</th>
                                        <th>Kode</th>
                                        <th width="200">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expeditions as $i => $exp): ?>
                                    <?php $photo = $photosMap[$exp['id']] ?? null; ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td>
                                            <?php if ($photo && $photo['thumb_url']): ?>
                                                <img src="<?= $photo['thumb_url'] ?>" alt="<?= e($exp['name']) ?>"
                                                     style="width:40px;height:40px;object-fit:cover;border-radius:4px;cursor:pointer;"
                                                     class="img-preview-thumb"
                                                     data-url="<?= $photo['url'] ?>"
                                                     data-name="<?= e($exp['name']) ?>">
                                            <?php else: ?>
                                                <div style="width:40px;height:40px;background:#e9ecef;border-radius:4px;display:flex;align-items:center;justify-content:center;">
                                                    <i class="fas fa-truck text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="exp-name-<?= $exp['id'] ?>"><?= e($exp['name']) ?></span>
                                        </td>
                                        <td>
                                            <code class="exp-code-<?= $exp['id'] ?>"><?= e($exp['code']) ?></code>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info btn-files-exp"
                                                data-id="<?= $exp['id'] ?>"
                                                data-name="<?= e($exp['name']) ?>"
                                                title="File Attachment">
                                                <i class="fas fa-paperclip"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning btn-edit-exp"
                                                data-action="<?= BASE_URL ?>expeditions/update/<?= $exp['id'] ?>"
                                                data-name="<?= e($exp['name']) ?>"
                                                data-code="<?= e($exp['code']) ?>"
                                                data-photo-thumb="<?= $photo ? $photo['thumb_url'] : '' ?>"
                                                data-photo-url="<?= $photo ? $photo['url'] : '' ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="<?= BASE_URL ?>expeditions/delete/<?= $exp['id'] ?>" class="d-inline">
                                                <button type="button" class="btn btn-sm btn-danger btn-delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($expeditions)): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada ekspedisi.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editExpModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editExpForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Edit Ekspedisi</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Ekspedisi</label>
                        <input type="text" name="name" id="editExpName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Kode</label>
                        <input type="text" name="code" id="editExpCode" class="form-control" required maxlength="20">
                    </div>
                    <div class="form-group">
                        <label>Foto / Logo</label>
                        <div class="text-center mb-2">
                            <img id="editPhotoPreview" src="" alt="" style="max-width:100%;max-height:150px;display:none;border-radius:4px;">
                            <div id="editPhotoPlaceholder" class="text-muted py-3" style="display:none;">
                                <i class="fas fa-image fa-2x"></i><br><small>Belum ada foto</small>
                            </div>
                        </div>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="editPhotoInput" name="photo" accept="image/*">
                            <label class="custom-file-label" for="editPhotoInput">Pilih foto baru...</label>
                        </div>
                        <small class="text-muted">Kosongkan jika tidak ingin mengganti foto. Maks 5MB.</small>
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

<!-- Modal Files -->
<div class="modal fade" id="filesExpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title"><i class="fas fa-paperclip mr-1"></i> File Attachment - <span id="filesExpName"></span></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="expedition-file-section"></div>
            </div>
        </div>
    </div>
</div>

<?php $pageScripts = ['file-upload.js', 'expeditions.js']; ?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
