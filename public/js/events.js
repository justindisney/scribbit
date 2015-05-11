$(document).ready(function () {
    $.fn.editable.defaults.mode = 'inline';
    $.fn.editable.defaults.ajaxOptions = {type: "PUT"};

    $('li.scribbit h3 a').editable({
        type: 'text',
        pk: 1,
        url: '/scribbit',
        title: 'Enter username'
    });

    $("li.scribbit .delete").click(function () {
        var scribbit = $(this).parents("li").data("scribbit");

        $.ajax({
            type: 'DELETE',
            url: '/scribbit/' + scribbit,
            success: function (data, textStatus, jqXHR) {
                window.location.assign("/");
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(scribbit + ' delete failed');
            }
        });
    });
});