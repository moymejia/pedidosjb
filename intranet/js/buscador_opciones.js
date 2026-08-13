(function () {
    var opciones_menu = [];
    var opcion_activa = -1;

    function normalizar_busqueda(texto) {
        return (texto || "")
            .toString()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .toLowerCase()
            .trim();
    }

    function obtener_palabras_busqueda(texto) {
        return normalizar_busqueda(texto)
            .split(/[^a-z0-9]+/)
            .filter(function (palabra) {
                return palabra !== "";
            });
    }

    function construir_indice_opciones(menu_lateral) {
        opciones_menu = [];

        menu_lateral.querySelectorAll("li:not(.sidebar-search-option) ul.collapse li > a[onclick*='mostrar_opcion']").forEach(function (enlace) {
            var opcion = enlace.textContent.replace(/\s+/g, " ").trim();

            opciones_menu.push({
                enlace: enlace,
                opcion: opcion,
                palabras: obtener_palabras_busqueda(opcion)
            });
        });
    }

    function coincide_busqueda(item, busqueda) {
        var palabras_buscadas = obtener_palabras_busqueda(busqueda);

        if (palabras_buscadas.length === 0) {
            return false;
        }

        return palabras_buscadas.every(function (palabra_buscada) {
            return item.palabras.some(function (palabra_opcion) {
                return palabra_opcion.indexOf(palabra_buscada) === 0;
            });
        });
    }

    function buscar_opciones(valor) {
        var busqueda = normalizar_busqueda(valor);

        if (busqueda === "") {
            return [];
        }

        return opciones_menu.filter(function (item) {
            return coincide_busqueda(item, busqueda);
        });
    }

    function marcar_resultado_activo(contenedor_resultados) {
        contenedor_resultados.querySelectorAll(".sidebar-option-result").forEach(function (resultado, indice) {
            resultado.classList.toggle("active", indice === opcion_activa);
        });
    }

    function retraer_menu_lateral() {
        var boton_sidebar;

        if (window.matchMedia("(max-width: 767px)").matches) {
            if (document.body.classList.contains("show-sidebar")) {
                boton_sidebar = document.querySelector(".nav-toggler");
                if (boton_sidebar) {
                    boton_sidebar.click();
                } else {
                    document.body.classList.remove("show-sidebar");
                }
            }
            return;
        }
    }

    function seleccionar_resultado(item, buscador, contenedor_resultados) {
        buscador.value = "";
        buscador.blur();
        contenedor_resultados.classList.add("d-none");
        contenedor_resultados.innerHTML = "";
        item.enlace.click();
        retraer_menu_lateral();

        if (document.activeElement && document.activeElement.blur) {
            document.activeElement.blur();
        }
    }

    function pintar_resultados(coincidencias, buscador, contenedor_resultados) {
        contenedor_resultados.innerHTML = "";
        opcion_activa = coincidencias.length > 0 ? 0 : -1;

        if (coincidencias.length === 0) {
            var vacio = document.createElement("div");
            vacio.className = "list-group-item small text-muted";
            vacio.textContent = "Sin resultados";
            contenedor_resultados.appendChild(vacio);
            contenedor_resultados.classList.remove("d-none");
            return;
        }

        coincidencias.slice(0, 10).forEach(function (item, indice) {
            var boton = document.createElement("button");
            var texto_opcion = document.createElement("span");

            boton.type = "button";
            boton.className = "list-group-item list-group-item-action sidebar-option-result";
            boton.setAttribute("data-index", indice);
            texto_opcion.textContent = item.opcion;

            boton.appendChild(texto_opcion);
            boton.addEventListener("click", function () {
                seleccionar_resultado(item, buscador, contenedor_resultados);
            });
            contenedor_resultados.appendChild(boton);
        });

        marcar_resultado_activo(contenedor_resultados);
        contenedor_resultados.classList.remove("d-none");
    }

    function inicializar_eventos() {
        var buscador = document.getElementById("buscador_opciones");
        var contenedor_resultados = document.getElementById("resultados_buscador_opciones");
        var boton_mini = document.getElementById("abrir_buscador_opciones");

        if (!buscador || !contenedor_resultados || !boton_mini) {
            return;
        }

        function actualizar_busqueda() {
            var coincidencias = buscar_opciones(buscador.value);

            if (normalizar_busqueda(buscador.value) === "") {
                contenedor_resultados.classList.add("d-none");
                contenedor_resultados.innerHTML = "";
                return;
            }

            pintar_resultados(coincidencias, buscador, contenedor_resultados);
        }

        boton_mini.addEventListener("click", function () {
            if (normalizar_busqueda(buscador.value) !== "") {
                buscador.value = "";
                actualizar_busqueda();
            }

            setTimeout(function () {
                buscador.focus();
            }, 0);
        });
        boton_mini.addEventListener("keydown", function (event) {
            if (event.key === "Enter" || event.key === " ") {
                event.preventDefault();
                boton_mini.click();
            }
        });

        buscador.addEventListener("input", actualizar_busqueda);
        buscador.addEventListener("focus", function () {
            if (normalizar_busqueda(buscador.value) !== "") {
                actualizar_busqueda();
            }
        });
        buscador.addEventListener("keydown", function (event) {
            var resultados = contenedor_resultados.querySelectorAll(".sidebar-option-result");

            if (event.key === "ArrowDown" && resultados.length > 0) {
                event.preventDefault();
                opcion_activa = opcion_activa >= resultados.length - 1 ? 0 : opcion_activa + 1;
                marcar_resultado_activo(contenedor_resultados);
            } else if (event.key === "ArrowUp" && resultados.length > 0) {
                event.preventDefault();
                opcion_activa = opcion_activa <= 0 ? resultados.length - 1 : opcion_activa - 1;
                marcar_resultado_activo(contenedor_resultados);
            } else if (event.key === "Enter" && resultados.length > 0 && opcion_activa >= 0) {
                event.preventDefault();
                resultados[opcion_activa].click();
            } else if (event.key === "Escape" && normalizar_busqueda(buscador.value) !== "") {
                buscador.value = "";
                actualizar_busqueda();
            }
        });

        document.addEventListener("click", function (event) {
            if (!event.target.closest(".sidebar-option-search")) {
                contenedor_resultados.classList.add("d-none");
            }
        });
    }

    function inicializar_buscador_opciones() {
        var menu_lateral = document.getElementById("sidebarnav");

        if (!menu_lateral) {
            return;
        }

        construir_indice_opciones(menu_lateral);
        inicializar_eventos();
    }

    document.addEventListener("DOMContentLoaded", inicializar_buscador_opciones);
})();
