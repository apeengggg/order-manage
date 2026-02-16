<?php $_globalView = \App\TenantContext::isSuperAdmin(); ?>
<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/navbar.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">Audit Log</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Audit Log</li>
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
                    <form method="GET" action="<?= BASE_URL ?>audit" class="row align-items-end">
                        <div class="col-md-2 form-group">
                            <label>Entity</label>
                            <select name="entity_type" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                <?php
                                $entityTypes = ['order'=>'Order','expedition'=>'Ekspedisi','user'=>'User','role'=>'Role','permission'=>'Permission','tenant'=>'Tenant','setting'=>'Setting','file'=>'File','template'=>'Template'];
                                foreach ($entityTypes as $val => $lbl): ?>
                                <option value="<?= $val ?>" <?= ($filters['entity_type'] ?? '') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Action</label>
                            <select name="action" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                <option value="create" <?= ($filters['action'] ?? '') === 'create' ? 'selected' : '' ?>>Create</option>
                                <option value="update" <?= ($filters['action'] ?? '') === 'update' ? 'selected' : '' ?>>Update</option>
                                <option value="delete" <?= ($filters['action'] ?? '') === 'delete' ? 'selected' : '' ?>>Delete</option>
                                <option value="export" <?= ($filters['action'] ?? '') === 'export' ? 'selected' : '' ?>>Export</option>
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Dari</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($filters['date_from'] ?? '') ?>">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Sampai</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="<?= e($filters['date_to'] ?? '') ?>">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Cari</label>
                            <input type="text" name="search" class="form-control form-control-sm" value="<?= e($filters['search'] ?? '') ?>" placeholder="Label, user...">
                        </div>
                        <div class="col-md-2 form-group">
                            <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fas fa-search mr-1"></i> Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history mr-1"></i> Log Aktivitas</h3>
                    <div class="card-tools">
                        <span class="badge badge-info"><?= number_format($totalCount) ?> record</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm mb-0">
                        <thead>
                            <tr>
                                <th width="150">Waktu</th>
                                <?php if ($_globalView): ?><th>Tenant</th><?php endif; ?>
                                <th>User</th>
                                <th width="80">Action</th>
                                <th>Entity</th>
                                <th>Label</th>
                                <th width="70">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                            <tr><td colspan="<?= $_globalView ? 7 : 6 ?>" class="text-center text-muted py-4">Belum ada log aktivitas</td></tr>
                            <?php endif; ?>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><small><?= e($log['created_at']) ?></small></td>
                                <?php if ($_globalView): ?>
                                <td><span class="badge badge-secondary"><?= e($log['tenant_name'] ?? 'System') ?></span></td>
                                <?php endif; ?>
                                <td>
                                    <?= e($log['user_name'] ?? '-') ?>
                                    <br><small class="text-muted">IP: <?= e($log['ip_address'] ?? '-') ?></small>
                                </td>
                                <td>
                                    <?php
                                    $actionBadges = ['create'=>'success','update'=>'warning','delete'=>'danger','export'=>'info'];
                                    $badge = $actionBadges[$log['action']] ?? 'secondary';
                                    ?>
                                    <span class="badge badge-<?= $badge ?>"><?= e(ucfirst($log['action'])) ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-light"><?= e(ucfirst($log['entity_type'])) ?></span>
                                    <small class="text-muted">#<?= e($log['entity_id']) ?></small>
                                </td>
                                <td><?= e($log['entity_label'] ?? '-') ?></td>
                                <td>
                                    <?php if ($log['old_values'] || $log['new_values']): ?>
                                    <button type="button" class="btn btn-xs btn-outline-info btn-audit-detail"
                                        data-action="<?= e($log['action']) ?>"
                                        data-entity="<?= e(ucfirst($log['entity_type'])) ?> #<?= e($log['entity_id']) ?>"
                                        data-old="<?= e($log['old_values'] ?? '{}') ?>"
                                        data-new="<?= e($log['new_values'] ?? '{}') ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                <?php if ($totalPages > 1): ?>
                <div class="card-footer clearfix">
                    <ul class="pagination pagination-sm m-0 float-right">
                        <?php
                        $queryParams = $filters;
                        for ($p = 1; $p <= $totalPages; $p++):
                            $queryParams['page'] = $p;
                            $qs = http_build_query(array_filter($queryParams, fn($v) => $v !== ''));
                        ?>
                        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>audit?<?= $qs ?>"><?= $p ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                    <small class="text-muted">Hal <?= $page ?> dari <?= $totalPages ?></small>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
<script>
$(function() {
    function esc(str) { return $('<span>').text(str || '').html(); }

    $(document).on('click', '.btn-audit-detail', function() {
        var action = $(this).data('action');
        var entity = $(this).data('entity');
        var oldData = {};
        var newData = {};
        try { oldData = JSON.parse($(this).attr('data-old')); } catch(e) {}
        try { newData = JSON.parse($(this).attr('data-new')); } catch(e) {}

        var html = '<div class="text-left" style="font-size:13px;">';

        if (action === 'create') {
            html += '<h6 class="text-success font-weight-bold mb-2">Data Baru</h6>';
            html += buildTable(newData, 'table-success');
        } else if (action === 'delete') {
            html += '<h6 class="text-danger font-weight-bold mb-2">Data Dihapus</h6>';
            html += buildTable(oldData, 'table-danger');
        } else if (action === 'update') {
            html += '<div class="row">';
            html += '<div class="col-6"><h6 class="text-warning font-weight-bold mb-2">Sebelum</h6>' + buildTable(oldData, 'table-warning') + '</div>';
            html += '<div class="col-6"><h6 class="text-success font-weight-bold mb-2">Sesudah</h6>' + buildTable(newData, 'table-success') + '</div>';
            html += '</div>';
        } else {
            html += '<h6 class="font-weight-bold mb-2">Detail</h6>';
            if (Object.keys(oldData).length) html += buildTable(oldData, '');
            if (Object.keys(newData).length) html += buildTable(newData, '');
        }

        html += '</div>';

        Swal.fire({
            title: 'Detail: ' + entity,
            html: html,
            width: action === 'update' ? 800 : 550,
            confirmButtonText: 'Tutup'
        });
    });

    function buildTable(data, cssClass) {
        if (!data || !Object.keys(data).length) return '<p class="text-muted">Tidak ada data</p>';
        var html = '<table class="table table-sm table-bordered ' + cssClass + '">';
        for (var key in data) {
            if (!data.hasOwnProperty(key)) continue;
            var val = data[key];
            if (val === null || val === undefined) val = '<em class="text-muted">null</em>';
            else if (typeof val === 'object') val = '<code>' + esc(JSON.stringify(val)) + '</code>';
            else val = esc(String(val));
            html += '<tr><td class="font-weight-bold" style="width:40%;">' + esc(key) + '</td><td>' + val + '</td></tr>';
        }
        html += '</table>';
        return html;
    }
});
</script>
