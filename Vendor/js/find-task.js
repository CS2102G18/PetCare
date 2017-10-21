$(function() {
    $(".autosize").autogrow({horizontal: false});

    $('#start-datetimepicker').datetimepicker();
    $('#end-datetimepicker').datetimepicker();
});

function viewTask(element) {
	let ele = $(element)
	$('#task_id').val($(ele).attr('tid'))
	$('#viewTask').submit()
}