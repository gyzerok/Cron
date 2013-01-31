$(document).ready(function(){
    $(".open-dialog-list").bind('click', function(){
        $.ajax({
            url: '/chat/getDialogList',
            success: function(response){
                if (!$.trim(response))
                    response = '<div class="dialogs-empty-text">Диалогов нет.</div>';
                $(".dialogsWrapper .dialogs-container").html(response);
            }
        });
        $(".dialogsWrapper .dialogs-container").html('<div class="dialogs-empty-text">Загрузка...</div>');
        $(".dialogsWrapper").toggle();
    });
    $(".closeDialogWindow a").bind('click', function(){
        $(".dialogsWrapper").hide();
        return false;
    });
    $(".closeDialogs a").live('click', function(){
        if (confirm('Удалить диалог?')) {
            var singleDialog = $(this).closest('.singleDialog');
            $.ajax({
                url:'/chat/deleteDialog',
                method: 'POST',
                data: { dialog:singleDialog.data('dialog') }
            });
            removeSingleDialog(singleDialog);
        }
        return false;
    });
    $(".spamDialogs a").live('click', function(){
        if (confirm('Пометить как спам?')) {
            var singleDialog = $(this).closest('.singleDialog');
            $.ajax({
                url:'/chat/checkDialogAsSpam',
                method: 'POST',
                data: { dialog:singleDialog.data('dialog') }
            });
            removeSingleDialog(singleDialog);
        }
        return false;
    });
    function removeSingleDialog (obj) {
        obj.remove();
        if (!$(".singleDialog").size()){
            $(".dialogsWrapper .dialogs-container").html('<div class="dialogs-empty-text">Диалогов нет.</div>');
        }
    }

    $(".chatInvite").bind('click', function(){
        $.ajax({
            url: '/chat/getInviteList',
            success: function(response){
                if (!$.trim(response))
                    response = '<div class="invites-empty-text">Приглашений нет.</div>';
                $(".chatInviteWindow .invites-container").html(response);
            }
        });
        $(".invites-container").html('<div class="invites-empty-text">Загрузка...</div>');
        $(".chatInviteWindow").toggle();
    });
    $(".closeInviteWindow a").bind('click', function(){
        $(".chatInviteWindow").hide();
        return false;
    });
    $(".decline-invite").live('click', function(){
        var singleInvite = $(this).closest('.singleInvite');
        $.ajax({
            url:'/chat/declineChatInvite',
            method: 'POST',
            data: { invite:singleInvite.data('invite') }
        });
        removeSingleInvite(singleInvite);
        return false;
    });
    $(".accept-invite").live('click', function(){
        var singleInvite = $(this).closest('.singleInvite');
        $.ajax({
            url:'/chat/acceptChatInvite',
            method: 'POST',
            data: { invite:singleInvite.data('invite') }
        });
        removeSingleInvite(singleInvite);
        return false;
    });
    function removeSingleInvite (obj) {
        obj.remove();
        if (!$(".singleInvite").size()){
            $(".chatInviteWindow .invites-container").html('<div class="invites-empty-text">Приглашений нет.</div>');
        }
    }
    //Открытие чата
    var openChat = $('.openChat');
    openChat.hide();
    $('.chatUpButton').click(function() {
        if (!$('.chat').size()){
            $.ajax({
                url: '/chat/loadChat',
                success: function(response){
                    $(".chat-container").html(response);
                    var objDiv = $('.chat');
                    objDiv[0].scrollTop = objDiv[0].scrollHeight;
                }
            });
            $(".chat-container").html('<div class="chat-empty-text">Загрузка...</div>');
        }
        openChat.fadeIn();
    });
    $('.closeChat').click(function() {
        openChat.fadeOut();
    });
    $(".kickUser").live('click', function(){
        $(this).closest('.singleUser').remove();
    });
    $(".chat-submit").live('click', function(){

    });
    $(".chat-input").live('keyup', function(e){
        var code= (e.keyCode ? e.keyCode : e.which);
        if (code == 13){
            var chatInput = $(this);
            if ($.trim(chatInput.val())){
                var current_chat = $(".chat-content:visible");
                $.ajax({
                    url: '/chat/sendDialogMsg',
                    data: {
                        dialog:current_chat.data('dialog-id'),
                        to_user:current_chat.data('to-user'),
                        message:chatInput.val()
                    },
                    success: function(response){
                        /*$(".chat-container").html(response);
                        var objDiv = $('.chat');
                        objDiv[0].scrollTop = objDiv[0].scrollHeight;*/
                    }
                });
                current_chat.find(".chat > div").append('<div class="singleMessage"><div class="chatUsername">'+chatInput.data('nick')+'</div><div class="messageText">'+chatInput.val()+'</div></div>');
                chatInput.val('');
                var objDiv = current_chat.find(".chat");
                objDiv[0].scrollTop = objDiv[0].scrollHeight;
            }
            return false;
        }
    });
    $(".chat-tab").live('click', function(){
        $(".chat-tab").removeClass('active');
        $(this).addClass('active');
        $(".chat-content").hide();
        var active_chat = $(".chat-content[tab="+$(this).data('tab')+"]");
        active_chat.show();
        var objDiv = active_chat.find(".chat");
        objDiv[0].scrollTop = objDiv[0].scrollHeight;
    });
});