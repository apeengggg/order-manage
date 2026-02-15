$(function() {
    // Edit tenant
    $(document).on('click', '.btn-edit-tenant', function() {
        var btn = $(this);
        $('#editTenantForm').attr('action', btn.data('action'));
        $('#editTenantName').val(btn.data('name'));
        $('#editTenantSlug').val(btn.data('slug'));
        $('#editTenantDomain').val(btn.data('domain'));
        $('#editTenantMaxUsers').val(btn.data('max-users'));
        $('#editTenantActive').prop('checked', btn.data('is-active') == 1);
        $('#editTenantModal').modal('show');
    });

    // Delete tenant
    $(document).on('click', '.btn-delete-tenant', function() {
        var btn = $(this);
        var form = btn.closest('form');
        Swal.fire({
            title: 'Hapus Tenant?',
            html: 'Tenant <strong>' + btn.data('name') + '</strong> akan dihapus beserta semua datanya.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) form.submit();
        });
    });

    // Impersonate confirmation
    $(document).on('click', '.btn-sm.btn-info[href*="impersonate"]', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        Swal.fire({
            title: 'Masuk sebagai Tenant?',
            text: 'Anda akan melihat data sebagai tenant ini.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Masuk',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) window.location = url;
        });
    });
});
