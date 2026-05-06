var ventana_impresion_liquidacion = null;

function generar_reporte_liquidacion_de_ingresos() {
    var formulario = element('formulario_liquidacion_de_ingresos');
    if (formulario && !formulario.reportValidity()) {
        return;
    }

    ventana_impresion_liquidacion = window.open('', '_blank');
    if (!ventana_impresion_liquidacion) {
        notify_warning('El navegador bloqueo la ventana de impresion. Habilita popups para continuar.');
        return;
    }

    ventana_impresion_liquidacion.document.open();
    ventana_impresion_liquidacion.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Liquidacion de ingresos</title></head><body>Generando reporte...</body></html>');
    ventana_impresion_liquidacion.document.close();

    callback_reporte = function () {
        imprimir_liquidacion_de_ingresos();
    };

    download_div_content(
        'fecha_desde,fecha_hasta',
        'liquidacion_de_ingresos',
        'generar_reporte_liquidacion_de_ingresos',
        'contenedor_liquidacion_de_ingresos',
        callback_reporte,
        false
    );
}

function limpiar_reporte_liquidacion_de_ingresos() {
    if (element('contenedor_liquidacion_de_ingresos')) {
        element('contenedor_liquidacion_de_ingresos').innerHTML = '';
    }
}

function imprimir_liquidacion_de_ingresos() {
    var contenido = element('contenedor_liquidacion_de_ingresos').innerHTML;

    if (contenido.trim() === '') {
        notify_warning('No hay contenido para imprimir.');
        return;
    }

    var ventana = ventana_impresion_liquidacion;
    if (!ventana || ventana.closed) {
        ventana = window.open('', '_blank');
    }

    if (!ventana) {
        notify_warning('No se pudo abrir la ventana de impresion.');
        return;
    }

    var estilos_impresion = '<style>' +
        ':root{--line:#222;--light:#d9d9d9;--font:Arial,Helvetica,sans-serif;}*{box-sizing:border-box;}body{margin:0;padding:14px;font-family:var(--font);color:#000;background:#fff;}' +
        '.sheet{max-width:1050px;margin:0 auto;}.title{text-align:center;font-size:24px;letter-spacing:.4px;margin:0 0 10px;font-weight:700;text-transform:uppercase;text-decoration:underline;}' +
        '.top-grid{display:grid;grid-template-columns:1.25fr 1fr 1.25fr;gap:14px;align-items:end;margin-bottom:8px;font-size:14px;}.label{font-weight:700;text-transform:uppercase;}' +
        '.line-value{border-bottom:1px solid #000;min-height:20px;display:inline-block;min-width:190px;text-align:center;font-weight:700;padding:0 6px 2px;text-transform:uppercase;}.line-value.small{min-width:120px;}' +
        '.line-wrap{display:flex;align-items:flex-end;gap:8px;justify-content:center;font-weight:700;text-transform:uppercase;}.summary{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:8px;}' +
        'table{width:100%;border-collapse:collapse;border:1px solid var(--line);font-size:13px;}th,td{border:1px solid var(--line);padding:4px 6px;vertical-align:middle;}th{background:var(--light);font-weight:700;text-transform:uppercase;text-align:center;}' +
        '.main-table tbody td,.small-table tbody td,.summary tbody td{font-size:10px;line-height:1.2;}' +
        '.main-table tbody tr:last-child td,.small-table tbody tr:last-child td,.summary tbody tr:last-child td{font-size:11.5px;line-height:1.25;}' +
        '.text-right{text-align:right;}.text-center{text-align:center;}.text-left{text-align:left;}.strong{font-weight:700;}' +
        '.summary td:first-child{font-weight:700;text-transform:uppercase;width:40%;}.summary td.currency{width:8%;text-align:center;font-weight:700;}.summary td.amount{width:52%;text-align:right;font-weight:700;white-space:nowrap;font-variant-numeric:tabular-nums;}' +
        '.receipt-block{border:none;padding:8px;min-height:100%;display:flex;flex-direction:column;justify-content:space-between;gap:8px;}.receipt-title{text-align:center;font-size:18px;font-weight:700;text-transform:uppercase;margin:2px 0;}' +
        '.inline-row{display:flex;justify-content:space-between;align-items:center;gap:10px;text-transform:uppercase;font-weight:700;}.fill-line{flex:1;border-bottom:1px solid #000;text-align:center;min-height:18px;padding-bottom:2px;font-weight:400;font-size:11px;text-transform:none;white-space:nowrap;}' +
        '.main-table td.text-right,.small-table td.text-right{white-space:nowrap;font-variant-numeric:tabular-nums;}' +
        '.main-table th{font-size:11px;line-height:1.2;background:#d9d9d9;}.main-table .subhead{background:#d9d9d9;font-size:10px;}.small-table thead th{background:#d9d9d9;}.section-title{margin:10px 0 0;background:#d9d9d9;border:1px solid var(--line);border-bottom:none;text-align:center;font-size:20px;text-transform:uppercase;font-weight:700;padding:5px;letter-spacing:.3px;}' +
        '.small-table{margin-bottom:12px;}.small-table th{font-size:11px;}' +
        '.spacer-rows td{height:28px;}@media print{body{padding:8px;}.sheet{max-width:none;}*{-webkit-print-color-adjust:exact;print-color-adjust:exact;}}' +
    '</style>';

    ventana.document.open();
    ventana.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Liquidacion de ingresos</title>' + estilos_impresion + '</head><body>' + contenido + '</body></html>');
    ventana.document.close();

    setTimeout(function () {
        ventana.focus();
        ventana.onafterprint = function () {
            ventana.close();
            ventana_impresion_liquidacion = null;
        };
        ventana.print();
    }, 250);
}
