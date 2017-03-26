$('input[name="uploadDrugPics"]').on('click', function(e) {
	$('#drugPicChooser').trigger('click');
});

$("#drugPicChooser").change(function (){
    var $form = $('#drugPicsForm');
    $form.attr('action', '/admin/uploads/drugs/upload');
    $form.trigger('submit');
});

$('input[name="deleteDrugPics"]').on('click', function(e) {
	var $form = $('#drugPicsForm');
    $form.attr('action', '/admin/uploads/drugs/delete');
	e.preventDefault();
	$('#delete_drug_pics').modal()
	.one('click', '#confirm_button', function(e) {
		$form.trigger('submit');
	});
});
