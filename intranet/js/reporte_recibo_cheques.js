var ventana_impresion_reporte_recibo_cheques = null;

function generar_reporte_recibo_cheques() {
    var formulario = element('formulario_reporte_recibo_cheques');
    var fecha_desde = element('fecha_desde');
    var fecha_hasta = element('fecha_hasta');

    if (!fecha_desde || fecha_desde.value.trim() === '' || !fecha_hasta || fecha_hasta.value.trim() === '') {
        notify_warning('Debe seleccionar fecha desde y fecha hasta.');
        if (fecha_desde && fecha_desde.value.trim() === '') {
            fecha_desde.focus();
        } else if (fecha_hasta) {
            fecha_hasta.focus();
        }
        return;
    }

    if (formulario && !formulario.reportValidity()) {
        return;
    }

    ventana_impresion_reporte_recibo_cheques = window.open('', '_blank');
    if (!ventana_impresion_reporte_recibo_cheques) {
        notify_warning('El navegador bloqueo la ventana de impresion. Habilita popups para continuar.');
        return;
    }

    ventana_impresion_reporte_recibo_cheques.document.open();
    ventana_impresion_reporte_recibo_cheques.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Reporte de recibos</title></head><body>Generando reporte...</body></html>');
    ventana_impresion_reporte_recibo_cheques.document.close();

    var callback_reporte = function () {
        imprimir_reporte_recibo_cheques();
    };

    download_div_content(
        'idcliente,fecha_desde,fecha_hasta',
        'reporte_recibo_cheques',
        'generar_reporte_recibo_cheques',
        'contenedor_reporte_recibo_cheques',
        callback_reporte,
        false
    );
}

function limpiar_reporte_recibo_cheques() {
    if (element('contenedor_reporte_recibo_cheques')) {
        element('contenedor_reporte_recibo_cheques').innerHTML = '';
    }

    setTimeout(function () {
        limpiar_select_reporte_recibo_cheques('idcliente');
    }, 0);
}

function limpiar_select_reporte_recibo_cheques(id_select) {
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

function imprimir_reporte_recibo_cheques() {
    var contenido = element('contenedor_reporte_recibo_cheques').innerHTML;

    if (contenido.trim() === '') {
        notify_warning('No hay contenido para imprimir.');
        return;
    }

    var ventana = ventana_impresion_reporte_recibo_cheques;
    if (!ventana || ventana.closed) {
        ventana = window.open('', '_blank');
    }

    if (!ventana) {
        notify_warning('No se pudo abrir la ventana de impresion.');
        return;
    }

    var estilos_impresion = '<style>' +
        '*{box-sizing:border-box;}body{margin:0;padding:18px;background:#fff;color:#111;font-family:Arial,Helvetica,sans-serif;}' +
        '.sheet{max-width:850px;margin:0 auto;}.report-title{text-align:center;margin:0 0 8px;font-size:22px;font-weight:700;text-transform:uppercase;}' +
        '.report-range{display:flex;justify-content:center;align-items:center;gap:8px;margin:0 0 12px;font-size:12px;text-transform:uppercase;}' +
        'table{width:100%;border-collapse:collapse;table-layout:fixed;border:2px solid #20242a;}.recibos-table th,.recibos-table td{border:2px solid #20242a;padding:6px 6px;font-size:13px;line-height:1.2;vertical-align:middle;}' +
        '.recibos-table th{font-weight:700;text-align:center;background:#f4f5f7;}.text-center{text-align:center;}.text-right{text-align:right;}.text-left{text-align:left;}' +
        '.amount{white-space:nowrap;font-variant-numeric:tabular-nums;}.amount-box{display:flex;justify-content:space-between;gap:12px;width:100%;font-variant-numeric:tabular-nums;}' +
        '.recibo-total{font-size:16px;}.fila-total td{font-weight:700;background:#f7f7f7;}' +
        '@media print{body{padding:10px;}.sheet{max-width:none;}*{-webkit-print-color-adjust:exact;print-color-adjust:exact;}}' +
    '</style>';

    ventana.document.open();
    ventana.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Reporte de recibos</title>' + estilos_impresion + '</head><body>' + contenido + '</body></html>');
    ventana.document.close();

    setTimeout(function () {
        ventana.focus();
        ventana.print();
    }, 250);
}

window.generar_reporte_recibo_cheques = generar_reporte_recibo_cheques;
window.limpiar_reporte_recibo_cheques = limpiar_reporte_recibo_cheques;
