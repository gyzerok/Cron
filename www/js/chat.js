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
        $(this).removeClass('indicate');
    });
    $(".closeDialogWindow a").bind('click', function(){
        $(".dialogsWrapper").hide();
        return false;
    });

    $(".singleDialog").live('click', function(event){
        $(".chatWindow").click();
        var existed_tab = $(".chat-container .chat-tab[data-tab=d"+$(this).data('dialog')+"]");
        if (!$(event.target).closest(".closeDialogs").length && !$(event.target).closest(".spamDialogs").length && !existed_tab.size()){
            var new_tab = $('<div class="singleTab chat-tab" data-tab="d'+$(this).data('dialog')+'"><a href="#" class="closeDialogTab"></a>'+$(this).find(".dialogUsername").text()+'</div>');
            $(".chat-container .chatTabs").append(new_tab);
            new_tab.click();
            $.ajax({
                url: '/chat/openDialog',
                data: {dialog:$(this).data('dialog')},
                success: function(response){
                    if (!$.trim(response))
                        response = '<div class="dialogs-empty-text">Сообщения нет.</div>';
                    $(".chat-container .mainWindow").append(response);
                    new_tab.click();
                }
            });
        }
        existed_tab.click();
        $(".chat-input").focus();
    });
    $(".sendMessage").live('click', function () {
        var singleAnswer = $(this).closest('.singleAnswer');
        $(".chatWindow").click();
        $.ajax({
            url:'/chat/createDialog',
            data:{to_user:singleAnswer.data('user')},
            success:function (dialog) {
                var new_tab = $('<div class="singleTab chat-tab" data-tab="d' + dialog + '"><a href="#" class="closeDialogTab"></a>' + singleAnswer.find(".userName").text() + '</div>');
                $(".chat-container .chatTabs").append(new_tab);
                new_tab.click();
                $.ajax({
                    url:'/chat/openDialog',
                    data:{dialog:$(this).data('dialog')},
                    success:function (response) {
                        if (!$.trim(response))
                            response = '<div class="dialogs-empty-text">Сообщений нет.</div>';
                        $(".chat-container .mainWindow").append(response);
                        new_tab.click();
                    }
                });
            }
        });
        $(".chat-input").focus();
    });
    $(".inviteUser").live('click', function () {
        var singleAnswer = $(this).closest('.singleAnswer');
        $.ajax({
            url:'/chat/sendChatInvite',
            data:{user:singleAnswer.data('user')},
            success:function (dialog) {
                alert('Приглашение в чат отправлено.');
            }
        });
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
        $(".chat-container .chat-tab[data-tab=d"+obj.data('dialog')+"] .closeDialogTab").click();
        obj.remove();
        if (!$(".singleDialog").size()){
            $(".dialogsWrapper .dialogs-container").html('<div class="dialogs-empty-text">Диалогов нет.</div>');
        }
    }

    $(".chatInvite").bind('click', function(){
        /*$.ajax({
            url: '/chat/getInviteList',
            success: function(response){
                if (!$.trim(response))
                    response = '<div class="invites-empty-text">Приглашений нет.</div>';
                $(".chatInviteWindow .invites-container").html(response);
            }
        });*/
//        $(".invites-container").html('<div class="invites-empty-text">Загрузка...</div>');
        $(this).removeClass('indicate');
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
            data: { invite:singleInvite.data('invite') },
            success:function(){
                $(".chat-container").addClass('open-new-income-chat').empty();
                $(".chatWindow").click();
//                var existed_tab = $(".chat-container .chat-tab[data-tab=d"+$(this).data('dialog')+"]");
//                if (!$(event.target).closest(".closeDialogs").length && !$(event.target).closest(".spamDialogs").length && !existed_tab.size()){
                    /*$(".chat-container .chatTabs").append(new_tab);
                    new_tab.click();
                    $.ajax({
                        url: '/chat/openDialog',
                        data: {dialog:$(this).data('dialog')},
                        success: function(response){
                            if (!$.trim(response))
                                response = '<div class="dialogs-empty-text">Сообщения нет.</div>';
                            $(".chat-container .mainWindow").append(response);
                            new_tab.click();
                        }
                    });*/
//                }
//                existed_tab.click();
//                $(".chat-input").focus();
            }
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

    //Растягиваемое окошко чата
    openChat.resizable({
        handles:"n",
        maxHeight: 529,
        minHeight: 270,
        alsoResize: ".mainWindow",
        alsoResize: ".messageWrap",
        alsoResize: ".chat"
    });

    var chat_container = $(".chat-container");
    $('.chatWindow').click(function() {
        if (!$('.chat').size()){
            $.ajax({
                url: '/chat/loadChat',
                success: function(response){
                    chat_container.html(response);
                    var objDiv = $('.chat');
                    objDiv[0].scrollTop = objDiv[0].scrollHeight;
                    if (chat_container.is('.open-new-income-chat')){
                        $(".numberOnTab.chat-tab:last").click();
                        chat_container.removeClass('open-new-income-chat');
                    } else {
                        chat_container.find(".chat-tab").first().click();
                    }
                }
            });

            chat_container.html('<div class="chat-empty-text">Загрузка...</div>');
        } else {
            chat_container.find(".chat-tab").first().click();
        }
//        openChat.fadeIn();
        $('.chatWrapper').fadeIn();
        openChat.show();
        $(".chat-input").focus();
        $(".open-dialog-list").removeClass('indicate');
    });
    $('.closeChat').click(function() {
//        openChat.fadeOut();
        $('.chatWrapper').fadeOut();
        openChat.hide();
    });
    $(".kickUser").live('click', function(){
        if (confirm('Выгнать пользователя?')) {
            var current_chat = $(".chat-content:visible");
            $.ajax({
                url:'/chat/kickUser',
                data:{
                    chat:current_chat.data('chat-id'),
                    user:$(this).parent().data('user')
                }
            });
            $(this).closest('.singleUser').remove();
        }
    });
    $(".chat-submit").live('click', function(){

    });
    $(".chat-input").live('keyup', function(e){
        var code= (e.keyCode ? e.keyCode : e.which);
        var new_line_switch = $("#chat-nl-switch").attr('checked');
        if (code == 13 && e.shiftKey && !new_line_switch){

        } else if((code == 13 && e.shiftKey && new_line_switch) || (code == 13 && !new_line_switch)){
            var chatInput = $(this);
            if ($.trim(chatInput.val())){
                var current_chat = $(".chat-content:visible");
                if (current_chat.data('dialog-id')){
                    $.ajax({
                        url: '/chat/sendDialogMsg',
                        data: {
                            dialog:current_chat.data('dialog-id'),
                            to_user:current_chat.data('to-user'),
                            message:chatInput.val()
                        }
                    });
                } else {
                    $.ajax({
                        url: '/chat/sendChatMsg',
                        data: {
                            chat:current_chat.data('chat-id'),
                            message:chatInput.val()
                        }
                    });
                }
                current_chat.find(".chat > div").append('<div class="singleMessage"><div class="chatUsername">'+chatInput.data('nick')+'</div><div class="messageText">'+nl2br(chatInput.val())+'</div></div>');
                chatInput.val('');
                var objDiv = current_chat.find(".chat");
                objDiv[0].scrollTop = objDiv[0].scrollHeight;
            }
            return false;
        }

    });
    $(".chat-tab").live('click', function(){
        $(".chat-tab").removeClass('active');
        $(this).removeClass('indicate').addClass('active');
        $(".chat-content").hide();
        var tab_id = $(this).data('tab');

        var active_chat = $(".chat-content[tab="+tab_id+"]");
        active_chat.show();

        if (active_chat.data('dialog-id')){
            $.ajax({
                url: '/chat/readDialogMsgs',
                data: {
                    dialog:active_chat.data('dialog-id')
                }
            });
            $(".dialogsWrapper .dialogs-container .singleDialog[data-dialog="+active_chat.data('dialog-id')+"] .messagesAmount").text('');
        }

        var objDiv = active_chat.find(".chat");
        if (objDiv.size())
            objDiv[0].scrollTop = objDiv[0].scrollHeight;
        $(".chat-input").focus();

        var first_char_tab_id = tab_id.substr(0,1);
        if (first_char_tab_id=='m'){
            $(".leaveConversation").hide();
            $(".finishConversation").show();
        } else if (first_char_tab_id=='i') {
            $(".leaveConversation").show();
            $(".finishConversation").hide();
        } else {
            $(".leaveConversation").hide();
            $(".finishConversation").hide();
        }
    });

    $(".closeDialogTab").live('click', function(){
        var cur_tab = $(this).parent();
        var prev_tab = cur_tab.prevAll('.chat-tab');
        var current_chat = $(".chat-content:visible");
        $.ajax({
            url: '/chat/closeDialog',
            data: {
                dialog:current_chat.data('dialog-id')
            }
        });
        $(".chat-content[tab="+cur_tab.data('tab')+"]").remove();
        cur_tab.remove();
        prev_tab.click();
        return false;
    });

    $(".finishConversation input").live('click', function(){
        var current_chat = $(".chat-content:visible");
        $.ajax({
            url: '/chat/finishChat',
            data: {
                chat:current_chat.data('chat-id')
            }
        });
        $(".usersInChat .singleUser").remove();
        $(".chat-tab.active").remove();
        $(".chat-tab").first().click();
    });
    $(".leaveConversation input").live('click', function(){
        var current_chat = $(".chat-content:visible");
        $.ajax({
            url: '/chat/leaveChat',
            data: {
                chat:current_chat.data('chat-id')
            }
        });
        $(".chat-tab.active").removeClass('chat-tab');
        $(".chat-tab").first().click();
    });

    temp_loadChat();

    var chat_update_interval = setInterval('temp_updateChat()', 10000);


//    $('.chatWindow').click();
});

function temp_loadChat(){
    //Загруза окна чата сразу после озагрузки страницы
    $.ajax({
        url: '/chat/loadChat',
        success: function(response){
            $(".chat-container").html(response);
            var objDiv = $('.chat');
            objDiv[0].scrollTop = objDiv[0].scrollHeight;
        }
    });
};

    //Загрузка приглашений в чат
    $.ajax({
        url: '/chat/getInviteList',
        success: function(response){
            if (!$.trim(response)){
                response = '<div class="invites-empty-text">Приглашений нет.</div>';
            } else {
                $(".chatInvite").addClass('indicate');
            }
            $(".chatInviteWindow .invites-container").html(response);
        }
    });

    //Загрузка диалогов
    $.ajax({
        url: '/chat/getDialogList',
        success: function(response){
            if (!$.trim(response))
                response = '<div class="dialogs-empty-text">Диалогов нет.</div>';
            var dialogs_container = $(".dialogsWrapper .dialogs-container");
            dialogs_container.html(response);
            if (dialogs_container.find('.messagesAmount').text()!=''){
                $(".open-dialog-list").addClass('indicate');
            }
        }
    });

//Обновление чата
function temp_updateChat(){
    var chats = '';
    var dialogs = '';
    $(".chat-content").each(function(i){
        if ($(this).data('chat-id'))
            chats += $(this).data('chat-id')+';';
        else if ($(this).data('dialog-id'))
            dialogs += $(this).data('dialog-id')+';';
    });
    $.ajax({
        url: '/chat/updateChat',
        type: 'post',
        dataType: 'json',
        data: {
            chat_last_update: $("#chat-last-update").attr('value'),
            chats:  chats.substr(0,chats.length-1),
            dialogs: dialogs.substr(0,dialogs.length-1)
        },
        success: function(data){
            if (data.invites){
                $(".chatInvite").addClass('indicate');
            }
            if (data.new_dialogs){
                $(".open-dialog-list").addClass('indicate');
            }

            $("#chat-last-update").attr('value', data.chat_last_update);

            for (var i in data.chats){
                var cur_chat = $(".chat-content[data-chat-id="+i+"]");
                for (var j in data.chats[i]){
                    $(".chat-tab:not(.active)[data-tab="+cur_chat.attr('tab')+"]").addClass('indicate');
                    cur_chat.find(".messageWrap").append('<div class="singleMessage"><div class="chatUsername">'+data.chats[i][j].user_name+'</div><div class="messageText">'+data.chats[i][j].msg_text+'</div></div>');
                }
            }
            for (var i in data.dialogs){
                var cur_chat = $(".chat-content[data-dialog-id="+i+"]");
                for (var j in data.dialogs[i]){
                    $(".chat-tab:not(.active)[data-tab="+cur_chat.attr('tab')+"]").addClass('indicate');
                    cur_chat.find(".messageWrap").append('<div class="singleMessage"><div class="chatUsername">'+data.dialogs[i][j].user_name+'</div><div class="messageText">'+data.dialogs[i][j].msg_text+'</div></div>');
                }
            }

            for (var i in data.srvmsgs.chats){
                var cur_chat = $(".chat-content[data-dialog-id="+i+"]");
                for (var j in data.srvmsgs.chats[i]){
//                    $(".chat-tab:not(.active)[data-tab="+cur_chat.attr('tab')+"]").addClass('indicate');
                    cur_chat.find(".messageWrap").append('<div class="singleMessage chatSrvMsg"><div class="messageText">'+data.srvmsgs.chats[i][j].msg_text+'</div></div>');
                }
            }
            for (var i in data.srvmsgs.dialogs){
                var cur_chat = $(".chat-content[data-dialog-id="+i+"]");
                for (var j in data.srvmsgs.dialogs[i]){
//                    $(".chat-tab:not(.active)[data-tab="+cur_chat.attr('tab')+"]").addClass('indicate');
                    cur_chat.find(".messageWrap").append('<div class="singleMessage chatSrvMsg"><div class="messageText">'+data.srvmsgs.dialogs[i][j].msg_text+'</div></div>');
                }
            }

            $('.chat').each(function(){
                $(this)[0].scrollTop = $(this)[0].scrollHeight;
            });
        }
    });
    return true;
}

function nl2br (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
}