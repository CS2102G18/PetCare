$(function() {
    $(".autosize").autogrow({horizontal: false});

    $('#start-datetimepicker').datetimepicker();
    $('#end-datetimepicker').datetimepicker();

    $('.panel-footer .btn-default').on('click', function() {
        window.location = '/TaskSourcing/tasklist.php';
    })
});

