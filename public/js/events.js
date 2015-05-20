$.extend( $.expr[ ":" ], {
    data: $.expr.createPseudo ?
        $.expr.createPseudo(function( dataName ) {
            return function( elem ) {
                return !!$.data( elem, dataName );
            };
        }) :
        // support: jQuery <1.8
        function( elem, i, match ) {
            return !!$.data( elem, match[ 3 ] );
        }
});

$(document).ready(function () {
    $.fn.editable.defaults.mode = 'inline';
    $.fn.editable.defaults.ajaxOptions = {type: "PUT"};

    $('li.scribbit h3 a').editable({
        type: 'text',
        url: baseUrl + '/scribbit',
        title: 'Enter new name',
        inputclass: 'input-lg',
        toggle: 'manual',
        success: function (response, newValue) {
            var r = JSON.parse(response);

            // Update links etc that depend on the scribbit name
            $(this).editable('option', 'pk', r.new);
            $(this).parents("li").attr("data-scribbit", r.new);

            $(this).parents("li").find('a').each(function () {
                $(this).attr("href", $(this).attr("href").replace(r.old, r.new));
            });

            $(this).parents("li").find('div.btn-group button').each(function () {
                var newUrl = $(this).attr("data-url").replace(r.old, r.new);
                $(this).attr("data-url", newUrl);
            });
        }
    });

    $('li.scribbit h3 a.edit').click(function (e) {
        e.stopPropagation();
        var a = $(this).parents("li").find('h3 a').not(".edit");

        $(a).editable('toggle');
        $(this).hide();
    });

    $('.editable').on('hidden', function (e, reason) {
        if (reason === 'save' || reason === 'cancel') {
            $('li.scribbit h3 a.edit').show();
        }
    });

    $("li.scribbit .download").click(function () {
        // $(this).data("url") doesn't return new value if data-url has changed
        window.location = $(this).attr("data-url");
    });

    $("li.scribbit .delete").click(function () {
        // $(this).data("url") doesn't return new value if data-url has changed
        var url = $(this).attr("data-url");

        bootbox.confirm("Delete this entire scribbit?", function (result) {
            if (result) {
                $.ajax({
                    type: 'DELETE',
                    url: url,
                    success: function (data, textStatus, jqXHR) {
                        location.reload(true);
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
        var url = $(this).data("post-url");

        if ($("#bit").val()) {
            requestType = 'PUT';
            bit = $("#bit").val();
            url = $(this).data("put-url");
        }

        $.ajax({
            type: requestType,
            url: url,
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
        var url = $(this).data("url");
        var bitPanel = $(this).parents("div.panel");

        bootbox.confirm("Delete this bit?", function (result) {
            if (result) {
                $.ajax({
                    type: 'DELETE',
                    url: url,
                    success: function (data, textStatus, jqXHR) {
                        bitPanel.remove();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(url + ': delete failed');
                    }
                });
            }
        });
    });

    $("div.bit .download").click(function () {
        window.location = $(this).data("url");
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
