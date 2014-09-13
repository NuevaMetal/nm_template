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
		xLabels : "month",
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
			return monthNames[data.getYear()]
		},
	});
}