$(function() {
    var BASE = $('meta[name="base-url"]').attr('content');

    // Color picker sync
    function syncColor(name) {
        var $picker = $('#' + name);
        var $text = $('#' + name + '_text');

        $picker.on('input change', function() {
            $text.val(this.value);
        });
        $text.on('input', function() {
            var val = this.value;
            if (/^#[0-9a-fA-F]{6}$/.test(val)) {
                $picker.val(val);
            }
        });
    }
    syncColor('primary_color');
    syncColor('login_bg_color');

    // Reset color button
    $('.btn-reset-color').on('click', function() {
        var def = $(this).data('default');
        var target = $(this).data('target');
        $('#' + target).val(def).trigger('change');
        $('#' + target + '_text').val(def);
    });

    // Logo upload
    $('#logoFile').on('change', function() {
        var file = this.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            toastr.error('File harus berupa gambar.');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            toastr.error('Ukuran file maksimal 5MB.');
            return;
        }

        var fd = new FormData();
        fd.append('logo', file);

        $('#logoUploadProgress').removeClass('d-none');

        $.ajax({
            url: BASE + 'settings/uploadLogo',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    $('#logoPreview').attr('src', res.url).removeClass('d-none');
                    $('#logoPlaceholder').addClass('d-none');
                    $('#btnRemoveLogo').show();
                    toastr.success('Logo berhasil diupload.');
                } else {
                    toastr.error(res.message || 'Gagal upload logo.');
                }
            },
            error: function() {
                toastr.error('Gagal upload logo.');
            },
            complete: function() {
                $('#logoUploadProgress').addClass('d-none');
                $('#logoFile').val('');
            }
        });
    });

    // Remove logo
    $('#btnRemoveLogo').on('click', function() {
        Swal.fire({
            title: 'Hapus Logo?',
            text: 'Logo akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.post(BASE + 'settings/removeLogo', function(res) {
                    if (res.success) {
                        $('#logoPreview').addClass('d-none').attr('src', '');
                        $('#logoPlaceholder').removeClass('d-none');
                        $('#btnRemoveLogo').hide();
                        toastr.success(res.message);
                    } else {
                        toastr.error(res.message);
                    }
                });
            }
        });
    });

    // Login BG upload
    $('#loginBgFile').on('change', function() {
        var file = this.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            toastr.error('File harus berupa gambar.');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            toastr.error('Ukuran file maksimal 10MB.');
            return;
        }

        var fd = new FormData();
        fd.append('login_bg', file);

        $('#loginBgUploadProgress').removeClass('d-none');

        $.ajax({
            url: BASE + 'settings/uploadLoginBg',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    $('#loginBgPreview').attr('src', res.url).removeClass('d-none');
                    $('#loginBgPlaceholder').addClass('d-none');
                    $('#btnRemoveLoginBg').show();
                    toastr.success('Background login berhasil diupload.');
                } else {
                    toastr.error(res.message || 'Gagal upload background.');
                }
            },
            error: function() {
                toastr.error('Gagal upload background.');
            },
            complete: function() {
                $('#loginBgUploadProgress').addClass('d-none');
                $('#loginBgFile').val('');
            }
        });
    });

    // Remove login BG
    $('#btnRemoveLoginBg').on('click', function() {
        Swal.fire({
            title: 'Hapus Background?',
            text: 'Background login akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.post(BASE + 'settings/removeLoginBg', function(res) {
                    if (res.success) {
                        $('#loginBgPreview').addClass('d-none').attr('src', '');
                        $('#loginBgPlaceholder').removeClass('d-none');
                        $('#btnRemoveLoginBg').hide();
                        toastr.success(res.message);
                    } else {
                        toastr.error(res.message);
                    }
                });
            }
        });
    });
});
