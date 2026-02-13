/**
 * expeditions.js - Expedition management page
 */
$(function() {
    $('.btn-edit-exp').on('click', function() {
        $('#editExpName').val($(this).data('name'));
        $('#editExpCode').val($(this).data('code'));
        $('#editExpForm').attr('action', $(this).data('action'));
        $('#editExpModal').modal('show');
    });
});
