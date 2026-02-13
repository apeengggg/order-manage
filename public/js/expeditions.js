/**
 * expeditions.js - Expedition management page
 */
$(function() {
    // Edit expedition
    $('.btn-edit-exp').on('click', function() {
        $('#editExpName').val($(this).data('name'));
        $('#editExpCode').val($(this).data('code'));
        $('#editExpForm').attr('action', $(this).data('action'));
        $('#editExpModal').modal('show');
    });

    // File attachment modal
    $('.btn-files-exp').on('click', function() {
        var expId = $(this).data('id');
        var expName = $(this).data('name');

        $('#filesExpName').text(expName);
        $('#filesExpModal').modal('show');

        // Initialize file upload component for this expedition
        App.FileUpload.init({
            module: 'expeditions',
            moduleId: expId,
            container: '#expedition-file-section',
            canUpload: true,
            canDelete: true
        });
    });
});
