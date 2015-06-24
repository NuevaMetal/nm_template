$(document).ready(function() {
	actualizarEntradas();
	var segParaActualizar = 360;
	/* 
	 * Crear intervalo de actualización de las entradas.
	 * Actualizar cada 50 sg 
	 */
	setInterval(function(){
		actualizarEntradas();
	} , segParaActualizar * 1000);
});

function actualizarEntradas() {
	$('#post-sidebar .refrescar').trigger('click');
}

$(document).on('click', '#post-sidebar .refrescar', function(e) {
	e.preventDefault();
	$this = $(this);
	var url = $('#page').attr('url');
	var post = $this.parents('#post').attr('_id');
	var tipo = $this.attr('tipo');
	var nonce = $this.attr('nonce');
	var data = {
		submit : 'post',
		tipo: tipo,
		post: post,
		nonce: nonce,
	};
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		beforeSend: function(){
			$('#post-sidebar .fa').addClass('hidden');
			$('#post-sidebar .fa-spin').removeClass('hidden');
		},
		success : function(json) {
			if (json.code == 200) {
				$('#post-sidebar .fa').removeClass('hidden');
				$('#post-sidebar .fa-spin').addClass('hidden');
				var elemento = $('#post-sidebar #'+tipo);
				elemento.find('article').addClass("animated zoomOut");
				setTimeout(function() {
					elemento.empty(); // eliminar hijos
					elemento.append($(json.content).addClass('animated zoomIn')); // poner el contenido
				},500);
			}
		},
		error : function(xhr, ajaxOptions, thrownError) {
			console.log("status: " + xhr.status 
					//+ ",\n responseText: "+ xhr.responseText 
					+ ",\n thrownError " + thrownError);
		}
	});
});
