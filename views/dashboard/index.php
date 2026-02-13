<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/navbar.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Info Boxes -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $totalOrders ?></h3>
                            <p>Total Order</p>
                        </div>
                        <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                        <a href="<?= BASE_URL ?>orders" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $exported ?></h3>
                            <p>Sudah Export</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                        <a href="<?= BASE_URL ?>orders?is_exported=1" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $pending ?></h3>
                            <p>Belum Export</p>
                        </div>
                        <div class="icon"><i class="fas fa-clock"></i></div>
                        <a href="<?= BASE_URL ?>orders?is_exported=0" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= formatRupiah($revenue) ?></h3>
                            <p>Total Revenue</p>
                        </div>
                        <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                        <a href="#" class="small-box-footer">&nbsp;</a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-bolt mr-1"></i> Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <?php if (isCS() || isAdmin()): ?>
                            <a href="<?= BASE_URL ?>orders/create" class="btn btn-primary mr-2 mb-2">
                                <i class="fas fa-plus mr-1"></i> Input Data Customer
                            </a>
                            <a href="<?= BASE_URL ?>orders" class="btn btn-info mr-2 mb-2">
                                <i class="fas fa-list mr-1"></i> List Order
                            </a>
                            <?php endif; ?>
                            <?php if (isAdmin()): ?>
                            <a href="<?= BASE_URL ?>admin" class="btn btn-success mr-2 mb-2">
                                <i class="fas fa-file-export mr-1"></i> Export Order
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Informasi</h3>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Role:</strong> <span class="badge badge-<?= isAdmin() ? 'danger' : 'info' ?>"><?= strtoupper(auth('role')) ?></span></p>
                            <p class="mb-1"><strong>Login sebagai:</strong> <?= e(auth('name')) ?></p>
                            <hr>
                            <small class="text-muted">
                                <?php if (isCS()): ?>
                                    Anda bisa menginput data customer, melihat list order, edit dan hapus order yang belum diexport admin.
                                <?php else: ?>
                                    Anda bisa melihat semua order, export order per ekspedisi, dan mengelola data ekspedisi.
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
