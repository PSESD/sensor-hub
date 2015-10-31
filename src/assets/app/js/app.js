$preparer.add(function(context) {
    $('[data-datepicker]').each(function() {
        var initial = $(this).data('datepicker');
        $(this).removeAttr('data-datepicker');
        $(this).datepicker(initial);
    });
});