(function () {
    var state = {
        iddespacho: 0,
        idpedido: 0,
        nopedido: '',
        estado_despacho: 'ACTIVO',
        detalle: [],
        modo: 'lista'
    };

    function safe(val) {
        if (val === null || val === undefined || val === 'null') {
            return '';
        }
        return String(val);
    }

    function montoSeguro(val) {
        var n = Number(val || 0);
        return isNaN(n) || n < 0 ? 0 : n;
    }

    function formatearMonto(val) {
        return Number(val || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function formatearNumeroTalla(numero) {
        var valor = String(numero === null || numero === undefined ? '' : numero).trim();
        if (valor === '') {
            return '';
        }
        if (/^-?\d+\.0+$/.test(valor)) {
            return valor.replace(/\.0+$/, '');
        }
        return valor;
    }

    function despachoEsEditable() {
        return safe(state.estado_despacho).toUpperCase() === 'ACTIVO';
    }

    function setCamposEncabezadoEditable(esEditable) {
        element('monto_flete').disabled = !esEditable;
        element('monto_otros').disabled = !esEditable;
        element('numero_factura').disabled = !esEditable;
        element('observaciones').disabled = !esEditable;
        element('fecha_factura').disabled = !esEditable;
    }

    function agruparDetalle(data) {
        var agrupado = {};

        for (var i = 0; i < data.length; i++) {
            var row = data[i];
            var key = [
                row.idset_talla,
                row.idproducto,
                row.codigo || '',
                row.descripcion || '',
                row.color || '',
                row.material || '',
                row.precio_venta || 0,
                row.imagen || ''
            ].join('_');

            if (!agrupado[key]) {
                agrupado[key] = {
                    idset_talla: row.idset_talla,
                    set_talla: row.set_talla || '',
                    codigo: row.codigo || '',
                    descripcion: row.descripcion || '',
                    color: row.color || '',
                    marca: row.marca || '',
                    material: row.material || '',
                    precio: Number(row.precio_venta || 0),
                    imagen: row.imagen || '',
                    tallas: [],
                    cantidades: {},
                    ids_pendientes: [],
                    pares_pedido: 0,
                    pares: 0,
                    subtotal: 0,
                    subtotal_pendiente: 0,
                    estado: 'ACTIVO',
                    selected: false
                };
            }

            agrupado[key].tallas.push({
                id: row.idtalla,
                numero: formatearNumeroTalla(row.talla)
            });

            var pendiente = Number(row.cantidad_pendiente || 0);
            var cantidadPedido = Number(row.cantidad || 0);
            // Mantener visible la cantidad solicitada por talla aunque ya esté despachada.
            agrupado[key].cantidades[row.idtalla] = Number(agrupado[key].cantidades[row.idtalla] || 0) + cantidadPedido;
            agrupado[key].pares_pedido += cantidadPedido;
            agrupado[key].pares += pendiente;
            agrupado[key].subtotal += Number(row.subtotal || (cantidadPedido * Number(row.precio_venta || 0)));
            agrupado[key].subtotal_pendiente += Number(row.subtotal_pendiente || (pendiente * Number(row.precio_venta || 0)));

            if (pendiente > 0) {
                agrupado[key].ids_pendientes.push(row.idpedido_detalle);
            }
        }

        var salida = [];
        Object.keys(agrupado).forEach(function (k) {
            if (agrupado[k].pares <= 0) {
                agrupado[k].estado = 'DESPACHADO';
            } else if (agrupado[k].pares < agrupado[k].pares_pedido) {
                agrupado[k].estado = 'PARCIAL';
            } else {
                agrupado[k].estado = 'ACTIVO';
            }
            agrupado[k].tallas.sort(function (a, b) {
                return String(a.numero).localeCompare(String(b.numero), undefined, { numeric: true, sensitivity: 'base' });
            });
            salida.push(agrupado[k]);
        });

        return salida;
    }

    function renderResumen() {
        var lineas = 0;
        var pares = 0;
        var subtotal = 0;

        for (var i = 0; i < state.detalle.length; i++) {
            pares += Number(state.detalle[i].pares || 0);
            subtotal += Number(state.detalle[i].subtotal_pendiente || 0);
            if (Number(state.detalle[i].pares || 0) > 0) {
                lineas++;
            }
        }

        element('despacho_resumen_lineas').textContent = lineas;
        element('despacho_resumen_pares').textContent = pares;
        element('despacho_resumen_subtotal').textContent = 'Q ' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        if (state.iddespacho > 0) {
            if (despachoEsEditable()) {
                showElements('btn_cerrar_despacho,btn_despachar,btn_todos');
            } else {
                hideElements('btn_cerrar_despacho,btn_despachar,btn_todos');
            }
        } else {
            hideElements('btn_cerrar_despacho');
            showElements('btn_despachar,btn_todos');
        }
    }

    function renderDetalle() {
        var tbody = element('detalleDespachoBody');
        tbody.innerHTML = '';

        if (!state.detalle.length) {
            tbody.innerHTML = '<tr><td colspan="12" class="text-center text-muted py-4">No hay líneas para mostrar.</td></tr>';
            renderResumen();
            return;
        }

        for (var i = 0; i < state.detalle.length; i++) {
            var linea = state.detalle[i];
            var tablaTallas = '<table class="tabla-tallas"><tr>';
            for (var t = 0; t < linea.tallas.length; t++) {
                tablaTallas += '<td><strong>' + safe(linea.tallas[t].numero) + '</strong></td>';
            }
            tablaTallas += '</tr><tr>';
            for (var j = 0; j < linea.tallas.length; j++) {
                var tallaId = linea.tallas[j].id;
                var val = Number(linea.cantidades[tallaId] || 0);
                tablaTallas += '<td style="color:' + (val === 0 ? '#ff0000' : '#fff') + '">' + val + '</td>';
            }
            tablaTallas += '</tr></table>';

            var tr = document.createElement('tr');
            var esSeleccionable = Number(linea.pares || 0) > 0 && despachoEsEditable();
            if (linea.selected) {
                tr.classList.add('despacho-selected-row');
            }

            var imgSrc = linea.imagen ? '../' + linea.imagen + '?x=' + Date.now() : 'https://via.placeholder.com/50';
            tr.innerHTML =
                '<td><button type="button" class="btn btn-sm ' + (esSeleccionable ? (linea.selected ? 'btn-primary' : 'btn-outline-primary') : 'btn-secondary') + '" ' + (esSeleccionable ? ('onclick="despachoToggleGrupo(' + i + ');"') : 'disabled') + '>' + (esSeleccionable ? (linea.selected ? 'Marcado' : 'Seleccionar') : 'Despachado') + '</button></td>' +
                '<td><strong>' + safe(linea.codigo) + '</strong><br><small>' + safe(linea.descripcion) + '</small></td>' +
                '<td>' + safe(linea.set_talla) + '</td>' +
                '<td>' + safe(linea.color) + '</td>' +
                '<td>' + safe(linea.marca) + '</td>' +
                '<td>' + safe(linea.material) + '</td>' +
                '<td class="text-right">Q ' + formatearMonto(linea.precio || 0) + '</td>' +
                '<td style="text-align:center;"><img src="' + imgSrc + '" class="shoe-thumb"></td>' +
                '<td>' + tablaTallas + '</td>' +
                '<td class="text-center"><strong>' + Number(linea.pares_pedido || 0) + '</strong></td>' +
                '<td class="text-right"><strong>Q ' + formatearMonto(linea.subtotal || 0) + '</strong></td>' +
                '<td class="text-center"><strong>' + safe(linea.estado) + '</strong></td>';

            tbody.appendChild(tr);
        }

        renderResumen();
    }

    function limpiarContextoDespacho() {
        state.iddespacho = 0;
        state.idpedido = 0;
        state.nopedido = '';
        state.estado_despacho = 'ACTIVO';
        state.detalle = [];
        state.modo = 'lista';

        element('iddespacho').value = '';
        element('idpedido').value = '';
        element('despacho_seleccionado').value = 'Nuevo';
        element('pedido_seleccionado').value = 'Sin selección';
        element('monto_flete').value = '';
        element('monto_otros').value = 0;
        element('numero_factura').value = '';
        element('observaciones').value = '';
        element('fecha_factura').value = '';
        setCamposEncabezadoEditable(true);

        hideElements('detalle_despacho,btn_cerrar_despacho,btn_despachar,btn_todos');
        showElements('panel_seleccion_pedido');
    }

    function cargarDetalleDespacho(iddespacho) {
        callback_detalle_despacho = function (resp) {
            if (typeof resp === 'string') {
                resp = JSON.parse(resp);
            }

            state.iddespacho = Number(resp.iddespacho || 0);
            state.idpedido = Number(resp.idpedido || 0);
            state.estado_despacho = safe(resp.estado_despacho || 'ACTIVO').toUpperCase();
            state.detalle = agruparDetalle(resp.detalle || []);

            element('iddespacho').value = state.iddespacho;
            element('idpedido').value = state.idpedido;
            element('despacho_seleccionado').value = '#' + state.iddespacho;
            element('monto_flete').value = montoSeguro(resp.monto_flete || 0);
            element('monto_otros').value = montoSeguro(resp.monto_otros || 0);
            element('numero_factura').value = safe(resp.numero_factura || '');
            element('observaciones').value = safe(resp.observaciones || '');
            element('fecha_factura').value = safe(resp.fecha_factura || '');
            if (safe(resp.nopedido || '') !== '') {
                state.nopedido = safe(resp.nopedido);
            }
            element('pedido_seleccionado').value = state.nopedido || 'Sin selección';
            setCamposEncabezadoEditable(despachoEsEditable());

            hideElements('panel_seleccion_pedido');
            showElements('detalle_despacho');
            renderDetalle();
        };

        upload_action('iddespacho=' + iddespacho, 'despacho', 'obtener_detalle_despacho', callback_detalle_despacho);
    }

    function obtenerSeleccionadas() {
        var seleccion = [];
        for (var i = 0; i < state.detalle.length; i++) {
            if (!state.detalle[i].selected) {
                continue;
            }
            for (var j = 0; j < state.detalle[i].ids_pendientes.length; j++) {
                seleccion.push(state.detalle[i].ids_pendientes[j]);
            }
        }
        return seleccion;
    }

    window.despachoToggleGrupo = function (index) {
        var idx = Number(index);
        if (isNaN(idx) || !state.detalle[idx]) {
            return;
        }
        if (Number(state.detalle[idx].pares || 0) <= 0) {
            return;
        }
        state.detalle[idx].selected = !state.detalle[idx].selected;
        renderDetalle();
    };

    window.despachoMarcarTodosActivos = function () {
        if (!despachoEsEditable()) {
            notify_warning('El despacho está CERRADO y solo permite consulta.');
            return;
        }

        if (!state.detalle.length) {
            notify_warning('No hay productos pendientes para seleccionar.');
            return;
        }

        var seleccionables = [];
        for (var i = 0; i < state.detalle.length; i++) {
            if (Number(state.detalle[i].pares || 0) > 0) {
                seleccionables.push(state.detalle[i]);
            }
        }

        if (!seleccionables.length) {
            notify_warning('No hay líneas pendientes para seleccionar.');
            return;
        }

        var todosSeleccionados = true;
        for (var j = 0; j < seleccionables.length; j++) {
            if (!seleccionables[j].selected) {
                todosSeleccionados = false;
                break;
            }
        }

        for (var k = 0; k < seleccionables.length; k++) {
            seleccionables[k].selected = !todosSeleccionados;
        }

        renderDetalle();
    };

    window.despachoNuevo = function () {
        limpiarContextoDespacho();
        state.modo = 'nuevo';

        hideElements('div_tabla');
        showElements('div_form_despacho,panel_seleccion_pedido');
    };

    window.despachoVolverLista = function () {
        return mostrar_opcion(elementValue('idopcion_actual'), element('opcion_actual').textContent, element('menu_actual').textContent);
    };

    window.despachoSeleccionarExistente = function (iddespacho, idpedido, nopedido) {
        limpiarContextoDespacho();
        state.modo = 'existente';

        state.iddespacho = parseInt(iddespacho, 10) || 0;
        state.idpedido = parseInt(idpedido, 10) || 0;
        state.nopedido = safe(nopedido || '');

        element('iddespacho').value = state.iddespacho;
        element('idpedido').value = state.idpedido;
        element('despacho_seleccionado').value = '#' + state.iddespacho;
        element('pedido_seleccionado').value = nopedido || 'Sin selección';

        hideElements('div_tabla');
        showElements('div_form_despacho');

        cargarDetalleDespacho(state.iddespacho);
    };

    window.despachoSeleccionarPedido = function (idpedido, nopedido) {
        if (state.modo !== 'nuevo') {
            return;
        }

        var pedidoSeleccionado = parseInt(idpedido, 10) || 0;
        if (!pedidoSeleccionado) {
            notify_warning('Pedido inválido.');
            return;
        }

        if (!confirm('¿Desea crear un nuevo despacho para este pedido?')) {
            return;
        }

        var montoFlete = montoSeguro(elementValue('monto_flete'));
        var montoOtros = montoSeguro(elementValue('monto_otros'));
        var numeroFactura = safe(elementValue('numero_factura'));
        var observaciones = safe(elementValue('observaciones'));
        var fechaFactura = safe(elementValue('fecha_factura'));

        if (montoFlete <= 0) {
            notify_warning('El monto de flete es obligatorio y debe ser mayor a cero.');
            element('monto_flete').focus();
            return;
        }

        if (!numeroFactura) {
            notify_warning('El número de factura es obligatorio.');
            element('numero_factura').focus();
            return;
        }

        if (!fechaFactura) {
            notify_warning('La fecha de factura es obligatoria.');
            element('fecha_factura').focus();
            return;
        }

        callback_crear_despacho = function (resp) {
            if (typeof resp === 'string') {
                resp = JSON.parse(resp);
            }

            state.iddespacho = Number(resp.iddespacho || 0);
            state.idpedido = Number(resp.idpedido || pedidoSeleccionado);
            state.nopedido = safe(nopedido || '');
            state.estado_despacho = 'ACTIVO';

            element('iddespacho').value = state.iddespacho;
            element('idpedido').value = state.idpedido;
            element('despacho_seleccionado').value = '#' + state.iddespacho;
            element('pedido_seleccionado').value = state.nopedido || 'Sin selección';

            notify_success('Despacho creado correctamente.');
            cargarDetalleDespacho(state.iddespacho);
        };

        var parametros = 'idpedido=' + pedidoSeleccionado + ',monto_flete=' + montoFlete + ',monto_otros=' + montoOtros;
        if (numeroFactura) parametros += ',numero_factura=' + numeroFactura;
        if (observaciones) parametros += ',observaciones=' + observaciones;
        if (fechaFactura) parametros += ',fecha_factura=' + fechaFactura;

        upload_action(parametros, 'despacho', 'crear_despacho', callback_crear_despacho);
    };

    window.despachoDespacharSeleccionadas = function () {
        if (!despachoEsEditable()) {
            notify_warning('El despacho está CERRADO y no permite despachar más líneas.');
            return;
        }

        if (!state.iddespacho) {
            notify_warning('Debe seleccionar o crear un despacho.');
            return;
        }

        if (!state.idpedido) {
            notify_warning('Debe seleccionar un pedido.');
            return;
        }

        var seleccionadas = obtenerSeleccionadas();
        if (!seleccionadas.length) {
            notify_warning('Seleccione al menos un producto/set.');
            return;
        }

        if (!confirm('¿Confirma despachar las líneas seleccionadas en este despacho?')) {
            return;
        }

        var montoFlete = montoSeguro(elementValue('monto_flete'));
        var montoOtros = montoSeguro(elementValue('monto_otros'));

        var fields = 'iddespacho=' + state.iddespacho + ',idpedido=' + state.idpedido + ',monto_flete=' + montoFlete + ',monto_otros=' + montoOtros;
        for (var i = 0; i < seleccionadas.length; i++) {
            fields += ',ids_detalle[]=' + seleccionadas[i];
        }

        callback_despachar = function () {
            notify_success('Líneas despachadas correctamente.');
            cargarDetalleDespacho(state.iddespacho);
        };

        upload_action(fields, 'despacho', 'despachar_lineas', callback_despachar);
    };

    window.despachoCerrarActual = function () {
        if (!state.iddespacho) {
            notify_warning('Debe seleccionar un despacho.');
            return;
        }

        if (!confirm('¿Confirma cerrar este despacho? El pedido puede seguir recibiendo otros despachos.')) {
            return;
        }

        callback_cerrar_actual = function () {
            notify_success('Despacho cerrado correctamente.');
            return mostrar_opcion(elementValue('idopcion_actual'), element('opcion_actual').textContent, element('menu_actual').textContent);
        };

        upload_action('iddespacho=' + state.iddespacho, 'despacho', 'cerrar_despacho', callback_cerrar_actual);
    };
})();
