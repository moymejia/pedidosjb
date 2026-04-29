function cargarClientes() {
    if(elementValue('idcliente') === '') {
        download_select_options('idtemporada,idmarca', 'ventas_temporada', 'options_clientes_temporada_marca', 'idcliente');
    }
}

function cargarMarcas() {
    if(elementValue('idmarca') === '') {
        download_select_options('idtemporada,idcliente', 'ventas_temporada', 'options_marcas_temporada_cliente', 'idmarca');
    }
}

function cambio_temporada(callback = null) {
    clearElements('idcliente,idmarca');
    if (elementValue('idtemporada') != '') {
        download_select_options('idtemporada,idcliente', 'ventas_temporada', 'options_marcas_temporada_cliente', 'idmarca', function () {
            download_select_options('idtemporada,idmarca', 'ventas_temporada', 'options_clientes_temporada_marca', 'idcliente', function () {
                if (callback != null) {
                    callback();
                }
            });
        });
    } else if (callback != null) {
        callback();
    }
}

function abrir_pedido(idpedido) {
    element('idpedido_cargar').value = idpedido;
    show_external_option(9,'Pedido','Clientes','jsid,idtemporada,idcliente,idmarca');
}

function init() {
    if (element('idtemporada_cargar').value != '') {
        cambio_temporada(function () {
            objeto('idmarca').value = elementValue('idmarca_cargar');
            objeto('idcliente').value = elementValue('idcliente_cargar');
            objeto('generar_reporte').click();
        });
    }
}

init();