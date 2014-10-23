function pintarMorrisUser(elementId, url) {
	$.get(url, function(_json) {
		var json = JSON.parse(_json);
		crearMorrisUser(elementId, json);
	});
}

function crearMorrisUser(elementId, json) {
	new Morris.Area({
		element : elementId,
		data : json.data,
		lineColors : [ '#d43f3a' ],
		xkey : json.xkey,
		ykeys : json.ykeys,
		labels : json.labels,
		postUnits : '',
		dateFormat : function(data) {
			var monthNames = [ "Enero", "Febrero", "Marzo", "Abril", "Mayo",
					"Junio", "Julio", "Agosto", "Septiembre", "Octubre",
					"Noviembre", "Diciembre" ];
			return monthNames[new Date(data).getYear() - 1]
		},
		xLabelFormat : function(data) {
			var monthNames = [ "Enero", "Febrero", "Marzo", "Abril", "Mayo",
					"Junio", "Julio", "Agosto", "Septiembre", "Octubre",
					"Noviembre", "Diciembre" ];
			return monthNames[data.getYear() - 1]
		},
		hideHover : true,
		resize : true,
	});
}

/**
 * Animar los iconos para editar
 */
$(document).on('mouseover', '.autor .row', function(e) {
	e.preventDefault();
	$(this).find('.btn-xs').parent().removeClass('hidden');
});

$(document).on('mouseleave', '.autor .row', function(e) {
	e.preventDefault();
	$(this).find('.btn-xs').parent().addClass('hidden');
});


/**
 * Botón seguir
 */
$(document).on('click', '.seguir', function(e) {
	e.preventDefault();
	var $this =  $(this);
	var url = $this.attr('url');
	var nonce = $this.attr('nonce');
	var id = $this.attr('id');
	var seguir = $this.attr('seguir');
	var data = {
		submit : 'user',
		tipo: 'seguir',
		id : id,
		seguir: seguir,
		nonce: nonce
	};	
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		beforeSend: function() {
			$this.find('.fa-spin').removeClass('hidden');
			$this.find('.fa-plus').addClass('hidden');
			$this.find('.fa-remove').addClass('hidden');
			$this.attr('disabled', true);
		},
		success : function(json) {
			if(json.code == 200){
				$this.attr('disabled', false);
				$this.replaceWith(json.btn);
				$this.find('.fa-spin').addClass('hidden');
				$this.find('.fa-plus').removeClass('hidden');
				$this.find('.fa-remove').removeClass('hidden');
				mostrarAlerta(json.alert, 2);
			} else {
				mostrarAlerta(json.err, 2);
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log("status: "+xhr.status + ",\n responseText: "+xhr.responseText 
			+ ",\n thrownError "+thrownError);
	     }
	});
});

/**
 * Mostrar el campo para escribir un mensaje a un Usuario
 */
$(document).on('click', '.escribir-mensaje', function(e) {
	e.preventDefault();
	$this = $(this);
	$this.addClass('hidden');
	p = $this.parent('.mensaje');
	f = p.find('form');
	f.removeClass('hidden');
});

$(document).on('click', '.cancelar-enviar-mensaje', function(e) {
	e.preventDefault();
	cerrarEscribirMensaje($(this));
});

function cerrarEscribirMensaje($this){
	$this.find('.fa-spin').addClass('hidden');
	parent = $this.parents('.mensaje');
	form = $this.parents('.mensaje').find('form');
	form.addClass('hidden');
	form.find('#mensaje').val('');
	btnEscribir = parent.find('.escribir-mensaje');
	btnEscribir.removeClass('hidden');
}
/**
 * Botón enviar un mensaje
 */
$(document).on('click', '.enviar-mensaje', function(e) {
	e.preventDefault();
	var $this =  $(this);
	var page = $this.parents('#page');
	var url = page.attr('url');
	var parent = $this.parents('.mensaje');
	var form = parent.find('form');
	var mensaje = form.find('#mensaje').val();
	var nonce = form.attr('nonce');
	var mensaje_id = $this.attr('mensaje_id');
	var user_id = $this.attr('user_id');
	var respuesta_id = $this.attr('respuesta_id');

	if(mensaje.length == 0){
		alert("Texto demasiado corto");
		return;
	}
	var data = {
		submit : 'user',
		tipo: 'enviar-mensaje',
		nonce: nonce,
		mensaje: mensaje,
		mensaje_id: mensaje_id,
		user_id : user_id,
		respuesta_id: respuesta_id,
	};
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		beforeSend: function() {
			$this.find('.fa-spin').removeClass('hidden');
		},
		success : function(json) {
			if(json.code == 200){
				cerrarEscribirMensaje($this);
				//cant = $this.parents('#mensajes').find('.nav a[href="#enviados"] .cant');
				//cant.text(parseInt(cant.text(), 10)+1);
			}
			mostrarAlerta(json.alert, 2);
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log("status: "+xhr.status + ",\n responseText: "+xhr.responseText 
			+ ",\n thrownError "+thrownError);
	     }
	});
});

/**
 * Botón borrar un mensaje
 */
$(document).on('click', '.borrar-mensaje', function(e) {
	e.preventDefault();
	var $this =  $(this);
	var page = $this.parents('#page');
	var url = page.attr('url');
	var mensaje = $this.parents('.mensaje');
	var nonce = $this.attr('nonce');
	var mensaje_id  = $this.attr('mensaje_id');
	
	if(!confirm("¿Estás seguro de querer borrar el mensaje?")){
		return;
	}
	
	var data = {
		submit : 'user',
		tipo: 'borrar-mensaje',
		nonce: nonce,
		mensaje_id : mensaje_id,
	};
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		beforeSend: function() {
			$this.find('.fa-spin').removeClass('hidden');
		},
		success : function(json) {
			if(json.code == 200) {
				cant = $this.parents('#mensajes').find('.nav li[class="active"] .cant');
				cant.text(parseInt(cant.text(), 10)-1);

				$this.find('.fa-spin').addClass('hidden');
				mensaje.addClass('hidden');				
			}
			mostrarAlerta(json.alert, 2);			
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log("status: "+xhr.status + ",\n responseText: "+xhr.responseText 
			+ ",\n thrownError "+thrownError);
	     }
	});
});