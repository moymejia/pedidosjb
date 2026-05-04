(function () {
    var isRefreshingPanel = false;
    var isRefreshingPendientes = false;

    function normalizarMonto(valor) {
        var monto = String(valor === null || valor === undefined ? '' : valor).replace(/,/g, '').trim();
        var numero = Number(monto || 0);
        return isNaN(numero) ? 0 : numero;
    }

    function renderPreviewImagenDocumento(ruta) {
        var contenedor = objeto('preview_imagen_documento');
        if (!contenedor) {
            return;
        }

        if (!ruta) {
            contenedor.innerHTML = 'Sin imagen seleccionada';
            contenedor.classList.add('text-muted');
            return;
        }

        contenedor.classList.remove('text-muted');
        contenedor.innerHTML = "<img src='../" + ruta + "' alt='Imagen documento' style='max-width:100%; max-height:160px; object-fit:contain;'>";
    }

    function formatearMontoMiles(valor) {
        return normalizarMonto(valor).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function limpiarContenedorImpresionRecibo() {
        var contenedor = element('div_imprimir_recibo');
        if (contenedor) {
            contenedor.innerHTML = '';
        }
    }

    function activarFormatoMonto() {
        if (objeto('monto') === undefined) {
            return;
        }

        var inputMonto = objeto('monto');
        if (inputMonto.dataset.formatMiles === '1') {
            return;
        }

        inputMonto.dataset.formatMiles = '1';
        inputMonto.addEventListener('focus', function () {
            if (inputMonto.value !== '') {
                inputMonto.value = String(inputMonto.value).replace(/,/g, '');
            }
        });

        inputMonto.addEventListener('blur', function () {
            if (String(inputMonto.value).trim() === '') {
                return;
            }
            inputMonto.value = formatearMontoMiles(inputMonto.value);
        });
    }

    function limpiarPanelPagos() {
        element('iddespacho').value = '';
        hideElement('div_panel_pagos');
        if (objeto('div_contenido_pagos') !== undefined) {
            objeto('div_contenido_pagos').innerHTML = '';
        }
    }

    function inyectarFormularioPagoDesdePlantilla() {
        if (objeto('div_formulario_pago') === undefined || objeto('tpl_despacho_pago_formulario') === undefined) {
            return;
        }

        objeto('div_formulario_pago').innerHTML = objeto('tpl_despacho_pago_formulario').innerHTML;
        renderPreviewImagenDocumento('');
        despachoPagoManejarCambioTipoPago();
    }

    function capturarFormularioPago() {
        var form = {};
        var ids = [
            'iddespacho_pago',
            'imagen_documento_actual',
            'idtipo_pago',
            'idcliente_anticipo',
            'idtipo_documento',
            'estado',
            'fecha',
            'monto',
            'correlativo_documento',
            'banco',
            'referencia_pago',
            'observaciones'
        ];

        for (var i = 0; i < ids.length; i++) {
            if (objeto(ids[i]) !== undefined) {
                form[ids[i]] = elementValue(ids[i]);
            }
        }

        return form;
    }

    function restaurarFormularioPago(form) {
        if (!form) {
            activarFormatoMonto();
            return;
        }

        var ids = Object.keys(form);
        for (var i = 0; i < ids.length; i++) {
            if (objeto(ids[i]) !== undefined) {
                objeto(ids[i]).value = form[ids[i]];
            }
        }

        if (objeto('monto') !== undefined && String(objeto('monto').value).trim() !== '') {
            objeto('monto').value = formatearMontoMiles(objeto('monto').value);
        }

        activarFormatoMonto();
        despachoPagoManejarCambioTipoPago();
        actualizarModoFormulario();
    }

    function actualizarModoFormulario() {
        var esEdicion = objeto('iddespacho_pago') !== undefined && elementValue('iddespacho_pago') !== '';

        if (objeto('btn_guardar_documento') !== undefined) {
            objeto('btn_guardar_documento').innerHTML = esEdicion ? 'Guardar cambios' : 'Guardar documento';
            objeto('btn_guardar_documento').className = esEdicion
                ? 'btn btn-primary waves-effect waves-light m-r-10'
                : 'btn btn-success waves-effect waves-light m-r-10';
        }

        if (objeto('btn_imprimir_documento') !== undefined) {
            if (esEdicion) {
                showElement('btn_imprimir_documento');
            } else {
                hideElement('btn_imprimir_documento');
            }
        }
    }

    function cargarDocumentoParaEdicion(iddespacho_pago) {
        callback_obtener_despacho_pago = function (respuesta) {
            var row;
            var idclienteAnticipoSeleccionado = '';

            try {
                row = JSON.parse(respuesta);
            } catch (e) {
                notify_error('No se pudo cargar el documento para edicion.');
                return;
            }

            if (objeto('iddespacho_pago') !== undefined) objeto('iddespacho_pago').value = row.iddespacho_pago || '';
            if (objeto('imagen_documento_actual') !== undefined) objeto('imagen_documento_actual').value = row.imagen || '';
            if (objeto('idtipo_pago') !== undefined) objeto('idtipo_pago').value = row.idtipo_pago || '';
            idclienteAnticipoSeleccionado = row.idcliente_anticipo || '';
            if (objeto('idcliente_anticipo') !== undefined) objeto('idcliente_anticipo').value = idclienteAnticipoSeleccionado;
            if (objeto('idtipo_documento') !== undefined) objeto('idtipo_documento').value = row.idtipo_documento || '';
            if (objeto('estado') !== undefined) objeto('estado').value = row.estado || 'PROGRAMADO';
            if (objeto('fecha') !== undefined) objeto('fecha').value = row.fecha || '';
            if (objeto('monto') !== undefined) objeto('monto').value = formatearMontoMiles(row.monto || 0);
            if (objeto('correlativo_documento') !== undefined) objeto('correlativo_documento').value = row.correlativo_documento || '';
            if (objeto('banco') !== undefined) objeto('banco').value = row.banco || '';
            if (objeto('referencia_pago') !== undefined) objeto('referencia_pago').value = row.referencia_pago || '';
            if (objeto('observaciones') !== undefined) objeto('observaciones').value = row.observaciones || '';
            if (objeto('imagen_documento') !== undefined) objeto('imagen_documento').value = '';
            renderPreviewImagenDocumento(row.imagen || '');

            activarFormatoMonto();
            despachoPagoManejarCambioTipoPago(idclienteAnticipoSeleccionado);
            actualizarModoFormulario();
            notify_info('Documento cargado para edicion.');
        };

        upload_action('iddespacho_pago=' + iddespacho_pago, 'despacho_pago', 'obtener', callback_obtener_despacho_pago);
    }

    function refrescarPanelPagos(iddespacho, mantenerFormulario = true) {
        if (!iddespacho || isRefreshingPanel) {
            return;
        }

        var snapshot = mantenerFormulario ? capturarFormularioPago() : null;
        isRefreshingPanel = true;

        callback_panel_pagos = function () {
            showElement('div_panel_pagos');
            inyectarFormularioPagoDesdePlantilla();
            restaurarFormularioPago(snapshot);
            isRefreshingPanel = false;
        };

        download_div_content('iddespacho=' + iddespacho, 'despacho_pago', 'panel_pagos_despacho', 'div_contenido_pagos', callback_panel_pagos, true, function () {
            isRefreshingPanel = false;
        });
    }

    function refrescarPendientesCliente(idcliente) {
        if (!idcliente || isRefreshingPendientes) {
            return;
        }

        isRefreshingPendientes = true;
        download_div_content('idcliente=' + idcliente, 'despacho_pago', 'tabla_despachos_pendientes_cliente', 'div_despachos_pendientes', function () {
            isRefreshingPendientes = false;
        }, true, function () {
            isRefreshingPendientes = false;
        });
    }

    window.despachoPagoCargarPendientes = function () {
        var idcliente = elementValue('idcliente');
        limpiarPanelPagos();

        if (!idcliente) {
            objeto('div_despachos_pendientes').innerHTML = '<div class="text-muted">Seleccione un cliente para cargar la informacion.</div>';
            return;
        }

        refrescarPendientesCliente(idcliente);
    };

    window.despachoPagoSeleccionarDespacho = function (iddespacho) {
        if (!iddespacho) {
            notify_warning('Despacho invalido.');
            return;
        }

        element('iddespacho').value = iddespacho;
        hideElement('card_seleccion_cliente');
        hideElement('card_despachos_pendientes');
        showElement('div_btn_regresar');
        refrescarPanelPagos(iddespacho, false);
    };

    window.despachoPagoRegresar = function () {
        limpiarPanelPagos();
        hideElement('div_btn_regresar');
        showElement('card_seleccion_cliente');
        showElement('card_despachos_pendientes');
    };

    window.despachoPagoGuardar = function () {
        var iddespacho = elementValue('iddespacho');
        var iddespacho_pago = elementValue('iddespacho_pago');
        var idtipo_pago = elementValue('idtipo_pago');
        var idtipo_documento = elementValue('idtipo_documento');
        var estado = elementValue('estado');
        var fecha = elementValue('fecha');
        var correlativo_documento = elementValue('correlativo_documento');
        var monto = normalizarMonto(elementValue('monto'));
        var idcliente_anticipo = elementValue('idcliente_anticipo');
        var inputImagen = objeto('imagen_documento');
        var tieneImagenNueva = !!(inputImagen && inputImagen.files && inputImagen.files.length > 0);

        if (!iddespacho) {
            notify_warning('Debe seleccionar un despacho.');
            return false;
        }

        if (!idtipo_pago || !idtipo_documento || !estado || !fecha || !correlativo_documento) {
            notify_warning('Complete todos los campos obligatorios.');
            return false;
        }

        if (idtipo_pago === '10' && !idcliente_anticipo) {
            notify_warning('Debe seleccionar un anticipo para este tipo de pago.');
            return false;
        }

        if (isNaN(monto) || monto <= 0) {
            notify_warning('El monto debe ser mayor a cero.');
            return false;
        }

        if (objeto('monto') !== undefined) {
            objeto('monto').value = monto.toFixed(2);
        }

        var fields = 'iddespacho,iddespacho_pago,idtipo_pago,idcliente_anticipo,idtipo_documento,estado,fecha,monto,correlativo_documento,banco,referencia_pago,observaciones';

        callback_guardar_despacho_pago = function (respuesta) {
            if ((respuesta + '') === 'editado') {
                notify_success('Documento actualizado correctamente.');
            } else {
                notify_success('Pago registrado correctamente.');
            }

            window.despachoPagoLimpiarFormulario();
            refrescarPanelPagos(iddespacho, false);

            if (elementValue('idcliente')) {
                refrescarPendientesCliente(elementValue('idcliente'));
            }
        };

        if (tieneImagenNueva) {
            upload_file(fields, 'imagen_documento', 'despacho_pago', 'guardar', callback_guardar_despacho_pago);
        } else {
            upload_action(fields, 'despacho_pago', 'guardar', callback_guardar_despacho_pago);
        }
        return false;
    };

    window.despachoPagoEjecutarRegistro = function (iddespacho_pago) {
        var iddespacho = elementValue('iddespacho');

        if (!iddespacho_pago) {
            notify_warning('Documento de pago invalido.');
            return;
        }

        if (!confirm('¿Confirma ejecutar este documento programado?')) {
            return;
        }

        callback_ejecutar_despacho_pago = function () {
            notify_success('Documento ejecutado correctamente.');

            if (iddespacho) {
                refrescarPanelPagos(iddespacho, false);
            }

            if (elementValue('idcliente')) {
                refrescarPendientesCliente(elementValue('idcliente'));
            }
        };

        upload_action('iddespacho_pago=' + iddespacho_pago, 'despacho_pago', 'ejecutar', callback_ejecutar_despacho_pago);
    };

    window.despachoPagoEditarRegistro = function (iddespacho_pago) {
        if (!iddespacho_pago) {
            notify_warning('Documento de pago invalido.');
            return;
        }

        cargarDocumentoParaEdicion(iddespacho_pago);
    };

    window.despachoPagoEliminarRegistro = function (iddespacho_pago) {
        var iddespacho = elementValue('iddespacho');

        if (!iddespacho_pago) {
            notify_warning('Documento de pago invalido.');
            return;
        }

        if (!confirm('¿Confirma eliminar este documento de pago?')) {
            return;
        }

        callback_eliminar_despacho_pago = function () {
            notify_success('Documento eliminado correctamente.');

            if (elementValue('iddespacho_pago') === String(iddespacho_pago)) {
                window.despachoPagoLimpiarFormulario();
            }

            if (iddespacho) {
                refrescarPanelPagos(iddespacho, false);
            }

            if (elementValue('idcliente')) {
                refrescarPendientesCliente(elementValue('idcliente'));
            }
        };

        upload_action('iddespacho_pago=' + iddespacho_pago, 'despacho_pago', 'eliminar', callback_eliminar_despacho_pago);
    };

    window.despachoPagoImprimirRegistro = function (iddespacho_pago_param) {
        var iddespacho_pago = iddespacho_pago_param || elementValue('iddespacho_pago');

        if (!iddespacho_pago) {
            notify_warning('Debe cargar un documento para imprimir.');
            return;
        }

        callback_imprimir_despacho_pago = function () {
            if (objeto('div_imprimir_recibo') === undefined) {
                notify_error('No se encontró el contenedor de impresión.');
                return;
            }

            var contenedor = objeto('div_imprimir_recibo');
            if (!contenedor || !contenedor.innerHTML.trim()) {
                notify_warning('No se pudo generar la vista de impresion.');
                limpiarContenedorImpresionRecibo();
                return;
            }

            if (!contenedor.querySelector('#contenedor_documentos_impresion')) {
                notify_warning('No se encontró el contenedor de documentos para imprimir.');
                limpiarContenedorImpresionRecibo();
                return;
            }

            var imagenes = contenedor.querySelectorAll('#contenedor_documentos_impresion img');
            if (!imagenes || imagenes.length === 0) {
                notify_warning('No hay imagenes disponibles para imprimir.');
                limpiarContenedorImpresionRecibo();
                return;
            }

            for (var i = 0; i < imagenes.length; i++) {
                var srcOriginal = imagenes[i].getAttribute('src');
                if (!srcOriginal) {
                    continue;
                }

                try {
                    var srcAbsoluto = new URL(srcOriginal, window.location.href).href;
                    imagenes[i].setAttribute('src', srcAbsoluto);
                } catch (e) {
                    // Si una imagen no puede normalizarse, se deja con su ruta actual.
                }
            }

            print_div('div_imprimir_recibo');
            limpiarContenedorImpresionRecibo();
        };

        download_div_content('iddespacho_pago=' + iddespacho_pago, 'despacho_pago', 'imprimir', 'div_imprimir_recibo', callback_imprimir_despacho_pago);
    };

    window.despachoPagoLimpiarFormulario = function () {
        if (objeto('iddespacho_pago') !== undefined) objeto('iddespacho_pago').value = '';
        if (objeto('imagen_documento_actual') !== undefined) objeto('imagen_documento_actual').value = '';
        if (objeto('idtipo_pago') !== undefined) objeto('idtipo_pago').value = '';
        if (objeto('idtipo_documento') !== undefined) objeto('idtipo_documento').value = '';
        if (objeto('monto') !== undefined) objeto('monto').value = '';
        if (objeto('correlativo_documento') !== undefined) objeto('correlativo_documento').value = '';
        if (objeto('banco') !== undefined) objeto('banco').value = '';
        if (objeto('referencia_pago') !== undefined) objeto('referencia_pago').value = '';
        if (objeto('observaciones') !== undefined) objeto('observaciones').value = '';
        if (objeto('imagen_documento') !== undefined) objeto('imagen_documento').value = '';
        if (objeto('estado') !== undefined) objeto('estado').value = 'PROGRAMADO';
        if (objeto('fecha') !== undefined) objeto('fecha').value = (new Date()).toISOString().slice(0, 10);
        if (objeto('btn_imprimir_documento') !== undefined) hideElement('btn_imprimir_documento');
        if (objeto('idcliente_anticipo') !== undefined) objeto('idcliente_anticipo').value = '';
        renderPreviewImagenDocumento('');
        activarFormatoMonto();
        actualizarModoFormulario();
        despachoPagoManejarCambioTipoPago();
    };

    window.despachoPagoPreviewImagenDocumento = function (input) {
        if (!input || !input.files || input.files.length === 0) {
            renderPreviewImagenDocumento(elementValue('imagen_documento_actual'));
            return;
        }

        var file = input.files[0];
        var tiposPermitidos = ['image/jpeg', 'image/png'];
        if (tiposPermitidos.indexOf(file.type) === -1) {
            notify_warning('Tipo de archivo no permitido. Debe cargar imagenes en formato JPEG, JPG o PNG.');
            input.value = '';
            renderPreviewImagenDocumento(elementValue('imagen_documento_actual'));
            return;
        }

        var lector = new FileReader();
        lector.onload = function (e) {
            var contenedor = objeto('preview_imagen_documento');
            if (!contenedor) {
                return;
            }

            contenedor.classList.remove('text-muted');
            contenedor.innerHTML = "<img src='" + e.target.result + "' alt='Imagen documento' style='max-width:100%; max-height:160px; object-fit:contain;'>";
        };
        lector.readAsDataURL(file);
    };

    window.despachoPagoManejarCambioTipoPago = function (idcliente_anticipo_seleccionado) {
        var idtipo_pago = elementValue('idtipo_pago');
        var selectAnticipo = objeto('idcliente_anticipo');

        if (!selectAnticipo) {
            return;
        }

        // ID 10 es ANTICIPO según el INSERT proporcionado
        if (idtipo_pago === '10') {
            selectAnticipo.removeAttribute('disabled');
            selectAnticipo.setAttribute('required', 'required');
            despachoPagoCargarAnticiposCliente(idcliente_anticipo_seleccionado || selectAnticipo.value || '');
        } else {
            selectAnticipo.setAttribute('disabled', 'disabled');
            selectAnticipo.removeAttribute('required');
            selectAnticipo.value = '';
        }
    };

    window.despachoPagoCargarAnticiposCliente = function (idcliente_anticipo_seleccionado) {
        var idcliente = elementValue('idcliente');
        var selectAnticipo = objeto('idcliente_anticipo');

        if (!idcliente || !selectAnticipo) {
            return;
        }

        callback_cargar_anticipos = function (respuesta) {
            selectAnticipo.innerHTML = '<option value="">-- Seleccione un anticipo --</option>' + respuesta;

            if (idcliente_anticipo_seleccionado) {
                selectAnticipo.value = String(idcliente_anticipo_seleccionado);
            }
        };

        upload_action('idcliente=' + idcliente, 'despacho_pago', 'obtener_anticipos_cliente', callback_cargar_anticipos);
    };

})();

