/**
 * modules.js - Module/menu management with icon picker
 */
$(function() {
    var activeTarget = 'create';

    // Icon search
    $('#iconSearch').on('input', function() {
        var val = $(this).val().toLowerCase();
        $('.icon-item').each(function() {
            $(this).toggle($(this).data('icon').toLowerCase().includes(val));
        });
    });

    // Icon select
    $(document).on('click', '.icon-selectable', function() {
        var icon = $(this).closest('.icon-item').data('icon');
        $('.icon-selectable').removeClass('active');
        $(this).addClass('active');

        if (activeTarget === 'edit') {
            $('#editIconInput').val(icon);
            $('#editIconPreview').html('<i class="' + icon + '"></i>');
        } else {
            $('#iconInput').val(icon);
            $('#iconPreview').html('<i class="' + icon + '"></i>');
        }
        $('#iconPickerModal').modal('hide');
    });

    // Open icon picker for create
    $('[data-target="#iconPickerModal"]').on('click', function() {
        activeTarget = 'create';
    });

    // Open icon picker for edit
    $('.btn-pick-edit-icon').on('click', function() {
        activeTarget = 'edit';
        $('#iconPickerModal').modal('show');
    });

    // Delete module confirmation
    $('.btn-delete').on('click', function() {
        var $form = $(this).closest('form');
        var name = $(this).closest('tr').find('strong').text();
        Swal.fire({
            title: 'Hapus Modul?',
            text: 'Modul "' + name + '" akan dihapus (nonaktif).',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) $form.submit();
        });
    });

    // Edit module modal
    $('.btn-edit-module').on('click', function() {
        var $btn = $(this);
        $('#editName').val($btn.data('name'));
        $('#editSlug').val($btn.data('slug'));
        $('#editUrl').val($btn.data('url'));
        $('#editIconInput').val($btn.data('icon'));
        $('#editIconPreview').html('<i class="' + $btn.data('icon') + '"></i>');
        $('#editSort').val($btn.data('sort'));
        $('#editModuleForm').attr('action', $btn.data('action'));
        $('#editModuleModal').modal('show');
    });
});
