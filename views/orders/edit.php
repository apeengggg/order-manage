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
            <form method="POST" action="<?= BASE_URL ?>orders/edit/<?= $order['id'] ?>" id="orderForm">
                <!-- Pilih Ekspedisi -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-truck mr-1"></i> Pilih Ekspedisi</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <label>Ekspedisi <span class="text-danger">*</span></label>
                            <select name="expedition_id" id="expedition_select" class="form-control select2" required>
                                <option value="">-- Pilih Ekspedisi --</option>
                                <?php foreach ($expeditions as $exp):
                                    $hasTpl = isset($templateMap[$exp['id']]);
                                ?>
                                <option value="<?= $exp['id'] ?>"
                                    data-has-template="<?= $hasTpl ? '1' : '0' ?>"
                                    <?= $order['expedition_id'] == $exp['id'] ? 'selected' : '' ?>>
                                    <?= e($exp['name']) ?> (<?= e($exp['code']) ?>)
                                    <?= !$hasTpl ? '- Belum ada template' : '' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Warning: no template -->
                <div id="no-template-warning" style="display:none;">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Ekspedisi ini belum memiliki template. Hubungi admin untuk upload template di halaman Kelola Ekspedisi.
                    </div>
                </div>

                <!-- Loading -->
                <div id="template-loading" style="display:none;">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2 text-muted">Memuat template...</p>
                    </div>
                </div>

                <!-- Dynamic template fields container -->
                <div id="template-fields-container" style="display:none;"></div>

                <!-- Submit -->
                <div id="submit-section" style="display:none;">
                    <div class="row mb-4">
                        <div class="col-12">
                            <a href="<?= BASE_URL ?>orders" class="btn btn-secondary mr-2">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save mr-1"></i> Update Order
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
var existingExtraFields = <?= json_encode($order['extra_fields_decoded'] ?? new stdClass(), JSON_UNESCAPED_UNICODE) ?>;
</script>
<?php $pageScripts = ['order-form.js']; ?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
