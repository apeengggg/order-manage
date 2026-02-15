/**
 * users.js - User management page
 */
$(function() {
    // Edit user modal
    $('.btn-edit-user').on('click', function() {
        var $btn = $(this);
        $('#editUserUsername').val($btn.data('username'));
        $('#editUserName').val($btn.data('name'));
        $('#editUserRole').val($btn.data('role-id'));
        $('#editUserForm').attr('action', $btn.data('action'));
        $('#editUserModal').modal('show');
    });

    // Change password modal
    $('.btn-change-pwd').on('click', function() {
        var $btn = $(this);
        $('#changePwdName').text($btn.data('name'));
        $('#changePwdForm').attr('action', $btn.data('action'));
        $('#newPassword').val('');
        $('#confirmPassword').val('').removeClass('is-invalid');
        $('#changePwdModal').modal('show');
    });

    // Password confirmation validation
    $('#changePwdForm').on('submit', function(e) {
        var pwd = $('#newPassword').val();
        var confirm = $('#confirmPassword').val();
        if (pwd !== confirm) {
            e.preventDefault();
            $('#confirmPassword').addClass('is-invalid');
            return false;
        }
        $('#confirmPassword').removeClass('is-invalid');
    });

    $('#confirmPassword').on('input', function() {
        if ($(this).val() === $('#newPassword').val()) {
            $(this).removeClass('is-invalid');
        }
    });

    // Delete user confirmation
    $('.btn-delete-user').on('click', function() {
        var $btn = $(this);
        var form = $btn.closest('form')[0];
        var name = $btn.data('name');

        App.confirmAction({
            title: 'Hapus User?',
            text: 'User "' + name + '" akan dihapus permanen.',
            confirmText: 'Ya, Hapus!',
            confirmColor: '#dc3545',
            onConfirm: function() { form.submit(); }
        });
    });
});
