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
	var que_id = $(this).attr('que_id');
	var url = $(this).attr('url');
	var data = {
		submit: submit,
		estado: estado,
		que_id: que_id
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

$(document).on('click','.quitar-header', function(e) {
	e.preventDefault();
	var $this = $(this);
	if(!confirm('¿Estás seguro de querer quitarte el header?')){
		return;
	}
	var url = $this.attr('url');
	var que = $this.attr('que');
	var user = $this.attr('user');
	var data = {
		submit : 'admin-panel-user',
		que : que,
		user : user
	};
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		success : function(json) {			
			var header = $('.img-header img');
			header.attr('src','');
			header.addClass('sin-thumbnail');			
		},
		error : function(xhr, ajaxOptions, thrownError) {
			console.log("status: " + xhr.status + ",\n responseText: "
					+ xhr.responseText + ",\n thrownError "
					+ thrownError);
		}
	});
});

/**
 * Hacer colaborador a un suscriptor
 */
$(document).on('click','.hacer-colaborador', function(e) {
	e.preventDefault();
	var $this = $(this);
	if(!confirm('¿Estás seguro de querer realizar esta acción? '+  $this.text())){
		return;
	}
	var url = $this.attr('url');
	var user = $this.attr('user');
	var editor = $this.attr('editor');
	var que = $this.attr('que');
	var data = {
		submit : 'hacer-colaborador',
		user : user,
		editor: editor,
		que: que
	};
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		success : function(json) {
			location.reload();
		},
		error : function(xhr, ajaxOptions, thrownError) {
			console.log("status: " + xhr.status + ",\n responseText: "
					+ xhr.responseText + ",\n thrownError "
					+ thrownError);
		}
	});
});

/**
 * 
 */
$(document).on('click','.quitar-header, .quitar-avatar, .bloquear, .desbloquear', function(e) {
	e.preventDefault();
	var $this = $(this);
	if(!confirm('¿Estás seguro de querer '+$this.text().toUpperCase()+' a este usuario?')){
		return;
	}
	var url = $this.attr('url');
	var que = $this.attr('que');
	var user = $this.attr('user');
	var data = {
		submit : 'admin-panel-user',
		que : que,
		user : user
	};
	if(que == 'bloquear' && !confirm('Bloquear a un usuario implica que este ya no podrá acceder a su cuenta'
			+' además de que ya no se podrá visualizar su perfil de usuario público a menos que se desactive el bloqueo.'
			+' ¿Estás seguro de continuar?')){
		return;
	}
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		success : function(json) {
			location.reload();
		},
		error : function(xhr, ajaxOptions, thrownError) {
			console.log("status: " + xhr.status + ",\n responseText: "
					+ xhr.responseText + ",\n thrownError "
					+ thrownError);
		}
	});
});
