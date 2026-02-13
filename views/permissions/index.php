<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/navbar.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<?php
$permTypes = [
    'can_view' => ['label' => 'View', 'icon' => 'fas fa-eye', 'color' => 'info'],
    'can_add' => ['label' => 'Add', 'icon' => 'fas fa-plus', 'color' => 'success'],
    'can_edit' => ['label' => 'Edit', 'icon' => 'fas fa-edit', 'color' => 'warning'],
    'can_delete' => ['label' => 'Delete', 'icon' => 'fas fa-trash', 'color' => 'danger'],
    'can_view_detail' => ['label' => 'View Detail', 'icon' => 'fas fa-search', 'color' => 'primary'],
    'can_upload' => ['label' => 'Upload', 'icon' => 'fas fa-upload', 'color' => 'secondary'],
    'can_download' => ['label' => 'Download', 'icon' => 'fas fa-download', 'color' => 'dark'],
];
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">Kelola Permission</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Permission</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Role Tabs -->
            <div class="card card-outline card-primary">
                <div class="card-header p-0 pt-1">
                    <ul class="nav nav-tabs" id="roleTabs" role="tablist">
                        <?php foreach ($roles as $i => $role): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $i === 0 ? 'active' : '' ?>" id="tab-<?= $role ?>"
                               data-toggle="tab" href="#content-<?= $role ?>" role="tab">
                                <i class="fas fa-user-shield mr-1"></i>
                                <?= strtoupper($role) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="roleTabContent">
                        <?php foreach ($roles as $i => $role): ?>
                        <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>"
                             id="content-<?= $role ?>" role="tabpanel">

                            <form method="POST" action="<?= BASE_URL ?>permissions/update" class="permission-form">
                                <input type="hidden" name="role" value="<?= e($role) ?>">

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover mb-0">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th width="200">
                                                    <i class="fas fa-cubes mr-1"></i> Modul
                                                </th>
                                                <?php foreach ($permTypes as $key => $pt): ?>
                                                <th class="text-center" width="100">
                                                    <i class="<?= $pt['icon'] ?> mr-1 text-<?= $pt['color'] ?>"></i>
                                                    <br><small><?= $pt['label'] ?></small>
                                                </th>
                                                <?php endforeach; ?>
                                                <th class="text-center" width="80">
                                                    <small>Semua</small>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($modules as $module): ?>
                                            <?php
                                                $rolePerms = $allPermissions[$role] ?? [];
                                                $mp = null;
                                                foreach ($rolePerms as $rp) {
                                                    if ((int)$rp['module_id'] === (int)$module['id']) {
                                                        $mp = $rp;
                                                        break;
                                                    }
                                                }
                                            ?>
                                            <tr>
                                                <td>
                                                    <i class="<?= e($module['icon']) ?> mr-2 text-primary"></i>
                                                    <strong><?= e($module['name']) ?></strong>
                                                    <br><small class="text-muted"><?= e($module['slug']) ?></small>
                                                </td>
                                                <?php foreach ($permTypes as $key => $pt): ?>
                                                <td class="text-center">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox"
                                                               class="custom-control-input perm-checkbox"
                                                               id="<?= $role ?>_<?= $module['id'] ?>_<?= $key ?>"
                                                               name="permissions[<?= $module['id'] ?>][<?= $key ?>]"
                                                               value="1"
                                                               data-row="<?= $role ?>_<?= $module['id'] ?>"
                                                               <?= ($mp && (int)$mp[$key]) ? 'checked' : '' ?>>
                                                        <label class="custom-control-label"
                                                               for="<?= $role ?>_<?= $module['id'] ?>_<?= $key ?>"></label>
                                                    </div>
                                                </td>
                                                <?php endforeach; ?>
                                                <td class="text-center">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox"
                                                               class="custom-control-input toggle-all-row"
                                                               id="<?= $role ?>_<?= $module['id'] ?>_all"
                                                               data-row="<?= $role ?>_<?= $module['id'] ?>">
                                                        <label class="custom-control-label"
                                                               for="<?= $role ?>_<?= $module['id'] ?>_all"></label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary btn-save-perm">
                                        <i class="fas fa-save mr-1"></i> Simpan Permission <?= strtoupper($role) ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Keterangan Permission</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($permTypes as $key => $pt): ?>
                        <div class="col-md-3 col-6 mb-2">
                            <span class="badge badge-<?= $pt['color'] ?>">
                                <i class="<?= $pt['icon'] ?> mr-1"></i> <?= $pt['label'] ?>
                            </span>
                            <small class="text-muted ml-1">
                                <?php
                                $desc = [
                                    'can_view' => 'Melihat halaman/menu',
                                    'can_add' => 'Menambah data baru',
                                    'can_edit' => 'Mengubah data',
                                    'can_delete' => 'Menghapus data',
                                    'can_view_detail' => 'Melihat detail data',
                                    'can_upload' => 'Upload file',
                                    'can_download' => 'Download/export data',
                                ];
                                echo $desc[$key];
                                ?>
                            </small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

<script>
$(function() {
    // Toggle all checkboxes in a row
    $('.toggle-all-row').on('change', function() {
        var row = $(this).data('row');
        var checked = this.checked;
        $('input.perm-checkbox[data-row="' + row + '"]').prop('checked', checked);
    });

    // Update "all" checkbox when individual checkboxes change
    $('.perm-checkbox').on('change', function() {
        var row = $(this).data('row');
        var total = $('input.perm-checkbox[data-row="' + row + '"]').length;
        var checkedCount = $('input.perm-checkbox[data-row="' + row + '"]:checked').length;
        $('input.toggle-all-row[data-row="' + row + '"]').prop('checked', total === checkedCount);
    });

    // Init "all" checkbox state
    $('.toggle-all-row').each(function() {
        var row = $(this).data('row');
        var total = $('input.perm-checkbox[data-row="' + row + '"]').length;
        var checkedCount = $('input.perm-checkbox[data-row="' + row + '"]:checked').length;
        $(this).prop('checked', total === checkedCount && total > 0);
    });

    // Confirm save with SweetAlert
    $('.permission-form').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        var role = $(form).find('input[name="role"]').val().toUpperCase();
        Swal.fire({
            title: 'Simpan Permission?',
            text: 'Permission untuk role ' + role + ' akan diupdate.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
