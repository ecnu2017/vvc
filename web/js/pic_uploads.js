$(".thumbnail").click(function() {
    var $selected = $(this);
    var $name = $selected.siblings('div').children('#picName').text();
    var $input = $selected.siblings('input');
    var $id = $selected.parents('.hidable').attr('id');

    if ($selected.hasClass('outline')) {
        $selected.removeClass('outline');
        $input.val('');
    } else {
        $selected.addClass('outline');
        $input.val($name);
    }
});

$('input[name="uploadPics"]').on('click', function(e) {
	$('#picChooser').trigger('click');
});

$("#picChooser").change(function (){
    var $form = $('#picsForm');
    $form.attr('action', '/admin/uploads/pictures/upload');
    $form.trigger('submit');
});

$('input[name="addPics"]').on('click', function(e) {
	var $form = $('#picsForm');
    $form.attr('action', '/admin/uploads/pictures/add');
	e.preventDefault();
	$('#add_to_illness').modal()
	.one('click', '#confirm_button', function(e) {
		$form.trigger('submit');
	});
});

$('input[name="deletePics"]').on('click', function(e) {
	var $form = $('#picsForm');
    $form.attr('action', '/admin/uploads/pictures/delete');
	e.preventDefault();
	$('#delete_pics').modal()
	.one('click', '#confirm_button', function(e) {
		$form.trigger('submit');
	});
});
