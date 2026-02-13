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
                        <form method="POST" action="<?= BASE_URL ?>expeditions/create">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Nama Ekspedisi</label>
                                    <input type="text" name="name" class="form-control" required placeholder="Contoh: JNE">
                                </div>
                                <div class="form-group">
                                    <label>Kode</label>
                                    <input type="text" name="code" class="form-control" required placeholder="Contoh: JNE" maxlength="20">
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
                                        <th>Nama</th>
                                        <th>Kode</th>
                                        <th width="160">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expeditions as $i => $exp): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td>
                                            <span class="exp-name-<?= $exp['id'] ?>"><?= e($exp['name']) ?></span>
                                        </td>
                                        <td>
                                            <code class="exp-code-<?= $exp['id'] ?>"><?= e($exp['code']) ?></code>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning btn-edit-exp"
                                                data-id="<?= $exp['id'] ?>"
                                                data-name="<?= e($exp['name']) ?>"
                                                data-code="<?= e($exp['code']) ?>">
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
                                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada ekspedisi.</td></tr>
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
        <form method="POST" id="editExpForm">
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

<script>
$(function() {
    $('.btn-edit-exp').on('click', function() {
        var id = $(this).data('id');
        $('#editExpName').val($(this).data('name'));
        $('#editExpCode').val($(this).data('code'));
        $('#editExpForm').attr('action', '<?= BASE_URL ?>expeditions/update/' + id);
        $('#editExpModal').modal('show');
    });
});
</script>
