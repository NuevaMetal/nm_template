/**
 * Documento listo para JQuery
 */
$(document).ready(function() {
	
});

/**
 * Disparador
 */
$(document).on('click', '.dispara-revision', function(e) {
	e.preventDefault();
	var $this = $(this);
	var estado = $(this).attr('estado');
	var submit = $(this).attr('submit');
	var post = $(this).attr('post');
	var url = $(this).attr('url');
	var data = {
		submit: submit,
		estado: estado,
		post: post		
	};
	console.log(data);	
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		beforeSend: function() {
			$this.find('.fa-spin').removeClass('hidden');
			$this.attr("disabled", true);
		},
		success : function(json) {
			console.log(json);
			if(json.code == 200 ) {
				$('#alertas').html(json.content);
				$('#alertas').fadeIn();
				$this.find('.fa-spin').addClass('hidden');				
				setTimeout(function(){
					$('#alertas').fadeOut();
				}, 3000);
			}
			$this.find('.fa-spin').addClass('hidden');
			$this.attr("disabled", false);
			location.reload();
		},
		error: function (xhr, ajaxOptions, thrownError) {
	        alert("Ocurrió un error inesperado.\n" 
	        		+"Por favor, ponte en contacto con los administradores y coméntale qué sucedió.");
			console.log("status: "+xhr.status + ",\n responseText: "+xhr.responseText 
			+ ",\n thrownError "+thrownError);
	     }
	});
});
