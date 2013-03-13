
$(document).ready(function() {

    var answerFrom = $('.answerForm');
    $('.answerButton').click(function() {
        var cur_answerForm = $(this).closest('.singleQuestion').nextAll('.answerForm').first();
        $(".answerForm").not(cur_answerForm).slideUp(500);
        cur_answerForm.slideToggle(500);
        var cur_myAnswer = $(this).closest('.singleQuestion').nextAll('.myAnswer').first();
        cur_myAnswer.slideToggle(500);
        $(".myAnswer").not(cur_myAnswer).slideUp(500);
    });

//		var answerFormNavigation = $('.answerFormNavigation');
    $('.cancelButton').click(function() {
        $(this).closest('.answerForm').slideUp(500);
        $(this).closest('.answerForm').prevAll('.myAnswer').first().slideUp(500);
    });
    $(".submitAnswerButton").live('click', function(){
        var answerForm = $(this).closest('.answerForm');
        $.ajax({
            url: '/ajax/postAnswer',
            type: 'post',
            data: {
                id: answerForm.prevAll(".singleQuestion").data('id'),
                text: answerForm.find('.answerTextarea').val()
            },
            success: function(response){
                answerForm.prevAll('.myAnswer').first().append(response);
                answerForm.remove();
            }
        });
        answerForm.find('.answerTextarea').val('');
        return false;
    });



    $(".add-bookmark").click(function(){
        $(".userLinkAddWindow").show();
        return false;
    });
    $(".closeUserLinkWindow").click(function(){
        $(this).closest('.userLinkAddWindow').hide();
        return false;
    });
    $(".cancelUserLink").click(function(){
        $(this).closest('.userLinkAddWindow').hide();
        return false;
    });
    $(".saveUserLink").click(function(){
        var form = $(this).closest('form');
        form.ajaxSubmit({
            success: function(response){
                $(".add-bookmark").parent('li').before(response);
            }
        });
        $("#user-link-title, #user-link-url").val(null);
        $(".closeUserLinkWindow").click();
        return false;
    });

    $(".open-feedback-window").click(function(){
        $(".feedbackWindow").show();
        return false;
    });
    $(".closeFeedbackWindow").click(function(){
        $(this).closest('.feedbackWindow').hide();
        return false;
    });
    $(".cancel-feedback").click(function(){
        $(this).closest('.feedbackWindow').hide();
        return false;
    });
    $(".submit-feedback").click(function(){
        var form = $(this).closest('form');
        form.ajaxSubmit({
            success: function(response){
                alert('Ваше сообщение отправлено. Спасибо!');
            }
        });
        $("#feedback-text").val(null);
        $(".closeFeedbackWindow").click();
        return false;
    });

    $(".open-reply-feedback-window").click(function(){
        $(".replyFeedbackWindow").show();
        $("#reply-text").focus();
        $("#feedback-id").val($(this).closest('.feedback-li').data('id'));
        return false;
    });
    $(".closeReplyFeedbackWindow").click(function(){
        $(this).closest('.replyFeedbackWindow').hide();
        return false;
    });
    $(".cancelReplyFeedback").click(function(){
        $(this).closest('.replyFeedbackWindow').hide();
        return false;
    });
    $(".submitReplyFeedback").click(function(){
        var form = $(this).closest('form');
        form.ajaxSubmit({
            success: function(response){
                alert('Ответ отправлен!');
                $('.feedback-li[data-id='+$('#feedback-id').val()+']').remove();
            }
        });
        $(".closeReplyFeedbackWindow").click();
        return false;
    });

    $(".repostButton").live('click', function(){
		$(this).addClass('repostButtonActive');
        $.ajax({
            url: '/ajax/repostQuestion',
            data:{
                question: $(this).closest('.singleQuestion').data('id')
            },
            success: function(response){
            }
        });
    });
    $(".bookmarkArticle").bind('click', function(){
		$(this).addClass('bookmarkArticleActive');
        $.ajax({
            url: '/ajax/bookmarkArticle',
            data:{
                article: $(this).closest('.article-li').data('id')
            },
            success: function(response){
                alert('Статья добавлена в заметки.')
            }
        });
        return false;
    });
    $(".unbookmarkArticle").bind('click', function(){
        if (confirm('Удалить статью из заметок?')) {
            $.ajax({
                url:'/ajax/unbookmarkArticle',
                data:{
                    article:$(this).closest('li').data('id')
                }
            });
            $(this).closest('li').remove();
        }
        return false;
    });
    $(".open-notepad").click(function(){
        $("#notepad").show();
        $("#notepad-text").focus();
        return false;
    });
    $(".closeNotepad").click(function(){
        $("#notepad").hide();
        return false;
    });

    var notepadTimeout = 0;
    $("#notepad-text").live('keyup', function(){
        clearTimeout(notepadTimeout);
        notepadTimeout = setTimeout('updateNotepad();', 2000);
    });


	//Делаем заметки статей ссылками
	$(".article-notes-list > li").click(function(){  
	  window.location=$(this).find("a").attr("href"); return false;  
	});
	
	$(".article-list > li.article-li").click(function(){  
	  window.location=$(this).find("a").attr("href"); return false;  
	});
	
	$('.article-li').hover(function() {
		$(this).find('a span').css({'text-decoration': 'underline'})
	},
	function() {
		$(this).find('a span').css({'text-decoration': 'none'})	
	});
    
    
    //Sounds
    soundManager.setup({ 
        url: '/flash/',
        flashVersion: 9
    });
            
    soundManager.onload = function() {    
        mySound = soundManager.createSound({
            id: 'newQuestion',     
            url: '/audio/newQuestion.mp3',         
            volume: 100        
            });
        mySound = soundManager.createSound({
            id: 'personalMessage',     
            url: '/audio/personalMessage.mp3',         
            volume: 100       
            }); 
        mySound = soundManager.createSound({
            id: 'questionIsClosed',     
            url: '/audio/questionIsClosed.mp3',         
            volume: 100        
            }); 
        mySound = soundManager.createSound({
            id: 'inChat',     
            url: '/audio/inChat.mp3',         
            volume: 100        
        }); 
            mySound = soundManager.createSound({
            id: 'chatInvite',     
            url: '/audio/chatInvite.mp3',         
            volume: 100        
        });     
    }
  
});

function updateNotepad(){
    $.ajax({
        url: '/ajax/updateNotepad',
        data: {
            text: $("#notepad-text").val()
        }
    });
}