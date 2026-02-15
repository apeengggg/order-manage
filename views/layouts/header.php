<?php
$_appName = appSetting('app_name', APP_NAME);
$_primaryColor = appSetting('primary_color', '#007bff');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="base-url" content="<?= BASE_URL ?>">
    <title><?= e($pageTitle ?? 'Dashboard') ?> | <?= e($_appName) ?></title>

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <!-- AdminLTE 3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Tagify -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify@4/dist/tagify.css">

    <style>
        :root {
            --primary-color: <?= e($_primaryColor) ?>;
        }
        .content-wrapper { min-height: calc(100vh - 57px); }
        .table td { vertical-align: middle !important; }
        .badge-exported { background-color: #28a745; color: #fff; }
        .badge-pending { background-color: #ffc107; color: #333; }
        .icon-selectable:hover, .icon-selectable.active { background-color: var(--primary-color) !important; color: #fff; }
        .icon-selectable:hover i, .icon-selectable.active i { color: #fff !important; }

        /* Primary color overrides */
        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); }
        .btn-primary:hover, .btn-primary:focus { background-color: var(--primary-color); border-color: var(--primary-color); filter: brightness(0.9); }
        .btn-outline-primary { color: var(--primary-color); border-color: var(--primary-color); }
        .btn-outline-primary:hover { background-color: var(--primary-color); border-color: var(--primary-color); color: #fff; }
        .card-primary.card-outline { border-top-color: var(--primary-color); }
        .nav-pills .nav-link.active { background-color: var(--primary-color); }
        a { color: var(--primary-color); }
        .page-item.active .page-link { background-color: var(--primary-color); border-color: var(--primary-color); }

        /* Responsive: tables */
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        @media (max-width: 767.98px) {
            .content-header h1 { font-size: 1.4rem; }
            .btn-group .btn span.d-none.d-md-inline { display: none !important; }
            .card-body { padding: 0.75rem; }
            .table th, .table td { white-space: nowrap; font-size: 0.85rem; padding: 0.4rem 0.5rem; }
            .modal-dialog { margin: 0.5rem; max-width: calc(100% - 1rem); }
            .modal-body { padding: 0.75rem; }
            .select2-container { width: 100% !important; }
            .form-group label { font-size: 0.9rem; }
            .main-footer { font-size: 0.8rem; padding: 0.5rem; }
            .main-footer .float-right { float: none !important; display: block; margin-top: 0.25rem; }
        }
        @media (max-width: 991.98px) {
            .modal-lg, .modal-xl { max-width: calc(100% - 1rem); margin: 0.5rem auto; }
        }
    </style>
    <script>
        // Apply dark mode from localStorage before render to prevent flash
        (function() {
            if (localStorage.getItem('dark_mode') === '1') {
                document.documentElement.classList.add('dark-mode-pending');
            }
        })();
    </script>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<script>
    // Apply dark mode immediately after body is created
    if (localStorage.getItem('dark_mode') === '1') {
        document.body.classList.add('dark-mode');
    }
</script>
<div class="wrapper">
