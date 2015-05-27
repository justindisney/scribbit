/*
 * Source: https://github.com/evilstreak/markdown-js/blob/master/src/render_tree.js
 */
function escapeHTML(text) {
    if (text && text.length > 0) {
        return text.replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#39;");
    } else {
        return "";
    }
}

function unescapeHTML(text) {
    if (text && text.length > 0) {
        return text.replace(/&amp;/g, "&")
                .replace(/&lt;/g, "<")
                .replace(/&gt;/g, ">")
                .replace(/&quot;/g, "\"")
                .replace(/&#39;/g, "'");
    } else {
        return "";
    }
}

var editableParams = {
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
}

$(document).ready(function () {
    $.fn.editable.defaults.mode = 'inline';
    $.fn.editable.defaults.ajaxOptions = {type: "PUT"};

    $('button.new-scribbit').click(function (e) {
        e.preventDefault();

        var url = $(this).attr("data-url");
        var scribbit = $("#newScribbit").val();

        $.ajax({
            type: 'POST',
            url: url,
            data: {
                scribbit: scribbit
            },
            success: function (data, textStatus, jqXHR) {
                data = JSON.parse(data);

                var str = $('.scribbit-template')[0].outerHTML;
                str = str.replace(/SCRIBBIT_NAME/g, data.scribbit_name);
                str = str.replace(/SCRIBBIT_DISPLAY_NAME/g, data.scribbit_display_name);

                html = $.parseHTML(str);

                $(html).removeClass('scribbit-template').addClass('scribbit');
                $(html).find("h3 a").editable(editableParams);

                $(html).find("h3 a").on('hidden', function (e, reason) {
                    if (reason === 'save' || reason === 'cancel') {
                        $('li.scribbit h3 button.edit').show();
                    }
                });

                $("ul.scribbits").prepend($(html));

                $("#newScribbit").val('');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(scribbit + ' creation failed');
            }
        });
    });

    $('li.scribbit h3 a').editable(editableParams);

    $(document).on('click', 'li.scribbit h3 button.edit', function (e) {
        e.stopPropagation();
        var a = $(this).parents("li").find('h3 a');

        $(a).editable('toggle');
        $(this).hide();
    });

    $('.editable').on('hidden', function (e, reason) {
        if (reason === 'save' || reason === 'cancel') {
            $('li.scribbit h3 button.edit').show();
        }
    });

    $(document).on('click', "li.scribbit .download", function () {
        // $(this).data("url") doesn't return new value if data-url has changed
        window.location = $(this).attr("data-url");
    });

    $(document).on('click', "li.scribbit .delete", function () {
        // $(this).data("url") doesn't return new value if data-url has changed
        var url = $(this).attr("data-url");
        var li = $(this).parents("li.scribbit");

        bootbox.confirm("Delete this entire scribbit?", function (result) {
            if (result) {
                $.ajax({
                    type: 'DELETE',
                    url: url,
                    success: function (data, textStatus, jqXHR) {
                        li.remove();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(scribbit + ' delete failed');
                    }
                });
            }
        });
    });

    if ($('#bit-editor').length) {
        var converter = new Showdown.converter();

        var editor = ace.edit("bit-editor");
        editor.setTheme("ace/theme/tomorrow");
        editor.getSession().setMode("ace/mode/markdown");
        editor.getSession().setUseWrapMode(true);
        editor.setValue("Write some **markdown** here...");
        editor.clearSelection();
        editor.$blockScrolling = Infinity;
        editor.focus();

        $('#bit-editor').keyup(function () {
            $('#bit-preview').html(converter.makeHtml(escapeHTML(editor.getValue())));
        });

        $('#bit-editor').keyup();

        $('.markdown').each(function () {
            $(this).html(converter.makeHtml($(this).data('source')));
            $(this).find('img').each(function () {
                $(this).addClass('img-responsive');
            });
        });
    }

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
        editor.setValue(unescapeHTML(source));
        $('#bit-editor').keyup();
        editor.clearSelection();
        editor.focus();
        editor.gotoLine(editor.session.doc.getAllLines().length);
        editor.navigateLineEnd();
    });

    $("#uploadModal div.modal-body button").click(function () {
        var url = $(this).data("url");
        var image_url = $("#image-url").val();

        $.ajax({
            type: 'POST',
            url: url,
            data: {
                image_url: image_url
            },
            success: function (data, textStatus, jqXHR) {
                $("#uploadModal").modal('toggle');
                location.reload(true);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(url + ': image save failed');
            }
        });
    });

    $('#fileupload').fileupload({
        url: $(this).data("url"),
        dataType: 'json',
        done: function (e, data) {
//            $.each(data.result.files, function (index, file) {
//                $('<p/>').text(file.name).appendTo('#files');
//            });
console.log("when is this called?");
            $("#uploadModal").modal('toggle');
        },
        success: function (e, data) {
//            $.each(data.result.files, function (index, file) {
//                $('<p/>').text(file.name).appendTo('#files');
//            });
console.log("when is this called?");
            $("#uploadModal").modal('toggle');
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .progress-bar').css(
                'width',
                progress + '%'
            );
        }
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
});
