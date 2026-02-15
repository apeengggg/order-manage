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
                    <div class="table-responsive">
                    <table id="dataTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="40">#</th>
                                <th>Customer</th>
                                <th>Telepon</th>
                                <th>Ekspedisi</th>
                                <th>Detail</th>
                                <th>Status</th>
                                <th width="140">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $i => $o):
                                $extra = json_decode($o['extra_fields'] ?? '{}', true) ?: [];
                                // Get customer name from mapped field or first extra_fields value
                                $displayName = $o['customer_name'];
                                $displayPhone = $o['customer_phone'];
                                $displayAddress = $o['customer_address'] ?? '';
                                if (empty($displayName) && !empty($extra)) {
                                    // Try to find a name-like field from extra_fields
                                    foreach ($extra as $key => $val) {
                                        $lk = strtolower($key);
                                        if (str_contains($lk, 'nama') && str_contains($lk, 'penerima') && !empty($val)) {
                                            $displayName = $val;
                                            break;
                                        }
                                    }
                                    if (empty($displayName)) {
                                        // Fallback: use first non-empty value
                                        foreach ($extra as $val) {
                                            if (!empty(trim((string)$val))) { $displayName = $val; break; }
                                        }
                                    }
                                }
                                if (empty($displayPhone) && !empty($extra)) {
                                    foreach ($extra as $key => $val) {
                                        $lk = strtolower($key);
                                        if ((str_contains($lk, 'telepon') || str_contains($lk, 'phone') || str_contains($lk, 'handphone') || str_contains($lk, 'kontak')) && !empty($val)) {
                                            $displayPhone = $val;
                                            break;
                                        }
                                    }
                                }
                                // Build summary of key extra fields (max 3)
                                $summaryParts = [];
                                $shown = 0;
                                foreach ($extra as $key => $val) {
                                    if (empty(trim((string)$val))) continue;
                                    $cleanKey = trim(ltrim($key, '* '));
                                    if (strpos($cleanKey, '//') !== false) $cleanKey = trim(explode('//', $cleanKey)[0]);
                                    $lk = strtolower($cleanKey);
                                    // Skip fields already shown as name/phone
                                    if (str_contains($lk, 'nama') && str_contains($lk, 'penerima')) continue;
                                    if (str_contains($lk, 'telepon') || str_contains($lk, 'phone') || str_contains($lk, 'handphone')) continue;
                                    $summaryParts[] = '<small><strong>' . e($cleanKey) . ':</strong> ' . e(mb_strimwidth((string)$val, 0, 30, '...')) . '</small>';
                                    $shown++;
                                    if ($shown >= 3) break;
                                }
                                $moreCount = max(0, count(array_filter($extra, fn($v) => !empty(trim((string)$v)))) - $shown - 2);
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <strong><?= e($displayName ?: '-') ?></strong>
                                    <?php if ($displayAddress): ?>
                                        <br><small class="text-muted"><?= e(mb_strimwidth($displayAddress, 0, 50, '...')) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($displayPhone ?: '-') ?></td>
                                <td>
                                    <?= e($o['expedition_name'] ?? '-') ?>
                                    <?php if ($o['resi']): ?>
                                        <br><code class="small"><?= e($o['resi']) ?></code>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($summaryParts)): ?>
                                        <?= implode('<br>', $summaryParts) ?>
                                        <?php if ($moreCount > 0): ?>
                                            <br><small class="text-muted">+<?= $moreCount ?> field lainnya</small>
                                        <?php endif; ?>
                                    <?php elseif ($o['product_name']): ?>
                                        <small><strong>Produk:</strong> <?= e($o['product_name']) ?></small>
                                        <?php if ($o['qty'] > 0): ?>
                                            <br><small><strong>Qty:</strong> <?= $o['qty'] ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($o['is_exported']): ?>
                                        <span class="badge badge-exported"><i class="fas fa-check mr-1"></i>Exported</span>
                                    <?php else: ?>
                                        <span class="badge badge-pending"><i class="fas fa-clock mr-1"></i>Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info btn-detail" title="Detail"
                                        data-id="<?= $o['id'] ?>"
                                        data-customer="<?= e($o['customer_name']) ?>"
                                        data-phone="<?= e($o['customer_phone']) ?>"
                                        data-address="<?= e($o['customer_address'] ?? '') ?>"
                                        data-product="<?= e($o['product_name'] ?? '') ?>"
                                        data-qty="<?= $o['qty'] ?? 0 ?>"
                                        data-price="<?= $o['price'] ?? 0 ?>"
                                        data-total="<?= $o['total'] ?? 0 ?>"
                                        data-expedition="<?= e($o['expedition_name'] ?? '-') ?>"
                                        data-resi="<?= e($o['resi'] ?? '') ?>"
                                        data-notes="<?= e($o['notes'] ?? '') ?>"
                                        data-extra="<?= e($o['extra_fields'] ?? '{}') ?>"
                                        data-exported="<?= $o['is_exported'] ? '1' : '0' ?>"
                                        data-created="<?= e($o['created_at'] ?? '') ?>"
                                        data-createdby="<?= e($o['created_by_name'] ?? '') ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
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
        </div>
    </section>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
<script>
$(function() {
    function esc(str) { return $('<span>').text(str || '').html(); }
    function formatRp(num) {
        num = parseFloat(num) || 0;
        return 'Rp ' + num.toLocaleString('id-ID');
    }

    $(document).on('click', '.btn-detail', function() {
        var d = $(this).data();
        var extra = {};
        try { extra = JSON.parse($(this).attr('data-extra')); } catch(e) {}

        var html = '<div class="text-left" style="font-size:13px;">';
        html += '<table class="table table-sm table-bordered mb-3">';

        // Standard fields
        var fields = [
            ['ID Order', '#' + d.id],
            ['Customer', d.customer],
            ['Telepon', d.phone],
            ['Alamat', d.address],
            ['Produk', d.product],
            ['Qty', d.qty],
            ['Harga', formatRp(d.price)],
            ['Total', formatRp(d.total)],
            ['Ekspedisi', d.expedition],
            ['Resi', d.resi],
            ['Catatan', d.notes],
            ['Status', d.exported === '1' ? '<span class="badge badge-success">Exported</span>' : '<span class="badge badge-warning">Pending</span>'],
            ['Dibuat oleh', d.createdby],
            ['Tanggal', d.created]
        ];

        for (var i = 0; i < fields.length; i++) {
            var val = fields[i][1];
            if (val === null || val === undefined || String(val).trim() === '' || val === '0' || val === 'Rp 0') continue;
            html += '<tr><td class="font-weight-bold" style="width:35%;">' + esc(fields[i][0]) + '</td>';
            if (fields[i][0] === 'Status') {
                html += '<td>' + val + '</td></tr>';
            } else {
                html += '<td>' + esc(String(val)) + '</td></tr>';
            }
        }
        html += '</table>';

        // Extra fields from template
        var hasExtra = false;
        var extraHtml = '<h6 class="font-weight-bold mb-2">Data Template</h6>';
        extraHtml += '<table class="table table-sm table-bordered">';
        for (var key in extra) {
            if (!extra.hasOwnProperty(key)) continue;
            var val = extra[key];
            if (val === null || val === undefined || String(val).trim() === '') continue;
            hasExtra = true;
            var cleanKey = key.replace(/^\*\s*/, '');
            if (cleanKey.indexOf('//') !== -1) cleanKey = cleanKey.split('//')[0].trim();
            extraHtml += '<tr><td class="font-weight-bold" style="width:35%;">' + esc(cleanKey) + '</td>';
            extraHtml += '<td>' + esc(String(val)) + '</td></tr>';
        }
        extraHtml += '</table>';

        if (hasExtra) html += extraHtml;
        html += '</div>';

        Swal.fire({
            title: 'Detail Order #' + d.id,
            html: html,
            width: 650,
            confirmButtonText: 'Tutup'
        });
    });
});
</script>
