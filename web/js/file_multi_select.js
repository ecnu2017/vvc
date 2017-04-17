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

$("#confirm_button_1").click(function() {
    var values = $("input[name='pics_1[]']").map(function(){
        var value = $(this).val();
        if (value != '') {
            return value;
        }
    }).get();
    var count = values.length;
    if (count == 1) {
      $("#pictures_1").val(values);
    } else {
      $("#pictures_1").val(count + " pictures selected");
    }
});

$("#uploads_1").change(function (){
    $("input[name='pics_1[]']").map(function(){
        $(this).val('');
    });
    var count = document.getElementById('uploads_1').files.length;
    if (count == 1) {
        var fileName = $(this).val();
        var normalized = fileName.replace(/\\/g, '/');
        var split = normalized.split('/');
        $("#pictures_1").val(split[split.length - 1]);
    } else {
        $("#pictures_1").val(count + " pictures to upload");
    }

});

$("#confirm_button_2").click(function() {
    var values = $("input[name='pics_2[]']").map(function(){
        var value = $(this).val();
        if (value != '') {
            return value;
        }
    }).get();
    var count = values.length;
    if (count == 1) {
      $("#pictures_2").val(values);
    } else {
      $("#pictures_2").val(count + " pictures selected");
    }
});

$("#uploads_2").change(function (){
    var count = document.getElementById('uploads_2').files.length;
    if (count == 1) {
        var fileName = $(this).val();
        var normalized = fileName.replace(/\\/g, '/');
        var split = normalized.split('/');
        $("#pictures_2").val(split[split.length - 1]);
    } else {
        $("#pictures_2").val(count + " pictures to upload");
    }

});

$("#confirm_button_3").click(function() {
    var values = $("input[name='pics_3[]']").map(function(){
        var value = $(this).val();
        if (value != '') {
            return value;
        }
    }).get();
    var count = values.length;
    if (count == 1) {
      $("#pictures_3").val(values);
    } else {
      $("#pictures_3").val(count + " pictures selected");
    }
});

$("#uploads_3").change(function (){
    var count = document.getElementById('uploads_3').files.length;
    if (count == 1) {
        var fileName = $(this).val();
        var normalized = fileName.replace(/\\/g, '/');
        var split = normalized.split('/');
        $("#pictures_3").val(split[split.length - 1]);
    } else {
        $("#pictures_3").val(count + " pictures to upload");
    }

});

$("#confirm_button_4").click(function() {
    var values = $("input[name='pics_4[]']").map(function(){
        var value = $(this).val();
        if (value != '') {
            return value;
        }
    }).get();
    var count = values.length;
    if (count == 1) {
      $("#pictures_4").val(values);
    } else {
      $("#pictures_4").val(count + " pictures selected");
    }
});

$("#uploads_4").change(function (){
    var count = document.getElementById('uploads_4').files.length;
    if (count == 1) {
        var fileName = $(this).val();
        var normalized = fileName.replace(/\\/g, '/');
        var split = normalized.split('/');
        $("#pictures_4").val(split[split.length - 1]);
    } else {
        $("#pictures_4").val(count + " pictures to upload");
    }

});
