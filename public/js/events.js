$(document).ready(function() {
    $("li.scribbit .delete").click(function() {
        var scribbit = $(this).parents("li").data("scribbit");
        
        $.ajax({
            type: 'DELETE',
            url: '/scribbit/' + scribbit + '?XDEBUG_SESSION_START=netbeans-xdebug',
            success: function(data, textStatus, jqXHR) {
                console.log(scribbit + ' deleted successfully');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(scribbit + ' delete failed');
            }
        });
    });
});