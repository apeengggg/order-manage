/**
 * expeditions.js - Expedition management page
 */
$(function() {

    // ========================================
    // Photo preview helper (reusable)
    // ========================================
    function previewPhoto(input, previewImg) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                previewImg.attr('src', e.target.result).show();
            };
            reader.readAsDataURL(input.files[0]);
            $(input).next('.custom-file-label').text(input.files[0].name);
        }
    }

    // Create form: photo preview
    $('#createPhotoInput').on('change', function() {
        previewPhoto(this, $('#createPhotoPreview'));
    });

    // Edit modal: photo preview on new file select
    $('#editPhotoInput').on('change', function() {
        previewPhoto(this, $('#editPhotoPreview'));
        $('#editPhotoPlaceholder').hide();
    });

    // ========================================
    // Edit expedition modal
    // ========================================
    $('.btn-edit-exp').on('click', function() {
        var $btn = $(this);
        $('#editExpName').val($btn.data('name'));
        $('#editExpCode').val($btn.data('code'));
        $('#editExpForm').attr('action', $btn.data('action'));

        // Reset file input
        $('#editPhotoInput').val('');
        $('#editPhotoInput').next('.custom-file-label').text('Pilih foto baru...');

        // Show current photo or placeholder
        var thumbUrl = $btn.data('photo-thumb');
        if (thumbUrl) {
            $('#editPhotoPreview').attr('src', thumbUrl).show();
            $('#editPhotoPlaceholder').hide();
        } else {
            $('#editPhotoPreview').hide();
            $('#editPhotoPlaceholder').show();
        }

        $('#editExpModal').modal('show');
    });

    // ========================================
    // Click thumbnail in table to preview original + download
    // ========================================
    $(document).on('click', '.img-preview-thumb', function() {
        var url = $(this).data('url');
        var name = $(this).data('name');
        var downloadUrl = $(this).data('download');

        Swal.fire({
            title: name,
            imageUrl: url,
            imageAlt: name,
            width: 'auto',
            showCloseButton: true,
            showConfirmButton: !!downloadUrl,
            confirmButtonText: '<i class="fas fa-download mr-1"></i> Download Original',
            confirmButtonColor: '#007bff'
        }).then(function(result) {
            if (result.isConfirmed && downloadUrl) {
                window.location.href = downloadUrl;
            }
        });
    });

    // ========================================
    // File attachment modal
    // ========================================
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
