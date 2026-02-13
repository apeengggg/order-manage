<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/navbar.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">Edit Order #<?= $order['id'] ?></h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>orders">List Order</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if ($err = flash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-exclamation-triangle mr-1"></i> <?= e($err) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>orders/edit/<?= $order['id'] ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-user mr-1"></i> Data Customer</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Nama Customer <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_name" class="form-control" value="<?= e($order['customer_name']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>No. Telepon <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_phone" class="form-control" value="<?= e($order['customer_phone']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Alamat Lengkap <span class="text-danger">*</span></label>
                                    <textarea name="customer_address" class="form-control" rows="3" required><?= e($order['customer_address']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-box mr-1"></i> Data Order</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Nama Produk <span class="text-danger">*</span></label>
                                    <input type="text" name="product_name" class="form-control" value="<?= e($order['product_name']) ?>" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Qty <span class="text-danger">*</span></label>
                                            <input type="number" name="qty" id="qty" class="form-control" value="<?= e($order['qty']) ?>" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Harga Satuan <span class="text-danger">*</span></label>
                                            <input type="number" name="price" id="price" class="form-control" value="<?= e($order['price']) ?>" min="0" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Total</label>
                                    <h4 class="text-primary" id="total_display">Rp 0</h4>
                                </div>
                                <div class="form-group">
                                    <label>Ekspedisi</label>
                                    <select name="expedition_id" class="form-control select2">
                                        <option value="">-- Pilih Ekspedisi --</option>
                                        <?php foreach ($expeditions as $exp): ?>
                                        <option value="<?= $exp['id'] ?>" <?= $order['expedition_id'] == $exp['id'] ? 'selected' : '' ?>>
                                            <?= e($exp['name']) ?> (<?= e($exp['code']) ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>No. Resi</label>
                                    <input type="text" name="resi" class="form-control" value="<?= e($order['resi']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Catatan</label>
                                    <textarea name="notes" class="form-control" rows="2"><?= e($order['notes']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <a href="<?= BASE_URL ?>orders" class="btn btn-secondary mr-2">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save mr-1"></i> Update Order
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
