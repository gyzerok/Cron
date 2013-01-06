
var engine = {
 
	posts : [],
	target : null,
	busy : false,
	// count : 5,
	last_time: 0,
 
	render : function(id, obj){

		var xhtml = '<div class="singleQuestion" id=question_'+id+'>';
		if (obj.user) {
			xhtml += '<div class="userName">'+obj.user+'</div>';
		}
		if (obj.date) {
			xhtml += '<div class="questionDate">'+obj.date+'</div>';
		}
		xhtml += '<div class="closeButton"></div><div style="clear: both;"></div>';
					
		if (obj.text) {
			xhtml += '<div class="questionText">' + obj.text;
					xhtml += '<div class="socialIcons">'+
							'<div class="spamButton"></div>'+
							'<div class="likeButton"></div>'+
							'<div class="repostButton"></div>'+
							'<form class="answer">'+
								'<input class="answerButton" type="submit" />'+
							'</form>'+
						'</div>';
		xhtml += '</div>';
		}

 
		return xhtml;
	},
 
	init : function(posts, target){
	
		if (!target)
			return;
		
		this.target = $(target);
		
		this.append(posts);
 
		var that = this;
		$(window).scroll(function(){
			if ($(document).height() - $(window).height() <= $(window).scrollTop() + 50) {
				that.scrollPosition = $(window).scrollTop();
				that.get();
			}
		});
		
		$('#showQuestion').on('click', '.closeButton', function(){
			var id = $(this).closest('.singleQuestion').attr('id');
			id = id.replace('question_', '');
				$.ajax({'url': '/ajax/delQuestion', 'type':'post', 'data': 'question_id='+id,
					'success': function(){
						$('.closeButton').click(function() {
						$(this).closest('.singleQuestion').fadeOut(800, function(){$(this).remove();});	
						});		
					}
				});
		});
		
		$('#showQuestionIndex').on('click', '.closeButton', function(){
			var id = $(this).closest('.singleQuestion').attr('id');
			id = id.replace('question_', '');
				$.ajax({'url': '/ajax/delQuestion', 'type':'post', 'data': 'question_id='+id,
					'success': function(){
						$('.closeButton').click(function() {
						$(this).closest('.singleQuestion').fadeOut(800, function(){$(this).remove();});	
						});		
					}
				});
		});
		
	},
 
	append : function(posts){
		//posts = (posts instanceof Object) ? posts : [];
		if(!posts) return;
		//this.posts = this.posts.concat(posts);
		this.posts = this.posts.concat(posts['categorized']);
	
			for(var i in posts['categorized']){
				var question = posts['categorized'][i];
			this.target.append(this.render(i, question));
		}
 
		if (this.scrollPosition !== undefined && this.scrollPosition !== null) {
			$(window).scrollTop(this.scrollPosition);
		}
	},
 
	get : function() {
 
		if (!this.target || this.busy) return;
 
		if (this.posts && this.posts.length) {
			var lastId = this.posts[this.posts.length-1].id;
		} else {
			var lastId = 0;
		}
 
		this.setBusy(true);
		var that = this;
 

		$.ajax({'url': '/ajax/getUpdate', 'type':'post', 'data':'last_time='+this.last_time,
			'success': function(data){
				data = JSON.parse(data);
				//if (data.length > 0) {
					that.append(data);
					that.last_time = data.last_time;
				//}
				that.setBusy(false);
			}
		});
	},
	
	showLoading : function(bState){
		var loading = $('#loading');

		if (bState) {
			$(this.target).append(loading);
			loading.show('slow');
		} else {
			$('#loading').hide();
		}
	},

	setBusy : function(bState){
	this.showLoading(this.busy = bState);
	}
};
 
// usage
$(document).ready(function(){
	engine.init(null, "#showQuestion");
	engine.get();
});