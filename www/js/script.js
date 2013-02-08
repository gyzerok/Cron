
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
    $(".repostButton").live('click', function(){
        $.ajax({
            url: '/ajax/repostQuestion',
            data:{
                question: $(this).closest('.singleQuestion').data('id')
            },
            success: function(response){
                alert('Вопрос добавлен в заметки.')
            }
        });
    });
    $(".bookmarkArticle").bind('click', function(){
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


    //todo collapse
    $.ajax({
        url: '/ajax/getUserLinks',
        success: function(response){
            $(".add-bookmark").parent('li').before(response);
        }
    });
    //todo collapse
    $.ajax({
        url: '/ajax/loadNotepad',
        success: function(response){
            $("#notepad-text").val(response);
        }
    });
});

function updateNotepad(){
    $.ajax({
        url: '/ajax/updateNotepad',
        data: {
            text: $("#notepad-text").val()
        }
    });
}