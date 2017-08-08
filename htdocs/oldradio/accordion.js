$(document).ready(function(){
	$(".radiostation div").slideUp();
	$(".radiostation div:first").slideDown()
	$("h2").click(function(){
		$(".radiostation div:visible").slideUp('slow'); 
		$(this).next('div').slideToggle('slow');
		
	});
	
});
