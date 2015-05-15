$(document).ready(function () {
    $.fn.editable.defaults.mode = 'inline';
    $.fn.editable.defaults.ajaxOptions = {type: "PUT"};

    $('li.scribbit h3 a').editable({
        type: 'text',
        pk: 1,
        url: '/scribbit',
        title: 'Enter new name',
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
        bootbox.confirm("Delete this entire scribbit?", function (result) {
            if (result) {
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
            }
        });
    });

    var markdownConverter = new Showdown.converter();

    if ($('#bit-editor').length) {
        var editor = ace.edit("bit-editor");
        editor.setTheme("ace/theme/tomorrow");
        editor.getSession().setMode("ace/mode/markdown");
        editor.getSession().setUseWrapMode(true);
        editor.setValue("Write some **markdown** here...");
        editor.clearSelection();
        editor.$blockScrolling = Infinity;
        editor.focus();
    }

    $('#bit-editor').keyup(function () {
        $('#bit-preview').html(markdownConverter.makeHtml(editor.getValue()));
    });

    $('#bit-editor').keyup();

    $('.markdown').each(function () {
        $(this).html(markdownConverter.makeHtml($(this).data('source')));
    });

    $("div.editor-buttons button.cancel").click(function () {
        editor.setValue("Write some **markdown** here...");
        $('#bit-editor').keyup();
        editor.clearSelection();
        editor.focus();
    });

    $("div.editor-buttons button.save").click(function () {
        var content = editor.getValue();
        var requestType = 'POST';
        var scribbit = $("#scribbit").val();
        var bit = '';

        if ($("#bit").val()) {
            requestType = 'PUT';
            bit = $("#bit").val();
        }

        $.ajax({
            type: requestType,
            url: '/bit',
            data: {
                bit: bit,
                content: content,
                scribbit: scribbit
            },
            success: function (data, textStatus, jqXHR) {
                location.reload(true);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(' create failed');
            }
        });

        editor.focus();
    });

    $("div.bit .delete").click(function () {
        bootbox.confirm("Delete this bit?", function (result) {
            if (result) {
                var bitPanel = $(this).parents("div.panel");
                var scribbit = bitPanel.data("scribbit");
                var bit = bitPanel.data("bit");

                $.ajax({
                    type: 'DELETE',
                    url: '/bit',
                    data: {
                        bit: bit,
                        scribbit: scribbit
                    },
                    success: function (data, textStatus, jqXHR) {
                        bitPanel.remove();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(bit + ' delete failed');
                    }
                });
            }
        });
    });

    $("div.bit .edit").click(function () {
        var bitPanel = $(this).parents("div.panel");
        var bit = bitPanel.data("bit");
        $("#bit").val(bit);

        var source = bitPanel.find("div.markdown").data('source');
        editor.setValue(source);
        $('#bit-editor').keyup();
        editor.clearSelection();
        editor.focus();
    });
});