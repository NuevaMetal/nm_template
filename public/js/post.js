$(document).ready(function() {
	actualizarEntradas("entradas-similares");
	actualizarEntradas("entradas-relacionadas");
	crearIntervaloActualizarEntradas();
});

/** 
 * Crear intervalo de actualización de las entradas.
 */
function crearIntervaloActualizarEntradas() {
	var segParaActualizar = 50;
	/* Actualizar cada 50 sg */
	setInterval(function(){
		actualizarEntradas("entradas-similares");
	} , segParaActualizar * 1000);
	setInterval(function(){
		actualizarEntradas("entradas-relacionadas");
	}, segParaActualizar * 1000);
}

$(document).on('click', '#sidebar .refrescar', function(e) {
	e.preventDefault();
	actualizarEntradas($(this).attr('tipo'));
});