/**
 * app.js - Global utility functions & initializations
 */
var App = (function($) {
    'use strict';

    // ========================================
    // Toastr Defaults
    // ========================================
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 3000,
        showMethod: 'slideDown',
        hideMethod: 'slideUp'
    };

    // ========================================
    // Toast / Alert Helpers
    // ========================================
    function success(message, title) {
        toastr.success(message, title || 'Berhasil');
    }

    function info(message, title) {
        toastr.info(message, title || 'Info');
    }

    function warning(message, title) {
        toastr.warning(message, title || 'Peringatan');
    }

    function error(message, title) {
        Swal.fire({
            icon: 'error',
            title: title || 'Error',
            text: message,
            confirmButtonColor: '#d33'
        });
    }

    // ========================================
    // SweetAlert Helpers
    // ========================================
    function confirm(options) {
        var defaults = {
            title: 'Yakin?',
            text: '',
            icon: 'warning',
            confirmText: 'Ya',
            cancelText: 'Batal',
            confirmColor: '#d33',
            onConfirm: function() {}
        };
        var opts = $.extend({}, defaults, options);

        return Swal.fire({
            title: opts.title,
            text: opts.text,
            icon: opts.icon,
            showCancelButton: true,
            confirmButtonColor: opts.confirmColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: opts.confirmText,
            cancelButtonText: opts.cancelText
        }).then(function(result) {
            if (result.isConfirmed && typeof opts.onConfirm === 'function') {
                opts.onConfirm();
            }
            return result;
        });
    }

    function confirmDelete(form) {
        confirm({
            title: 'Yakin hapus?',
            text: 'Data yang dihapus tidak bisa dikembalikan!',
            icon: 'warning',
            confirmText: 'Ya, Hapus!',
            confirmColor: '#d33',
            onConfirm: function() {
                form.submit();
            }
        });
    }

    function confirmAction(options) {
        return confirm({
            title: options.title || 'Konfirmasi',
            text: options.text || '',
            icon: 'question',
            confirmText: options.confirmText || 'Ya, Lanjutkan!',
            confirmColor: options.confirmColor || '#007bff',
            onConfirm: options.onConfirm || function() {}
        });
    }

    // ========================================
    // DataTable Helper
    // ========================================
    function initDataTable(selector, options) {
        var defaults = {
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            }
        };
        return $(selector).DataTable($.extend({}, defaults, options));
    }

    // ========================================
    // Select2 Helper
    // ========================================
    function initSelect2(selector, options) {
        var defaults = { theme: 'bootstrap4' };
        $(selector).select2($.extend({}, defaults, options));
    }

    // ========================================
    // Price Calculator
    // ========================================
    function initPriceCalculator(qtySelector, priceSelector, displaySelector) {
        function calc() {
            var qty = parseInt($(qtySelector).val()) || 0;
            var price = parseFloat($(priceSelector).val()) || 0;
            var total = qty * price;
            $(displaySelector).text('Rp ' + total.toLocaleString('id-ID'));
        }
        $(qtySelector + ', ' + priceSelector).on('input', calc);
        calc();
    }

    // ========================================
    // Flash Message Handler (called from PHP)
    // ========================================
    function handleFlash(type, message) {
        if (!message) return;
        if (type === 'success') {
            success(message);
        } else if (type === 'error') {
            error(message);
        } else if (type === 'warning') {
            warning(message);
        } else if (type === 'info') {
            info(message);
        }
    }

    // ========================================
    // Global Init on DOM Ready
    // ========================================
    $(function() {
        // Init DataTable if exists
        if ($('#dataTable').length) {
            initDataTable('#dataTable');
        }

        // Init Select2
        initSelect2('.select2');

        // Global delete button handler
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            confirmDelete($(this).closest('form'));
        });

        // Init price calculator if fields exist
        if ($('#qty').length && $('#price').length) {
            initPriceCalculator('#qty', '#price', '#total_display');
        }
    });

    // ========================================
    // Public API
    // ========================================
    return {
        success: success,
        info: info,
        warning: warning,
        error: error,
        confirm: confirm,
        confirmDelete: confirmDelete,
        confirmAction: confirmAction,
        initDataTable: initDataTable,
        initSelect2: initSelect2,
        initPriceCalculator: initPriceCalculator,
        handleFlash: handleFlash
    };

})(jQuery);
