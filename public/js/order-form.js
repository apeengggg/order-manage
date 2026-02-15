/**
 * order-form.js - Dynamic order form based on expedition template
 */
$(function() {
    var BASE = $('meta[name="base-url"]').attr('content') || '/';
    var existingValues = window.existingExtraFields || {};
    var isEditMode = Object.keys(existingValues).length > 0;

    // ========================================
    // Expedition change → load template fields
    // ========================================
    $('#expedition_select').on('change', function() {
        var expId = $(this).val();
        var $option = $(this).find('option:selected');

        $('#template-fields-container').hide().empty();
        $('#no-template-warning').hide();
        $('#submit-section').hide();

        if (!expId) return;

        // Check if expedition has template
        var hasTemplate = $option.data('has-template');
        if (hasTemplate === 0 || hasTemplate === '0') {
            $('#no-template-warning').show();
            return;
        }

        // Load template fields via AJAX
        $('#template-loading').show();

        $.get(BASE + 'orders/getTemplateFields/' + expId, function(resp) {
            $('#template-loading').hide();

            if (resp.success && resp.columns && resp.columns.length > 0) {
                renderTemplateFields(resp.columns);
                $('#template-fields-container').show();
                $('#submit-section').show();
            } else {
                $('#no-template-warning').show();
            }
        }).fail(function() {
            $('#template-loading').hide();
            toastr.error('Gagal memuat template.');
        });
    });

    // ========================================
    // Render template fields dynamically
    // ========================================
    function renderTemplateFields(columns) {
        var html = '';

        // Split columns into two groups for two-column layout
        var half = Math.ceil(columns.length / 2);
        var leftCols = columns.slice(0, half);
        var rightCols = columns.slice(half);

        html += '<div class="row">';

        // Left column
        html += '<div class="col-md-6">';
        html += '<div class="card card-info">';
        html += '<div class="card-header"><h3 class="card-title"><i class="fas fa-file-alt mr-1"></i> Data Template (1/' + Math.ceil(columns.length / half) + ')</h3></div>';
        html += '<div class="card-body">';
        html += renderFieldGroup(leftCols);
        html += '</div></div></div>';

        // Right column
        html += '<div class="col-md-6">';
        html += '<div class="card card-info">';
        html += '<div class="card-header"><h3 class="card-title"><i class="fas fa-file-alt mr-1"></i> Data Template (2/' + Math.ceil(columns.length / half) + ')</h3></div>';
        html += '<div class="card-body">';
        html += renderFieldGroup(rightCols);
        html += '</div></div></div>';

        html += '</div>';

        $('#template-fields-container').html(html);

        // Initialize Select2 on dynamically created selects
        $('#template-fields-container .select2-dynamic').each(function() {
            var opts = {
                theme: 'bootstrap4',
                allowClear: true,
                placeholder: 'Pilih...'
            };
            // For large option lists, require typing before showing results
            if ($(this).find('option').length > 100) {
                opts.minimumInputLength = 2;
            }
            $(this).select2(opts);
        });
    }

    function renderFieldGroup(cols) {
        var html = '';
        for (var i = 0; i < cols.length; i++) {
            var col = cols[i];
            var fieldName = 'tpl_' + col.position;
            var isRequired = col.is_required;
            var requiredAttr = isRequired ? ' required' : '';
            var asterisk = isRequired ? ' <span class="text-danger">*</span>' : '';

            // Check for existing value (edit mode)
            var existingVal = '';
            if (existingValues[col.name] !== undefined) {
                existingVal = existingValues[col.name];
            }

            html += '<div class="form-group">';
            html += '<label>' + escapeHtml(col.clean_name) + asterisk + '</label>';

            if (col.input_type === 'select' && col.options && col.options.length > 0) {
                // Select dropdown - always use Select2 for searchability
                html += '<select name="' + fieldName + '" class="form-control select2-dynamic"' + requiredAttr + '>';
                html += '<option value="">-- Pilih --</option>';
                for (var j = 0; j < col.options.length; j++) {
                    var opt = col.options[j];
                    var selected = (existingVal === opt) ? ' selected' : '';
                    html += '<option value="' + escapeHtml(opt) + '"' + selected + '>' + escapeHtml(opt) + '</option>';
                }
                html += '</select>';
            } else {
                // Text input
                html += '<input type="text" name="' + fieldName + '" class="form-control" value="' + escapeHtml(existingVal) + '" placeholder="' + escapeHtml(col.clean_name) + '"' + requiredAttr + '>';
            }

            html += '</div>';
        }
        return html;
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ========================================
    // Fill dummy data
    // ========================================
    var dummyData = {
        'nama penerima': 'Budi Santoso',
        'penerima': 'Budi Santoso',
        'nama pengirim': 'Toko Online ABC',
        'pengirim': 'Toko Online ABC',
        'nomor telepon penerima': '081234567890',
        'no handphone penerima': '081234567890',
        'no.hp penerima': '081234567890',
        'telpon1_penerima': '081234567890',
        'kontak penerima': '081234567890',
        'no handphone': '081234567890',
        'kontak': '081234567890',
        'nomor telepon pengirim': '089876543210',
        'no handphone pengirim': '089876543210',
        'telpon1_pengirim': '089876543210',
        'alamat lengkap': 'Jl. Merdeka No. 123 RT 01/02',
        'alamat penerima': 'Jl. Merdeka No. 123 RT 01/02',
        'alamat_penerima_1': 'Jl. Merdeka No. 123 RT 01/02',
        'detail address': 'Jl. Merdeka No. 123 RT 01/02',
        'alamat pengirim': 'Jl. Sudirman No. 456',
        'alamat_pengirim': 'Jl. Sudirman No. 456',
        'nama barang': 'Kaos Polos Hitam',
        'deskripsi barang': 'Kaos Polos Hitam',
        'deskripsi_barang': 'Kaos Polos Hitam',
        'item name': 'Kaos Polos Hitam',
        'jumlah barang': '1',
        'jumlah': '1',
        'koli': '1',
        'item quantity': '1',
        'harga barang': '50000',
        'harga paket': '50000',
        'nilai_barang': '50000',
        'parcel value': '50000',
        'berat': '500',
        'berat paket': '500',
        'weight': '500',
        'berat_barang': '500',
        'catatan': 'Test dummy data',
        'notes': 'Test dummy data',
        'instruksi khusus': 'Jangan dibanting',
        'keterangan': 'Barang mudah pecah',
    };

    $(document).on('click', '#btnFillDummy', function() {
        $('#template-fields-container').find('input[type="text"], select').each(function() {
            var $field = $(this);
            var $label = $field.closest('.form-group').find('label');
            var labelText = $label.text().replace('*', '').trim().toLowerCase();

            if ($field.is('select')) {
                // For select: pick first non-empty option, or random option
                var $opts = $field.find('option').filter(function() { return $(this).val() !== ''; });
                if ($opts.length > 0) {
                    var randomIdx = Math.floor(Math.random() * $opts.length);
                    var val = $opts.eq(randomIdx).val();
                    $field.val(val);
                    // Update Select2 if initialized
                    if ($field.hasClass('select2-dynamic')) {
                        $field.trigger('change.select2');
                    }
                }
            } else {
                // For text: try matching from dummyData, otherwise generate generic value
                var filled = false;
                for (var key in dummyData) {
                    if (labelText === key || labelText.indexOf(key) !== -1) {
                        $field.val(dummyData[key]);
                        filled = true;
                        break;
                    }
                }
                if (!filled && $field.val() === '') {
                    $field.val('Test ' + $label.text().replace('*', '').trim());
                }
            }
        });

        toastr.info('Dummy data berhasil diisi.');
    });

    // ========================================
    // Auto-trigger on page load (edit mode)
    // ========================================
    if (isEditMode && $('#expedition_select').val()) {
        $('#expedition_select').trigger('change');
    }
});
