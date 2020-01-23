$(function(){
	app.init(false);
});

var app = 
{
	prop : 
	{
		windowHeight : 0
	}
	, init : function(resize)
	{
		app.prop.windowHeight = $(window).outerHeight();
		$('.page-home').length == 1 && app.page.home(resize);
		$(window).unbind('resize').on('resize',function(){
			app.init(true);
		});
		!resize && app.listener();
	}
	, listener : function()
	{
		$('header a').on('click',app.headerNavClick);
	}
	, headerNavClick : function(e)
	{
		if ( typeof $($(this).attr('href')) == 'object' )
		{
			$('html,body').animate({
				scrollTop : $($(this).attr('href')).offset().top
			},500);
		}

		e.preventDefault();
	}
	, page : 
	{
		home : function(resize)
		{
			$('.section-home').css('height', ( app.prop.windowHeight - $('header').outerHeight() ) + 'px' );
			$('.page-height').css('height', app.prop.windowHeight + 'px' );
		}
	}
}
