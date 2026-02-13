/**
 * permissions.js - Permission management page
 */
$(function() {
    // Toggle all checkboxes in a row
    $('.toggle-all-row').on('change', function() {
        var row = $(this).data('row');
        $('input.perm-checkbox[data-row="' + row + '"]').prop('checked', this.checked);
    });

    // Update "all" checkbox when individual changes
    $('.perm-checkbox').on('change', function() {
        var row = $(this).data('row');
        var total = $('input.perm-checkbox[data-row="' + row + '"]').length;
        var checked = $('input.perm-checkbox[data-row="' + row + '"]:checked').length;
        $('input.toggle-all-row[data-row="' + row + '"]').prop('checked', total === checked);
    });

    // Init "all" checkbox state
    $('.toggle-all-row').each(function() {
        var row = $(this).data('row');
        var total = $('input.perm-checkbox[data-row="' + row + '"]').length;
        var checked = $('input.perm-checkbox[data-row="' + row + '"]:checked').length;
        $(this).prop('checked', total === checked && total > 0);
    });

    // Confirm save
    $('.permission-form').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        var role = $(form).find('input[name="role"]').val().toUpperCase();
        App.confirmAction({
            title: 'Simpan Permission?',
            text: 'Permission untuk role ' + role + ' akan diupdate.',
            confirmText: 'Ya, Simpan!',
            confirmColor: '#007bff',
            onConfirm: function() { form.submit(); }
        });
    });
});
