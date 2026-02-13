/**
 * admin-export.js - Admin export order page
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

    $('#exportForm').on('submit', function(e) {
        var count = $('.order-check:checked').length;
        if (count === 0) {
            e.preventDefault();
            App.error('Pilih minimal satu order untuk diexport.');
            return false;
        }
        e.preventDefault();
        var form = this;
        App.confirmAction({
            title: 'Export ' + count + ' order?',
            text: 'Order yang sudah diexport tidak bisa diedit/dihapus oleh CS.',
            confirmText: 'Ya, Export!',
            confirmColor: '#28a745',
            onConfirm: function() { form.submit(); }
        });
    });

    updateCount();
});
