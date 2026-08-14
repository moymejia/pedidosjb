// 
var _dtAddFilterSelects = {}; // mapa global: tableId -> [nodo select, ...]
var _dtFilterState = {}; // tableId_colIdx -> [valores seleccionados]
var _dtAggregationState = {}; // tableId_col:nombreColumna -> "sum" | "count"

function clave_estado_datatable(idtabla) {
    var usuario = window.usuario_actual_datatables || 'sin_usuario';
    return 'DataTables_' + encodeURIComponent(usuario) + '_' + idtabla;
}

function obtener_texto_agregacion(valor) {
    if (valor === undefined || valor === null) {
        return '';
    }

    var container = document.createElement('div');
    container.innerHTML = String(valor);
    container.querySelectorAll('input, select, textarea, button, script, style').forEach(function (elemento) {
        elemento.parentNode.removeChild(elemento);
    });
    return (container.textContent || container.innerText || '').replace(/\u00a0/g, ' ').trim();
}

function parsear_numero_agregacion(valor) {
    var texto = obtener_texto_agregacion(valor);
    if (texto === '') {
        return null;
    }

    var negativo_parentesis = /^\s*\(.*\)\s*$/.test(texto);
    texto = texto
        .replace(/[()]/g, '')
        .trim()
        .replace(/^(?:GTQ|USD|EUR|Q(?:UETZALES?)?\.?|US\$|\$|€|£)\s*/i, '')
        .replace(/\s*(?:GTQ|USD|EUR|Q(?:UETZALES?)?\.?|US\$|\$|€|£|%)$/i, '')
        .replace(/\s+/g, '');

    // Rechazar texto con números incidentales, por ejemplo "Sector 4".
    if (texto === '' || !/^[+-]?[0-9][0-9,.]*$/.test(texto)) {
        return null;
    }

    var ultima_coma = texto.lastIndexOf(',');
    var ultimo_punto = texto.lastIndexOf('.');
    var separador_decimal = '';

    if (ultima_coma !== -1 && ultimo_punto !== -1) {
        separador_decimal = ultima_coma > ultimo_punto ? ',' : '.';
    } else if (ultima_coma !== -1) {
        var decimales_coma = texto.length - ultima_coma - 1;
        separador_decimal = decimales_coma > 0 && decimales_coma <= 2 ? ',' : '';
    } else if (ultimo_punto !== -1) {
        var decimales_punto = texto.length - ultimo_punto - 1;
        separador_decimal = decimales_punto > 0 && decimales_punto <= 2 ? '.' : '';
    }

    if (separador_decimal !== '') {
        var posicion_decimal = texto.lastIndexOf(separador_decimal);
        var parte_entera = texto.substring(0, posicion_decimal).replace(/[,.]/g, '');
        var parte_decimal = texto.substring(posicion_decimal + 1).replace(/[,.]/g, '');
        texto = parte_entera + '.' + parte_decimal;
    } else {
        texto = texto.replace(/[,.]/g, '');
    }

    var numero = Number(texto);
    if (!Number.isFinite(numero)) {
        return null;
    }
    return negativo_parentesis ? -Math.abs(numero) : numero;
}

function normalizar_columna_agregacion(titulo) {
    return obtener_texto_agregacion(titulo).replace(/\s+/g, ' ').trim().toUpperCase();
}

function obtener_id_columna_agregacion(titulo) {
    return 'col:' + encodeURIComponent(normalizar_columna_agregacion(titulo));
}

function obtener_indice_actual_agregacion(dt, columna_id) {
    var nombre_buscado = '';
    if (typeof columna_id === 'string' && columna_id.indexOf('col:') === 0) {
        try {
            nombre_buscado = decodeURIComponent(columna_id.substring(4));
        } catch (e) {
            nombre_buscado = '';
        }
    }
    if (nombre_buscado === '') {
        return -1;
    }

    var indice_actual = -1;
    dt.columns().every(function (indice) {
        if (normalizar_columna_agregacion(this.title()) === nombre_buscado) {
            indice_actual = indice;
            return false;
        }
    });
    return indice_actual;
}

function calcular_agregacion_columna(dt, indices_filas, indice_columna, tipo) {
    var resultado = 0;
    var INDICES = indices_filas && typeof indices_filas.toArray === 'function'
        ? indices_filas.toArray()
        : Array.prototype.slice.call(indices_filas || []);

    for (var i = 0; i < INDICES.length; i++) {
        var valor = dt.cell(INDICES[i], indice_columna).render('filter');
        if (tipo === 'count') {
            if (obtener_texto_agregacion(valor) !== '') {
                resultado++;
            }
        } else if (tipo === 'sum') {
            var numero = parsear_numero_agregacion(valor);
            if (numero !== null) {
                resultado += numero;
            }
        }
    }
    return resultado;
}

function formatear_resultado_agregacion(valor, tipo) {
    if (tipo === 'count') {
        return String(valor);
    }
    // Limitar la salida para ocultar residuos binarios sin perder decimales significativos.
    return new Intl.NumberFormat('es-GT', { maximumFractionDigits: 8 }).format(valor);
}

function contiene_resultado_agregacion(valor) {
    var contenido = valor;
    if (valor && typeof valor === 'object') {
        contenido = valor.text;
        if (Array.isArray(contenido)) {
            contenido = contenido.map(function (parte) {
                return parte && typeof parte === 'object' ? parte.text : parte;
            }).join(' ');
        }
    }
    var texto = obtener_texto_agregacion(contenido);
    return /(^|\u2014\s)(Suma|Conteo)\s/i.test(texto);
}

function estilizar_filas_agregacion_pdf(doc) {
    if (!doc || !Array.isArray(doc.content)) return;
    doc.content.forEach(function (contenido) {
        if (!contenido || !contenido.table || !Array.isArray(contenido.table.body)) return;
        contenido.table.body.forEach(function (fila) {
            if (!Array.isArray(fila) || !fila.some(contiene_resultado_agregacion)) return;
            fila.forEach(function (celda, indice) {
                if (typeof celda === 'string') {
                    fila[indice] = { text: celda, bold: true, fillColor: '#E2E3E5' };
                } else if (celda && typeof celda === 'object') {
                    celda.bold = true;
                    celda.fillColor = '#E2E3E5';
                }
            });
        });
    });
}

function estilizar_filas_agregacion_impresion(doc) {
    if (!doc) return;
    doc.querySelectorAll('table tbody tr, table tfoot tr').forEach(function (fila) {
        if (!contiene_resultado_agregacion(fila.textContent)) return;
        fila.style.fontWeight = '700';
        fila.style.backgroundColor = '#e2e3e5';
        fila.querySelectorAll('th, td').forEach(function (celda) {
            celda.style.backgroundColor = '#e2e3e5';
        });
    });
}

function estilizar_filas_agregacion_excel(xlsx) {
    if (!xlsx || !xlsx.xl || !xlsx.xl.worksheets) return;
    var hoja = xlsx.xl.worksheets['sheet1.xml'];
    // En Buttons el archivo se encuentra directamente bajo xl. Mantener el
    // segundo acceso como compatibilidad con otras versiones de la extensión.
    var estilos = xlsx.xl['styles.xml'] ||
        (xlsx.xl.styles && xlsx.xl.styles['styles.xml']);
    if (!hoja || !estilos) return;

    var textos_compartidos = [];
    var shared = xlsx.xl['sharedStrings.xml'];
    if (shared) {
        $('si', shared).each(function () { textos_compartidos.push($(this).text()); });
    }
    var filas_agregacion = {};
    $('sheetData row c', hoja).each(function () {
        var celda = $(this);
        var texto = '';
        if (celda.attr('t') === 's') {
            texto = textos_compartidos[parseInt(celda.find('v').text(), 10)] || '';
        } else {
            // Buttons escribe normalmente las cadenas como inlineStr. Usar el
            // contenido completo de la celda evita depender de selectores XML
            // sensibles al namespace del documento.
            texto = celda.text() || '';
        }
        if (contiene_resultado_agregacion(texto)) {
            filas_agregacion[celda.closest('row').attr('r')] = true;
        }
    });
    if (Object.keys(filas_agregacion).length === 0) return;

    // DataTables Buttons incluye el estilo 7: negrita con fondo gris.
    $('sheetData row', hoja).each(function () {
        if (filas_agregacion[$(this).attr('r')]) {
            $(this).find('c').attr('s', '7');
        }
    });
}

function obtener_agregaciones_estado_tabla(idtabla) {
    var AGREGACIONES = [];
    var prefijo = idtabla + '_';
    Object.keys(_dtAggregationState).forEach(function (state_key) {
        if (state_key.indexOf(prefijo) !== 0 || !_dtAggregationState[state_key]) {
            return;
        }
        var columna_id = state_key.substring(prefijo.length);
        if (columna_id.indexOf('col:') === 0) {
            AGREGACIONES.push({
                columna_id: columna_id,
                tipo: _dtAggregationState[state_key]
            });
        }
    });
    return AGREGACIONES;
}

function construir_filas_reporte_datatable(dt) {
    var INDICES_FILAS = dt.rows({ search: 'applied', order: 'applied', page: 'all' }).indexes().toArray();
    var filas = [];
    var agregar_fila_datos = function (indice_fila) {
        var nodo = dt.row(indice_fila).node();
        if (!nodo) {
            return;
        }
        var fila = nodo.cloneNode(true);
        fila.querySelectorAll('input, select, textarea, button').forEach(function (elemento) {
            elemento.parentNode.removeChild(elemento);
        });
        filas.push(fila.outerHTML);
    };

    var rowgroup_activo = dt.rowGroup && dt.rowGroup().enabled();
    var AGREGACIONES = obtener_agregaciones_estado_tabla(dt.table().node().id);
    if (!rowgroup_activo) {
        INDICES_FILAS.forEach(agregar_fila_datos);

        if (AGREGACIONES.length > 0) {
            var fila_total = document.createElement('tr');
            fila_total.className = 'dt-aggregation-export-total';
            for (var columna = 0; columna < dt.columns().count(); columna++) {
                fila_total.appendChild(document.createElement('td'));
            }
            AGREGACIONES.forEach(function (agregacion) {
                var indice_actual = obtener_indice_actual_agregacion(dt, agregacion.columna_id);
                if (indice_actual === -1 || !fila_total.cells[indice_actual]) {
                    return;
                }
                var resultado = calcular_agregacion_columna(dt, INDICES_FILAS, indice_actual, agregacion.tipo);
                fila_total.cells[indice_actual].textContent = (agregacion.tipo === 'sum' ? 'Suma ' : 'Conteo ') +
                    formatear_resultado_agregacion(resultado, agregacion.tipo);
                fila_total.cells[indice_actual].classList.add('dt-aggregation-group-value');
            });
            filas.push(fila_total.outerHTML);
        }
        return filas.join('');
    }

    var fuente_grupo = dt.rowGroup().dataSrc();
    if (Array.isArray(fuente_grupo)) {
        fuente_grupo = fuente_grupo[0];
    }
    var indice_grupo = typeof fuente_grupo === 'number' ? fuente_grupo : -1;
    if (indice_grupo === -1 && typeof fuente_grupo === 'string') {
        dt.columns().every(function (indice) {
            if (this.dataSrc() === fuente_grupo || normalizar_columna_agregacion(this.title()) === normalizar_columna_agregacion(fuente_grupo)) {
                indice_grupo = indice;
            }
        });
    }
    if (indice_grupo === -1) {
        INDICES_FILAS.forEach(agregar_fila_datos);
        return filas.join('');
    }

    var grupo_anterior;
    var INDICES_GRUPO = [];
    var finalizar_grupo = function (grupo) {
        if (INDICES_GRUPO.length === 0) {
            return;
        }
        var fila_grupo = document.createElement('tr');
        fila_grupo.className = 'dtrg-group dtrg-start dtrg-level-0 dt-aggregation-group-row';
        var cantidad_columnas = dt.columns().count();
        for (var c = 0; c < cantidad_columnas; c++) {
            var celda = document.createElement(c === indice_grupo ? 'th' : 'td');
            if (c === indice_grupo) {
                celda.setAttribute('scope', 'row');
                celda.appendChild(document.createTextNode(grupo + ' (' + INDICES_GRUPO.length + ' registros)'));
            }
            fila_grupo.appendChild(celda);
        }
        AGREGACIONES.forEach(function (agregacion) {
            var indice_actual = obtener_indice_actual_agregacion(dt, agregacion.columna_id);
            if (indice_actual === -1 || !fila_grupo.cells[indice_actual]) {
                return;
            }
            var resultado = calcular_agregacion_columna(dt, INDICES_GRUPO, indice_actual, agregacion.tipo);
            var elemento = document.createElement('span');
            elemento.className = 'dt-aggregation-group-value';
            elemento.textContent = (agregacion.tipo === 'sum' ? 'Suma ' : 'Conteo ') +
                formatear_resultado_agregacion(resultado, agregacion.tipo);
            if (indice_actual === indice_grupo) {
                elemento.style.display = 'block';
            } else {
                fila_grupo.cells[indice_actual].classList.add('dt-aggregation-group-value');
            }
            fila_grupo.cells[indice_actual].appendChild(elemento);
        });
        filas.push(fila_grupo.outerHTML);
        INDICES_GRUPO.forEach(agregar_fila_datos);
        INDICES_GRUPO = [];
    };

    INDICES_FILAS.forEach(function (indice_fila) {
        var grupo_actual = obtener_texto_agregacion(dt.cell(indice_fila, indice_grupo).render('display'));
        if (grupo_anterior !== undefined && grupo_actual !== grupo_anterior) {
            finalizar_grupo(grupo_anterior);
        }
        grupo_anterior = grupo_actual;
        INDICES_GRUPO.push(indice_fila);
    });
    finalizar_grupo(grupo_anterior === undefined ? '' : grupo_anterior);
    return filas.join('');
}
document.addEventListener('click', function (e) {
    const label = e.target.closest('.dt-search label');
    if (!label) return;

    const container = label.closest('.dt-search');
    const input = container.querySelector('input[type="search"]');

    input.value = '';
    input.dispatchEvent(new Event('input', { bubbles: true }));
    console.log('llimpiar');
});
//
function prepare_print_window_styles(win, idtabla, callback) {
    var doc = win.document;
    var pending = 0;
    var finalize = function () {
        pending--;
        if (pending <= 0) {
            callback();
        }
    };

    // inject_print_fallback_styles(doc);

    pending++;
    append_print_stylesheet(doc, resolve_print_asset_url("../css/dt2.print.css?x=" + version), false, finalize);

    pending++;
    append_print_stylesheet(doc, resolve_print_asset_url("../css/print/" + idtabla + ".css?x=" + version), true, finalize);
}
//
function datatables_staterestore_ajax(idtabla, data, callback) {
    console.log('staterestore_ajax action:', data ? data.action : 'sin data');
    if (!data || !data.action) {
        if (callback) callback({});
        return;
    }

    if (data.action === "load") {
        upload_action(
            "idtabla=" + encodeURIComponent(idtabla),
            "datatables",
            "cargar_estados_datatables_staterestore",
            function (respuesta) {
                var estados = {};
                try {
                    // estados = JSON.parse(respuesta || "{}");
                    respuesta = (respuesta || "{}").replace(/##PIPE##/g, '|');
                    //console.log('respuesta después de replace:', respuesta);
                    //console.log('largo:', respuesta.length);
                    estados = JSON.parse(respuesta);
                } catch (e) {
                    estados = {};
                }
                // console.log('estados parseados:', estados);  // ← agrega esto
                if (callback) callback(estados);
                // Hidratar _dtFilterState con los customSelectFilters de cada estado
                Object.keys(estados).forEach(function(nombreEstado) {
                    var estado = estados[nombreEstado];
                    if (estado && estado.customSelectFilters) {
                        Object.keys(estado.customSelectFilters).forEach(function(colIdx) {
                            _dtFilterState[idtabla + '_' + colIdx] = estado.customSelectFilters[colIdx];
                        });
                    }
                });
            },
            function () {
                if (callback) callback({});
            },
        );
        return;
    }

    var estados = data.stateRestore || {};
    var nombres = Object.keys(estados);
    if (nombres.length === 0) {
        if (callback) callback();
        return;
    }

    var pendientes = nombres.length;
    var cerrar_modal_confirmacion = function () {
        var fondo = document.querySelector("div.dtsr-background");
        if (fondo) {
            fondo.click();
            return;
        }

        var confirmacion = document.querySelector("div.dtsr-confirmation");
        if (confirmacion && confirmacion.parentNode) {
            confirmacion.parentNode.removeChild(confirmacion);
        }
    };

    var finalizar = function (exito, respuesta) {
        if (!exito) {
            console.log(respuesta);
            // Para remove con error (ej. estado protegido), cerramos modal sin
            // ejecutar callback de StateRestore para que no desaparezca del listado.
            if (data.action === "remove") {
                cerrar_modal_confirmacion();
                return;
            }
        }

        console.log(respuesta);
        pendientes--;
        if (pendientes <= 0 && callback) {
            callback();
        }
    };

    nombres.forEach(function (nombre) {
        var payload = "";
        if (data.action === "save") {
            var estadoConFiltros = Object.assign({}, estados[nombre]);
            estadoConFiltros.customSelectFilters = {};
            Object.keys(_dtFilterState).forEach(function (key) {
                if (key.startsWith(idtabla + '_')) {
                    var colIdx = key.replace(idtabla + '_', '');
                    if (_dtFilterState[key] && _dtFilterState[key].length > 0) {
                        estadoConFiltros.customSelectFilters[colIdx] = _dtFilterState[key];
                    }
                }
            });
            payload =
                "idtabla=" +
                encodeURIComponent(idtabla) +
                ",nombre_estado=" +
                encodeURIComponent(nombre) +
                ",estadotabla=" +
                encodeURIComponent(JSON.stringify(estadoConFiltros));
            upload_action(
                payload,
                "datatables",
                "guardar_estado_datatables_staterestore",
                function (respuesta) {
                    finalizar(true, respuesta);
                },
                function (respuesta) {
                    finalizar(false, respuesta);
                },
            );
        } else if (data.action === "rename") {
            payload =
                "idtabla=" +
                encodeURIComponent(idtabla) +
                ",nombre_estado_actual=" +
                encodeURIComponent(nombre) +
                ",nombre_estado_nuevo=" +
                encodeURIComponent(estados[nombre]);
            upload_action(
                payload,
                "datatables",
                "renombrar_estado_datatables_staterestore",
                function (respuesta) {
                    finalizar(true, respuesta);
                },
                function (respuesta) {
                    finalizar(false, respuesta);
                },
            );
        } else if (data.action === "remove") {
            payload = "idtabla=" + encodeURIComponent(idtabla) + ",nombre_estado=" + encodeURIComponent(nombre);
            upload_action(
                payload,
                "datatables",
                "eliminar_estado_datatables_staterestore",
                function (respuesta) {
                    finalizar(true, respuesta);
                },
                function (respuesta) {
                    finalizar(false, respuesta);
                },
            );
        } else {
            finalizar(true, null);
        }
    });
}
//
function print_all_datatables(dt) {
    var clone = build_all_datatables_report_content();
    if (!clone) return;

    var mywindow = window.open("", "PRINT", "fullscreen=yes");
    mywindow.document.write("<html><head><title>" + document.title + "</title>");
    mywindow.document.write("</head><body>");
    mywindow.document.write(clone.innerHTML);
    mywindow.document.write("</body></html>");
    mywindow.document.close();
    mywindow.focus();

    append_print_stylesheet(
        mywindow.document,
        resolve_print_asset_url("../css/dt2.print_all.css?x=" + version),
        false,
        function () {
            estilizar_filas_agregacion_impresion(mywindow.document);
            mywindow.print();
        }
    );
}
//
function build_all_datatables_report_content() {
    var container = document.getElementById("contenedor_principal");
    if (!container) return null;

    // Para cada tabla activa dentro del contenedor, obtener todas las filas filtradas
    var filasTablas = {};
    if (Array.isArray(tablas_activas)) {
        tablas_activas.forEach(function (dtInst) {
            if (!dtInst) return;
            var tbl = dtInst.table().node();
            if (!container.contains(tbl) || !tbl.id) return;
            filasTablas[tbl.id] = construir_filas_reporte_datatable(dtInst);
        });
    }

    // Clonar el contenedor para no modificar el DOM visible
    var clone = container.cloneNode(true);

    clone.querySelectorAll("form").forEach(function (form) {
        form.parentNode.removeChild(form);
    });

    clone.querySelectorAll(".header-titulo").forEach(function (el) {
        el.parentNode.removeChild(el);
    });

    // Reemplazar el tbody de cada tabla con todas sus filas filtradas
    Object.keys(filasTablas).forEach(function (id) {
        var tbl = clone.querySelector("#" + id);
        if (!tbl) return;
        var tbody = tbl.querySelector("tbody");
        if (tbody) tbody.innerHTML = filasTablas[id];

        // El total general ya está en el tbody para que también lo procese Excel.
        // Se retira únicamente su representación del pie del clon para no duplicarlo.
        tbl.querySelectorAll("tfoot .dt-aggregation-result").forEach(function (resultado) {
            resultado.parentNode.removeChild(resultado);
        });
    });

    clone.querySelectorAll("table.datatable th, table.datatables th, table.dataTable th").forEach(function (th) {
        var titulo = th.querySelector(".dt-column-title");
        var texto = titulo ? titulo.textContent : th.textContent;
        th.textContent = texto.trim();
    });

    return clone;
}
//
function export_all_datatables(dt) {
    var clone = build_all_datatables_report_content();
    if (!clone) return;

    var tempContainer = document.createElement("div");
    var tempId = "tmp_export_all_" + new Date().getTime();
    tempContainer.id = tempId;
    tempContainer.style.display = "none";
    tempContainer.appendChild(clone);
    document.body.appendChild(tempContainer);

    try {
        export_to_xlsx(tempId, "reporte.xlsx");
    } finally {
        if (tempContainer && tempContainer.parentNode) {
            tempContainer.parentNode.removeChild(tempContainer);
        }
    }
}
//
function fila_agregada(row, data, dataIndex) {
    //notify_info("fila agregada agregada.");
}
//
function resolve_print_asset_url(relativePath) {
    return new URL(relativePath, window.location.href).href;
}
/*
function inject_print_fallback_styles(doc) {
    var style = doc.createElement("style");
    style.type = "text/css";
    style.textContent =
        "@page { margin: 10mm; }" +
        "body.dt-print-view, body { background: #ffffff !important; color: #000000 !important; }" +
        ".dt-print-header { margin-bottom: 16px; text-align: center; }" +
        ".dt-print-title { font-size: 18px; font-weight: 700; color: #000000 !important; }" +
        ".dt-print-company { font-size: 12px; color: #000000 !important; }" +
        "table, table.dataTable { width: 100% !important; border-collapse: collapse !important; }" +
        "table thead th, table.dataTable thead th { background: #1e3a5f !important; color: #ffffff !important; border: 1px solid #000000 !important; padding: 0.5em !important; text-align: left !important; }" +
        "table tbody td, table.dataTable tbody td { background: #f2f2f2 !important; color: #000000 !important; border: 1px solid #000000 !important; padding: 0.5em !important; }" +
        "table tbody tr:nth-child(even) td, table.dataTable tbody tr:nth-child(even) td { background: #dfe6ee !important; }";
    doc.head.appendChild(style);
}
*/
function append_print_stylesheet(doc, href, optional, onComplete) {
    var link = doc.createElement("link");
    var finished = false;
    var finalize = function () {
        if (finished) {
            return;
        }
        finished = true;
        onComplete();
    };

    link.rel = "stylesheet";
    link.type = "text/css";
    link.onload = finalize;
    link.onerror = function () {
        if (!optional) {
            console.error("No se pudo cargar el CSS de impresión:", href);
        }
        finalize();
    };
    link.href = href;
    doc.head.appendChild(link);

    setTimeout(finalize, 400);
}
//
function activar_tablas() {
    var tablas_encontradas = document.querySelectorAll("table.datatable, table.datatables");
    var tablas_activadas = [];

    tablas_encontradas.forEach(function (tabla_html) {
        if (tabla_html.id && tabla_html.id.trim() !== "") {
            tablas_activadas.push(activar_tabla(tabla_html.id));
        }
    });

    return tablas_activadas;
}
function aplicar_estilos_compactos_datatables() {
    var style_id = 'dt2-estilos-compactos';
    if (document.getElementById(style_id)) return;

    var style = document.createElement('style');
    style.id = style_id;
    style.type = 'text/css';
    style.textContent =
        'table.dataTable span.dtcc,div.dtcc-dropdown{font-size:75%;}' +
        'div.dtcc-dropdown div.dtcc-dropdown-liner{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);align-items:center;}' +
        'div.dtcc-dropdown div.dtcc-dropdown-liner>button.dtcc-button_orderAsc,div.dtcc-dropdown div.dtcc-dropdown-liner>button.dtcc-button_orderDesc{width:auto;min-width:0;white-space:nowrap;}' +
        'div.dtcc-dropdown div.dtcc-dropdown-liner>.dtcc-search{grid-column:1/-1;}' +
        'div.dtcc-dropdown div.dtcc-dropdown-liner>.dtcc-aggregation{grid-column:1/-1;padding:.5em 1em;}' +
        'div.dtcc-dropdown .dtcc-aggregation select{width:100%;}' +
        'table.dataTable tr.dt-aggregation-group-row>.dt-aggregation-group-value{text-align:right;white-space:nowrap;}' +
        'table.dataTable tr.dt-aggregation-group-row,table.dataTable tr.dt-aggregation-export-total,table.dataTable tfoot tr:has(.dt-aggregation-result){font-weight:700;background-color:#e2e3e5;}' +
        'table.dataTable tr.dt-aggregation-group-row>*,table.dataTable tr.dt-aggregation-export-total>*,table.dataTable tfoot tr:has(.dt-aggregation-result)>*{background-color:#e2e3e5;}';
    document.head.appendChild(style);
}
// Definir UNA SOLA VEZ el contenido base de columnControl
var contenidoOrdenBase = [
    { extend: 'order', iconNone: '' },
    [
        { extend: 'orderAsc', text: 'ASC' },
        { extend: 'orderDesc', text: 'DES' },
        'search',
        'aggregationSelect'
    ]
];
//
function obtenerValoresUnicosColumna(idtabla, indiceColumna) {
    var valores = [];
    var vistos = {};

    $('#' + idtabla + ' tbody tr').each(function () {
        var celda = $(this).find('td').eq(indiceColumna);
        if (celda.length === 0) {
            return;
        }

        // Limpiar HTML interno (botones, inputs, etc.) igual que en exportOptions
        var tmp = document.createElement('div');
        tmp.innerHTML = celda.html() || '';
        tmp.querySelectorAll('input, select, textarea, button').forEach(function (el) {
            el.parentNode.removeChild(el);
        });
        var texto = (tmp.textContent || tmp.innerText || '').trim();

        if (texto !== '' && !vistos[texto]) {
            vistos[texto] = true;
            valores.push(texto);
        }
    });

    valores.sort(function (a, b) {
        return a.localeCompare(b, 'es', { sensitivity: 'base' });
    });

    return valores;
}
//
function mostrar_popup_compartir(idtabla, nombreEstado) {
    var fondo = document.createElement('div');
    fondo.className = 'dtsr-background';
    var modal = document.createElement('div');
    modal.id = 'modal_compartir_' + idtabla;
    modal.className = 'dtsr-confirmation';
    modal.innerHTML =
        '<style>' +
            '#div_usuarios_compartir .form-check { display: block !important; margin: 4px 0 !important; padding: 0 !important; }' +
            '#div_usuarios_compartir .form-check-input { width: auto !important; height: auto !important; margin-right: 6px !important; vertical-align: middle !important; position: static !important; }' +
            '#div_usuarios_compartir .form-check-label { font-weight: normal !important; display: inline !important; margin: 0 !important; }' +
        '</style>' +
        '<h3 style="color: #000000 !important;">Compartir estado: ' + nombreEstado + '</h3>' +
        '<div id="div_usuarios_compartir"></div>' +
        '<button class="dtsr-confirm-button">Compartir</button>' +
        '<button class="dtsr-cancel-button">Cancelar</button>';
    document.body.appendChild(fondo);
    document.body.appendChild(modal);
    download_div_content('', 'usuario', 'options_usuarios_activos', 'div_usuarios_compartir', null, false);

    fondo.addEventListener('click', function () {
        fondo.remove();
        modal.remove();
    });

    modal.querySelector('.dtsr-cancel-button').addEventListener('click', function () {
        fondo.remove();
        modal.remove();
    });

    modal.querySelector('.dtsr-confirm-button').addEventListener('click', function () {
        var checkboxes = modal.querySelectorAll('#div_usuarios_compartir input[type="checkbox"]:checked');
        var usuarios = [];
        checkboxes.forEach(function (chk) {
            usuarios.push(chk.value);
        });

        var payload = "idtabla=" + encodeURIComponent(idtabla) +
            ",nombre_estado=" + encodeURIComponent(nombreEstado) +
            ",usuarios=" + encodeURIComponent(usuarios.join(','));

        upload_action(payload, 'datatables', 'compartir_estados', function (respuesta) {
            notify_success(respuesta);
            //console.log(respuesta);
        });

        fondo.remove();
        modal.remove();
    });
}
//
//
function activar_tabla(idtabla) {
    var tabla = document.getElementById(idtabla);
    var ds = tabla.dataset;
    var normalizarNombreColumna = function (texto) {
        if (texto === undefined || texto === null) {
            return '';
        }
        return String(texto).replace(/_/g, ' ').trim().toUpperCase();
    };
    var pagingUser = (ds.confPaging === undefined) ? true : (ds.confPaging === "true");
    var selectUser = ds.confSelect === "true";
    var buttonsConfigRaw = (ds.confButtons === undefined || ds.confButtons === null) ? "false" : String(ds.confButtons).trim();
    var buttonsConfigNormalized = buttonsConfigRaw.toLowerCase();
    var buttonsUser = !(buttonsConfigNormalized === "" || buttonsConfigNormalized === "false" || buttonsConfigNormalized === "0");
    var exportButtonsRequested = { copy: false, csv: false, excel: false, pdf: false, print: false };
    if (buttonsConfigNormalized === "all" || buttonsConfigNormalized === "true") {
        exportButtonsRequested.copy = true;
        exportButtonsRequested.csv = true;
        exportButtonsRequested.excel = true;
        exportButtonsRequested.pdf = true;
        exportButtonsRequested.print = true;
    } else if (buttonsUser) {
        buttonsConfigNormalized.split(',').forEach(function (buttonName) {
            var nombre = buttonName.trim();
            if (Object.prototype.hasOwnProperty.call(exportButtonsRequested, nombre)) {
                exportButtonsRequested[nombre] = true;
            }
        });
    }
    var fixedHeaderDisponible = (typeof DataTable !== "undefined" && typeof DataTable.FixedHeader !== "undefined") ||
        (typeof $.fn !== "undefined" && $.fn.dataTable && typeof $.fn.dataTable.FixedHeader !== "undefined");
    var fixedHeaderUser = ds.confFixedheader === "true" && fixedHeaderDisponible;
    //
    var responsiveDisponible = (typeof DataTable !== "undefined" && typeof DataTable.Responsive !== "undefined") ||
        (typeof $.fn !== "undefined" && $.fn.dataTable && typeof $.fn.dataTable.Responsive !== "undefined");
    var responsiveConfigRaw = (ds.confResponsive === undefined || ds.confResponsive === null) ? "false" : String(ds.confResponsive).trim();
    var responsiveUser = !(responsiveConfigRaw === "" || responsiveConfigRaw.toLowerCase() === "false") && responsiveDisponible;
    if (responsiveUser === false && responsiveConfigRaw !== "" && responsiveConfigRaw.toLowerCase() !== "false" && !responsiveDisponible) {
        console.warn('[' + idtabla + '] responsive solicitado pero DataTables.Responsive no está cargado.');
    }
    var responsivePriorityIndices = [];
    if (responsiveUser) {
        responsiveConfigRaw.split(',').forEach(function (nombreColumna) {
            var nombreBuscado = normalizarNombreColumna(nombreColumna);
            if (nombreBuscado === '') {
                return;
            }
            var indiceEncontrado = -1;
            $('#' + idtabla + ' thead th').each(function (i) {
                if (normalizarNombreColumna($(this).text()) === nombreBuscado) {
                    indiceEncontrado = i;
                    return false;
                }
            });
            if (indiceEncontrado !== -1 && responsivePriorityIndices.indexOf(indiceEncontrado) === -1) {
                responsivePriorityIndices.push(indiceEncontrado);
            }
        });
    }
    //
    var exportTitle = (ds.confTitulotabla) ? ds.confTitulotabla : "Listado";
    var exportCompany = "Solo moda S.A.S.";
    var exportFileName = (ds.confFilename) ? "Listado_de_" + ds.confFilename + "_Solo_moda_S.A.S." : "Listado";

    var nombreABuscar = normalizarNombreColumna(ds.confRowgroup);
    var indiceReal = -1;
    $('#' + idtabla + ' thead th').each(function (i) {
        if (normalizarNombreColumna($(this).text()) === nombreABuscar) {
            indiceReal = i;
        }
    });
    var rowGroupUser = (indiceReal !== -1);
    var columnControlExcludeConfig = (ds.confColumncontrolexclude === undefined || ds.confColumncontrolexclude === null) ? "false" : String(ds.confColumncontrolexclude).trim();
    var columnControlExcludeIndices = [];
    if (columnControlExcludeConfig !== "" && columnControlExcludeConfig.toLowerCase() !== "false") {
        columnControlExcludeConfig.split(',').forEach(function (nombreColumna) {
            var nombreBuscado = normalizarNombreColumna(nombreColumna);
            if (nombreBuscado === '') {
                return;
            }

            var indiceEncontrado = -1;
            $('#' + idtabla + ' thead th').each(function (i) {
                if (normalizarNombreColumna($(this).text()) === nombreBuscado) {
                    indiceEncontrado = i;
                    return false;
                }
            });

            if (indiceEncontrado !== -1 && columnControlExcludeIndices.indexOf(indiceEncontrado) === -1) {
                columnControlExcludeIndices.push(indiceEncontrado);
            }
        });
    }
    // SELECT PERSONALIZADO 
    var addFilterConfig = (ds.confAddfilter === undefined || ds.confAddfilter === null) ? "false" : String(ds.confAddfilter).trim();
    var addFilterIndices = [];
    if (addFilterConfig !== "" && addFilterConfig.toLowerCase() !== "false") {
        addFilterConfig.split(',').forEach(function (nombreColumna) {
            var nombreBuscado = normalizarNombreColumna(nombreColumna);
            if (nombreBuscado === '') {
                return;
            }

            var indiceEncontrado = -1;
            $('#' + idtabla + ' thead th').each(function (i) {
                if (normalizarNombreColumna($(this).text()) === nombreBuscado) {
                    indiceEncontrado = i;
                    return false;
                }
            });

            if (indiceEncontrado !== -1 && addFilterIndices.indexOf(indiceEncontrado) === -1) {
                addFilterIndices.push(indiceEncontrado);
            }
        });
    }
    //
    var resetUser = ds.confReset === "true";
    var exportAllUser = ds.confExportall === "true";
    var stateRestoreUser = ds.confStaterestore === "true";
    var stateRestoreDisponible = typeof DataTable !== "undefined" && DataTable.ext && DataTable.ext.buttons && DataTable.ext.buttons.savedStates;
    var stateRestoreActivo = stateRestoreUser && stateRestoreDisponible;
    // var responsiveUser = ds.confResponsive === "true";
    var colReorderUser = ds.confColreorder === "true";
    var columnControlUser = ds.confColumncontrol === "true";
    var orderingUser = (ds.confOrdering === undefined || ds.confOrdering === "true");
    var columnasAuto = [];

    var orderInicial = [];
    if (rowGroupUser) {
        orderInicial.push([indiceReal, 'asc']);
    }
    $('#' + idtabla + ' thead th').each(function () {
        columnasAuto.push({ name: $(this).text().trim() });
    });

    var botones = [];
    if (buttonsUser) { // BOTONES DE EXPORTACION 
        if (exportButtonsRequested.copy) { // BOTON DE COPIAR 
            botones.push({
                extend: "copy",
                text: '<i class="fas fa-copy"></i>',
                titleAttr: 'Copiar al portapapeles',
                className: "btn btn-secondary btn-sm",
                title: exportTitle,
                messageTop: exportCompany
            });
        }
        if (exportButtonsRequested.csv) {  // BOTON CSV
            botones.push({
                extend: "csv",
                text: '<i class="fas fa-file"></i>',
                titleAttr: 'Exportar a CSV',
                className: "btn btn-secondary btn-sm",
                title: exportTitle,
                filename: exportFileName
            });
        }
        if (exportButtonsRequested.excel) { //BOTON EXCEL 
            botones.push({
                extend: "excel",
                text: '<i class="fas fa-file-excel"></i>',
                titleAttr: 'Exportar a Excel',
                className: "btn btn-success btn-sm",
                title: exportTitle,
                filename: exportFileName,
                messageTop: exportCompany,
                customize: function (xlsx) {
                    estilizar_filas_agregacion_excel(xlsx);
                }
            });
        }
        if (exportButtonsRequested.pdf) { // BOTON PDF
            botones.push({
                extend: "pdf",
                text: '<i class="fas fa-file-pdf"></i>',
                titleAttr: 'Exportar a PDF',
                className: "btn btn-danger btn-sm",
                title: exportTitle,
                messageTop: exportCompany,
                filename: exportFileName,
                orientation: "landscape",
                pageSize: "LEGAL",
                customize: function (doc) {
                    doc.pageMargins = [12, 34, 12, 18];
                    doc.defaultStyle = {
                        color: "#000000",
                        fontSize: 7,
                        alignment: "center"
                    };
                    doc.styles.title = {
                        color: "#000000",
                        fontSize: 14,
                        bold: true,
                        alignment: "center"
                    };
                    doc.styles.message = {
                        color: "#000000",
                        fontSize: 10,
                        alignment: "center",
                        margin: [0, 0, 0, 10]
                    };
                    doc.styles.tableHeader = {
                        color: "#000000",
                        fillColor: null,
                        bold: true,
                        alignment: "center"
                    };

                    var tableNode = null;
                    if (Array.isArray(doc.content)) {
                        for (var i = 0; i < doc.content.length; i++) {
                            if (doc.content[i] && doc.content[i].table) {
                                tableNode = doc.content[i];
                                break;
                            }
                        }
                    }

                    if (tableNode && tableNode.table && Array.isArray(tableNode.table.body) && tableNode.table.body.length > 0) {
                        var colCount = Array.isArray(tableNode.table.body[0]) ? tableNode.table.body[0].length : 0;
                        if (colCount > 0) {
                            tableNode.table.widths = Array(colCount).fill('*');
                        }

                        tableNode.margin = [0, 0, 0, 0];
                        tableNode.alignment = 'center';
                        tableNode.layout = {
                            hLineColor: function () { return "#000000"; },
                            vLineColor: function () { return "#000000"; },
                            hLineWidth: function () { return 0.5; },
                            vLineWidth: function () { return 0.5; },
                            paddingLeft: function () { return 2; },
                            paddingRight: function () { return 2; },
                            paddingTop: function () { return 2; },
                            paddingBottom: function () { return 2; }
                        };

                        for (var r = 0; r < tableNode.table.body.length; r++) {
                            for (var c = 0; c < tableNode.table.body[r].length; c++) {
                                var cell = tableNode.table.body[r][c];
                                if (typeof cell === 'string') {
                                    tableNode.table.body[r][c] = { text: cell, alignment: 'center' };
                                } else if (cell && typeof cell === 'object') {
                                    cell.alignment = 'center';
                                }
                            }
                        }
                    }
                    estilizar_filas_agregacion_pdf(doc);
                }
            });
        }
        if (exportButtonsRequested.print) { // BOTON IMPRIMIR 
            botones.push({
                extend: "print",
                text: '<i class="fas fa-print"></i>',
                titleAttr: 'Imprimir',
                className: "btn btn-primary btn-sm",
                title: "",
                messageTop: "",
                filename: exportFileName,
                autoPrint: false,
                customize: function (win) {
                    var doc = win.document;
                    $(doc.body).prepend(
                        '<div class="dt-print-header">' +
                        '<div class="dt-print-title">' + exportTitle + '</div>' +
                        '<div class="dt-print-company">' + exportCompany + '</div>' +
                        '</div>'
                    );

                    prepare_print_window_styles(win, idtabla, function () {
                        estilizar_filas_agregacion_impresion(doc);
                        win.print();
                    });
                }
            });
        }
        if (stateRestoreActivo) { // BOTONES DE ESTADO 
            botones.push({
                extend: 'savedStates',
                text: 'Vistas',
                config: {
                    splitSecondaries: [
                        'updateState',
                        'renameState',
                        'removeState',
                        {
                            text: 'Compartir',
                            action: function (e, dt, node, config) {
                                var stateRestore = config.parent._stateRestore;
                                var stateName = stateRestore.s.identifier;
                                mostrar_popup_compartir(idtabla, stateName);
                            }

                        }
                    ],
                    ajax: function (data, callback) {
                        datatables_staterestore_ajax(idtabla, data, callback);
                    }
                }
            });
            botones.push({           // BOTON GUARDAD ESTADO
                extend: 'createState',
                text: '<i class="fas fa-save"></i>',
                titleAttr: 'Guardar estado',
                config: {
                    creationModal: true,
                    ajax: function (data, callback) {
                        datatables_staterestore_ajax(idtabla, data, callback);
                    }
                }
            });
        }
        botones.push({
            extend: "colvis",
            text: "Columnas",
            //columns: ":not(:first-child)"
        });
        
    }
    if (resetUser) { // INSERTAR EL BOTON REINICIAR 
        botones.push({
            text: '<i class="fas fa-sync"></i>' ,
            titleAttr: 'Reiniciar tabla',
            className: 'btn btn-warning btn-sm',
            action: function (e, dt) {
                localStorage.removeItem(clave_estado_datatable(dt.settings()[0].sInstance));
                if (dt.state && typeof dt.state.clear === 'function') dt.state.clear();
                Object.keys(_dtAggregationState).forEach(function (state_key) {
                    if (state_key.indexOf(idtabla + '_') === 0) delete _dtAggregationState[state_key];
                });
                document.querySelectorAll('.dtcc-aggregation select[data-table-id="' + idtabla + '"]').forEach(function (select) {
                    select.value = '';
                });
                dt.search('');
                dt.columns().search('');
                dt.columns().visible(true, false);

                // Limpiar UI interna de ColumnControl (inputs, selects nativos)
                if (typeof dt.columns().ccSearchClear === 'function') {
                    dt.columns().ccSearchClear();
                }

                if (dt.colReorder && typeof dt.colReorder.reset === 'function') dt.colReorder.reset();
                dt.order([]);
                dt.draw();
            }
        });
    }
    if (selectUser) {
        botones.push({
            text: 'Deseleccionar',
            action: function (e, dt) {
                dt.rows().deselect();
            }
        });
    }
    if (exportAllUser) {
        botones.push(
            {
                text: 'Imprimir todo',
                className: 'btn btn-primary btn-sm',
                action: function (e, dt) { print_all_datatables(dt); }
            },
            {
                text: 'Exportar todo',
                className: 'btn btn-success btn-sm',
                action: function (e, dt) { export_all_datatables(dt); }
            }
        );
    }
    var configTopStart = [];
    if (buttonsUser || exportAllUser || stateRestoreActivo) configTopStart.push('buttons');
    if (pagingUser) configTopStart.push('pageLength');
    configTopStart.push('search');
    //CODIGO DANIEL 
    var agregarAgrupacionAExportacion = function (data) {
        if (!data || !Array.isArray(data.body) || data.body.length === 0 || typeof tabla_nueva === "undefined" || !tabla_nueva) {
            return;
        }

        var AGREGACIONES_EXPORTACION = {};
        if (Array.isArray(data.header)) {
            obtener_agregaciones_tabla().forEach(function (agregacion) {
                for (var a = 0; a < data.header.length; a++) {
                    if (obtener_id_columna_agregacion(data.header[a]) === agregacion.columna_id) {
                        AGREGACIONES_EXPORTACION[a] = agregacion.tipo;
                        break;
                    }
                }
            });
        }

        var aplicar_valor_agregado = function (ACUMULADOS, indice_columna, tipo, valor) {
            if (tipo === 'count') {
                if (obtener_texto_agregacion(valor) !== '') {
                    ACUMULADOS[indice_columna] = (ACUMULADOS[indice_columna] || 0) + 1;
                }
                return;
            }
            var numero = parsear_numero_agregacion(valor);
            if (numero !== null) {
                ACUMULADOS[indice_columna] = (ACUMULADOS[indice_columna] || 0) + numero;
            }
        };
        var formatear_fila_agregada = function (fila_resultado, ACUMULADOS) {
            Object.keys(AGREGACIONES_EXPORTACION).forEach(function (indice_columna) {
                var tipo = AGREGACIONES_EXPORTACION[indice_columna];
                var valor = ACUMULADOS[indice_columna] || 0;
                var texto_agregado = (tipo === 'sum' ? 'Suma ' : 'Conteo ') + formatear_resultado_agregacion(valor, tipo);
                fila_resultado[indice_columna] = fila_resultado[indice_columna]
                    ? fila_resultado[indice_columna] + ' — ' + texto_agregado
                    : texto_agregado;
            });
        };

        var agrupacion_activa = rowGroupUser && tabla_nueva.rowGroup && tabla_nueva.rowGroup().enabled();
        if (!agrupacion_activa) {
            if (Object.keys(AGREGACIONES_EXPORTACION).length === 0) {
                return;
            }
            var fila_total = new Array(data.header.length).fill('');
            var ACUMULADOS_TOTAL = {};
            data.body.forEach(function (fila) {
                if (!Array.isArray(fila)) {
                    return;
                }
                Object.keys(AGREGACIONES_EXPORTACION).forEach(function (indice_columna) {
                    aplicar_valor_agregado(ACUMULADOS_TOTAL, indice_columna, AGREGACIONES_EXPORTACION[indice_columna], fila[indice_columna]);
                });
            });
            formatear_fila_agregada(fila_total, ACUMULADOS_TOTAL);
            data.body.push(fila_total);
            return;
        }

        var indiceGrupoExportado = -1;
        if (Array.isArray(data.header)) {
            for (var i = 0; i < data.header.length; i++) {
                if (normalizarNombreColumna(data.header[i]) === nombreABuscar) {
                    indiceGrupoExportado = i;
                    break;
                }
            }
        }

        var datosFilasOrdenadas = [];
        if (indiceGrupoExportado === -1) {
            datosFilasOrdenadas = tabla_nueva.rows({ search: 'applied', order: 'applied', page: 'all' }).data().toArray();
            if (datosFilasOrdenadas.length !== data.body.length) {
                return;
            }
        }

        var bodyConGrupos = [];
        var grupoAnterior = null;
        var filaGrupoActual = null;
        var ACUMULADOS_GRUPO = {};
        data.body.forEach(function (fila, indiceFila) {
            if (!Array.isArray(fila)) {
                bodyConGrupos.push(fila);
                return;
            }

            var grupoActual = (indiceGrupoExportado !== -1) ? fila[indiceGrupoExportado] : datosFilasOrdenadas[indiceFila][indiceReal];
            if (grupoActual !== grupoAnterior) {
                if (filaGrupoActual) {
                    formatear_fila_agregada(filaGrupoActual, ACUMULADOS_GRUPO);
                }
                var filaGrupo = new Array(fila.length).fill('');
                filaGrupo[indiceGrupoExportado !== -1 ? indiceGrupoExportado : 0] = grupoActual;
                bodyConGrupos.push(filaGrupo);
                filaGrupoActual = filaGrupo;
                ACUMULADOS_GRUPO = {};
                grupoAnterior = grupoActual;
            }

            Object.keys(AGREGACIONES_EXPORTACION).forEach(function (indice_columna) {
                aplicar_valor_agregado(ACUMULADOS_GRUPO, indice_columna, AGREGACIONES_EXPORTACION[indice_columna], fila[indice_columna]);
            });

            bodyConGrupos.push(fila);
        });

        if (filaGrupoActual) {
            formatear_fila_agregada(filaGrupoActual, ACUMULADOS_GRUPO);
        }

        data.body = bodyConGrupos;
    };
    // FIN CODIGO DANIEL 
    
    var layoutConfig = {
        topStart: configTopStart.length > 0 ? { features: configTopStart } : null,
        topEnd: null,
        bottomStart: pagingUser ? 'paging' : null,
        bottomEnd: null,
        top: null,
        bottom: null
    };
    var asegurar_pie_agregaciones = function () {
        var tfoot = tabla.tFoot;
        if (!tfoot) {
            tfoot = document.createElement('tfoot');
            tfoot.setAttribute('data-dt-aggregation-created', 'true');
            tabla.appendChild(tfoot);
        }

        var fila = tfoot.rows[0];
        if (!fila) {
            fila = tfoot.insertRow();
            fila.setAttribute('data-dt-aggregation-created', 'true');
        }

        var cantidad_columnas = tabla.tHead && tabla.tHead.rows.length > 0
            ? tabla.tHead.rows[tabla.tHead.rows.length - 1].cells.length
            : 0;
        while (fila.cells.length < cantidad_columnas) {
            fila.appendChild(document.createElement('th'));
        }
    };
    var obtener_agregaciones_tabla = function () {
        // El estado usa el título normalizado para permanecer unido a la columna tras ColReorder.
        return obtener_agregaciones_estado_tabla(idtabla);
    };
    var rowgroup_esta_activo = function () {
        return tabla_nueva && tabla_nueva.rowGroup && tabla_nueva.rowGroup().enabled();
    };
    var renderizar_pie_agregaciones = function () {
        // Sin RowGroup, el resultado considera todas las filas que superan los filtros.
        if (!tabla_nueva) {
            return;
        }

        var tfoot = tabla_nueva.table().footer();
        if (!tfoot) {
            return;
        }

        tfoot.querySelectorAll('.dt-aggregation-result').forEach(function (elemento) {
            elemento.parentNode.removeChild(elemento);
        });

        var AGREGACIONES = obtener_agregaciones_tabla();
        var agrupacion_activa = rowgroup_esta_activo();
        var fila_creada = tfoot.querySelector('tr[data-dt-aggregation-created="true"]');
        if (fila_creada) {
            fila_creada.style.display = AGREGACIONES.length > 0 && !agrupacion_activa ? '' : 'none';
        }
        if (agrupacion_activa) {
            return;
        }

        var indices_filas = tabla_nueva.rows({ search: 'applied', page: 'all' }).indexes();
        AGREGACIONES.forEach(function (agregacion) {
            var indice_actual = obtener_indice_actual_agregacion(tabla_nueva, agregacion.columna_id);
            if (indice_actual === -1) {
                return;
            }
            var footer = tabla_nueva.column(indice_actual).footer();
            if (!footer) {
                return;
            }
            var resultado = calcular_agregacion_columna(tabla_nueva, indices_filas, indice_actual, agregacion.tipo);
            var titulo = tabla_nueva.column(indice_actual).title();
            var elemento = document.createElement('span');
            elemento.className = 'dt-aggregation-result';
            elemento.textContent = (agregacion.tipo === 'sum' ? 'Suma' : 'Conteo') + ': ' +
                formatear_resultado_agregacion(resultado, agregacion.tipo);
            elemento.setAttribute('title', titulo);
            footer.appendChild(elemento);
        });
    };
    var renderizar_agregaciones_grupo = function (rows, group, level) {
        // RowGroup entrega aquí los índices exactos de las filas del grupo renderizado.
        var texto = group + ' (' + rows.count() + ' registros)';
        var AGREGACIONES = obtener_agregaciones_tabla();
        if (AGREGACIONES.length === 0) {
            return texto;
        }

        var dt_grupo = rows.table();
        var RESULTADOS = {};
        AGREGACIONES.forEach(function (agregacion) {
            var indice_actual = obtener_indice_actual_agregacion(dt_grupo, agregacion.columna_id);
            if (indice_actual === -1) {
                return;
            }
            var resultado = calcular_agregacion_columna(dt_grupo, rows.indexes(), indice_actual, agregacion.tipo);
            RESULTADOS[indice_actual] = (agregacion.tipo === 'sum' ? 'Suma ' : 'Conteo ') +
                formatear_resultado_agregacion(resultado, agregacion.tipo);
        });

        var INDICES_VISIBLES = dt_grupo.columns(':visible').indexes().toArray();
        if (INDICES_VISIBLES.length === 0) {
            return texto;
        }

        var indice_grupo = INDICES_VISIBLES[0];

        var fila = document.createElement('tr');
        fila.className = 'dt-aggregation-group-row';
        INDICES_VISIBLES.forEach(function (indice_actual) {
            var celda = document.createElement(indice_actual === indice_grupo ? 'th' : 'td');
            if (indice_actual === indice_grupo) {
                celda.setAttribute('scope', 'row');
                celda.appendChild(document.createTextNode(texto));
            }
            if (RESULTADOS[indice_actual]) {
                var resultado_elemento = document.createElement('span');
                resultado_elemento.className = 'dt-aggregation-group-value';
                resultado_elemento.textContent = RESULTADOS[indice_actual];
                if (indice_actual === indice_grupo) {
                    resultado_elemento.style.display = 'block';
                } else {
                    celda.classList.add('dt-aggregation-group-value');
                }
                celda.appendChild(resultado_elemento);
            }
            fila.appendChild(celda);
        });
        return fila;
    };

    if (columnControlUser) {
        asegurar_pie_agregaciones();
    }

    // SELECT PERSONALIZADO - registro perezoso, garantizado antes de construir la tabla
    if (typeof DataTable !== "undefined" && DataTable.ColumnControl && !DataTable.ColumnControl.content.aggregationSelect) {
        DataTable.ColumnControl.content.aggregationSelect = {
            defaults: {},
            init: function () {
                var dt = this.dt();
                var indice_columna = this.idx();
                var columna_id = obtener_id_columna_agregacion(dt.column(indice_columna).title());
                var state_key = dt.table().node().id + '_' + columna_id;
                var container = document.createElement('div');
                container.className = 'dtcc-aggregation';

                var select = document.createElement('select');
                select.setAttribute('aria-label', 'Agregación de columna');
                select.setAttribute('data-table-id', dt.table().node().id);
                select.setAttribute('data-column-id', columna_id);
                [
                    { value: '', text: '-- Agregación --' },
                    { value: 'sum', text: 'Suma' },
                    { value: 'count', text: 'Conteo' }
                ].forEach(function (opcion_config) {
                    var opcion = document.createElement('option');
                    opcion.value = opcion_config.value;
                    opcion.textContent = opcion_config.text;
                    select.appendChild(opcion);
                });
                select.value = _dtAggregationState[state_key] || '';

                select.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
                select.addEventListener('change', function (e) {
                    e.stopPropagation();
                    if (select.value === 'sum' || select.value === 'count') {
                        _dtAggregationState[state_key] = select.value;
                    } else {
                        delete _dtAggregationState[state_key];
                    }
                    dt.draw(false);
                    if (dt.state && typeof dt.state.save === 'function') {
                        dt.state.save();
                    }
                });

                var namespace = '.dtAggregation_col' + indice_columna;
                dt.on('stateSaveParams' + namespace, function (e, settings, data) {
                    if (!data.customAggregations) {
                        data.customAggregations = {};
                    }
                    if (_dtAggregationState[state_key]) {
                        data.customAggregations[columna_id] = _dtAggregationState[state_key];
                    } else {
                        delete data.customAggregations[columna_id];
                    }
                });
                dt.on('stateLoaded' + namespace, function (e, settings, data) {
                    var tipo = data && data.customAggregations ? data.customAggregations[columna_id] : '';
                    if (tipo === 'sum' || tipo === 'count') {
                        _dtAggregationState[state_key] = tipo;
                        select.value = tipo;
                    } else {
                        delete _dtAggregationState[state_key];
                        select.value = '';
                    }
                    setTimeout(function () {
                        dt.draw(false);
                    }, 0);
                });

                container.appendChild(select);
                return container;
            }
        };
    }

    // SELECT PERSONALIZADO - registro perezoso, garantizado antes de construir la tabla
    if (typeof DataTable !== "undefined" && DataTable.ColumnControl && !DataTable.ColumnControl.content.addFilterSelect) {
        DataTable.ColumnControl.content.addFilterSelect = {
            defaults: {
                opciones: ['UNO', 'DOS', 'TRES']
            },
    //
        init: function (config) {
            var dt = this.dt();
            var colIdx = this.idx();
            var stateKey = dt.table().node().id + '_' + colIdx;
            console.log('init ejecutado - stateKey:', stateKey, '- estado:', JSON.stringify(_dtFilterState[stateKey]));
            // Inicializar estado si no existe
            if (!_dtFilterState[stateKey]) {
                _dtFilterState[stateKey] = [];
            }
            var container = document.createElement('div');
            container.className = 'dtcc-search';
            var inputsWrapper = document.createElement('div');
            var typeIcon = document.createElement('div');
            typeIcon.className = 'dtcc-search-type-icon';
            typeIcon.innerHTML = '<i class="fas fa-filter"></i>';

            var select = document.createElement('select');
            select.multiple = true;
            select.setAttribute('data-column-index', colIdx);

            var optDefault = document.createElement('option');
            optDefault.value = '';
            optDefault.textContent = '--';
            select.appendChild(optDefault);

            config.opciones.forEach(function (valor) {
                var opt = document.createElement('option');
                opt.value = valor;
                opt.textContent = valor;
                // Restaurar desde estado global
                if (_dtFilterState[stateKey].indexOf(valor) !== -1) {
                    opt.selected = true;
                }
                select.appendChild(opt);
            });

            select.addEventListener('click', function (e) { e.stopPropagation(); });
            select.addEventListener('change', function (e) {
                e.stopPropagation();
                var seleccionados = [];
                for (var i = 0; i < select.options.length; i++) {
                    if (select.options[i].selected && select.options[i].value !== '') {
                        seleccionados.push(select.options[i].value);
                    }
                }
                // Guardar en estado global
                _dtFilterState[stateKey] = seleccionados;

                var termino = seleccionados.length > 0
                    ? '^(' + seleccionados.map(function (v) { return v.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }).join('|') + ')$'
                    : '';
                dt.column(colIdx).search(termino, true, false).draw();
            });

            dt.on('draw.addFilter_col' + colIdx, function () {
                if (!dt.column(colIdx).search()) {
                    _dtFilterState[stateKey] = [];
                    for (var i = 0; i < select.options.length; i++) {
                        select.options[i].selected = false;
                    }
                }
            });

            inputsWrapper.appendChild(typeIcon);
            inputsWrapper.appendChild(select);
            container.appendChild(inputsWrapper);
            // Escuchar click en el header de esta columna para restaurar estado
            setTimeout(function () {
                var th = dt.column(colIdx).header();
                
                document.addEventListener('click', function (e) {
                    // Verificar si el click fue dentro del th de esta columna
                    if (!th.contains(e.target)) return;
                    setTimeout(function () {
                        var stateKey = dt.table().node().id + '_' + colIdx;
                        var saved = _dtFilterState[stateKey] || [];
                        for (var i = 0; i < select.options.length; i++) {
                            select.options[i].selected = saved.indexOf(select.options[i].value) !== -1;
                        }
                        // Forzar repintado visual
                        select.blur();
                        select.focus();
                    }, 50);
                }, true);
            }, 0);

            return container;
        }
    //
        };
    }
    //
    aplicar_estilos_compactos_datatables();

    if ($.fn.DataTable.isDataTable('#' + idtabla)) {
        $('#' + idtabla).DataTable().destroy();
    }
    //
    var tabla_nueva = new DataTable('#' + idtabla, {
        layout: layoutConfig,
        // retrieve: true,
        fixedHeader: fixedHeaderUser,
        responsive: responsiveUser,
        colReorder: colReorderUser,
        select: selectUser ? { style: 'multi' } : false,
        paging: true,
        pageLength: 10,
        lengthChange: pagingUser,
        stateSave: true,
        stateDuration: 0,
        orderFixed: rowGroupUser ? { pre: [[indiceReal, 'asc']] } : undefined,
        order: orderInicial,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        columnControl: columnControlUser ? {
            target: 0,
            content: contenidoOrdenBase
        } : false,
        //
        columnDefs: (function () {
            var defs = columnControlExcludeIndices.length > 0 ? columnControlExcludeIndices.map(function (indice) {
                return { target: indice, columnControl: [] };
            }) : [];

            if (addFilterIndices.length > 0) {
                addFilterIndices.forEach(function (indice) {
                    if (columnControlExcludeIndices.indexOf(indice) !== -1) {
                        return;
                    }

                    var opcionesColumna = obtenerValoresUnicosColumna(idtabla, indice);

                    var contenidoConFiltro = [
                        contenidoOrdenBase[0],
                        contenidoOrdenBase[1].concat([
                            { extend: 'addFilterSelect', opciones: opcionesColumna }
                        ])
                    ];

                    defs.push({
                        target: indice,
                        columnControl: contenidoConFiltro
                    });
                });
            }
            //
            if (responsiveUser && responsivePriorityIndices.length > 0) {
                var buscarDef = function (indice) {
                    for (var i = 0; i < defs.length; i++) {
                        if (defs[i].target === indice) return defs[i];
                    }
                    return null;
                };

                responsivePriorityIndices.forEach(function (indice, orden) {
                    var defExistente = buscarDef(indice);
                    if (defExistente) {
                        defExistente.responsivePriority = orden + 1;
                    } else {
                        defs.push({ target: indice, responsivePriority: orden + 1 });
                    }
                });

                $('#' + idtabla + ' thead th').each(function (i) {
                    if (responsivePriorityIndices.indexOf(i) === -1) {
                        var defExistente2 = buscarDef(i);
                        var prioridadBaja = 1000 + i;
                        if (defExistente2) {
                            if (defExistente2.responsivePriority === undefined) {
                                defExistente2.responsivePriority = prioridadBaja;
                            }
                        } else {
                            defs.push({ target: i, responsivePriority: prioridadBaja });
                        }
                    }
                });
            }
            //
            return defs;
        })(),
        //
        buttons: botones.map(function (btn) {
            var configBtn = (typeof btn === "string") ? { extend: btn } : Object.assign({}, btn);
            var exportOptionsActual = Object.assign({}, configBtn.exportOptions);
            var modifierActual = Object.assign({}, exportOptionsActual.modifier);
            var esBotonImprimir = configBtn.extend === 'print';
            var esBotonPdf = configBtn.extend === 'pdf' || configBtn.extend === 'pdfHtml5';
            var esBotonExcel = configBtn.extend === 'excel' || configBtn.extend === 'excelHtml5';
            var esBotonDatos = esBotonImprimir || esBotonPdf || esBotonExcel || configBtn.extend === 'copy' || configBtn.extend === 'csv';

            var haySeleccion = selectUser && tabla_nueva && tabla_nueva.rows({ selected: true }).count() > 0;

            exportOptionsActual.modifier = Object.assign({}, modifierActual, {
                search: 'applied',
                order: 'applied',
                page: 'all',
                selected: haySeleccion ? true : null
            });

            exportOptionsActual.columns = function (idx, data, node) {
                if (tabla_nueva.column(idx).visible()) {
                    return true;
                }
                var header = tabla_nueva.column(idx).header();
                return !!(header && header.className && header.className.indexOf('dtr-hidden') !== -1);
            };
            if (esBotonImprimir || esBotonPdf || esBotonExcel) {
                exportOptionsActual.stripHtml = false;
            }
            exportOptionsActual.format = {
                body: function(data, row, column, node) {
                    if (typeof data === 'string' && data.indexOf('<') !== -1) {
                        var tmp = document.createElement('div');
                        tmp.innerHTML = data;
                        var elementos = tmp.querySelectorAll('input, select, textarea, button');
                        for (var i = 0; i < elementos.length; i++) {
                            elementos[i].parentNode.removeChild(elementos[i]);
                        }
                        if (esBotonImprimir) {
                            return tmp.innerHTML.trim();
                        }
                        if (esBotonPdf) {
                            var tablaInterna = tmp.querySelector('table');
                            if (tablaInterna) {
                                var lineas = [];
                                var filas = tablaInterna.querySelectorAll('tr');
                                for (var f = 0; f < filas.length; f++) {
                                    var columnas = filas[f].querySelectorAll('th, td');
                                    var valores = [];
                                    for (var k = 0; k < columnas.length; k++) {
                                        var valor = (columnas[k].textContent || columnas[k].innerText || '').trim();
                                        if (valor !== '') {
                                            valores.push(valor);
                                        }
                                    }
                                    if (valores.length > 0) {
                                        lineas.push(valores.join(' | '));
                                    }
                                }
                                return lineas.join('\n');
                            }
                        }
                        if (esBotonExcel) {
                            var tablaInternaExcel = tmp.querySelector('table');
                            if (tablaInternaExcel) {
                                var lineasExcel = [];
                                var filasExcel = tablaInternaExcel.querySelectorAll('tr');
                                for (var fx = 0; fx < filasExcel.length; fx++) {
                                    var columnasExcel = filasExcel[fx].querySelectorAll('th, td');
                                    var valoresExcel = [];
                                    for (var kx = 0; kx < columnasExcel.length; kx++) {
                                        var valorExcel = (columnasExcel[kx].textContent || columnasExcel[kx].innerText || '').trim();
                                        if (valorExcel !== '') {
                                            valoresExcel.push(valorExcel);
                                        }
                                    }
                                    if (valoresExcel.length > 0) {
                                        lineasExcel.push(valoresExcel.join(' | '));
                                    }
                                }
                                return lineasExcel.join('\n');
                            }
                        }
                        return (tmp.textContent || tmp.innerText || '').trim();
                    }
                    return data;
                }
            };
            // CODIGO DANIEL2 
            if (esBotonDatos) {
                var customizeDataOriginal = exportOptionsActual.customizeData;
                exportOptionsActual.customizeData = function (data) {
                    if (typeof customizeDataOriginal === 'function') {
                        customizeDataOriginal(data);
                    }
                    agregarAgrupacionAExportacion(data);
                };
            }
            // FIN CODIGO DANIEL2
            configBtn.exportOptions = exportOptionsActual;
            return configBtn;
        }),
        language: {
            url: "../assets/plugins/datatables2/dt2.spanish.json",
            columnControl: {
                orderAsc: "ASC",
                orderDesc: "DES"
            }
        },        
        ordering: {
            handler: orderingUser,
            indicators: false
        },
        createdRow: function (row, data, dataIndex) {
            if (typeof fila_agregada === "function") fila_agregada(row, data, dataIndex);
        },
        rowGroup: rowGroupUser ? {
            dataSrc: indiceReal,
            enable: true,
            startRender: function (rows, group, level) { return renderizar_agregaciones_grupo(rows, group, level); }
        } : false,
        stateSaveCallback: function (settings, data) {
            if (settings.sTableId === 'tabla_datos') {
                return;
            }
            if (rowGroupUser) {
                data.rowGroup = tabla_nueva.rowGroup().dataSrc();
                data.rowGroupEnabled = tabla_nueva.rowGroup().enabled();
            }
            if (selectUser) {
                data.selectedRows = tabla_nueva.rows({ selected: true }).indexes().toArray();
            }
            data.customAggregations = {};
            obtener_agregaciones_tabla().forEach(function (agregacion) {
                data.customAggregations[agregacion.columna_id] = agregacion.tipo;
            });
            localStorage.setItem(clave_estado_datatable(settings.sInstance), JSON.stringify(data));
            if (typeof upload_action === "function") {
                upload_action('idtabla=' + settings.sTableId + ',estadotabla=' + encodeURIComponent(JSON.stringify(data)), 'datatables', 'guardar_estado_datatables');
            }
        },
        stateLoadCallback: function (settings) {
            if (settings.sTableId === 'tabla_datos') {
                return null;
            }
            var data = JSON.parse(localStorage.getItem(clave_estado_datatable(settings.sInstance)));
            if (data) {
                Object.keys(_dtAggregationState).forEach(function (state_key) {
                    if (state_key.indexOf(idtabla + '_') === 0) delete _dtAggregationState[state_key];
                });
                if (data.customAggregations) {
                    Object.keys(data.customAggregations).forEach(function (columna_id) {
                        var tipo = data.customAggregations[columna_id];
                        if (columna_id.indexOf('col:') === 0 && (tipo === 'sum' || tipo === 'count')) {
                            _dtAggregationState[idtabla + '_' + columna_id] = tipo;
                        }
                    });
                }
                setTimeout(function () {
                    /*
                    if (rowGroupUser && data.rowGroup !== undefined) {
                        if (data.rowGroupEnabled) tabla_nueva.rowGroup().enable();
                        tabla_nueva.rowGroup().dataSrc(data.rowGroup);
                        // Aplicamos el orden fijado sobre el índice cargado
                        tabla_nueva.order.fixed({ pre: [[data.rowGroup, 'asc']] }).draw();
                    }
                    */
                    if (selectUser && data.selectedRows) {
                        tabla_nueva.rows(data.selectedRows).select();
                    }
                }, 0);
            }
            return data;
        }
    });
    tabla_nueva.on('draw.dtAggregation column-reorder.dtAggregation column-visibility.dtAggregation rowgroup-datasrc.dtAggregation', function () {
        renderizar_pie_agregaciones();
    });
    renderizar_pie_agregaciones();
    return tabla_nueva;
}
//

