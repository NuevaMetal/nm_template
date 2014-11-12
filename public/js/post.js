$(document).ready(function() {
	actualizarEntradas(ENTRADAS_SIMILARES);
	actualizarEntradas(ENTRADAS_RELACIONADAS);
	crearIntervaloActualizarEntradas();
});

/** 
 * Crear intervalo de actualización de las entradas.
 */
function crearIntervaloActualizarEntradas() {
	var segParaActualizar = 50;
	/* Actualizar cada 50 sg */
	setInterval(function(){
		actualizarEntradas(ENTRADAS_SIMILARES);
	} , segParaActualizar * 1000);
	setInterval(function(){
		actualizarEntradas(ENTRADAS_RELACIONADAS);
	}, segParaActualizar * 1000);
}

$(document).on('click', '#sidebar .refrescar', function(e) {
	e.preventDefault();
	actualizarEntradas($(this).attr('tipo'));
});