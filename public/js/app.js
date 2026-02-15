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
    // Dark Mode (per-user, localStorage)
    // ========================================
    function applyDarkMode(enabled) {
        if (enabled) {
            $('body').addClass('dark-mode');
            $('#mainNavbar').removeClass('navbar-white navbar-light').addClass('navbar-dark');
            $('#btnToggleDarkMode i').removeClass('fa-moon').addClass('fa-sun');
        } else {
            $('body').removeClass('dark-mode');
            $('#mainNavbar').addClass('navbar-white navbar-light').removeClass('navbar-dark');
            $('#btnToggleDarkMode i').removeClass('fa-sun').addClass('fa-moon');
        }
    }

    function toggleDarkMode() {
        var isDark = localStorage.getItem('dark_mode') === '1';
        var newVal = isDark ? '0' : '1';
        localStorage.setItem('dark_mode', newVal);
        applyDarkMode(!isDark);
    }

    // ========================================
    // Global Init on DOM Ready
    // ========================================
    $(function() {
        // Apply dark mode from localStorage
        var isDark = localStorage.getItem('dark_mode') === '1';
        applyDarkMode(isDark);

        // Dark mode toggle button
        $('#btnToggleDarkMode').on('click', function(e) {
            e.preventDefault();
            toggleDarkMode();
        });
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

        // Global form confirmation handler for POST forms
        // Detects action type from URL and shows appropriate SweetAlert
        $(document).on('submit', 'form[method="POST"]', function(e) {
            var $form = $(this);
            if ($form.data('confirmed')) {
                $form.removeData('confirmed');
                return;
            }

            var action = ($form.attr('action') || '').toLowerCase();
            var formId = ($form.attr('id') || '').toLowerCase();

            // Skip: login, export, delete forms (delete has its own handler)
            if (action.indexOf('auth/login') !== -1) return;
            if (action.indexOf('/export') !== -1) return;
            if (action.indexOf('/delete') !== -1) return;

            // Determine action type
            var conf = { title: 'Konfirmasi', text: '', icon: 'question', btnText: 'Ya, Lanjutkan', btnColor: '#007bff' };

            if (action.indexOf('/create') !== -1) {
                conf.title = 'Tambah Data';
                conf.text = 'Apakah Anda yakin ingin menyimpan data baru?';
                conf.icon = 'question';
                conf.btnText = 'Ya, Simpan';
                conf.btnColor = '#007bff';
            } else if (action.indexOf('/edit') !== -1 || formId.indexOf('edit') !== -1) {
                conf.title = 'Simpan Perubahan';
                conf.text = 'Apakah Anda yakin ingin menyimpan perubahan?';
                conf.icon = 'question';
                conf.btnText = 'Ya, Simpan';
                conf.btnColor = '#e0a800';
            } else if (action.indexOf('/update') !== -1 || formId.indexOf('setting') !== -1 || formId.indexOf('permission') !== -1) {
                conf.title = 'Simpan Perubahan';
                conf.text = 'Apakah Anda yakin ingin menyimpan perubahan?';
                conf.icon = 'question';
                conf.btnText = 'Ya, Simpan';
                conf.btnColor = '#e0a800';
            } else if (formId.indexOf('changepwd') !== -1 || formId.indexOf('password') !== -1) {
                conf.title = 'Ubah Password';
                conf.text = 'Apakah Anda yakin ingin mengubah password?';
                conf.icon = 'warning';
                conf.btnText = 'Ya, Ubah';
                conf.btnColor = '#e0a800';
            } else {
                // Unknown POST form, skip confirmation
                return;
            }

            e.preventDefault();

            Swal.fire({
                title: conf.title,
                text: conf.text,
                icon: conf.icon,
                showCancelButton: true,
                confirmButtonColor: conf.btnColor,
                cancelButtonColor: '#6c757d',
                confirmButtonText: conf.btnText,
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $form.data('confirmed', true);
                    $form.submit();
                }
            });
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
