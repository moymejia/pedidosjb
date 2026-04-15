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

function  cambio_temporada() {
    clearElements('idcliente,idmarca');
    if (elementValue('idtemporada') != '') {
        download_select_options('idtemporada,idcliente', 'ventas_temporada', 'options_marcas_temporada_cliente', 'idmarca');
        download_select_options('idtemporada,idmarca', 'ventas_temporada', 'options_clientes_temporada_marca', 'idcliente');
    }
}

function abrir_pedido(idpedido) {
    element('idpedido_cargar').value = idpedido;
    show_external_option(9,'Pedido','Clientes','jsid,idtemporada');
}