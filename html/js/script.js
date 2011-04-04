if(jQuery){
	
	$('a#top').each(function(index){ 
		$(this).click(function(){	
			this.children[0].innerHTML=(parseInt(this.children[0].innerHTML)+1);	
		});
	});
	
	console.log($('#displayform'));
	
	$('#displayform').hide();
	$('#displayform').dialog({
		autoOpen: false,
		modal:true,
		width: 500,
		height: 350,
		title: 'Compose a Message',
		buttons: {
			"Post message": function(){
				console.log($('.subject')[0].value);
				console.log($('.message')[0].value);
				var inject = '<li><a href="#" id="top"><div id="up">1</div></a><a href="#" class="topic-title">'+$('.subject')[0].value+'</a><p>'+ $('.message')[0].value +'</p><div id="bar"><div id="comments">0</div>	<a href="#" class="more">read more</a><div id="user"><span class="time">just now</span><span class="username">ryali1</span><span class="reputation">48</span></div></div></li>';
				console.log(inject);
				$('#topic-list').prepend(inject);
				$( this ).dialog( "close" );
			}
		}
	});
	
	$('.compose').click(function(){
		$('#displayform').dialog("open");
	});
	
	$('#topic-list li').each(function(index){
		//console.log(this);
		//$(this).slideUp();
	});
	
	$('.more').each(function(index){
		var li =$(this).closest('li')[0];
		console.log();
		console.log();
		$(this).click(function(){
			$('p').slideDown();
			if(index == 0){
				$(li).children('p')[0].innerHTML += "our program. Do you have any idea why select would be doing this?";
			} else if(index == 1){	
				$(li).children('p')[0].innerHTML += "I will confirm this on website after I make the classroom reservation. MP2 is due on Mar 30. Although this MP should be easier than MP1, I don't think you can finish if you start working on it after spring break. Shu";
			}
		});
	});
} else {
	alert("jQuery wasn't loaded. Try using a modern browser such as Firefox. (some features may not work)");
}
	
	