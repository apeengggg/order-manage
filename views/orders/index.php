<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/navbar.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">List Order</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">List Order</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-check-circle mr-1"></i> <?= e($msg) ?>
            </div>
            <?php endif; ?>
            <?php if ($err = flash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-exclamation-triangle mr-1"></i> <?= e($err) ?>
            </div>
            <?php endif; ?>

            <!-- Filter -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?= BASE_URL ?>orders" class="row align-items-end">
                        <div class="col-md-4 form-group">
                            <label>Cari</label>
                            <input type="text" name="search" class="form-control" value="<?= e($filters['search'] ?? '') ?>" placeholder="Nama, telepon, produk, resi...">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Ekspedisi</label>
                            <select name="expedition_id" class="form-control select2">
                                <option value="">Semua</option>
                                <?php foreach ($expeditions as $exp): ?>
                                <option value="<?= $exp['id'] ?>" <?= ($filters['expedition_id'] ?? '') == $exp['id'] ? 'selected' : '' ?>>
                                    <?= e($exp['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Status Export</label>
                            <select name="is_exported" class="form-control">
                                <option value="">Semua</option>
                                <option value="0" <?= ($filters['is_exported'] ?? '') === '0' ? 'selected' : '' ?>>Belum Export</option>
                                <option value="1" <?= ($filters['is_exported'] ?? '') === '1' ? 'selected' : '' ?>>Sudah Export</option>
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search mr-1"></i> Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-table mr-1"></i> Data Order</h3>
                    <div class="card-tools">
                        <a href="<?= BASE_URL ?>orders/create" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus mr-1"></i> Tambah Order
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table id="dataTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="40">#</th>
                                <th>Customer</th>
                                <th>Telepon</th>
                                <th>Produk</th>
                                <th>Qty</th>
                                <th>Total</th>
                                <th>Ekspedisi</th>
                                <th>Resi</th>
                                <th>Status</th>
                                <th width="140">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $i => $o): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <strong><?= e($o['customer_name']) ?></strong>
                                    <br><small class="text-muted"><?= e(mb_strimwidth($o['customer_address'], 0, 50, '...')) ?></small>
                                </td>
                                <td><?= e($o['customer_phone']) ?></td>
                                <td><?= e($o['product_name']) ?></td>
                                <td><?= $o['qty'] ?></td>
                                <td><?= formatRupiah($o['total']) ?></td>
                                <td><?= e($o['expedition_name'] ?? '-') ?></td>
                                <td><code><?= e($o['resi'] ?: '-') ?></code></td>
                                <td>
                                    <?php if ($o['is_exported']): ?>
                                        <span class="badge badge-exported"><i class="fas fa-check mr-1"></i>Exported</span>
                                    <?php else: ?>
                                        <span class="badge badge-pending"><i class="fas fa-clock mr-1"></i>Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$o['is_exported']): ?>
                                        <a href="<?= BASE_URL ?>orders/edit/<?= $o['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="<?= BASE_URL ?>orders/delete/<?= $o['id'] ?>" class="d-inline">
                                            <button type="button" class="btn btn-sm btn-danger btn-delete" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted" title="Diblokir karena sudah diexport">
                                            <i class="fas fa-lock"></i> Terkunci
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
