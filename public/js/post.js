$(document).ready(function() {
	actualizarEntradas();
	var segParaActualizar = 40;
	/* 
	 * Crear intervalo de actualización de las entradas.
	 * Actualizar cada 50 sg 
	 */
	setInterval(function(){
		actualizarEntradas();
	} , segParaActualizar * 1000);
});

function actualizarEntradas() {
	$('#sidebar .refrescar').trigger('click');
}

$(document).on('click', '#sidebar .refrescar', function(e) {
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
			$('#sidebar .fa').addClass('hidden');
			$('#sidebar .fa-spin').removeClass('hidden');
		},
		success : function(json) {
			if (json.code == 200) {
				$('#sidebar .fa').removeClass('hidden');
				$('#sidebar .fa-spin').addClass('hidden');
				var elemento = $('#sidebar #'+tipo);
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
