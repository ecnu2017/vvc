$(function() {
    $('[data-toggle="popover"]').popover('show');
    $('[data-toggle="popover"]').popover('hide');
});

$('.pop-toggle').click(function () {
    var $pop = $(this).children('div');
    $('[data-toggle="popover"]').not($pop).popover('hide');
    $pop.popover('toggle');
});
