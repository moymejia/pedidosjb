var ventana_impresion_cartera = null;

function generar_reporte_cartera_de_clientes() {
    var formulario = element('formulario_cartera_de_clientes');
    if (formulario && !formulario.reportValidity()) {
        return;
    }

    ventana_impresion_cartera = window.open('', '_blank');
    if (!ventana_impresion_cartera) {
        notify_warning('El navegador bloqueo la ventana de impresion. Habilita popups para continuar.');
        return;
    }

    ventana_impresion_cartera.document.open();
    ventana_impresion_cartera.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Cartera de clientes</title></head><body>Generando reporte...</body></html>');
    ventana_impresion_cartera.document.close();

    callback_reporte = function () {
        imprimir_cartera_de_clientes();
    };

    download_div_content(
        'idvendedor,idcliente,fecha_desde,fecha_hasta,idmostrar_cheques',
        'cartera_de_clientes',
        'generar_reporte_cartera_de_clientes',
        'contenedor_cartera_de_clientes',
        callback_reporte,
        false
    );
}

function limpiar_reporte_cartera_de_clientes() {
    if (element('contenedor_cartera_de_clientes')) {
        element('contenedor_cartera_de_clientes').innerHTML = '';
    }

    setTimeout(function () {
        limpiar_select_cartera('idvendedor');
        limpiar_select_cartera('idcliente');
        limpiar_select_cartera('idmostrar_cheques');
    }, 0);
}

function limpiar_select_cartera(id_select) {
    var select = element(id_select);
    if (!select) {
        return;
    }

    if (select.querySelector('option[value=""]')) {
        select.value = '';
    } else {
        select.selectedIndex = 0;
    }

    if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.select2) {
        jQuery(select).trigger('change');
    } else if (typeof Event === 'function') {
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }
}

function imprimir_cartera_de_clientes() {
    var contenido = element('contenedor_cartera_de_clientes').innerHTML;

    if (contenido.trim() === '') {
        notify_warning('No hay contenido para imprimir.');
        return;
    }

    var ventana = ventana_impresion_cartera;
    if (!ventana || ventana.closed) {
        ventana = window.open('', '_blank');
    }

    if (!ventana) {
        notify_warning('No se pudo abrir la ventana de impresion.');
        return;
    }

    var estilos_impresion = '<style>' +
        '*{box-sizing:border-box;}body{margin:0;padding:12px;color:#111;background:#fff;font-family:"Courier New",Courier,monospace;font-size:12px;}' +
        '.sheet{max-width:1320px;margin:0 auto;}.encabezado-superior{display:grid;grid-template-columns:1fr 1fr 1fr;align-items:center;margin:0 0 2px 0;}' +
        '.encabezado-superior .left{text-align:left;}.encabezado-superior .center{text-align:center;}.encabezado-superior .right{text-align:right;}.linea-2{margin-bottom:2px;}' +
        '.strong{font-weight:700;}.titulo-centro{text-align:center;margin:1px 0;letter-spacing:.2px;}.linea-separador{border-top:1px solid #222;margin:8px 0 5px 0;}' +
        'table{width:100%;border-collapse:collapse;table-layout:fixed;}thead th{font-size:12px;font-weight:400;text-transform:uppercase;text-align:left;padding:2px 2px;border-bottom:1px solid #222;}' +
        'tbody td{padding:2px 2px;vertical-align:top;line-height:1.2;font-size:12px;border:none;}th.text-right,td.text-right{text-align:right;}th.text-center,td.text-center{text-align:center;}' +
        '.fila-vendedor td{padding-top:10px;padding-bottom:3px;font-weight:700;font-size:12px;text-align:left;}' +
        '.fila-detalle td{font-size:11px;line-height:1.1;white-space:nowrap;}' +
        '.fila-detalle td:nth-child(2){font-size:10px;white-space:normal;line-height:1.1;max-height:2.2em;overflow:hidden;}' +
        '.fila-total-ruta td{padding-top:4px;padding-bottom:2px;font-weight:400;font-size:10px;line-height:1.05;}' +
        '.fila-total-ruta td:first-child{text-align:right;}' +
        '.fila-porcentaje-ruta td{padding-top:0;padding-bottom:4px;}' +
        '.caja-total{border-top:1px solid #222;}.caja-total-b{border-bottom:1px solid #222;}.caja-l{border-left:1px solid #222;}.caja-r{border-right:1px solid #222;}' +
        '.fila-espaciador td{height:6px;padding:0;}' +
        '@media print{body{padding:6px;}*{-webkit-print-color-adjust:exact;print-color-adjust:exact;}}' +
    '</style>';

    ventana.document.open();
    ventana.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Cartera de clientes</title>' + estilos_impresion + '</head><body>' + contenido + '</body></html>');
    ventana.document.close();

    setTimeout(function () {
        ventana.focus();
        ventana.onafterprint = function () {
            ventana.close();
            ventana_impresion_cartera = null;
        };
        ventana.print();
    }, 250);
}
