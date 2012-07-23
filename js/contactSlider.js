$(function() {
	
	$('div.contact').hide();
	
	$(".btn-toggle").click(function () {
		$('div.contact').addClass('showContact');
     	$("div.contact'").slideToggle("slow");
    });
	
	$('#submit').click(function () {		
		
		//Get the data from all the fields
		var name = $('input[name=name]');
		var email = $('input[name=email]');
		var website = $('input[name=website]');
		var comment = $('textarea[name=comment]');

		if (name.val()=='') {
			name.addClass('highlight');
			return false;
		} else name.removeClass('highlight');
		
		if (email.val()=='') {
			email.addClass('highlight');
			return false;
		} else email.removeClass('highlight');
		
		if (comment.val()=='') {
			comment.addClass('highlight');
			return false;
		} else comment.removeClass('highlight');

		var data = 'name=' + name.val() + '&email=' + email.val() + '&website=' + 
		website.val() + '&comment='  + encodeURIComponent(comment.val());
		
		$('.text').attr('disabled','true');
		
		$('.loading').show();
		
		$.ajax({
			//this is the php file that processes the data and send mail
			url: "../scripts/process.php",	

			type: "GET",
			data: data,	
			cache: false,
			
			//success
			success: function (html) {				
				//if process.php returned 1/true (send mail success)
				if (html==1) {					
					$('.contact').fadeOut('slow');					
					$('.done').fadeIn('slow').delay('4000').fadeOut('slow');
				} else alert('Sorry, unexpected error. Please try again later.');				
			}		
		});

		return false;
	});	
});