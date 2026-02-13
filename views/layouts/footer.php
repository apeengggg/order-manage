    <!-- Main Footer -->
    <footer class="main-footer">
        <strong>&copy; <?= date('Y') ?> <?= APP_NAME ?></strong>
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.0
        </div>
    </footer>
</div><!-- ./wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- AdminLTE 3 -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!-- App Core -->
<script src="<?= BASE_URL ?>js/app.js"></script>

<?php if ($flashSuccess = flash('success')): ?>
<script>App.handleFlash('success', '<?= e($flashSuccess) ?>');</script>
<?php endif; ?>
<?php if ($flashError = flash('error')): ?>
<script>App.handleFlash('error', '<?= e($flashError) ?>');</script>
<?php endif; ?>

<?php if (!empty($pageScripts)): ?>
<?php foreach ($pageScripts as $script): ?>
<script src="<?= BASE_URL ?>js/<?= $script ?>"></script>
<?php endforeach; ?>
<?php endif; ?>

</body>
</html>
