<?php $_globalView = \App\TenantContext::isSuperAdmin(); ?>
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

                        <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="40"><input type="checkbox" id="checkAll"></th>
                                    <th>#</th>
                                    <?php if ($_globalView): ?><th>Tenant</th><?php endif; ?>
                                    <th>Customer</th>
                                    <th>Telepon</th>
                                    <th>Ekspedisi</th>
                                    <th>Detail</th>
                                    <th>Dibuat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $i => $o):
                                    $extra = json_decode($o['extra_fields'] ?? '{}', true) ?: [];
                                    $displayName = $o['customer_name'];
                                    $displayPhone = $o['customer_phone'];
                                    if (empty($displayName) && !empty($extra)) {
                                        foreach ($extra as $key => $val) {
                                            $lk = strtolower($key);
                                            if (str_contains($lk, 'nama') && str_contains($lk, 'penerima') && !empty($val)) {
                                                $displayName = $val; break;
                                            }
                                        }
                                        if (empty($displayName)) {
                                            foreach ($extra as $val) {
                                                if (!empty(trim((string)$val))) { $displayName = $val; break; }
                                            }
                                        }
                                    }
                                    if (empty($displayPhone) && !empty($extra)) {
                                        foreach ($extra as $key => $val) {
                                            $lk = strtolower($key);
                                            if ((str_contains($lk, 'telepon') || str_contains($lk, 'phone') || str_contains($lk, 'handphone') || str_contains($lk, 'kontak')) && !empty($val)) {
                                                $displayPhone = $val; break;
                                            }
                                        }
                                    }
                                    // Summary of key fields
                                    $summaryParts = [];
                                    $shown = 0;
                                    foreach ($extra as $key => $val) {
                                        if (empty(trim((string)$val))) continue;
                                        $cleanKey = trim(ltrim($key, '* '));
                                        if (strpos($cleanKey, '//') !== false) $cleanKey = trim(explode('//', $cleanKey)[0]);
                                        $lk = strtolower($cleanKey);
                                        if (str_contains($lk, 'nama') && str_contains($lk, 'penerima')) continue;
                                        if (str_contains($lk, 'telepon') || str_contains($lk, 'phone') || str_contains($lk, 'handphone')) continue;
                                        $summaryParts[] = '<small>' . e(mb_strimwidth((string)$val, 0, 25, '...')) . '</small>';
                                        $shown++;
                                        if ($shown >= 2) break;
                                    }
                                ?>
                                <tr>
                                    <td><input type="checkbox" name="order_ids[]" value="<?= $o['id'] ?>" class="order-check"></td>
                                    <td><?= $i + 1 ?></td>
                                    <?php if ($_globalView): ?>
                                    <td><span class="badge badge-secondary"><?= e($o['tenant_name'] ?? 'Super Admin') ?></span></td>
                                    <?php endif; ?>
                                    <td><strong><?= e($displayName ?: '-') ?></strong></td>
                                    <td><?= e($displayPhone ?: '-') ?></td>
                                    <td>
                                        <?= e($o['expedition_name'] ?? '-') ?>
                                        <?php if ($o['resi']): ?>
                                            <br><code class="small"><?= e($o['resi']) ?></code>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= !empty($summaryParts) ? implode(', ', $summaryParts) : '<span class="text-muted">-</span>' ?></td>
                                    <td><small><?= date('d/m/Y', strtotime($o['created_at'])) ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Export Progress Overlay -->
<div id="exportOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center;">
    <div class="card shadow-lg" style="width:450px; max-width:90%; border-radius:12px; overflow:hidden;">
        <div class="card-body text-center py-4 px-4">
            <div id="exportSpinner" class="mb-3">
                <i class="fas fa-file-export fa-3x text-success mb-2"></i>
                <h5 class="mb-1" id="exportTitle">Memproses Export...</h5>
                <p class="text-muted small mb-3" id="exportSubtitle">Menyiapkan file, mohon tunggu</p>
            </div>
            <div class="progress mb-2" style="height:22px; border-radius:11px;">
                <div id="exportProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                     role="progressbar" style="width:0%; transition: width 0.3s ease;">
                    <span id="exportProgressText" class="font-weight-bold">0%</span>
                </div>
            </div>
            <p class="text-muted small mb-0" id="exportInfo">Memproses data...</p>
        </div>
    </div>
</div>

<?php $pageScripts = ['admin-export.js']; ?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
