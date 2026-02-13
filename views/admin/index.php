<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/navbar.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">Admin - Export Order</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Export Order</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Step 1: Pilih Ekspedisi -->
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-truck mr-1"></i> Step 1: Pilih Ekspedisi</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?= BASE_URL ?>admin" class="row align-items-end">
                        <div class="col-md-6 form-group">
                            <label>Ekspedisi</label>
                            <select name="expedition_id" class="form-control select2" onchange="this.form.submit()">
                                <option value="">-- Semua Ekspedisi (Belum Export) --</option>
                                <?php foreach ($expeditions as $exp): ?>
                                <option value="<?= $exp['id'] ?>" <?= $selectedExpedition == $exp['id'] ? 'selected' : '' ?>>
                                    <?= e($exp['name']) ?> (<?= e($exp['code']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <button type="submit" class="btn btn-info btn-block"><i class="fas fa-filter mr-1"></i> Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Step 2: List Order & Export -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-export mr-1"></i> Step 2: Pilih & Export Order</h3>
                    <div class="card-tools">
                        <span class="badge badge-info"><?= count($orders) ?> order belum diexport</span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada order yang perlu diexport.</p>
                        </div>
                    <?php else: ?>
                    <form method="POST" action="<?= BASE_URL ?>admin/export" id="exportForm">
                        <input type="hidden" name="expedition_id" value="<?= e($selectedExpedition) ?>">

                        <div class="mb-3">
                            <button type="button" id="selectAll" class="btn btn-sm btn-outline-primary mr-1">
                                <i class="fas fa-check-double mr-1"></i> Pilih Semua
                            </button>
                            <button type="button" id="deselectAll" class="btn btn-sm btn-outline-secondary mr-1">
                                <i class="fas fa-times mr-1"></i> Batal Pilih
                            </button>
                            <button type="submit" class="btn btn-sm btn-success" id="btnExport">
                                <i class="fas fa-file-export mr-1"></i> Export yang Dipilih (<span id="selectedCount">0</span>)
                            </button>
                        </div>

                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="40"><input type="checkbox" id="checkAll"></th>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Telepon</th>
                                    <th>Alamat</th>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                    <th>Ekspedisi</th>
                                    <th>Resi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $i => $o): ?>
                                <tr>
                                    <td><input type="checkbox" name="order_ids[]" value="<?= $o['id'] ?>" class="order-check"></td>
                                    <td><?= $i + 1 ?></td>
                                    <td><strong><?= e($o['customer_name']) ?></strong></td>
                                    <td><?= e($o['customer_phone']) ?></td>
                                    <td><small><?= e($o['customer_address']) ?></small></td>
                                    <td><?= e($o['product_name']) ?></td>
                                    <td><?= $o['qty'] ?></td>
                                    <td><?= formatRupiah($o['total']) ?></td>
                                    <td><?= e($o['expedition_name'] ?? '-') ?></td>
                                    <td><code><?= e($o['resi'] ?: '-') ?></code></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<?php $pageScripts = ['admin-export.js']; ?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
