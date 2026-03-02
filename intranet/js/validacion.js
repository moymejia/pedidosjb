function fullscreen() {
    var objetofullscreen = document.getElementsByTagName("body")[0];
    var pantalla = objetofullscreen.requestFullscreen();
    // element.requestFullscreen();
}

function mostrar_biemvenida() {
    // console.log("mostrar_biemvenida");
    showElement("div_espera");
    hideElements("div_calificacion,agradecimiento");
    element("calificacion_1").style.backgroundColor = "";
    element("calificacion_2").style.backgroundColor = "";
    element("calificacion_3").style.backgroundColor = "";
    element("calificacion_4").style.backgroundColor = "";
    element("calificacion_5").style.backgroundColor = "";
}

function mostrar_calificacion() {
    // console.log("mostrar_calificacion");
    showElement("div_calificacion");
    hideElements("div_espera,agradecimiento");
}

function mostrar_agradecimiento(seleccionado) {
    // console.log("mostrar_agradecimiento");
    seleccionado.style.backgroundColor = "#4d5f80";
    // en 0.25 segt mostrar_agradecimientof2
    setTimeout(mostrar_agradecimientof2, 250);
    guardar_calificacion(seleccionado);
}

function mostrar_agradecimientof2() {
    // console.log("mostrar_agradecimientof2");
    // seleccionado.style.backgroundColor = "";
    showElement("agradecimiento");
    hideElements("div_espera,div_calificacion");
    // 5 seconds later mostrar_biemvenida
    setTimeout(mostrar_biemvenida, 5000);
}

// iniciar consultas recurrentes de solicitude de calificacion cada 2 segundos
setInterval(consultar_calificacion, 3000);

function consultar_calificacion() {
    // console.log("consultar_calificacion");
    procesar_solicitud = function (data) {
        data = JSON.parse(data);
        if (data["cantidad_solicitudes"] > 0) {
            mostrar_calificacion();
            // showElement("agradecimiento");
            // hideElements("div_espera,div_calificacion");
        } else {
            // setTimeout(mostrar_biemvenida, 5000);
            mostrar_biemvenida();
            // showElement("agradecimiento");
            // hideElements("div_espera,div_calificacion");
        }
    };
    upload_action("", "calificacion", "consultar_solicitud", procesar_solicitud);
}

function guardar_calificacion(seleccionado) {
    calificacion = seleccionado.id.split("_")[1];
    upload_action("calificacion=" + calificacion, "calificacion", "guardar_calificacion");
}
