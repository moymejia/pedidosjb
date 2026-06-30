// 
var _dtAddFilterSelects = {}; // mapa global: tableId -> [nodo select, ...]
var _dtFilterState = {}; // tableId_colIdx -> [valores seleccionados]
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
            var filas = "";
            dtInst.rows({ search: "applied", page: "all" }).every(function () {
                var row = this.node().cloneNode(true);
                row.querySelectorAll("input, select, textarea, button").forEach(function (elem) {
                    elem.parentNode.removeChild(elem);
                });
                filas += row.outerHTML;
            });
            filasTablas[tbl.id] = filas;
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
//
// Definir UNA SOLA VEZ el contenido base de columnControl
var contenidoOrdenBase = [
    { extend: 'order', iconNone: '' },
    ['orderAsc', 'orderDesc', 'search']
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
    var responsiveUser = ds.confResponsive === "true";
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
                messageTop: exportCompany
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
                localStorage.removeItem('DataTables_' + dt.settings()[0].sInstance);
                if (dt.state && typeof dt.state.clear === 'function') dt.state.clear();
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
    if (rowGroupUser) {
        botones.push({
            text: 'Limpiar Agrupación',
            action: function (e, dt) {
                if (dt.rowGroup().enabled()) {
                    dt.rowGroup().disable();
                    dt.draw();
                }
            },
            className: 'btn-limpiar'
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
        if (!rowGroupUser || !data || !Array.isArray(data.body) || data.body.length === 0 || typeof tabla_nueva === "undefined" || !tabla_nueva) {
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
        data.body.forEach(function (fila, indiceFila) {
            if (!Array.isArray(fila)) {
                bodyConGrupos.push(fila);
                return;
            }

            var grupoActual = (indiceGrupoExportado !== -1) ? fila[indiceGrupoExportado] : datosFilasOrdenadas[indiceFila][indiceReal];
            if (grupoActual !== grupoAnterior) {
                var filaGrupo = new Array(fila.length).fill('');
                filaGrupo[0] = grupoActual;
                bodyConGrupos.push(filaGrupo);
                grupoAnterior = grupoActual;
            }

            bodyConGrupos.push(fila);
        });

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

    var tabla_nueva = new DataTable('#' + idtabla, {
        layout: layoutConfig,
        retrieve: true,
        fixedHeader: fixedHeaderUser,
        responsive: responsiveUser,
        colReorder: colReorderUser,
        select: selectUser ? { style: 'multi' } : false,
        paging: true,
        pageLength: 10,
        lengthChange: pagingUser,
        stateSave: true,
        stateDuration: 0,
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

            var haySeleccion = selectUser && tabla_nueva && tabla_nueva.rows({ selected: true }).count() > 0;

            exportOptionsActual.modifier = Object.assign({}, modifierActual, {
                search: 'applied',
                order: 'applied',
                page: 'all',
                selected: haySeleccion ? true : null
            });

            exportOptionsActual.columns = ':visible';
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
            if (rowGroupUser && (esBotonImprimir || esBotonPdf || esBotonExcel || configBtn.extend === 'copy' || configBtn.extend === 'csv')) {
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
            url: "../assets/plugins/datatables2/dt2.spanish.json"
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
            startRender: function (rows, group) { return group + ' (' + rows.count() + ' registros)'; }
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
            localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            if (typeof upload_action === "function") {
                upload_action('idtabla=' + settings.sTableId + ',estadotabla=' + encodeURIComponent(JSON.stringify(data)), 'datatables', 'guardar_estado_datatables');
            }
        },
        stateLoadCallback: function (settings) {
            if (settings.sTableId === 'tabla_datos') {
                return null;
            }
            var data = JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            if (data) {
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
    return tabla_nueva;
}
//
