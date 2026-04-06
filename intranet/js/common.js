//Variables generales.
var tabla; // tabla datatables actual
var row_seleccionada; //fila seleccionada al editar registro.
var crud_url = "../php/wisetech/crud.php";
// si el path actual es 'empleos'
cantidad_paths = window.location.pathname.split("/").length;
if (window.location.pathname.split("/")[cantidad_paths - 2] == "empleos" || window.location.pathname.split("/")[cantidad_paths - 2] == "catalogo") {
    var crud_url = "../intranet/php/wisetech/crud.php";
}

function inicializar() {
    return true;
}

function mostrar_opcion(idopcion, opcion, menu, callback_retorno = null) {
    element("idopcion_actual").value = idopcion;
    delete callback_download;
    callback_download = function () {
        habilitar_floating_labels();
        desactivar_tabla(tabla);
        if (objeto("tabla_datos") != undefined && objeto("tabla_datos").classList.contains("datatable")) {
            var inputDT = document.getElementById('datatableid');
            var idFinal = 'tabla_datos'; // Valor por defecto

            if (inputDT && inputDT.value.trim() !== "") {
                var nuevoId = inputDT.value.trim();
                var tablaPorDefecto = document.getElementById('tabla_datos');

                if (tablaPorDefecto) {
                    // Si existe la tabla con el ID genérico, se lo cambiamos al del input
                    tablaPorDefecto.id = nuevoId;
                    idFinal = nuevoId;
                } else if (document.getElementById(nuevoId)) {
                    // Si no existe 'tabla_datos' pero ya existe una con el ID del input, la usamos
                    idFinal = nuevoId;
                }

            }
            var idtabla = idFinal;
            tabla = activar_tabla(idtabla);
        }
        activate_select2();
        activate_switch();
        if (objeto("jsid") != undefined) {            
            var jsid = element("jsid").value;
            var script = document.createElement('script');
            script.src = '../js/'+jsid+'.js'  ; 
            script.type = 'text/javascript';
            script.async = true; // o true, según lo que necesites
            document.head.appendChild(script);

        }

    };
    download_div_content("idopcion_actual", "opcion", "cargar_opcion", "contenedor_principal",callback_retorno);
    document.getElementById("contenedor_principal").innerHTML = "";
    document.getElementById("menu_actual").innerHTML = menu;
    document.getElementById("opcion_actual").innerHTML = opcion;
    document.getElementById("titulo_actual").innerHTML = opcion;
    document.getElementById("detalle_actual").innerHTML = "";
    return false;
}

/*INICIO FUNCIONES AJAX*/
function ajax_tofunction(datos, url, funcion, error_funcion = null) {
    console.log(url);
    var peticion = new XMLHttpRequest();
    peticion.withCredentials = true;
    peticion.onreadystatechange = function () {
        if (peticion.readyState == 4) {
            if (peticion.status == 200) {
                funcion(peticion);
            } else if (error_funcion != null) {
                error_funcion(peticion);
            }
        }
    };
    peticion.open("POST", url, true);
    // peticion.setRequestHeader("Set-Cookie", "SameSite=None; Secure");
    // peticion.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    peticion.send(datos);
}

function ajax_todiv(datos, url, divid) {
    var peticion = new XMLHttpRequest();
    peticion.onreadystatechange = function () {
        if (peticion.readyState == 4 && peticion.status == 200) {
            var datos = peticion.responseText.split("|");
            if (datos[1] == "correcto") {
                document.getElementById(divid).innerHTML = datos[2];
            } else if (datos[1] == "error") {
                notify_error(datos[2]);
            } else {
                notify_error("Error desconocido.");
                logError(peticion.responseText);
            }
        }
    };
    peticion.open("POST", url, true);
    //peticion.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    peticion.send(datos);
}
function ajax_toinput(datos, url, inputid) {
    var peticion = new XMLHttpRequest();
    peticion.onreadystatechange = function () {
        if (peticion.readyState == 4 && peticion.status == 200) {
            var datos = peticion.responseText.split("|");
            if (datos[1] == "correcto") {
                document.getElementById(inputid).value = datos[2];
            } else if (datos[1] == "error") {
                notify_error(datos[2]);
            } else {
                notify_error("Error desconocido.");
                logError(peticion.responseText);
            }
        }
    };
    peticion.open("POST", url, true);
    // peticion.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    peticion.send(datos);
}

function ajax_subir_archivo(input, datos, url, funcion) {
    var archivos = input.files;
    var formData = new FormData();
    var archivo = archivos[0];
    formData.append("archivo", archivo, archivo.name);
    formData.append("datos", datos);

    var peticion = new XMLHttpRequest();

    peticion.onreadystatechange = function () {
        if (peticion.readyState == 4 && peticion.status == 200) {
            funcion(peticion);
        }
    };
    peticion.open("POST", url, true);
    peticion.send(formData);
    return false;
}
/*FIN FUNCIONES AJAX*/

/*INICIO DE NOTIFICACIONES */
function notify_error(mensaje) {
    $.toast({
        heading: "Error",
        text: mensaje,
        position: "top-right",
        loaderBg: "#ff6849",
        icon: "error",
        hideAfter: 3500,
    });
}

function notify_info(mensaje) {
    $.toast({
        heading: "Informacion:",
        text: mensaje,
        position: "top-right",
        loaderBg: "#ff6849",
        icon: "info",
        hideAfter: 3000,
        stack: 6,
    });
}

function notify_warning(mensaje) {
    $.toast({
        heading: "Advertencia",
        text: mensaje,
        position: "top-right",
        loaderBg: "#ff6849",
        icon: "warning",
        hideAfter: 3500,
        stack: 6,
    });
}

function notify_success(mensaje) {
    $.toast({
        heading: "Operacion correcta",
        text: mensaje,
        position: "top-right",
        loaderBg: "#ff6849",
        icon: "success",
        hideAfter: 3500,
        stack: 6,
    });
}
/*fin DE NOTIFICACIONES */

/*INICIO MANEJO DE FORMULARIOS/REGISTROS*/
function datos_formulario(formulario) {
    return new FormData(formulario);
    //rellenar automaticamente.
    var datos = "";
    var cantidad_datos = 0;
    var inputs = formulario.getElementsByTagName("input");
    for (var i = inputs.length - 1; i >= 0; i--) {
        if (datos == "") {
            datos = inputs[i].id + "=" + inputs[i].value;
        } else {
            datos += "&" + inputs[i].id + "=" + inputs[i].value;
        }
    }
    var selects = formulario.getElementsByTagName("select");
    for (var i = selects.length - 1; i >= 0; i--) {
        if (datos == "") {
            datos = selects[i].id + "=" + selects[i].value;
        } else {
            datos += "&" + selects[i].id + "=" + selects[i].value;
        }
    }
    return datos;
}

function guardar_registro(formulario, callback = null) {
    var a = 1;
    datos = datos_formulario(formulario);
    var onRresponse = function (respuesta) {
        var datos = respuesta.responseText.split("|");
        if (datos[1] == "correcto") {
            if (datos[2] == "nuevo") {
                notify_success("Registro agregado correctamente");
            } else if (datos[2] == "editado") {
                notify_success("Registro modificado correctamente");
            }
            if (typeof callback_guardar_registro_correcto !== "undefined") {
                callback_guardar_registro_correcto();
            }
            if (callback != null) {
                callback();
            }
        } else if (datos[1] == "error") {
            notify_error(datos[2]);
        } else {
            notify_error(respuesta.responseText);
        }
        delete callback_guardar_registro_correcto;
    };
    ajax_tofunction(datos, crud_url + "?operacion=guardar", onRresponse);
    return true;
}

function download_select_options(fields, table, operation, destiny, callback = null, callback_error = null) {
    var datos = new FormData();
    if (fields != "") {
        var campos = fields.split(",");
        for (let i = 0; i < campos.length; i++) {
            valores = campos[i].split("=");
            if (valores.length > 1) {
                datos.append(valores[0], valores[1]);
            } else {
                datos.append(campos[i], elementValue(campos[i]));
            }
        }
    }
    datos.append("table", table);
    var onRresponse = function (respuesta) {
        var datos = respuesta.responseText.split("|");
        if (datos[1] == "correcto") {
            objeto(destiny).innerHTML = datos[2];
            if (typeof callback_download !== "undefined") {
                callback_download();
                delete callback_download;
            }
            if (callback != null) {
                if (callback.length > 0) {
                    callback(datos[2]);
                } else {
                    callback();
                }
            }
        } else if (datos[1] == "error") {
            notify_error(datos[2]);
        } else {
            notify_error(respuesta.responseText);
        }
        delete callback_download;
    };
    ajax_tofunction(datos, crud_url + "?operacion=" + operation, onRresponse, callback_error);
    return true;
}

function upload_action(fields, table, operation, callback = null, callback_error = null) {
    var datos = new FormData();
    if (fields != "") {
        var campos = fields.split(",");
        for (let i = 0; i < campos.length; i++) {
            valores = campos[i].split("=");
            if (valores.length > 1) {
                datos.append(valores[0], valores[1]);
            } else {
                datos.append(campos[i], elementValue(campos[i]));
            }
        }
    }
    datos.append("table", table);
    var onRresponse = function (respuesta) {
        var datos = respuesta.responseText.split("|");
        if (datos[1] == "correcto") {
            if (typeof callback_upload !== "undefined") {
                callback_upload();
            }
            if (callback != null) {
                if (callback.length > 0) {
                    callback(datos[2]);
                } else {
                    callback();
                }
            }
        } else if (datos[1] == "error") {
            notify_error(datos[2]);
        } else {
            notify_error(respuesta.responseText);
        }
        delete callback_upload;
    };
    ajax_tofunction(datos, crud_url + "?operacion=" + operation, onRresponse, callback_error);
    return true;
}

function upload_file(fields, file_field_name, table, operation, callback = null, callback_upload = null) {
    var formData = new FormData();
    if (fields != "") {
        var campos = fields.split(",");
        for (let i = 0; i < campos.length; i++) {
            valores = campos[i].split("=");
            if (valores.length > 1) {
                formData.append(valores[0], valores[1]);
            } else {
                formData.append(campos[i], elementValue(campos[i]));
            }
        }
    }
    formData.append("table", table);
    var file_field = element(file_field_name);
    var files = file_field.files;
    var file_to_upload = files[0];
    formData.append("file_uploaded", file_to_upload, file_to_upload.name);

    var onRresponse = function (respuesta) {
        var datos = respuesta.responseText.split("|");
        if (datos[1] == "correcto") {
            if (typeof callback_upload !== "undefined" && callback_upload != null) {
                callback_upload();
            }
            if (callback != null) {
                if (datos.length > 0) {
                    callback(datos[2]);
                } else {
                    callback();
                }
            }
        } else if (datos[1] == "error") {
            notify_error(datos[2]);
        } else {
            notify_error(respuesta.responseText);
        }
        delete callback_upload;
    };
    ajax_tofunction(formData, crud_url + "?operacion=" + operation, onRresponse);
    return true;
}

function download_input_value(fields, table, operation, destiny, callback = null, callback_error = null) {
    var datos = new FormData();
    if (fields != "") {
        var campos = fields.split(",");
        for (let i = 0; i < campos.length; i++) {
            valores = campos[i].split("=");
            if (valores.length > 1) {
                datos.append(valores[0], valores[1]);
            } else {
                datos.append(campos[i], elementValue(campos[i]));
            }
        }
    }
    datos.append("table", table);
    var onRresponse = function (respuesta) {
        var datos = respuesta.responseText.split("|");
        if (datos[1] == "correcto") {
            objeto(destiny).value = datos[2];
            element(destiny).parentNode.classList.toggle("focused", true);
            if (typeof callback_download !== "undefined") {
                callback_download();
                delete callback_download;
            }
            if (callback != null) {
                if (callback.length > 0) {
                    callback(datos[2]);
                } else {
                    callback();
                }
            }
        } else if (datos[1] == "error") {
            notify_error(datos[2]);
        } else {
            notify_error(respuesta.responseText);
        }
        delete callback_download;
    };
    ajax_tofunction(datos, crud_url + "?operacion=" + operation, onRresponse, callback_error);
    return true;
}

function download_div_content(fields, table, operation, destiny, callback = null, do_activate_switch = true, callback_error = null) {
    var datos = new FormData();
    if (fields != "") {
        var campos = fields.split(",");
        for (let i = 0; i < campos.length; i++) {
            valores = campos[i].split("=");
            if (valores.length > 1) {
                datos.append(valores[0], valores[1]);
            } else {
                datos.append(campos[i], elementValue(campos[i]));
            }
        }
    }
    datos.append("table", table);
    var onRresponse = function (respuesta) {
        var datos = respuesta.responseText.split("|");
        if (datos[1] == "correcto") {
            objeto(destiny).innerHTML = datos[2];
            activate_select2();
            if (do_activate_switch) {
                activate_switch();
            }
            if (typeof callback_download !== "undefined") {
                callback_download();
                delete callback_download;
            }
            if (callback != null) {
                if (callback.length > 0) {
                    callback(datos[2]);
                } else {
                    callback();
                }
            }
        } else if (datos[1] == "error") {
            notify_error(datos[2]);
        } else {
            notify_error(respuesta.responseText);
        }
        delete callback_download;
    };
    ajax_tofunction(datos, crud_url + "?operacion=" + operation, onRresponse, callback_error);
    return true;
}

function editar_registro(datos, fila) {
    row_seleccionada = fila;
    var datos = datos.split("&");
    for (var i = datos.length - 1; i >= 0; i--) {
        var par = datos[i].split("=");
        if (par.length > 1 && document.getElementById(par[0]) != undefined) {
            document.getElementById(par[0]).value = par[1];
            document.getElementById(par[0]).parentNode.classList.toggle("focused", par[1].length > 0);
        }
    }
    if (!!objeto("botones_edicion")) objeto("botones_edicion").style.display = "";
}

function move_label(idcampo) {
    document.getElementById(idcampo).parentNode.classList.toggle("focused", 1);
}

function botones_accion_registro(id) {
    if (document.getElementById("botones_accion_registro") != undefined && document.getElementById(id).value == "") {
        document.getElementById("botones_accion_registro").style.display = "none";
    } else {
        if (document.getElementById("botones_accion_registro") != undefined) {
            document.getElementById("botones_accion_registro").style.display = "";
        }
    }
}

function ocultar_cuerpo_panel(panel) {
    var cuerpo = panel.getElementsByClassName("panel-body")[0];
    cuerpo.style.display = cuerpo.style.display == "" ? "none" : "";
}
/*FIN MANEJO DE FORMULARIOS/REGISTROS*/

/* REPORTES/IMPRIMIR EXPOTRTAR */
function fnExcelReport(elementid, file_name, linkid) {
    var tab_text = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    tab_text = tab_text + "<head><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>";
    tab_text = tab_text + "<x:Name>Test Sheet</x:Name>";
    tab_text = tab_text + "<x:WorksheetOptions><x:Panes></x:Panes></x:WorksheetOptions></x:ExcelWorksheet>";
    tab_text = tab_text + "</x:ExcelWorksheets></x:ExcelWorkbook></xml></head><body>";
    tab_text = tab_text + "<table border='1px'>";
    tab_text = tab_text + document.getElementById(elementid).innerHTML;
    tab_text = tab_text + "</table></body></html>";
    var data_type = "data:application/vnd.ms-excel";

    var ua = window.navigator.userAgent;
    var msie = ua.indexOf("MSIE ");

    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
        if (window.navigator.msSaveBlob) {
            var blob = new Blob([tab_text], {
                type: "application/csv;charset=utf-8;",
            });
            navigator.msSaveBlob(blob, file_name + ".xls");
        }
    } else {
        document.getElementById(linkid).setAttribute("href", data_type + ", " + escape(tab_text));
        document.getElementById(linkid).setAttribute("download", file_name + ".xls");
    }
}

function print_div(divid) {
    var mywindow = window.open("", "PRINT", "fullscreen=yes");
    mywindow.document.write("<html><head><title>" + document.title + "</title>");
    mywindow.document.write("</head><body >");
    mywindow.document.write(document.getElementById(divid).innerHTML);
    mywindow.document.write("</body></html>");

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10*/

    mywindow.print();
    //mywindow.close();

    return true;
}
/* FIN REPORTES/IMPRIMIR EXPOTRTAR */

/**
 * FUNCIONES PARA EL MANEJO DE OBJETOS.
 */

function element(id) {
    return document.getElementById(id);
}

function elementValue(id) {
    return document.getElementById(id).value;
}

function showElement(id) {
    element(id).style.display = "";
}

function hideElement(id) {
    element(id).style.display = "none";
}

function showElements(ids) {
    ids = ids.split(",");
    for (var i = ids.length - 1; i >= 0; i--) {
        showElement(ids[i]);
    }
}

function hideElements(ids) {
    ids = ids.split(",");
    for (var i = ids.length - 1; i >= 0; i--) {
        hideElement(ids[i]);
    }
}

function placeFocus(id) {
    element(id).focus();
}

function goTop() {
    window.scrollTo(0, 0);
}

function IrPanel(tab) {
    $('.nav-tabs a[href="#' + tab + '"]').tab("show");
}

function disableElements(ids) {
    ids = ids.split(",");
    for (var i = ids.length - 1; i >= 0; i--) {
        element(ids[i]).disabled = true;
        element(ids[i]).readOnly = true;
    }
}

function enableElements(ids) {
    ids = ids.split(",");
    for (var i = ids.length - 1; i >= 0; i--) {
        element(ids[i]).disabled = false;
        element(ids[i]).readOnly = false;
    }
}

var Elements_clear_innerhtml = ["div", "span", "p", "h1", "h2", "h3", "h4", "h5", "h6", "tbody", "table", "tr", "td", "th"];
function clearElements(ids) {
    ids = ids.split(",");
    for (var i = ids.length - 1; i >= 0; i--) {
        if (element(ids[i]) != undefined) {
            if (Elements_clear_innerhtml.includes(element(ids[i]).nodeName.toLowerCase())) {
                element(ids[i]).innerHTML = "";
            } else {
                element(ids[i]).value = "";
            }
        }
        element(ids[i]).value = "";
    }
}
//fin sustiruir dom

//TODO: completar funcion.
function logError(texto) {
    return false;
}

/**LEGACY */
function objeto(id) {
    //sustituir por element()
    return document.getElementById(id);
}

function valorObjeto(id) {
    //sustituir por elementValue
    //return document.getElementById(id).value;
    if ($("#" + id).val() != null) {
        return $("#" + id)
            .val()
            .toString();
    } else {
        return "";
    }
}

/*FIN FUNCIONES PARA EL MANEJO DE OBJETOS */

/**
 * FUNCIONES PARA EL MANEJO DE ADDONS
 */

//FLOATEING LABESL
function habilitar_floating_labels() {
    $(".floating-labels .form-control")
        .on("focus blur", function (e) {
            $(this)
                .parents(".form-group")
                .toggleClass("focused", e.type === "focus" || this.value.length > 0);
        })
        .trigger("blur");
}

function activate_select2() {
    $(".select2").select2();
}

//DATA TABLES
function activar_tabla(idtabla) {      
    //
    
    //

    var tabla = document.getElementById(idtabla);            
    var ds = tabla.dataset;                                  
    var pagingUser = (ds.confPaging === undefined) ? true : (ds.confPaging === "true");          
    var selectUser = ds.confSelect === "true";               
    var buttonsUser = ds.confButtons === "true";             
    
    var nombreABuscar = ds.confRowgroup; 
    var indiceReal = -1;
    $('#' + idtabla + ' thead th').each(function(i) {
        if ($(this).text().trim() === nombreABuscar) {
            indiceReal = i; 
        }
    });
    var rowGroupUser = (indiceReal !== -1);

    var responsiveUser = ds.confResponsive === "true";       
    var colReorderUser = ds.confColreorder === "true";       
    var columnControlUser = ds.confColumncontrol === "true"; 
    var columnasAuto = [];

    $('#' + idtabla + ' thead th').each(function() {
        columnasAuto.push({ name: $(this).text().trim() });
    });
    
    var botones = [];
    if (buttonsUser) {  
        botones.push("copy", "csv", "excel", "pdf", "print");
    }
    if (rowGroupUser) { 
        botones.push({
            text: 'Limpiar Agrupación',
            action: function(e, dt) {
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
            action: function(e, dt) {
                dt.rows().deselect();
            }
        });
    }

    var configTopStart = [];
    if (buttonsUser) configTopStart.push('buttons');   
    if (pagingUser) configTopStart.push('pageLength'); 
    configTopStart.push('search');

    var layoutConfig = {
        topStart: configTopStart.length > 0 ? { features: configTopStart } : null, 
        topEnd: null,                              
        bottomStart: pagingUser ? 'paging' : null, 
        bottomEnd: null,                           
        top: null,                                 
        bottom: null                               
    };

    var tabla_nueva = new DataTable('#' + idtabla, {
        layout: layoutConfig,   
        retrieve: true,         
        columnControl: columnControlUser ? { 
            target: 0, 
            content: ['order', ['orderAsc', 'orderDesc', 'search']]
        } : false,
        buttons: botones.map(function(btn) {
            if (typeof btn === "string") { 
                return { 
                    extend: btn,
                    exportOptions: {
                        modifier: function() {
                            var seleccionadas = tabla_nueva.rows({ selected: true }).count();
                            return (selectUser && seleccionadas > 0) 
                                ? { selected: true }  
                                : { selected: null }; 
                        }
                    }
                }; 
            }
            return btn;
        }),
        language: { url: "../assets/plugins/datatables/media/datatables.spanish.lang" }, 
        responsive: responsiveUser,                         
        colReorder: colReorderUser,                         
        select: selectUser ? { style: 'multi' } : false,    
        paging: true,                                       
        pageLength: 10,                                     
        lengthChange: pagingUser,                           
        stateSave: true,                                    
        stateDuration: 0,                                   
        ordering: true,                                     
        
        // --- CAMBIO 3: Usar el índice numérico para el orden inicial ---
        order: rowGroupUser ? [[indiceReal, 'asc']] : [[3, 'asc']],

        createdRow: function (row, data, dataIndex) { 
            if (typeof fila_agregada === "function") fila_agregada(row, data, dataIndex); 
        },
        lengthMenu: [[10,25,50,-1],[10,25,50,"All"]],
        
        // --- CAMBIO 4: Usar el índice numérico para el agrupamiento ---
        rowGroup: rowGroupUser ? {
            dataSrc: indiceReal, 
            enable: true,
            startRender: function(rows, group) { return group + ' (' + rows.count() + ' registros)'; }
        } : false,

        stateSaveCallback: function(settings, data) {
            if (rowGroupUser) {
                data.rowGroup = tabla_nueva.rowGroup().dataSrc();
                data.rowGroupEnabled = tabla_nueva.rowGroup().enabled();
            }
            if (selectUser) {
                data.selectedRows = tabla_nueva.rows({ selected: true }).indexes().toArray();
            }
            localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            if (typeof upload_action === "function") {
                upload_action('idtabla=' + settings.sTableId +',estadotabla=' + encodeURIComponent(JSON.stringify(data)),'datatables','guardar_estado_datatables');
            }
        },
        stateLoadCallback: function(settings) {
            var data = JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            if (data) {
                setTimeout(function() {
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

function desactivar_tabla(tabla_instancia) {

    if (!tabla_instancia) return;

    if (typeof tabla_instancia.destroy === "function") {
        tabla_instancia.destroy();
    }
}

function agregar_fila_tabla(tabla, items) {
    var items = items.split("|");
    return tabla.row.add(items).draw();
}

function fila_agregada(row, data, dataIndex) {
    //notify_info("fila agregada agregada.");
}

function editar_fila_tabla(fila_seleccionada, items_nuevos) {
    var celdas = fila_seleccionada.getElementsByTagName("td");
    items_nuevos = items_nuevos.split("|");
    for (var i = celdas.length - 1; i >= 0; i--) {
        celdas[i].innerHTML = items_nuevos[i];
    }
}

//BOOTSTRAP SWITCH
function activate_switch() {
    $("input[type='checkbox'], input[type='radio']").bootstrapSwitch();
    $(".radio-switch").on("switch-change", function () {
        $(".radio-switch").bootstrapSwitch("toggleRadioState");
    }),
        $(".radio-switch").on("switch-change", function () {
            $(".radio-switch").bootstrapSwitch("toggleRadioStateAllowUncheck");
        }),
        $(".radio-switch").on("switch-change", function () {
            $(".radio-switch").bootstrapSwitch("toggleRadioStateAllowUncheck", !1);
        });
}

function download_chart_data(fields, table, operation, destiny, callback = null, error_callback = null) {
    var datos = new FormData();

    if (fields != "") {
        var campos = fields.split(",");
        for (let i = 0; i < campos.length; i++) {
            let valores = campos[i].split("=");
            if (valores.length > 1) {
                datos.append(valores[0], valores[1]);
            } else {
                datos.append(campos[i], elementValue(campos[i]));
            }
        }
    }

    datos.append("table", table);

    var onRresponse = function (respuesta) {
        let json;
        try {
            let texto = respuesta.responseText.trim();
            if (texto.startsWith('|correcto|')) texto = texto.split('|correcto|')[1];
            json = JSON.parse(texto);
        } catch (e) {
            notify_error("Error al parsear JSON: " + e.message + " => " + respuesta.responseText);
            if (error_callback) error_callback(respuesta.responseText);
            return;
        }

        if (json.error) {
            notify_error(json.error);
            if (error_callback) error_callback(json.error);
            return;
        }

        const params = json.parametros || {};
        const labels = params.labels || [];
        const type   = params.type || 'bar';
        const title  = params.title || "";
        const ctx    = document.getElementById(destiny).getContext('2d');

        if (ctx._chartInstance) ctx._chartInstance.destroy();

        // Función para oscurecer color
        function darkenRgba(rgba, factor = 0.6) {
            const match = rgba.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
            if (!match) return 'rgba(0,0,0,1)';
            let [ , r, g, b, a ] = match;
            r = Math.floor(parseInt(r) * factor);
            g = Math.floor(parseInt(g) * factor);
            b = Math.floor(parseInt(b) * factor);
            a = a !== undefined ? parseFloat(a) : 1;
            return `rgba(${r},${g},${b},${a})`;
        }

        const finalDatasets = json.data.map((ds, i) => {
            let bg = [];

            if (Array.isArray(ds.backgroundColor)) {
                if (json.data.length === 1) {
                    bg = ds.backgroundColor;
                } else {
                    bg = Array(ds.data.length).fill(ds.backgroundColor[0]);
                }
            } else {
                bg = Array(ds.data.length).fill(ds.backgroundColor || 'rgba(0,0,0,0.5)');
            }

            let border = ds.borderColor || bg.map(c => darkenRgba(c, 0.6));

            return {
                label: ds.label,
                data: ds.data,
                backgroundColor: bg,
                borderColor: border,
                borderWidth: 1
            };
        });

        const chart = new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: finalDatasets
            },
            options: {
                responsive: true,
                scales: (type === 'bar' || type === 'line') ? { y: { beginAtZero: true } } : {},
                plugins: {
                    title: { display: true, text: title }
                }
            }
        });

        ctx._chartInstance = chart;

        if (typeof callback_download !== "undefined") { 
            callback_download(); 
            delete callback_download; 
        }

        if (callback) callback(json);
    };

    ajax_tofunction(datos, crud_url + "?operacion=" + operation, onRresponse);
    return true;
}

function show_external_option(idopcion, opcion, menu, prohibidos = null) {

    let datos = {};

    let idsProhibidos = [];

    if (prohibidos) {
        idsProhibidos = prohibidos.split(',').map(id => id.trim());
    }

    idsProhibidos.push("table");

    let notSelector = idsProhibidos
        .map(id => `:not(#${CSS.escape(id)})`)
        .join('');

    const elementos = document.querySelectorAll(
        `input${notSelector}, select${notSelector}, textarea${notSelector}`
    );

    elementos.forEach(function (el) {

        const key = el.id || el.name;
        if (!key) return;

        if (el.type === "checkbox") {
            datos[key] = el.checked ? 1 : 0;

        } else if (el.type === "radio") {
            if (el.checked) {
                datos[key] = el.value;
            }

        } else {
            datos[key] = el.value;
        }
    });

    localStorage.setItem(
        "datos_json_externo",
        JSON.stringify(datos)
    );

    const url = window.location.pathname +
        "?idopcion=" + encodeURIComponent(idopcion) +
        "&opcion=" + encodeURIComponent(opcion) +
        "&menu=" + encodeURIComponent(menu);

    window.open(url, "_blank");
}

function restore_data_local_storage() {
    const datosGuardados = localStorage.getItem("datos_json_externo");

    if (!datosGuardados) return;

    const datos = JSON.parse(datosGuardados);

    Object.keys(datos).forEach(function (key) {

        const elemento = document.getElementById(key);

        if (!elemento) return;

        if (elemento.type === "checkbox") {
            elemento.checked = datos[key] == 1;
        }
        else if (elemento.type === "radio") {
            const radio = document.querySelector(
                "input[type='radio'][name='" + elemento.name + "'][value='" + datos[key] + "']"
            );
            if (radio) radio.checked = true;
        }
        else {
            elemento.value = datos[key];
        }

    });

}


function export_to_xlsx(idtabla, filename = "reporte.xlsx") {
    var table = document.getElementById(idtabla);

    if (!table) {
        console.error("No se encontró la tabla con id:", idtabla);
        return;
    }

    // generar fecha y hora
    var now = new Date();
    var fechaHora =
        now.getFullYear() +
        String(now.getMonth() + 1).padStart(2, "0") +
        String(now.getDate()).padStart(2, "0") + "_" +
        String(now.getHours()).padStart(2, "0") +
        String(now.getMinutes()).padStart(2, "0") +
        String(now.getSeconds()).padStart(2, "0");

    var finalFilename = filename.replace(".xlsx", "") + "_" + fechaHora + ".xlsx";

    try {

        var wb = XLSX.utils.table_to_book(table, {
            sheet: "Hoja 1",
            raw: true
        });

        var ws = wb.Sheets[wb.SheetNames[0]];

        if (ws && ws["!ref"]) {

            var range = XLSX.utils.decode_range(ws["!ref"]);

            for (var r = 1; r <= range.e.r; r++) {

                for (var c = 0; c <= range.e.c; c++) {

                    var addr = XLSX.utils.encode_cell({ r: r, c: c });
                    var cell = ws[addr];

                    if (!cell || cell.v == null || cell.v === "") continue;

                    var value = String(cell.v)
                        .replace(/[Q$,]/g, "") // quitar moneda y comas
                        .trim();

                    var n = Number(value);

                    if (isNaN(n)) continue;

                    cell.t = "n";
                    cell.v = n;

                    delete cell.w;
                    delete cell.z;
                }
            }
        }

        XLSX.writeFile(wb, finalFilename);

    } catch (error) {
        console.error("Error exportando la tabla:", error);
    }
}
