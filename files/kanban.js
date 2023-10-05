$(document).ready(function(){
    drop_status = false;
    $( ".card" ).draggable({ 
        revert: checkDropStatus,
    });
    $( ".kanbanColumn" ).droppable({
        activeClass: "ui-state-hover",
        hoverClass: "ui-state-active",
        drop: function( event, ui ) {
            new_tag = $(this).data("tag-id");
            console.log('Attempt to assign new tag id: ', new_tag)
            drop_status = moveColumnAjax( ui.draggable, new_tag );
            ui.draggable.appendTo( this );
            ui.draggable.css('left','0').css('top','0');
        }
    });
    $("#kanbanTableSidebar a").on('click', function() {
        let targetBoardId = $(this).data('target-version')
        $('.board-box').hide()
        $('#info-msg').hide()
        $('div[id="board-'+targetBoardId+'"]').show()
    })

    kanbanAJAXURL = $("#kanbanConfig").data('ajax-url');
    kanbanAPIToken = $('#kanbanConfig').data('api-token');

    function checkDropStatus() {
        return !drop_status;
    }
    
    function moveColumnAjax( ticketObj,  new_tag ) {
        ticketId    = ticketObj.data('ticketid');
        projectId   = ticketObj.data('projectid');
        userId      = ticketObj.data('userid');
        username    = ticketObj.data('user-name');
        old_tag     = ticketObj.data('tag-id');

        package = {
            method: "DELETE",
            headers: {
                Authorization: kanbanAPIToken,
            },
            url: kanbanAJAXURL+ticketId+'/tags/'+old_tag,
        }
        if (old_tag) {
            $.ajax(package)
            .done(function( msg ) {
                if(0 !== msg.length) {
                    package = {
                        method: "POST",
                        headers: {
                            Authorization: kanbanAPIToken,
                        },
                        url: kanbanAJAXURL+ticketId+'/tags',
                        data: {
                            "tags": [
                                {"id": new_tag},
                            ]
                        }
                    }
                    $.ajax(package)
                    .done(function( msg ) {
                        // if update failed, move the card back and re-apply the old tag
                        if (0 !== msg.length) {
                            console.log(new_tag)
                            ticketObj.attr('data-tag-id', new_tag)
                            ticketObj.data('tag-id', new_tag)
                            return true
                        }
                        return false
                    });
                }
                return false;
            });
        }
    }
});