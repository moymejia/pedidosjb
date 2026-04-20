function get_temporadas_parametro() {
	return $('#idtemporada').val().join('|');
}

function generar_comparativo() {
	let temporadas = $('#idtemporada').val();
	let temporadas_parametro = get_temporadas_parametro();
	let idcliente = elementValue('idcliente');
	let idmarca = elementValue('idmarca');

	if (temporadas.length === 0) {
		notify_warning('Debe seleccionar al menos una temporada.');
		return false;
	}

	if (idcliente === '' && idmarca === '') {
		notify_warning('Debe seleccionar un cliente o una marca.');
		return false;
	}

	var callback_reporte = function () {
		desactivar_tabla(tabla);
		if (objeto('tabla_datos') != undefined && objeto('tabla_datos').classList.contains('datatable')) {
			tabla = activar_tabla('tabla_datos');
		}
	};

	download_div_content('idcliente,idmarca,idtemporada=' + temporadas_parametro, 'comparativo_temporadas', 'generar_comparativo', 'lista_ventas_temporada', callback_reporte);
	return true;
}
