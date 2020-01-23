$(function(){


	var filterChange = function()
	{
		var name = $(this).attr('name');
		$('.filter select').each(function(i,el){
			if( $(el).attr('name') != name )
			{
				$(el).val('');
			}
		})
		$(this).parent().parent().submit();
	}



	$(".filter select").on("change",filterChange);

});