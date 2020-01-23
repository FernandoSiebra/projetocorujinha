$(function(){

	$(".entity-form button[type='submit']").on("click",buttonSend);
	$(".btn-novo").on("click",btnNovo);
	$(".btn-editar").on("click",buttonEdit);
	$(".btn-delete").on("click",buttonDelete);
	$(".view .table td").on('click',tdClick);

	setTimeout(function(){
		$(".alert").hide();
	},4000);

	if( $(".view .table tbody tr").length == 0 )
	{
		$(".view .table thead").html("");
		$(".view .table tbody").append('<tr><td colspan="10">Nada foi encontrado</td></tr>');
	}

	if( typeof viewLoad == 'function' )
	{
		viewLoad();
	}
});


function btnNovo()
{
	if( $('.view .table .selected').length > 0 && $(".entity-form").is(":visible") )
	{
		$('.view .table .selected').removeClass('selected');
		$('.btn-editar,.btn-delete').attr('disabled','disabled');	
	}
	else
	{
		$(".entity-form").toggle();
	}
	$('.entity-form input[type="text"], .entity-form input[type="hidden"], .entity-form input[type="password"], .entity-form textarea').val('');
	$('.entity-form input[type="checkbox"]').removeAttr('checked');
	$(".jqte_editor").html("");

	if( typeof btnNovoAfterClick == 'function' )
	{
		btnNovoAfterClick();
	}

	return false;
}

function buttonSend()
{
	if( typeof formValidate == "function" )
	{
		if ( formValidate() == false )
		{
			return false;
		}
	}
	var entity = location.href.split('/'); 
	entity = entity[entity.length-1];
	$(".entity-form form").attr("action",entity + '/salvar').submit();	
	return false;
}

function buttonEdit()
{
	var entity = location.href.split('/'); 
	entity = entity[entity.length-1];
	var url = entity + '/get/' + $('.view .table .selected').data('id');
	$(".entity-form,.body-protect,.body-load").show();
	
	$.ajax({
		url : url
		, cache : false
		, success : function(data)
		{
			entityForm(data);
			$(".body-protect,.body-load").hide();
		}
		, error : function(data)
		{
			alert("error");
		}
	});

}

function loading()
{
	if( !$(".body-protect").is(":visible") )
	{
		$(".body-protect,.body-load").show();	
	}
	else
	{
		$(".body-protect,.body-load").hide();	
	}
}

function buttonDelete()
{
	var entity = location.href.split('/'); 
	entity = entity[entity.length-1];
	location.href = entity + '/delete/' + $(".view .table .selected").data("id");
	return false;
}

function tdClick()
{
	var selected = $(this).parent().hasClass("selected");
	$(".view .table tr").removeClass('selected');
	selected ? $(this).parent().removeClass('selected') : $(this).parent().addClass('selected');
	$(".btn-editar,.btn-delete,.btn-for-selected").attr("disabled",'disabled');
	if( $(".view .table .selected").length > 0 )
	{
		$(".btn-editar,.btn-delete,.btn-for-selected").removeAttr('disabled');
	}
}



