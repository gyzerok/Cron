
var engine = {
 
	posts : [],
	target : null,
	busy : false,
	count : 5,
 
	render : function(obj){
		var xhtml = '<div class="singleQuestion" id=post_'+obj.id+'>';
		if (obj.title) {
			xhtml += '<h2>'+obj.user+'</h2>';
		}
		if (obj.posted_at) {
			xhtml += '<div class="posted_at">Posted on: '+obj.posted_at+'</div>';
		}
		if (obj.comments_count) {
			xhtml += '<div class="comments_count">Comments: ' + obj.comments_count + '</div>';
		}
		xhtml += '<div class="content">' + obj.content + '</div>';
		xhtml += '</div>';
 
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
	},
 
	append : function(posts){
		posts = (posts instanceof Array) ? posts : [];
		this.posts = this.posts.concat(posts);
 
		for (var i=0, len = posts.length; i<len; i++) {
			this.target.append(this.render(posts[i]));
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
 
		$.getJSON('getposts.php', {count:this.count, last:lastId},
			function(data){
				if (data.length > 0) {
					that.append(data);
				}
				that.setBusy(false);
			}
		);
	},

};
 
// usage
$(document).ready(function(){
	engine.init(null, $("#showQuestion"));
	engine.get();
});