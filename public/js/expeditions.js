/**
 * expeditions.js - Expedition management page
 */
$(function() {
    var BASE = $('meta[name="base-url"]').attr('content') || '/';

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

    // Create form: template file label
    $('#createTemplateInput').on('change', function() {
        $(this).next('.custom-file-label').text(this.files[0] ? this.files[0].name : 'Pilih template...');
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
    // Click thumbnail in table to preview + download
    // ========================================
    $(document).on('click', '.img-preview-thumb', function() {
        var url = $(this).attr('src'); // Use thumbnail for preview
        var name = $(this).data('name');
        var downloadUrl = $(this).data('download');

        Swal.fire({
            title: name,
            imageUrl: url,
            imageAlt: name,
            width: 400,
            imageWidth: 300,
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

        App.FileUpload.init({
            module: 'expeditions',
            moduleId: expId,
            container: '#expedition-file-section',
            canUpload: true,
            canDelete: true
        });
    });

    // ========================================
    // Template upload modal
    // ========================================
    var currentTemplateExpId = null;

    $('.btn-template-exp').on('click', function() {
        currentTemplateExpId = $(this).data('id');
        var expName = $(this).data('name');

        $('#templateExpName').text(expName);
        $('#templateFileInput').val('');
        $('#templateFileInput').next('.custom-file-label').text('Pilih file template...');
        $('#btnUploadTemplate').prop('disabled', true);
        $('#templateUploadProgress').hide();

        // Load existing template
        loadTemplatePreview(currentTemplateExpId);

        $('#templateExpModal').modal('show');
    });

    // File input change
    $('#templateFileInput').on('change', function() {
        var hasFile = this.files && this.files.length > 0;
        $(this).next('.custom-file-label').text(hasFile ? this.files[0].name : 'Pilih file template...');
        $('#btnUploadTemplate').prop('disabled', !hasFile);
    });

    // Upload template
    $('#btnUploadTemplate').on('click', function() {
        var fileInput = $('#templateFileInput')[0];
        if (!fileInput.files || !fileInput.files.length) return;

        var formData = new FormData();
        formData.append('template', fileInput.files[0]);

        $('#templateUploadProgress').show();
        $('#btnUploadTemplate').prop('disabled', true);

        $.ajax({
            url: BASE + 'expeditions/uploadTemplate/' + currentTemplateExpId,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(resp) {
                $('#templateUploadProgress').hide();
                if (resp.success) {
                    toastr.success(resp.message);
                    renderTemplatePreview(resp.columns, resp.sheet_name);
                    // Reload page to update template status badges
                    setTimeout(function() { location.reload(); }, 1500);
                } else {
                    toastr.error(resp.message);
                    $('#btnUploadTemplate').prop('disabled', false);
                }
            },
            error: function() {
                $('#templateUploadProgress').hide();
                toastr.error('Gagal upload template.');
                $('#btnUploadTemplate').prop('disabled', false);
            }
        });
    });

    function loadTemplatePreview(expId) {
        $.get(BASE + 'expeditions/getTemplate/' + expId, function(resp) {
            if (resp.success && resp.columns && resp.columns.length > 0) {
                renderTemplatePreview(resp.columns, resp.sheet_name);
            } else {
                $('#templatePreview').hide();
            }
        });
    }

    // Store Tagify instances per row position
    var tagifyInstances = {};

    function renderTemplatePreview(columns, sheetName) {
        // Destroy existing Tagify instances
        for (var key in tagifyInstances) {
            if (tagifyInstances[key]) tagifyInstances[key].destroy();
        }
        tagifyInstances = {};

        var html = '';
        for (var i = 0; i < columns.length; i++) {
            var col = columns[i];
            var reqChecked = col.is_required ? ' checked' : '';
            var isSelect = col.input_type === 'select';

            html += '<tr data-position="' + col.position + '">';
            html += '<td>' + (i + 1) + '</td>';

            // Editable clean_name + original name hint
            html += '<td>';
            html += '<input type="text" class="form-control form-control-sm col-clean-name" value="' + escapeAttr(col.clean_name) + '">';
            html += '<small class="text-muted">' + escapeHtml(col.name) + '</small>';
            html += '</td>';

            // Editable is_required
            html += '<td class="text-center">';
            html += '<input type="checkbox" class="col-required"' + reqChecked + '>';
            html += '</td>';

            // Editable input_type
            html += '<td>';
            html += '<select class="form-control form-control-sm col-input-type">';
            html += '<option value="text"' + (!isSelect ? ' selected' : '') + '>Text</option>';
            html += '<option value="select"' + (isSelect ? ' selected' : '') + '>Select</option>';
            html += '</select>';
            html += '</td>';

            // Options with Tagify (visible when type=select)
            html += '<td class="td-options">';
            if (isSelect) {
                html += '<input type="text" class="col-options" data-pos="' + col.position + '">';
            } else {
                html += '<input type="text" class="col-options" data-pos="' + col.position + '" style="display:none">';
                html += '<span class="text-muted">-</span>';
            }
            html += '</td>';

            html += '</tr>';
        }
        $('#templateColumnsBody').html(html);
        $('#templateSheetName').text(sheetName ? '(Sheet: ' + sheetName + ')' : '');
        $('#btnDownloadTemplate').attr('href', BASE + 'expeditions/downloadTemplate/' + currentTemplateExpId);
        $('#templatePreview').show();

        // Initialize Tagify on options inputs
        for (var i = 0; i < columns.length; i++) {
            var col = columns[i];
            if (col.input_type === 'select') {
                initTagify(col.position, col.options || []);
            }
        }
    }

    function initTagify(position, options) {
        var el = document.querySelector('.col-options[data-pos="' + position + '"]');
        if (!el) return;

        var tagify = new Tagify(el, {
            delimiters: ',',
            maxTags: Infinity,
            dropdown: { enabled: 0 },
            originalInputValueFormat: function(values) {
                return values.map(function(v) { return v.value; }).join(',');
            }
        });

        // Load existing options as tags
        if (options && options.length > 0) {
            tagify.addTags(options);
        }

        tagifyInstances[position] = tagify;
    }

    // Toggle options visibility when input_type changes
    $(document).on('change', '.col-input-type', function() {
        var $tr = $(this).closest('tr');
        var $td = $tr.find('.td-options');
        var pos = $tr.data('position');

        if ($(this).val() === 'select') {
            $td.find('.col-options').show();
            $td.find('.tagify').show();
            $td.find('span.text-muted').hide();
            if (!tagifyInstances[pos]) {
                initTagify(pos, []);
            }
        } else {
            $td.find('.col-options').hide();
            $td.find('.tagify').hide();
            $td.find('span.text-muted').show();
            if (tagifyInstances[pos]) {
                tagifyInstances[pos].removeAllTags();
            }
        }
    });

    // Save template column changes
    $(document).on('click', '#btnSaveTemplateColumns', function() {
        var columns = [];
        $('#templateColumnsBody tr').each(function() {
            var $row = $(this);
            var pos = parseInt($row.data('position'));
            var inputType = $row.find('.col-input-type').val();
            var options = null;

            if (inputType === 'select' && tagifyInstances[pos]) {
                var tags = tagifyInstances[pos].value;
                options = tags.map(function(t) { return t.value; }).join(',');
                if (!options) options = null;
            }

            columns.push({
                position: pos,
                clean_name: $row.find('.col-clean-name').val(),
                is_required: $row.find('.col-required').is(':checked'),
                input_type: inputType,
                options: options
            });
        });

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: BASE + 'expeditions/updateTemplateColumns/' + currentTemplateExpId,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ columns: columns }),
            success: function(resp) {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perubahan Kolom');
                if (resp.success) {
                    toastr.success(resp.message);
                } else {
                    toastr.error(resp.message);
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perubahan Kolom');
                toastr.error('Gagal menyimpan perubahan.');
            }
        });
    });

    function escapeHtml(str) {
        if (!str) return '';
        return $('<span>').text(str).html();
    }

    function escapeAttr(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
});
