$(".thumbnail").click(function() {
    var $selected = $(this);
    var $name = $selected.siblings('p').text();
    var $input = $selected.siblings('input');

    if ($selected.hasClass('outline')) {
        $selected.removeClass('outline');
        $input.val('');
    } else {
        $selected.addClass('outline');
        $input.val($name);
    }
});

$("#confirm_button").click(function() {
    var values = $("input[name='pics[]']").map(function(){
        var value = $(this).val();
        if (value != '') {
            return value;
        }
    }).get();
    var count = values.length;
    if (count == 1) {
      $("#pictures").val(values);
    } else {
      $("#pictures").val(count + " pictures selected");
    }
});

$("input:file").change(function (){
    var count = document.getElementById('uploads').files.length;
    if (count == 1) {
        var fileName = $(this).val();
        var normalized = fileName.replace(/\\/g, '/');
        var split = normalized.split('/');
        $("#pictures").val(split[split.length - 1]);
    } else {
        $("#pictures").val(count + " pictures to upload");
    }

});
