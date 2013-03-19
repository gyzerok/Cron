var indicate_interval = 0;
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
        $(this).animate({backgroundColor: '#fff'}, 500);
    });
    $(".closeDialogWindow a").bind('click', function(){
        $(".dialogsWrapper").hide();
        return false;
    });

    $(".singleDialog").live('click', function(event){
        getDialog($(this), event);
    });
    $(".sendMessage").live('click', function () {
		$(this).addClass('sendMessage-active');
        var singleAnswer = $(this).closest('.singleAnswer');
        $(".chatWindow").click();
        addChatLoadingOverlay();
        $.ajax({
            url:'/chat/createDialog',
            data:{to_user:singleAnswer.data('user')},
            success:function (dialog) {
                var existed_tab = $(".chat-tab[data-tab=d"+dialog+"]");
                if (!existed_tab.size()){
                    var new_tab = $('<div class="singleTab chat-tab" data-tab="d' + dialog + '"><a href="#" class="closeDialogTab"></a>' + singleAnswer.find(".userName").text() + '</div>');
                    $(".chat-container .chatTabs").append(new_tab);
                    new_tab.click();
                    $.ajax({
                        url:'/chat/openDialog',
                        data:{dialog:dialog},
                        success:function (response) {
                            if (!$.trim(response))
                                response = '<div class="dialogs-empty-text">Сообщений нет.</div>';
                            $(".chat-container .mainWindow").append(response);
                            new_tab.click();
                            removeChatLoadingOverlay();
                        }
                    });
                } else {
//                    setTimeout(existed_tab.click(), 1000);
                    existed_tab.click();
                    removeChatLoadingOverlay();
                }
            }
        });
        $(".chat-input").focus();
    });
    $(".inviteUser").live('click', function () {
		$(this).addClass('inviteUser-active');
        var singleAnswer = $(this).closest('.singleAnswer');
        $.ajax({
            url:'/chat/sendChatInvite',
            data:{
                user:singleAnswer.data('user'),
                question:singleAnswer.closest('.myAnswer').prevAll('.singleQuestion').data('id')
            },
            success:function (dialog) {
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
        $.ajax({
            url: '/chat/getInviteList',
            success: function(response){
                if (!$.trim(response))
                    response = '<div class="invites-empty-text">Приглашений нет.</div>';
                $(".chatInviteWindow .invites-container").html(response);
            }
        });
        $(".invites-container").html('<div class="invites-empty-text">Загрузка...</div>');
        soundManager.stop('chatInvite');
//        $(this).animate({borderWidth: '2px', borderColor: '#e6e6e6', color: '#333'}, 500);
//        clearTimeout(indicate_interval);
        $(".chatInvite").removeClass('indicate');
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
//        clearTimeout(indicate_interval);
        $(".chatInvite").removeClass('indicate');
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
//        clearTimeout(indicate_interval);
        $(".chatInvite").removeClass('indicate');
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
        maxHeight: 665,
        minHeight: 270,
        alsoResize: ".mainWindow",
        alsoResize: ".messageWrap",
        alsoResize: ".chat"
    });

    var chat_container = $(".chat-container");
    $('.chatWindow').click(function() {
        if (!$('.chat').size()){
            addChatLoadingOverlay();
            $.ajax({
                url: '/chat/loadChat',
                success: function(response){
                    chat_container.html(response);
                    var objDiv = $('.chat');
                    if (objDiv.size())
                        objDiv[0].scrollTop = objDiv[0].scrollHeight;
                    if (chat_container.is('.open-new-income-chat')){
                        $(".numberOnTab.chat-tab:last").click();
                        chat_container.removeClass('open-new-income-chat');
                    } else {
                        chat_container.find(".chat-tab").first().click();
                    }
                    removeChatLoadingOverlay();
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
        $(".open-dialog-list").animate({color: '#383838'}, 500);
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
        var chatInput = $(this);
        var code= (e.keyCode ? e.keyCode : e.which);
        var new_line_switch = $("#chat-nl-switch").attr('checked');
        if ((code == 13 && !e.ctrlKey && !new_line_switch)){

        } else {
            if(code == 13 && e.ctrlKey && new_line_switch){
                chatInput.val(chatInput.val()+'\n').trigger('autosize');
            } else if((code == 13 && e.ctrlKey && !new_line_switch) || (code == 13 && new_line_switch)){
                sendMessage(chatInput);
                return false;
            }
        }
    });

    $("#send-message").live('click', function(){
        sendMessage($(".chat-input"));
        return false;
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
            $("#send-message").hide();
            $(".enterArea").removeClass('wide');
        } else if (first_char_tab_id=='i') {
            $(".leaveConversation").show();
            $(".finishConversation").hide();
            $("#send-message").hide();
            $(".enterArea").removeClass('wide');
        } else {
            $(".leaveConversation").hide();
            $(".finishConversation").hide();
            $("#send-message").show();
            $(".enterArea").addClass('wide');
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

    if ($("body").is('.auth')){
        loadChat();

        var chat_update_interval = setInterval('updateChat();', 10000);

        indicate_interval = setInterval('indicateChatInvite();', 1000);

        var invites_container = $(".chatInviteWindow .invites-container");
        if (invites_container.find('.singleInvite').size()){
            if ($("body").is('.sound_chatInvite')){
                soundManager.play('chatInvite');
            }
            $(".chatInvite").addClass('indicate');
        }

        var dialogs_container = $(".dialogsWrapper .dialogs-container");
        if (dialogs_container.find('.messagesAmount').text()!=''){
            if ($("body").is('.sound_personalMessage')){
                soundManager.play('personalMessage');
            }
            $(".open-dialog-list").animate({color: '#e85b2d '}, 500); //todo color
        }
    }

});

function loadChat(){
    //Загруза окна чата сразу после озагрузки страницы
    $.ajax({
        url: '/chat/loadChat',
        success: function(response){
            $(".chat-container").html(response);
            var objDiv = $('.chat');
            if (objDiv.size()){
                objDiv[0].scrollTop = objDiv[0].scrollHeight;
            }
        }
    });
}

//Обновление чата
function updateChat(){
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
                if ($("body").is('.sound_chatInvite')){
                    soundManager.play('chatInvite');
                }
                $(".chatInvite").addClass('indicate');
//                $(document).ready(function() {
//                    $(".chatInvite").animate({borderWidth: '2px', borderColor: '#e85b2d', color: '#000'}, 500);
//                    $(".chatInvite").animate({borderWidth: '2px', borderColor: '#e6e6e6', color: '#333'}, 500);
//                    setTimeout(arguments.callee, 1000)
//                });
            }
            if (data.new_dialogs){
                if ($("body").is('.sound_personalMessage')){
                    soundManager.play('personalMessage');
                }
                $(".open-dialog-list").animate({color: '#e85b2d '}, 500);
            }

            $("#chat-last-update").attr('value', data.questions_last_update);

            for (var i in data.chats){
                var cur_chat = $(".chat-content[data-chat-id="+i+"]");
                for (var j in data.chats[i]){
                    $(".chat-tab:not(.active)[data-tab="+cur_chat.attr('tab')+"]").addClass('newMsgs');
                    $(".chatWindow").addClass('chatIndicate');                    
                    cur_chat.find(".messageWrap").append('<div class="singleMessage"><div class="chatUsername">'+data.chats[i][j].user_name+'</div><div class="messageText">'+data.chats[i][j].msg_text+'</div></div>');
                }
                
            }
            for (var i in data.dialogs){
                var cur_chat = $(".chat-content[data-dialog-id="+i+"]");
                for (var j in data.dialogs[i]){
                    $(".chat-tab:not(.active)[data-tab="+cur_chat.attr('tab')+"]").addClass('newMsgs');
                    $(".chatWindow").addClass('chatIndicate');
                    cur_chat.find(".messageWrap").append('<div class="singleMessage"><div class="chatUsername">'+data.dialogs[i][j].user_name+'</div><div class="messageText">'+data.dialogs[i][j].msg_text+'</div></div>');
                }
            }
            
            if (data.chats.length > 0 || data.dialogs.length > 0) {
                soundManager.play('inChat');
                $(".chatWindow").addClass('chatIndicate');                  
            }

            for (var i in data.srvmsgs.chats){
                var cur_chat = $(".chat-content[data-dialog-id="+i+"]");
                for (var j in data.srvmsgs.chats[i]){
                    $(".chat-tab:not(.active)[data-tab="+cur_chat.attr('tab')+"]").addClass('newMsgs');
                    cur_chat.find(".messageWrap").append('<div class="singleMessage chatSrvMsg"><div class="messageText">'+data.srvmsgs.chats[i][j].msg_text+'</div></div>');
                }
            }
            for (var i in data.srvmsgs.dialogs){
                var cur_chat = $(".chat-content[data-dialog-id="+i+"]");
                for (var j in data.srvmsgs.dialogs[i]){
                    $(".chat-tab:not(.active)[data-tab="+cur_chat.attr('tab')+"]").addClass('newMsgs');
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

function getDialog(obj, event){
    $(".chatWindow").click();
    var existed_tab = $(".chat-container .chat-tab[data-tab=d"+obj.data('dialog')+"]");
    if (!$(event.target).closest(".closeDialogs").length && !$(event.target).closest(".spamDialogs").length && !existed_tab.size()){
        addChatLoadingOverlay();
        var new_tab = $('<div class="singleTab chat-tab" data-tab="d'+obj.data('dialog')+'"><a href="#" class="closeDialogTab"></a>'+obj.find(".dialogUsername").text()+'</div>');
        $(".chat-container .chatTabs").append(new_tab);
        new_tab.click();
        $.ajax({
            url: '/chat/openDialog',
            data: {dialog:obj.data('dialog')},
            success: function(response){
                if (!$.trim(response))
                    response = '<div class="dialogs-empty-text">Сообщений нет.</div>';
                $(".chat-container .mainWindow").append(response);
                new_tab.click();
                removeChatLoadingOverlay();
            }
        });
    }
    existed_tab.click();
    $(".chat-input").focus();
}

function nl2br (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
}

function indicateChatInvite () {
    if ($(".indicate").size()){
        $(".chatInvite.indicate").animate({borderWidth: '2px', borderColor: '#e85b2d', color: '#000'}, 500)
            .animate({borderWidth: '2px', borderColor: '#e6e6e6', color: '#333'}, 500);
//        $(".chat-tab.indicate").animate({borderWidth: '2px', borderColor: '#e85b2d', color: '#000'}, 500)
//            .animate({borderWidth: '2px', borderColor: '#e6e6e6', color: '#333'}, 500);
        $(".chatWindow.chatIndicate").animate({backgroundColor: '#ffebdb'}, 1000)
            .animate({backgroundColor:'#ebf2fa'}, 1000);
    }
}

function addChatLoadingOverlay(){
    $(".chatWrapper").append('<div class="loading">Загрузка...</div>');
}
function removeChatLoadingOverlay(){
    $(".chatWrapper .loading").remove();
}

function sendMessage(chatInput) {
    var message = chatInput.val();
    if (message) {
        var current_chat = $(".chat-content:visible");
        if (current_chat.data('dialog-id')) {
            $.ajax({
                url: '/chat/sendDialogMsg',
                data: {
                    dialog: current_chat.data('dialog-id'),
                    to_user: current_chat.data('to-user'),
                    message: message
                }
            });
        } else {
            $.ajax({
                url: '/chat/sendChatMsg',
                data: {
                    chat: current_chat.data('chat-id'),
                    message: message
                }
            });
        }
        current_chat.find(".chat > div").append('<div class="singleMessage my-message"><div class="chatUsername">' + chatInput.data('nick') + '</div><div class="messageText">' + nl2br(message) + '</div></div>');
        chatInput.val('').trigger('autosize');
        var objDiv = current_chat.find(".chat");
        objDiv[0].scrollTop = objDiv[0].scrollHeight;
    }
}