/**
 * admin-export.js - Admin export order page with download progress
 */
$(function() {
    function updateCount() {
        var count = $('.order-check:checked').length;
        $('#selectedCount').text(count);
        $('#btnExport').prop('disabled', count === 0);
    }

    $('#checkAll').on('change', function() {
        $('.order-check').prop('checked', this.checked);
        updateCount();
    });

    $('#selectAll').on('click', function() {
        $('.order-check').prop('checked', true);
        $('#checkAll').prop('checked', true);
        updateCount();
    });

    $('#deselectAll').on('click', function() {
        $('.order-check').prop('checked', false);
        $('#checkAll').prop('checked', false);
        updateCount();
    });

    $('.order-check').on('change', updateCount);

    // ========================================
    // Export with progress tracking
    // ========================================
    function showProgress() {
        $('#exportProgressBar').css('width', '0%');
        $('#exportProgressText').text('0%');
        $('#exportTitle').text('Memproses Export...');
        $('#exportSubtitle').text('Menyiapkan file, mohon tunggu');
        $('#exportInfo').text('Memproses data...');
        $('#exportProgressBar').removeClass('bg-danger').addClass('bg-success progress-bar-animated');
        $('#exportOverlay').css('display', 'flex');
    }

    function updateProgress(percent, info) {
        percent = Math.min(Math.round(percent), 100);
        $('#exportProgressBar').css('width', percent + '%');
        $('#exportProgressText').text(percent + '%');
        if (info) $('#exportInfo').text(info);
    }

    function hideProgress() {
        $('#exportOverlay').hide();
    }

    function showError(msg) {
        $('#exportTitle').text('Export Gagal');
        $('#exportSubtitle').text(msg || 'Terjadi kesalahan');
        $('#exportProgressBar').removeClass('bg-success progress-bar-animated').addClass('bg-danger');
        $('#exportProgressBar').css('width', '100%');
        $('#exportProgressText').text('Error');
        $('#exportInfo').text('Klik dimana saja untuk menutup');
        $('#exportOverlay').one('click', function() { hideProgress(); });
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        var k = 1024;
        var sizes = ['B', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    function doExport(form) {
        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();

        showProgress();

        // Simulate initial processing phase
        var processingInterval = null;
        var processingPercent = 0;
        processingInterval = setInterval(function() {
            processingPercent += 2;
            if (processingPercent > 30) {
                clearInterval(processingInterval);
                return;
            }
            updateProgress(processingPercent, 'Menyiapkan file export...');
        }, 200);

        xhr.open('POST', $(form).attr('action'), true);
        xhr.responseType = 'blob';
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        // Track download progress
        xhr.onprogress = function(e) {
            clearInterval(processingInterval);
            if (e.lengthComputable) {
                var pct = 30 + (e.loaded / e.total) * 65;
                updateProgress(pct, 'Mengunduh... ' + formatBytes(e.loaded) + ' / ' + formatBytes(e.total));
            } else {
                updateProgress(60, 'Mengunduh... ' + formatBytes(e.loaded));
            }
        };

        xhr.onload = function() {
            clearInterval(processingInterval);

            if (xhr.status === 200) {
                var contentType = xhr.getResponseHeader('Content-Type') || '';

                // Check if response is JSON (error from server)
                if (contentType.indexOf('application/json') !== -1) {
                    var reader = new FileReader();
                    reader.onload = function() {
                        try {
                            var resp = JSON.parse(reader.result);
                            showError(resp.message || 'Export gagal.');
                        } catch (ex) {
                            showError('Export gagal.');
                        }
                    };
                    reader.readAsText(xhr.response);
                    return;
                }

                updateProgress(98, 'Menyimpan file...');

                // Extract filename from Content-Disposition
                var disposition = xhr.getResponseHeader('Content-Disposition') || '';
                var filename = 'export_orders.xlsx';
                var match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                if (match && match[1]) {
                    filename = match[1].replace(/['"]/g, '');
                }

                // Create blob and trigger download
                var blob = xhr.response;
                var url = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);

                updateProgress(100, 'Selesai! File berhasil didownload.');
                $('#exportTitle').text('Export Berhasil!');
                $('#exportSubtitle').text(filename);
                $('#exportProgressBar').removeClass('progress-bar-animated');

                // Reload page after short delay to show flash message
                setTimeout(function() {
                    hideProgress();
                    window.location.href = window.location.pathname + window.location.search;
                }, 1500);
            } else {
                showError('Server error (HTTP ' + xhr.status + ')');
            }
        };

        xhr.onerror = function() {
            clearInterval(processingInterval);
            showError('Koneksi gagal. Periksa koneksi internet Anda.');
        };

        xhr.ontimeout = function() {
            clearInterval(processingInterval);
            showError('Request timeout. Coba export ulang.');
        };

        xhr.timeout = 300000; // 5 minutes
        xhr.send(formData);
    }

    $('#exportForm').on('submit', function(e) {
        e.preventDefault();
        var count = $('.order-check:checked').length;
        if (count === 0) {
            App.error('Pilih minimal satu order untuk diexport.');
            return false;
        }

        var form = this;
        App.confirmAction({
            title: 'Export ' + count + ' order?',
            text: 'Order yang sudah diexport tidak bisa diedit/dihapus oleh CS.',
            confirmText: 'Ya, Export!',
            confirmColor: '#28a745',
            onConfirm: function() { doExport(form); }
        });
    });

    updateCount();
});
