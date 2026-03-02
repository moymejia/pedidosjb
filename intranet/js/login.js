function login(){
	var usuario = document.getElementById('usuario').value;
	var clave = document.getElementById('clave').value;

	var datos = datos_formulario(objeto('loginform'));

	var onRresponse = function(respuesta){
		resultado = respuesta.responseText.split("|");
		console.log(resultado);
		if (resultado[1]== "correcto") {
			notify_success("Acceso correcto");
			window.location.href = resultado[2];
		}else if(resultado[1]=="error"){
			notify_error(resultado[2]);
		}
	}
	ajax_tofunction(datos,"../php/login.php",onRresponse);
	return false;
}