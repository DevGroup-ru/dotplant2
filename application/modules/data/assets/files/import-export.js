$('#select_all').on('click', function () {
    $('.fields-to-import input:checkbox').prop('checked', true);
});
$('#unselect_all').on('click', function () {
    $('.fields-to-import input:checkbox').prop('checked', false);
});