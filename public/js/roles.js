/**
 * roles.js - Role management page
 */
$(function() {
    // Edit role modal
    $('.btn-edit-role').on('click', function() {
        var $btn = $(this);
        $('#editRoleName').val($btn.data('name'));
        $('#editRoleSlug').val($btn.data('slug'));
        $('#editRoleDesc').val($btn.data('description'));
        $('#editRoleForm').attr('action', $btn.data('action'));
        $('#editRoleModal').modal('show');
    });

    // Delete role confirmation
    $('.btn-delete-role').on('click', function() {
        var $btn = $(this);
        var form = $btn.closest('form')[0];
        var name = $btn.data('name');

        App.confirmAction({
            title: 'Hapus Role?',
            text: 'Role "' + name + '" akan dihapus permanen.',
            confirmText: 'Ya, Hapus!',
            confirmColor: '#dc3545',
            onConfirm: function() { form.submit(); }
        });
    });

    // Auto-generate slug from name in create form
    $('form[action*="roles/create"] input[name="name"]').on('input', function() {
        var slug = $(this).val()
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
        $('form[action*="roles/create"] input[name="slug"]').val(slug);
    });
});
