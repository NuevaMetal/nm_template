/**
 * 
 * @param elementId
 * @param url
 */
function pintarMorris(elementId, url) {
	$.get(url, function(_json) {
		var json = JSON.parse(_json);
		crearMorris(elementId, json);
	});
}

/**
 * Crear Morris
 * 
 * @param string
 *            elementId ID del elemento a dibujar
 * @param string
 *            _json JSON
 */
function crearMorris(elementId, json) {
	new Morris.Line({
		element : elementId,
		data : json.data,
		xkey : json.xkey,
		ykeys : json.ykeys,
		labels : json.labels,
		xLabelFormat : function(data) {
			// var monthNames = [ "January", "February", "March", "April",
			// "May",
			// "June", "July", "August", "September", "October",
			// "November", "December" ];
			var monthNames = [ "Enero", "Febreo", "Marzo", "Abril", "Mayo",
					"Junio", "Julio", "Agosto", "Septiembre", "Octubre",
					"Noviembre", "Diciembre" ];
			return data.getDate() + " - " + monthNames[data.getMonth()]
		},
	});
}