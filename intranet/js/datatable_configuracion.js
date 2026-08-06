function cargarEditorConfiguracionDatatable(idtabla, seccion) {
    hideElement('datatable_configuracion_listado');
    download_div_content(
        'idtabla=' + idtabla,
        'datatable_configuracion',
        'cargar_editor',
        'datatable_configuracion_editor',
        function () {
            showElement('datatable_configuracion_editor');
            mostrarStyleConfiguracionDatatable();
            mostrarPrioridadesResponsiveConfiguracionDatatable();
            if (seccion) {
                $(".nav-tabs a[href='#dtconf_" + seccion + "']").tab('show');
            }
        },
        false,
        function () {
            showElement('datatable_configuracion_listado');
        }
    );
}

function cerrarEditorConfiguracionDatatable() {
    objeto('datatable_configuracion_editor').innerHTML = '';
    hideElement('datatable_configuracion_editor');
    recargarListadoConfiguracionDatatable();
}

function recargarListadoConfiguracionDatatable() {
    desactivar_tablas();
    download_div_content(
        '',
        'datatable_configuracion',
        'cargar_listado',
        'datatable_configuracion_listado',
        function () {
            tablas_activas = activar_tablas();
            tabla = tablas_activas.length > 0 ? tablas_activas[0] : undefined;
            showElement('datatable_configuracion_listado');
        },
        false
    );
}

function guardarSeccionConfiguracionDatatable(formulario) {
    if (!window.confirm('¿ESTÁ SEGURO DE REALIZAR LOS CAMBIOS?')) {
        return false;
    }

    var idtabla = formulario.querySelector("input[name='idtabla']").value;
    var seccion = formulario.querySelector("input[name='seccion']").value;
    guardar_registro(formulario, function () {
        cargarEditorConfiguracionDatatable(idtabla, seccion);
    });
    return false;
}

function mostrarPrioridadesResponsiveConfiguracionDatatable() {
    var selector = objeto('dtconf_responsive_activo');
    var contenedor = objeto('dtconf_responsive_prioridades_contenedor');
    if (selector === undefined || selector === null || contenedor === undefined || contenedor === null) {
        return;
    }
    contenedor.style.display = selector.value === 'true' ? '' : 'none';
}

function mostrarStyleConfiguracionDatatable() {
    var selector = objeto('dtconf_style_activo');
    var contenedor = objeto('dtconf_style_contenedor');
    if (selector === undefined || selector === null || contenedor === undefined || contenedor === null) {
        return;
    }
    contenedor.style.display = selector.value === 'true' ? '' : 'none';
}
