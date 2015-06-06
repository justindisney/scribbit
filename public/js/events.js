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
    if ($.fn.editable) {
        $.fn.editable.defaults.mode = 'inline';
        $.fn.editable.defaults.ajaxOptions = {type: "PUT"};
        
        $('li.scribbit h3 a').editable(editableParams);
    }

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

        bootbox.confirm({
            message: "<h3>Delete this entire scribbit?</h3>",
            buttons: {
                confirm: {
                    label: "OK",
                    className: "btn-primary btn-lg"
                },
                cancel: {
                    label: "Cancel",
                    className: "btn-default btn-lg"
                }
            },
            callback: function (result) {
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
        }});
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

        $('div.bit-wrapper .markdown').each(function () {
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

    $("div.editor-buttons button.help").click(function () {
        if ($('img.deferred').attr('src') == '#') {
            $('img.deferred').attr('src', $('img.deferred').data('src'));
        }
    });

    function createImageBit(data) {
        var converter = new Showdown.converter();

        var str = $('.bit-template')[0].outerHTML;
        str = str.replace(/DATE/g, data.date);
        str = str.replace(/BIT_NAME/g, data.name);
        str = str.replace(/SCRIBBIT/g, data.scribbit);
        str = str.replace(/HTML_CONTENT/g, converter.makeHtml(data.rendered_content));
        str = str.replace(/CONTENT/g, data.rendered_content);

        html = $.parseHTML(str);

        $(html).removeClass('bit-template'); //.addClass('scribbit');

        return $(html);
    }

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
            dataType: 'json',
            url: url,
            data: {
                bit: bit,
                content: content,
                scribbit: scribbit
            },
            success: function (response) {
                var html = createImageBit(response);
                $("div.bit-wrapper").prepend($(html));

                // lazy way to reset the form
                $("div.editor-buttons button.cancel").click();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(' create failed');
            }
        });

        editor.focus();
    });

    $(document).on('click', 'div.bit .delete', function () {
        var url = $(this).data("url");
        var bitPanel = $(this).parents("div.panel");

        bootbox.confirm({
            message: "<h3>Delete this bit?</h3>",
            buttons: {
                confirm: {
                    label: "OK",
                    className: "btn-primary btn-lg"
                },
                cancel: {
                    label: "Cancel",
                    className: "btn-default btn-lg"
                }
            },
            callback: function (result) {
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
        }});
    });

    $(document).on('click', 'div.bit .download', function () {
        window.location = $(this).data("url");
    });

    $(document).on('click', 'div.bit .edit', function () {
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
            dataType: 'json',
            data: {
                image_url: image_url
            },
            success: function (response) {
                var html = createImageBit(response);

                $("div.bit-wrapper").prepend($(html));

                $("#uploadModal").modal('toggle');
                $("#image-url").val('');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(url + ': image save failed');
            }
        });
    });

    $('#fileupload').fileupload({
        url: $(this).data("url"),
        dataType: 'json',
        success: function (response) {
            var html = createImageBit(response);

            $("div.bit-wrapper").prepend($(html));

            $("#uploadModal").modal('toggle');
            $("#image-url").val('');
        }
    });
});
