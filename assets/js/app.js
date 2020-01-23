$(function(){


	/// video home height
	if( $('#video-full').length != 0 )
	{	
		var height = $(".middle").outerWidth() / 1.7778;
		$("#video-full iframe").attr('height',height+'px');
	}


})