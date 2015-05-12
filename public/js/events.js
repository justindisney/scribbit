$(document).ready(function () {
    $.fn.editable.defaults.mode = 'inline';
    $.fn.editable.defaults.ajaxOptions = {type: "PUT"};

    $('li.scribbit h3 a').editable({
        type: 'text',
        pk: 1,
        url: '/scribbit',
        title: 'Enter username',
        inputclass: 'input-lg',
        toggle: 'manual'
    });

    $('li.scribbit h3 a.edit').click(function (e) {
        e.stopPropagation();
        var a = $(this).parents("li").find('h3 a').not(".edit");

        $(a).editable('toggle');
        $(this).hide();
    });

    $('.editable').on('hidden', function (e, reason) {
        if (reason === 'save' || reason === 'cancel') {
            $('a.edit').show();
        }
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

    var markdownConverter = new Showdown.converter();

    var editor = ace.edit("bit-editor");
    editor.setTheme("ace/theme/tomorrow");
    editor.getSession().setMode("ace/mode/markdown");

    $('#bit-editor').keyup(function () {
        $('#bit-preview').html(markdownConverter.makeHtml(editor.getValue()));
    });

    $('#bit-editor').keyup();

    $('.markdown').each(function () {
        $(this).html(markdownConverter.makeHtml($(this).data('source')));
    });
});