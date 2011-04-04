if(jQuery){
	$('a#top').each(function(index){ 
		$(this).click(function(){	
			this.children[0].innerHTML=(parseInt(this.children[0].innerHTML)+1);	
		});
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
	
	