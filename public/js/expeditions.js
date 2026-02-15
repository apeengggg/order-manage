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
        var url = $(this).attr('src');
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

    // ========================================
    // Template column preview & editing
    // ========================================

    // Store Tagify instances per row position (for small option lists)
    var tagifyInstances = {};
    // Store full options data per position (for large option lists)
    var largeOptionsData = {};
    // Store columns data for reference
    var columnsData = [];

    function renderTemplatePreview(columns, sheetName) {
        // Destroy existing Tagify instances
        for (var key in tagifyInstances) {
            if (tagifyInstances[key]) tagifyInstances[key].destroy();
        }
        tagifyInstances = {};
        largeOptionsData = {};
        columnsData = columns;

        var html = '';
        for (var i = 0; i < columns.length; i++) {
            var col = columns[i];
            var reqChecked = col.is_required ? ' checked' : '';
            var isSelect = col.input_type === 'select';
            var isLargeOptions = isSelect && col.options_count && col.options_count > 100;

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

            // Options column
            html += '<td class="td-options">';
            if (isSelect && isLargeOptions) {
                // Large option list - show manage button
                html += '<button type="button" class="btn btn-sm btn-outline-primary btn-manage-options" data-pos="' + col.position + '">';
                html += '<i class="fas fa-list mr-1"></i> Kelola Opsi (<span class="options-count">' + col.options_count + '</span>)';
                html += '</button>';
                html += '<input type="hidden" class="col-options-mode" value="large">';
            } else if (isSelect) {
                // Small option list - use Tagify
                html += '<input type="text" class="col-options" data-pos="' + col.position + '">';
                html += '<input type="hidden" class="col-options-mode" value="small">';
            } else {
                // Text type - no options
                html += '<input type="text" class="col-options" data-pos="' + col.position + '" style="display:none">';
                html += '<input type="hidden" class="col-options-mode" value="none">';
                html += '<span class="text-muted">-</span>';
            }
            html += '</td>';

            html += '</tr>';
        }
        $('#templateColumnsBody').html(html);
        $('#templateSheetName').text(sheetName ? '(Sheet: ' + sheetName + ')' : '');
        $('#btnDownloadTemplate').attr('href', BASE + 'expeditions/downloadTemplate/' + currentTemplateExpId);
        $('#templatePreview').show();

        // Initialize Tagify on small option inputs
        for (var i = 0; i < columns.length; i++) {
            var col = columns[i];
            if (col.input_type === 'select' && !(col.options_count && col.options_count > 100)) {
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
            // Check if it was previously large
            var $modeInput = $td.find('.col-options-mode');
            if ($modeInput.val() === 'large') {
                $td.find('.btn-manage-options').show();
            } else {
                $td.find('.col-options').show();
                $td.find('.tagify').show();
                $td.find('span.text-muted').hide();
                if (!tagifyInstances[pos]) {
                    $modeInput.val('small');
                    initTagify(pos, []);
                }
            }
        } else {
            $td.find('.col-options').hide();
            $td.find('.tagify').hide();
            $td.find('.btn-manage-options').hide();
            $td.find('span.text-muted').show();
            if (tagifyInstances[pos]) {
                tagifyInstances[pos].removeAllTags();
            }
        }
    });

    // ========================================
    // Options Manager (for large option lists)
    // ========================================
    var optMgrPosition = null;
    var optMgrOptions = [];    // All loaded options
    var optMgrFiltered = [];   // Filtered by search
    var optMgrPage = 0;
    var optMgrSearchTerm = '';
    var optMgrLoading = false;
    var optMgrAllLoaded = false;
    var optMgrAdded = [];      // Newly added options
    var optMgrRemoved = {};    // Removed options (keyed by value)
    var PAGE_SIZE = 50;

    // Open options manager
    $(document).on('click', '.btn-manage-options', function() {
        optMgrPosition = $(this).data('pos');
        optMgrOptions = [];
        optMgrFiltered = [];
        optMgrPage = 0;
        optMgrSearchTerm = '';
        optMgrLoading = false;
        optMgrAllLoaded = false;
        optMgrAdded = [];
        optMgrRemoved = {};

        $('#optMgrSearch').val('');
        $('#optMgrList').empty();
        $('#optMgrNewOption').val('');
        $('#optMgrDuplicateWarning').hide();
        $('#optMgrStats').text('Memuat...');
        $('#optMgrLoadMore').hide();

        $('#optionsManagerModal').modal('show');

        // Load first page from server
        loadOptionsPage(1);
    });

    // Fix: restore scroll on parent modal when nested modal closes
    $('#optionsManagerModal').on('hidden.bs.modal', function() {
        if ($('#templateExpModal').hasClass('show')) {
            $('body').addClass('modal-open');
        }
    });

    function loadOptionsPage(page) {
        if (optMgrLoading) return;
        optMgrLoading = true;
        $('#optMgrLoadingSpinner').show();

        $.get(BASE + 'expeditions/searchTemplateOptions/' + currentTemplateExpId, {
            position: optMgrPosition,
            search: optMgrSearchTerm,
            page: page
        }, function(resp) {
            optMgrLoading = false;
            $('#optMgrLoadingSpinner').hide();

            if (resp.success) {
                // Append options
                for (var i = 0; i < resp.options.length; i++) {
                    var opt = resp.options[i];
                    if (!optMgrRemoved[opt]) {
                        optMgrOptions.push(opt);
                    }
                }

                optMgrPage = resp.page;
                optMgrAllLoaded = !resp.has_more;

                renderOptionsList();
                updateStats(resp.total);

                if (resp.has_more) {
                    $('#optMgrLoadMore').show();
                } else {
                    $('#optMgrLoadMore').hide();
                }
            }
        }).fail(function() {
            optMgrLoading = false;
            $('#optMgrLoadingSpinner').hide();
            toastr.error('Gagal memuat opsi.');
        });
    }

    function renderOptionsList() {
        var $list = $('#optMgrList');
        $list.empty();

        // Show newly added options at top
        for (var i = 0; i < optMgrAdded.length; i++) {
            $list.append(createOptionItem(optMgrAdded[i], true));
        }

        // Show loaded options
        for (var i = 0; i < optMgrOptions.length; i++) {
            if (!optMgrRemoved[optMgrOptions[i]]) {
                $list.append(createOptionItem(optMgrOptions[i], false));
            }
        }

        if (optMgrAdded.length === 0 && optMgrOptions.length === 0) {
            $list.append('<div class="text-center text-muted py-3">Tidak ada opsi ditemukan.</div>');
        }
    }

    function createOptionItem(value, isNew) {
        var badgeHtml = isNew ? ' <span class="badge badge-success ml-1">Baru</span>' : '';
        return '<div class="opt-item d-flex align-items-center justify-content-between py-1 px-2 border-bottom" data-value="' + escapeAttr(value) + '">'
            + '<span class="opt-text">' + escapeHtml(value) + badgeHtml + '</span>'
            + '<button type="button" class="btn btn-xs btn-outline-danger btn-remove-opt ml-2" title="Hapus">'
            + '<i class="fas fa-times"></i>'
            + '</button>'
            + '</div>';
    }

    function updateStats(total) {
        var removed = Object.keys(optMgrRemoved).length;
        var added = optMgrAdded.length;
        var finalTotal = total - removed + added;
        $('#optMgrStats').text(finalTotal + ' opsi' + (optMgrSearchTerm ? ' (filter: "' + optMgrSearchTerm + '")' : ''));
    }

    // Search options
    var searchTimer = null;
    $(document).on('input', '#optMgrSearch', function() {
        var val = $(this).val().trim();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            optMgrSearchTerm = val;
            optMgrOptions = [];
            optMgrPage = 0;
            optMgrAllLoaded = false;
            $('#optMgrList').empty();
            $('#optMgrLoadMore').hide();
            loadOptionsPage(1);
        }, 300);
    });

    // Load more button
    $(document).on('click', '#optMgrLoadMore', function() {
        loadOptionsPage(optMgrPage + 1);
    });

    // Infinite scroll in options list
    $('#optMgrListContainer').on('scroll', function() {
        var el = this;
        if (el.scrollTop + el.clientHeight >= el.scrollHeight - 30) {
            if (!optMgrAllLoaded && !optMgrLoading) {
                loadOptionsPage(optMgrPage + 1);
            }
        }
    });

    // Add new option
    $(document).on('click', '#btnAddOption', function() {
        addNewOption();
    });

    $(document).on('keypress', '#optMgrNewOption', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            addNewOption();
        }
    });

    // Check for duplicates while typing
    $(document).on('input', '#optMgrNewOption', function() {
        var val = $(this).val().trim();
        if (val === '') {
            $('#optMgrDuplicateWarning').hide();
            return;
        }

        // Check against loaded options and added options
        var found = false;
        var lowerVal = val.toLowerCase();

        for (var i = 0; i < optMgrAdded.length; i++) {
            if (optMgrAdded[i].toLowerCase() === lowerVal) {
                found = true;
                break;
            }
        }

        if (!found) {
            for (var i = 0; i < optMgrOptions.length; i++) {
                if (!optMgrRemoved[optMgrOptions[i]] && optMgrOptions[i].toLowerCase() === lowerVal) {
                    found = true;
                    break;
                }
            }
        }

        if (found) {
            $('#optMgrDuplicateWarning').text('Opsi "' + val + '" sudah ada.').show();
        } else {
            $('#optMgrDuplicateWarning').hide();
        }
    });

    function addNewOption() {
        var $input = $('#optMgrNewOption');
        var val = $input.val().trim();
        if (val === '') return;

        // Check duplicate
        var lowerVal = val.toLowerCase();
        for (var i = 0; i < optMgrAdded.length; i++) {
            if (optMgrAdded[i].toLowerCase() === lowerVal) {
                $('#optMgrDuplicateWarning').text('Opsi "' + val + '" sudah ada.').show();
                return;
            }
        }
        for (var i = 0; i < optMgrOptions.length; i++) {
            if (!optMgrRemoved[optMgrOptions[i]] && optMgrOptions[i].toLowerCase() === lowerVal) {
                $('#optMgrDuplicateWarning').text('Opsi "' + val + '" sudah ada.').show();
                return;
            }
        }

        // If was previously removed, un-remove it
        if (optMgrRemoved[val]) {
            delete optMgrRemoved[val];
        } else {
            optMgrAdded.push(val);
        }

        $input.val('');
        $('#optMgrDuplicateWarning').hide();
        renderOptionsList();
        toastr.info('Opsi "' + val + '" ditambahkan.');
    }

    // Remove option
    $(document).on('click', '.btn-remove-opt', function() {
        var $item = $(this).closest('.opt-item');
        var val = $item.data('value');

        // Check if it was newly added
        var addedIdx = optMgrAdded.indexOf(val);
        if (addedIdx !== -1) {
            optMgrAdded.splice(addedIdx, 1);
        } else {
            optMgrRemoved[val] = true;
        }

        $item.fadeOut(200, function() {
            $(this).remove();
        });
    });

    // Save options from manager back to column data
    $(document).on('click', '#btnSaveOptions', function() {
        // Build final options: original (minus removed) + added
        // We need to load ALL options to build the final list, not just the currently visible ones
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        // Load ALL options from server first, then apply changes
        loadAllOptionsAndSave($btn);
    });

    function loadAllOptionsAndSave($btn) {
        // Load all options (no search, no pagination limit)
        $.get(BASE + 'expeditions/searchTemplateOptions/' + currentTemplateExpId, {
            position: optMgrPosition,
            search: '',
            page: 1
        }, function(resp) {
            if (!resp.success) {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perubahan');
                toastr.error('Gagal memuat opsi.');
                return;
            }

            var totalPages = Math.ceil(resp.total / 50);
            if (totalPages <= 1) {
                finalizeSave(resp.options, $btn);
                return;
            }

            // Need to load remaining pages
            var allOptions = resp.options.slice();
            var loaded = 1;

            for (var p = 2; p <= totalPages; p++) {
                (function(page) {
                    $.get(BASE + 'expeditions/searchTemplateOptions/' + currentTemplateExpId, {
                        position: optMgrPosition,
                        search: '',
                        page: page
                    }, function(r2) {
                        if (r2.success) {
                            allOptions = allOptions.concat(r2.options);
                        }
                        loaded++;
                        if (loaded >= totalPages) {
                            finalizeSave(allOptions, $btn);
                        }
                    });
                })(p);
            }
        }).fail(function() {
            $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perubahan');
            toastr.error('Gagal memuat opsi.');
        });
    }

    function finalizeSave(allOriginalOptions, $btn) {
        // Apply removals
        var finalOptions = [];
        for (var i = 0; i < allOriginalOptions.length; i++) {
            if (!optMgrRemoved[allOriginalOptions[i]]) {
                finalOptions.push(allOriginalOptions[i]);
            }
        }

        // Apply additions
        for (var i = 0; i < optMgrAdded.length; i++) {
            finalOptions.push(optMgrAdded[i]);
        }

        // Update the column data
        largeOptionsData[optMgrPosition] = finalOptions;

        // Update the button count
        var $btnManage = $('.btn-manage-options[data-pos="' + optMgrPosition + '"]');
        $btnManage.find('.options-count').text(finalOptions.length);

        $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perubahan');
        $('#optionsManagerModal').modal('hide');
        toastr.success('Opsi berhasil diperbarui. Klik "Simpan Perubahan Kolom" untuk menyimpan ke server.');
    }

    // ========================================
    // Save template column changes (main save)
    // ========================================
    $(document).on('click', '#btnSaveTemplateColumns', function() {
        var columns = [];
        $('#templateColumnsBody tr').each(function() {
            var $row = $(this);
            var pos = parseInt($row.data('position'));
            var inputType = $row.find('.col-input-type').val();
            var options = null;
            var optionsMode = $row.find('.col-options-mode').val();

            if (inputType === 'select') {
                if (optionsMode === 'large' && largeOptionsData[pos]) {
                    // Large options - use data from options manager
                    options = largeOptionsData[pos].join(',');
                } else if (optionsMode === 'large' && !largeOptionsData[pos]) {
                    // Large options not edited - send null to keep original
                    options = undefined;
                } else if (tagifyInstances[pos]) {
                    // Small options - use Tagify data
                    var tags = tagifyInstances[pos].value;
                    options = tags.map(function(t) { return t.value; }).join(',');
                    if (!options) options = null;
                }
            }

            var col = {
                position: pos,
                clean_name: $row.find('.col-clean-name').val(),
                is_required: $row.find('.col-required').is(':checked'),
                input_type: inputType
            };

            if (options !== undefined) {
                col.options = options;
            }

            columns.push(col);
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
