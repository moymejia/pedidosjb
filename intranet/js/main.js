/*
function mostrar_opcion(idopcion, opcion, menu){
	element('idopcion_actual').value = idopcion; 
	download_div_content('idopcion_actual','opcion','cargar_opcion','contenedor_principal'); 
	document.getElementById('contenedor_principal').innerHTML = "";
	document.getElementById('menu_actual').innerHTML = menu;
	document.getElementById('opcion_actual').innerHTML = opcion;
	document.getElementById('titulo_actual').innerHTML = opcion;
	document.getElementById('detalle_actual').innerHTML = "";
	var url = opcion.replace(/ /g,'_');
	url = url.toLowerCase();
	url = "../php/opcion_"+url+".php";
	var onRresponse = function(respuesta){
		document.getElementById('contenedor_principal').innerHTML = respuesta.responseText;
		habilitar_floating_labels();
		desactivar_tabla(tabla);
		if(objeto('tabla_datos') != undefined && objeto('tabla_datos').classList.contains('datatable')){
			tabla = activar_tabla('tabla_datos');
		}
		activate_select2();
		activate_switch();
	}
	ajax_tofunction("", url, onRresponse);
	return false;
}*/