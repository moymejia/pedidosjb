<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/utils.php';
require_once '../entities/cliente.php';
require_once '../entities/tipo_pago.php';
require_once '../entities/tipo_documento.php';
require_once '../entities/cliente_anticipo.php';
require_once '../entities/cliente_anticipo_aplicacion.php';

class despacho_pago extends table
{
    use utils;

    private $last_error = '';
    private $ACCIONES = [];

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'despacho_pago');

        $this->ACCIONES['opcion_despacho_pago']    = 'Opcion_despacho_pago';
        $this->ACCIONES['consultar_despacho_pago'] = 'Consultar_despacho_pago';
        $this->ACCIONES['crear_despacho_pago']     = 'Crear_despacho_pago';
        $this->ACCIONES['modificar_despacho_pago'] = 'Modificar_despacho_pago';
        $this->ACCIONES['eliminar_despacho_pago']  = 'Eliminar_despacho_pago';
        $this->ACCIONES['ejecutar_despacho_pago']  = 'Ejecutar_despacho_pago';
        $this->ACCIONES['imprimir_despacho_pago']  = 'Imprimir_despacho_pago';

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'guardar') {
                if ($resultado = $this->guardar_despacho_pago($PARAMETROS)) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'ejecutar') {
                if (table::validate_parameter_existence(['iddespacho_pago'], $PARAMETROS, false)) {
                    if ($this->ejecutar_despacho_pago($PARAMETROS['iddespacho_pago'])) {
                        self::end_success('ejecutado');
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error('Datos incompletos.');
                }
            }

            if ($PARAMETROS['operacion'] == 'eliminar') {
                if (table::validate_parameter_existence(['iddespacho_pago'], $PARAMETROS, false)) {
                    if ($this->eliminar_despacho_pago($PARAMETROS['iddespacho_pago'])) {
                        self::end_success('eliminado');
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error('Datos incompletos.');
                }
            }

            if ($PARAMETROS['operacion'] == 'obtener') {
                if (table::validate_parameter_existence(['iddespacho_pago'], $PARAMETROS, false)) {
                    if ($resultado = $this->obtener_despacho_pago($PARAMETROS['iddespacho_pago'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error('Datos incompletos.');
                }
            }

            if ($PARAMETROS['operacion'] == 'tabla_despachos_pendientes_cliente') {
                if (table::validate_parameter_existence(['idcliente'], $PARAMETROS, false)) {
                    $mostrar_sin_saldo = isset($PARAMETROS['mostrar_sin_saldo']) ? $PARAMETROS['mostrar_sin_saldo'] : '';
                    if ($resultado = $this->tabla_despachos_pendientes_cliente($PARAMETROS['idcliente'], $mostrar_sin_saldo)) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error('Datos incompletos.');
                }
            }

            if ($PARAMETROS['operacion'] == 'panel_pagos_despacho') {
                if (table::validate_parameter_existence(['iddespacho'], $PARAMETROS, false)) {
                    if ($resultado = $this->panel_pagos_despacho($PARAMETROS['iddespacho'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error('Datos incompletos.');
                }
            }

            if ($PARAMETROS['operacion'] == 'imprimir') {
                if (table::validate_parameter_existence(['iddespacho_pago'], $PARAMETROS, false)) {
                    if ($resultado = $this->imprimir_documento($PARAMETROS['iddespacho_pago'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error('Datos incompletos.');
                }
            }

            if ($PARAMETROS['operacion'] == 'obtener_anticipos_cliente') {
                if (table::validate_parameter_existence(['idcliente'], $PARAMETROS, false)) {
                    $_CLIENTE_ANTICIPO = new cliente_anticipo();
                    $resultado = $_CLIENTE_ANTICIPO->obtener_anticipos_cliente($PARAMETROS['idcliente']);
                    self::end_success($resultado);
                } else {
                    self::end_error('Datos incompletos.');
                }
            }

        }
    }

    public function cargar_opcion()
    {
        $security = new security($this->ACCIONES['opcion_despacho_pago']);
        $security->registrar_bitacora($this->ACCIONES['opcion_despacho_pago'], 'cargar_opcion');

        $DATA = [];
        $_CLIENTE        = new cliente();
        $_TIPO_PAGO      = new tipo_pago();
        $_TIPO_DOCUMENTO = new tipo_documento();

        $DATA['options_clientes']       = $_CLIENTE->option_activas();
        $DATA['options_tipos_pago']     = $_TIPO_PAGO->option_activas();
        $DATA['options_tipos_documento'] = $_TIPO_DOCUMENTO->option_activas();
        $DATA['fecha_hoy']              = date('Y-m-d');

        $html = new html('despacho_pago', $DATA);
        return $html->get_html();
    }

    public function tabla_despachos_pendientes_cliente($idcliente, $mostrar_sin_saldo = '')
    {
        $security = new security($this->ACCIONES['consultar_despacho_pago']);

        $idcliente = trim($idcliente . '');
        $mostrar_sin_saldo = trim($mostrar_sin_saldo . '') === '1';
        $filtro_saldo = $mostrar_sin_saldo ? '' : ' AND saldo_pendiente > 0';

        $security->registrar_bitacora($this->ACCIONES['consultar_despacho_pago'], $idcliente, $mostrar_sin_saldo ? 'TODOS' : 'CON SALDO');

        $result = mysql::getresult("SELECT iddespacho, nopedido, numero_factura, fecha, monto_despacho, total_pagado_ejecutado, saldo_pendiente, total_programado_neto
            FROM view_despacho_pago_resumen
            WHERE idcliente = '$idcliente'
                $filtro_saldo
            ORDER BY iddespacho DESC");

        if (! $result) {
            $this->last_error = 'No se pudo cargar la lista de despachos pendientes.';
            utils::report_error(bd_error, $idcliente, $this->last_error);
            return false;
        }

        $tabla = "<table id='tabla_datos' class='display nowrap table table-hover table-bordered datatable' cellspacing='0' width='100%'>
            <thead>
                <tr>
                    <th>Acciones</th>
                    <th>No. factura</th>
                    <th>No. pedido</th>
                    <th>Fecha despacho</th>
                    <th>Monto despacho</th>
                    <th>Pagado ejecutado</th>
                    <th>Programado neto</th>
                    <th>Saldo pendiente</th>
                </tr>
            </thead>
            <tbody>";

        while ($row = mysql::getrowresult($result)) {
            $numero_factura = trim($row['numero_factura'] . '');
            $tabla .= "<tr>
                <td><button type='button' class='btn btn-sm btn-primary waves-effect waves-light' onclick='despachoPagoSeleccionarDespacho(" . (int)$row['iddespacho'] . ")'>Seleccionar</button></td>
                <td>" . (($numero_factura !== '') ? $numero_factura : ('#' . (int)$row['iddespacho'])) . "</td>
                <td>" . $row['nopedido'] . "</td>
                <td>" . $row['fecha'] . "</td>
                <td class='text-right'>Q " . number_format((float)$row['monto_despacho'], 2) . "</td>
                <td class='text-right'>Q " . number_format((float)$row['total_pagado_ejecutado'], 2) . "</td>
                <td class='text-right'>Q " . number_format((float)$row['total_programado_neto'], 2) . "</td>
                <td class='text-right'><strong>Q " . number_format((float)$row['saldo_pendiente'], 2) . "</strong></td>
            </tr>";
        }

        $tabla .= '</tbody></table>';
        return $tabla;
    }

    public function panel_pagos_despacho($iddespacho)
    {
        $security = new security($this->ACCIONES['consultar_despacho_pago']);
        $security->registrar_bitacora($this->ACCIONES['consultar_despacho_pago'], $iddespacho);

        $iddespacho = trim($iddespacho . '');

        $resumen = mysql::getrow("SELECT iddespacho, nopedido, numero_factura, cliente, monto_despacho, total_pagado_ejecutado, total_programado_neto, saldo_pendiente
            FROM view_despacho_pago_resumen
            WHERE iddespacho = '$iddespacho'
            LIMIT 1");

        if (! $resumen) {
            $this->last_error = 'Despacho no encontrado.';
            utils::report_error(validation_error, $iddespacho, $this->last_error);
            return false;
        }

        $numero_factura = trim($resumen['numero_factura'] . '');
        if ($numero_factura == '') {
            $numero_factura = '#' . (int)$resumen['iddespacho'];
        }

        $html = "
            <div class='row mb-3'>
                <div class='col-md-12'>
                    <h5>Factura " . $numero_factura . " - Pedido " . $resumen['nopedido'] . " &nbsp; Cliente: " . $resumen['cliente'] . "</h5>
                </div>
            </div>
            <div class='row mb-3'>
                <div class='col-md-3'><div class='border rounded p-2 bg-dark text-white'><div class='small text-light'>Monto despacho</div><strong>Q " . number_format((float)$resumen['monto_despacho'], 2) . "</strong></div></div>
                <div class='col-md-3'><div class='border rounded p-2 bg-dark text-white'><div class='small text-light'>Pagado ejecutado</div><strong>Q " . number_format((float)$resumen['total_pagado_ejecutado'], 2) . "</strong></div></div>
                <div class='col-md-3'><div class='border rounded p-2 bg-dark text-white'><div class='small text-light'>Programado neto</div><strong>Q " . number_format((float)$resumen['total_programado_neto'], 2) . "</strong></div></div>
                <div class='col-md-3'><div class='border rounded p-2 bg-dark text-white'><div class='small text-light'>Saldo pendiente</div><strong>Q " . number_format((float)$resumen['saldo_pendiente'], 2) . "</strong></div></div>
            </div>";

        $html .= "<div id='div_formulario_pago'></div>";

        $historial_pagos = $this->tabla_historial_pagos($iddespacho);
        if (! $historial_pagos) {
            return false;
        }
        $html .= $historial_pagos;

        $resumen_tipo_pago = $this->tabla_resumen_tipo_pago($iddespacho);
        if (! $resumen_tipo_pago) {
            return false;
        }
        $html .= $resumen_tipo_pago;

        return $html;
    }

    private function tabla_resumen_tipo_pago($iddespacho)
    {
        $result = mysql::getresult("SELECT tipo_pago, total_neto FROM view_despacho_pago_tipo_pago WHERE iddespacho = '$iddespacho' ORDER BY tipo_pago ASC");

        if (! $result) {
            $this->last_error = 'Error al cargar resumen por tipo de pago.';
            utils::report_error(bd_error, $iddespacho, $this->last_error);
            return false;
        }

        $tabla = "
            <div class='card border mb-3'>
                <div class='card-header bg-info text-white'><strong>Saldo por tipo de pago</strong></div>
                <div class='card-body p-0'>
                    <div class='table-responsive'>
                        <table class='table table-bordered table-sm m-b-0'>
                            <thead>
                                <tr>
                                    <th>Tipo de pago</th>
                                    <th class='text-right'>Total neto</th>
                                </tr>
                            </thead>
                            <tbody>";

        $hay = false;
        while ($row = mysql::getrowresult($result)) {
            $hay = true;
            $tabla .= "<tr>
                <td>" . $row['tipo_pago'] . "</td>
                <td class='text-right'>Q " . number_format((float)$row['total_neto'], 2) . "</td>
            </tr>";
        }

        if (! $hay) {
            $tabla .= "<tr><td colspan='2' class='text-center text-muted'>Sin documentos registrados.</td></tr>";
        }

        $tabla .= "
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>";

        return $tabla;
    }

    private function tabla_historial_pagos($iddespacho)
    {
        $result = mysql::getresult("SELECT v.iddespacho_pago, v.fecha, v.tipo_pago, v.tipo_documento, v.estado, v.signo, v.monto,
                v.correlativo_documento, v.banco, v.referencia_pago, v.observaciones, v.usuario_creacion, dp.imagen, dp.numero_recuperado
            FROM view_despacho_pago_detalle v
            LEFT JOIN despacho_pago dp ON dp.iddespacho_pago = v.iddespacho_pago
            WHERE v.iddespacho = '$iddespacho'
            ORDER BY v.fecha DESC, v.iddespacho_pago DESC");

        if (! $result) {
            $this->last_error = 'Error al cargar historial de pagos.';
            utils::report_error(bd_error, $iddespacho, $this->last_error);
            return false;
        }

        $tabla = "
            <div class='card border mb-3'>
                <div class='card-header bg-info text-white'><strong>Historial de documentos</strong></div>
                <div class='card-body p-0'>
                    <div class='table-responsive'>
                        <table class='table table-bordered table-sm m-b-0'>
                            <thead>
                                <tr>
                                    <th>Acciones</th>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Tipo documento</th>
                                    <th>Correlativo</th>
                                    <th>Tipo pago</th>
                                    <th>Banco</th>
                                    <th>Referencia</th>
                                    <th>Monto</th>
                                    <th>Observaciones</th>
                                    <th>Estado</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody>";

        $hay = false;
        while ($row = mysql::getrowresult($result)) {
            $hay = true;
            $acciones = "<span class='text-muted'>-</span>";
            $estado_actual = strtoupper(trim($row['estado'] . ''));

            $acciones = "<button type='button' class='btn btn-sm btn-info waves-effect waves-light m-r-5' onclick='despachoPagoEditarRegistro(" . (int)$row['iddespacho_pago'] . ")'>Editar</button>";

            if ($estado_actual === 'PROGRAMADO') {
                $acciones .= "<button type='button' class='btn btn-sm btn-warning waves-effect waves-light m-r-5' onclick='despachoPagoEjecutarRegistro(" . (int)$row['iddespacho_pago'] . ")'>Ejecutar</button>";
            }

            $acciones .= "<button type='button' class='btn btn-sm btn-danger waves-effect waves-light' onclick='despachoPagoEliminarRegistro(" . (int)$row['iddespacho_pago'] . ")'>Eliminar</button>";

            $tabla .= "<tr>
                <td>" . $acciones . "</td>
                <td>" . (int)$row['iddespacho_pago'] . "</td>
                <td>" . $row['fecha'] . "</td>
                <td>" . $row['tipo_documento'] . "</td>
                <td>" . $row['correlativo_documento'] . "</td>
                <td>" . $row['tipo_pago'] . "</td>
                <td>" . $row['banco'] . "</td>
                <td>" . $row['referencia_pago'] . "</td>
                <td class='text-right'>Q " . number_format((float)$row['monto'], 2) . "</td>
                <td>" . $row['observaciones'] . "</td>
                <td>" . $estado_actual . "</td>
                <td>" . $row['usuario_creacion'] . "</td>
            </tr>";
        }

        if (! $hay) {
            $tabla .= "<tr><td colspan='12' class='text-center text-muted'>Sin documentos registrados.</td></tr>";
        }

        $tabla .= "
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>";

        return $tabla;
    }

    public function guardar_despacho_pago($PARAMETROS)
    {
        if (! table::validate_parameter_existence(['iddespacho', 'fecha', 'idtipo_pago', 'idtipo_documento', 'monto', 'estado', 'correlativo_documento'], $PARAMETROS)) {
            $this->last_error = 'Datos incompletos.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        $estado = strtoupper(trim($PARAMETROS['estado']));
        if ($estado != 'PROGRAMADO' && $estado != 'EJECUTADO' && $estado != 'ANULADO') {
            $this->last_error = 'El estado debe ser PROGRAMADO, EJECUTADO o ANULADO.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        $monto = (float)$PARAMETROS['monto'];
        if ($estado === 'ANULADO') {
            $monto = 0;
        }

        if ($estado !== 'ANULADO' && $monto <= 0) {
            $this->last_error = 'El monto debe ser mayor a cero.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        $descuento = isset($PARAMETROS['descuento']) ? (float)$PARAMETROS['descuento'] : 0;
        $devolucion = isset($PARAMETROS['devolucion']) ? (float)$PARAMETROS['devolucion'] : 0;

        if ($descuento < 0 || $devolucion < 0) {
            $this->last_error = 'Descuento y devolucion deben ser mayores o iguales a cero.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        if ($estado === 'ANULADO') {
            $descuento = 0;
            $devolucion = 0;
        }

        if (trim($PARAMETROS['correlativo_documento']) == '') {
            $this->last_error = 'Debe ingresar correlativo del documento.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        $iddespacho_pago = isset($PARAMETROS['iddespacho_pago']) ? trim($PARAMETROS['iddespacho_pago']) : '';
        if (! $this->validar_correlativo_anulado_reutilizable($PARAMETROS['correlativo_documento'], $iddespacho_pago)) {
            return false;
        }

        $idtipo_pago = trim($PARAMETROS['idtipo_pago'] . '');
        $idtipo_documento = trim($PARAMETROS['idtipo_documento'] . '');
        $tipo_documento = mysql::getvalue("SELECT nombre FROM tipo_documento WHERE idtipo_documento = '" . addslashes($idtipo_documento) . "' LIMIT 1", 'nombre');
        $tipo_documento_normalizado = strtoupper(strtr(trim($tipo_documento . ''), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U']));
        $es_recuperacion = $tipo_documento_normalizado === 'RECUPERACION';
        $numero_recuperado = isset($PARAMETROS['numero_recuperado']) ? trim($PARAMETROS['numero_recuperado'] . '') : '';

        if ($es_recuperacion && $numero_recuperado == '') {
            $this->last_error = 'Debe ingresar el No. Recuperado.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        if (strlen($numero_recuperado) > 50) {
            $this->last_error = 'El No. Recuperado no puede exceder 50 caracteres.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        $iddespacho_pago_recupera = 'NULL';
        if ($es_recuperacion) {
            $row_cheque_principal = $this->obtener_cheque_principal_recuperacion($numero_recuperado);
            if (!$row_cheque_principal) {
                $this->last_error = 'El No. Recuperado no existe. No se puede registrar una recuperación.';
                utils::report_error(validation_error, ['numero_recuperado' => $numero_recuperado], $this->last_error);
                return false;
            }

            $total_recuperado = $this->obtener_total_recuperado_cheque_principal($row_cheque_principal['iddespacho_pago'], $numero_recuperado, $iddespacho_pago);
            $saldo_disponible = round((float)$row_cheque_principal['monto'] - $total_recuperado, 2);

            if ($monto > $saldo_disponible) {
                $this->last_error = 'El monto de la recuperación supera el saldo disponible del cheque principal.';
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);
                return false;
            }

            $iddespacho_pago_recupera = $row_cheque_principal['iddespacho_pago'];
        }

        // Si es tipo de pago ANTICIPO (10), validar que se proporcione idcliente_anticipo
        if ($idtipo_pago === '10' && $estado !== 'ANULADO') {
            if (!isset($PARAMETROS['idcliente_anticipo']) || trim($PARAMETROS['idcliente_anticipo']) == '') {
                $this->last_error = 'Debe seleccionar un anticipo del cliente.';
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);
                return false;
            }

            $idcliente_anticipo = trim($PARAMETROS['idcliente_anticipo'] . '');
            $_CLIENTE_ANTICIPO = new cliente_anticipo();
            $saldo_disponible = $_CLIENTE_ANTICIPO->obtener_saldo_disponible($idcliente_anticipo);

            if ($saldo_disponible === false) {
                $this->last_error = $_CLIENTE_ANTICIPO->last_error;
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);
                return false;
            }

            if ($monto > $saldo_disponible) {
                $this->last_error = 'El monto (' . $monto . ') supera el saldo disponible del anticipo (' . $saldo_disponible . ').';
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);
                return false;
            }
        }

        $iddespacho = trim($PARAMETROS['iddespacho'] . '');
        $DATOS = [];
        $DATOS['iddespacho']        = $iddespacho;
        $DATOS['fecha']             = $PARAMETROS['fecha'];
        $DATOS['idtipo_pago']       = $idtipo_pago;
        $DATOS['idtipo_documento']  = $idtipo_documento;
        $DATOS['monto']             = number_format($monto, 2, '.', '');
        $DATOS['estado']            = $estado;
        $DATOS['correlativo_documento'] = $PARAMETROS['correlativo_documento'];
        $DATOS['numero_recuperado'] = $es_recuperacion ? $numero_recuperado : 'NULL';
        $DATOS['iddespacho_pago_recupera'] = $iddespacho_pago_recupera;

        $idcliente_anticipo          = isset($PARAMETROS['idcliente_anticipo']) ? trim($PARAMETROS['idcliente_anticipo']) : '';
        $DATOS['idcliente_anticipo'] = $idcliente_anticipo == '' ? 'NULL' : $idcliente_anticipo;

        $banco                        = isset($PARAMETROS['banco']) ? $PARAMETROS['banco'] : '';
        $referencia_pago              = isset($PARAMETROS['referencia_pago']) ? $PARAMETROS['referencia_pago'] : '';
        $observaciones                = isset($PARAMETROS['observaciones']) ? $PARAMETROS['observaciones'] : '';
        $DATOS['banco']               = trim($banco) == '' ? 'NULL' : $banco;
        $DATOS['referencia_pago']     = trim($referencia_pago) == '' ? 'NULL' : $referencia_pago;
        $DATOS['observaciones']       = trim($observaciones) == '' ? 'NULL' : $observaciones;

        $imagen_nueva = $this->guardar_imagen_documento($iddespacho, $PARAMETROS['correlativo_documento']);
        if ($imagen_nueva === false) {
            return false;
        }

        if ($iddespacho_pago == '') {
            $security = new security($this->ACCIONES['crear_despacho_pago']);
            $idforma_pago_default = $this->obtener_idforma_pago_default();
            if (! $idforma_pago_default) {
                return false;
            }

            $DATOS['idforma_pago'] = $idforma_pago_default;
            $DATOS['usuario_creacion'] = $security->get_actual_user();
            $DATOS['imagen'] = $imagen_nueva ? $imagen_nueva : 'NULL';

            if (table::insert_record($DATOS)) {
                $iddespacho_pago_nuevo = mysql::last_id();

                // Si es ANTICIPO, aplicar el anticipo
                if ($idtipo_pago === '10' && $estado !== 'ANULADO' && $monto > 0) {
                    if (!$this->aplicar_y_registrar_anticipo($iddespacho_pago_nuevo, $idcliente_anticipo, $iddespacho, $monto, $PARAMETROS)) {
                        // Si falla la aplicación del anticipo, revertir el insert
                        mysql::put("DELETE FROM despacho_pago WHERE iddespacho_pago = '$iddespacho_pago_nuevo'");
                        return false;
                    }
                }

                if (! $this->registrar_movimientos_ajuste($DATOS, $descuento, $devolucion, $idforma_pago_default, $security->get_actual_user())) {
                    if ($idtipo_pago === '10' && $estado !== 'ANULADO' && $monto > 0) {
                        $_CLIENTE_ANTICIPO = new cliente_anticipo();
                        $_CLIENTE_ANTICIPO_APLICACION = new cliente_anticipo_aplicacion();

                        $_CLIENTE_ANTICIPO->revertir_anticipo($idcliente_anticipo, $monto);

                        $row_aplicacion = $_CLIENTE_ANTICIPO_APLICACION->obtener_aplicacion_por_despacho($iddespacho, $idcliente_anticipo);
                        if ($row_aplicacion) {
                            $_CLIENTE_ANTICIPO_APLICACION->cancelar_aplicacion($row_aplicacion['idcliente_anticipo_aplicacion']);
                        }
                    }

                    mysql::put("DELETE FROM despacho_pago WHERE iddespacho_pago = '$iddespacho_pago_nuevo'");
                    return false;
                }

                $security->registrar_bitacora($this->ACCIONES['crear_despacho_pago'], $iddespacho_pago_nuevo, $iddespacho, $DATOS['monto']);
                return 'nuevo';
            }

            $this->last_error = 'Error al guardar el documento de pago.';
            utils::report_error(bd_error, $PARAMETROS, $this->last_error);
            return false;
        }

        $iddespacho_pago = trim($iddespacho_pago . '');

        $row_actual = mysql::getrow("SELECT iddespacho_pago, iddespacho, estado, idtipo_pago, idcliente_anticipo, monto FROM despacho_pago WHERE iddespacho_pago = '$iddespacho_pago' LIMIT 1");

        if (! $row_actual) {
            $this->last_error = 'El documento de pago indicado no existe.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        $security = new security($this->ACCIONES['modificar_despacho_pago']);
        $DATOS['iddespacho_pago']        = $iddespacho_pago;
        $DATOS['iddespacho']             = $row_actual['iddespacho'];
        $DATOS['idcliente_anticipo']     = $idcliente_anticipo == '' ? 'NULL' : $idcliente_anticipo;
        $DATOS['usuario_modificacion']   = $security->get_actual_user();
        $DATOS['fecha_modificacion']     = date('Y-m-d H:i:s');
        $imagen_actual = mysql::getvalue("SELECT imagen FROM despacho_pago WHERE iddespacho_pago = '$iddespacho_pago' LIMIT 1", 'imagen');
        $DATOS['imagen'] = $imagen_nueva ? $imagen_nueva : ($imagen_actual != '' ? $imagen_actual : 'NULL');

        if ($this->update_record($DATOS, ['iddespacho_pago'])) {
            // Manejar cambios en anticipo
            $idtipo_pago_anterior = trim($row_actual['idtipo_pago'] . '');
            $idcliente_anticipo_anterior = trim($row_actual['idcliente_anticipo'] . '');
            $monto_anterior = (float)$row_actual['monto'];
            $estado_anterior = strtoupper(trim($row_actual['estado'] . ''));

            $era_anticipo_aplicable = ($idtipo_pago_anterior === '10' && $estado_anterior !== 'ANULADO' && $monto_anterior > 0);
            $es_anticipo_aplicable = ($idtipo_pago === '10' && $estado !== 'ANULADO' && $monto > 0);

            // Si era ANTICIPO antes y sigue siendo ANTICIPO
            if ($era_anticipo_aplicable && $es_anticipo_aplicable) {
                $monto_actual = $monto;
                
                // Si el monto cambió, actualizar la aplicación
                if ($monto_anterior != $monto_actual) {
                    $this->actualizar_aplicacion_anticipo($iddespacho_pago, $row_actual['iddespacho'], $idcliente_anticipo_anterior, $idcliente_anticipo, $monto_anterior, $monto_actual, $PARAMETROS);
                }
                // Si el anticipo cambió pero el monto es igual
                elseif ($idcliente_anticipo_anterior !== $idcliente_anticipo) {
                    $_CLIENTE_ANTICIPO = new cliente_anticipo();
                    $_CLIENTE_ANTICIPO_APLICACION = new cliente_anticipo_aplicacion();
                    
                    // Revertir el anterior
                    $_CLIENTE_ANTICIPO->revertir_anticipo($idcliente_anticipo_anterior, $monto_anterior);
                    
                    // Aplicar el nuevo
                    $_CLIENTE_ANTICIPO->aplicar_anticipo($idcliente_anticipo, $monto_actual);
                    
                    // Actualizar registro de aplicación
                    $row_aplicacion = $_CLIENTE_ANTICIPO_APLICACION->obtener_aplicacion_por_despacho($row_actual['iddespacho'], $idcliente_anticipo_anterior);
                    if ($row_aplicacion) {
                        $_CLIENTE_ANTICIPO_APLICACION->actualizar_aplicacion(
                            $row_aplicacion['idcliente_anticipo_aplicacion'],
                            $idcliente_anticipo,
                            $monto_actual,
                            isset($PARAMETROS['observaciones']) ? $PARAMETROS['observaciones'] : null
                        );
                    }
                }
            }
            // Si NO era ANTICIPO y ahora SÍ es ANTICIPO
            elseif (!$era_anticipo_aplicable && $es_anticipo_aplicable) {
                $this->aplicar_y_registrar_anticipo($iddespacho_pago, $idcliente_anticipo, $iddespacho, $monto, $PARAMETROS);
            }
            // Si ERA ANTICIPO y ahora NO es ANTICIPO
            elseif ($era_anticipo_aplicable && !$es_anticipo_aplicable) {
                $_CLIENTE_ANTICIPO = new cliente_anticipo();
                $_CLIENTE_ANTICIPO_APLICACION = new cliente_anticipo_aplicacion();
                
                $_CLIENTE_ANTICIPO->revertir_anticipo($idcliente_anticipo_anterior, $monto_anterior);
                
                // Obtener y cancelar la aplicación
                $row_aplicacion = $_CLIENTE_ANTICIPO_APLICACION->obtener_aplicacion_por_despacho($row_actual['iddespacho'], $idcliente_anticipo_anterior);
                if ($row_aplicacion) {
                    $_CLIENTE_ANTICIPO_APLICACION->cancelar_aplicacion($row_aplicacion['idcliente_anticipo_aplicacion']);
                }
            }

            $security->registrar_bitacora($this->ACCIONES['modificar_despacho_pago'],$iddespacho_pago,$iddespacho, ($row_actual['estado'] . '->' . $estado));

            return 'editado';
        }

        $this->last_error = 'Error al modificar el documento de pago.';
        utils::report_error(bd_error, $PARAMETROS, $this->last_error);
        return false;
    }

    private function registrar_movimientos_ajuste($DATOS_BASE, $descuento, $devolucion, $idforma_pago_default, $usuario_creacion)
    {
        $descuento = (float)$descuento;
        $devolucion = (float)$devolucion;
        $IDS_AJUSTE = [];

        if ($descuento <= 0 && $devolucion <= 0) {
            return true;
        }

        if ($descuento > 0) {
            $idtipo_pago_descuento = $this->obtener_idtipo_pago_por_aliases(['DESCUENTO']);
            if (! $idtipo_pago_descuento) {
                $this->last_error = 'No existe un tipo de pago activo para DESCUENTO.';
                utils::report_error(validation_error, 'DESCUENTO', $this->last_error);
                return false;
            }

            $id_ajuste_descuento = $this->crear_movimiento_ajuste($DATOS_BASE, $idtipo_pago_descuento, $descuento, $idforma_pago_default, $usuario_creacion, 'DESCUENTO');
            if (! $id_ajuste_descuento) {
                return false;
            }

            $IDS_AJUSTE[] = $id_ajuste_descuento;
        }

        if ($devolucion > 0) {
            $idtipo_pago_devolucion = $this->obtener_idtipo_pago_por_aliases(['DEVOLUCION', 'DEVOLUCIÓN']);
            if (! $idtipo_pago_devolucion) {
                $this->last_error = 'No existe un tipo de pago activo para DEVOLUCION.';
                utils::report_error(validation_error, 'DEVOLUCION', $this->last_error);
                return false;
            }

            $id_ajuste_devolucion = $this->crear_movimiento_ajuste($DATOS_BASE, $idtipo_pago_devolucion, $devolucion, $idforma_pago_default, $usuario_creacion, 'DEVOLUCION');
            if (! $id_ajuste_devolucion) {
                foreach ($IDS_AJUSTE as $id_ajuste) {
                    mysql::put("DELETE FROM despacho_pago WHERE iddespacho_pago = '" . (int)$id_ajuste . "'");
                }
                return false;
            }

            $IDS_AJUSTE[] = $id_ajuste_devolucion;
        }

        return true;
    }

    private function crear_movimiento_ajuste($DATOS_BASE, $idtipo_pago, $monto, $idforma_pago_default, $usuario_creacion, $etiqueta)
    {
        $security = new security($this->ACCIONES['crear_despacho_pago']);
        $DATOS_AJUSTE = [];
        $DATOS_AJUSTE['iddespacho'] = $DATOS_BASE['iddespacho'];
        $DATOS_AJUSTE['fecha'] = $DATOS_BASE['fecha'];
        $DATOS_AJUSTE['idtipo_pago'] = $idtipo_pago;
        $DATOS_AJUSTE['idtipo_documento'] = $DATOS_BASE['idtipo_documento'];
        $DATOS_AJUSTE['monto'] = number_format((float)$monto, 2, '.', '');
        $DATOS_AJUSTE['estado'] = $DATOS_BASE['estado'];
        $DATOS_AJUSTE['correlativo_documento'] = $DATOS_BASE['correlativo_documento'];
        $DATOS_AJUSTE['idcliente_anticipo'] = 'NULL';
        $DATOS_AJUSTE['banco'] = 'NULL';
        $DATOS_AJUSTE['referencia_pago'] = 'NULL';
        $observaciones_base = trim((string)$DATOS_BASE['observaciones']);
        if ($observaciones_base === '' || strtoupper($observaciones_base) === 'NULL') {
            $DATOS_AJUSTE['observaciones'] = $etiqueta;
        } else {
            $DATOS_AJUSTE['observaciones'] = $observaciones_base . ' - ' . $etiqueta;
        }
        $DATOS_AJUSTE['idforma_pago'] = $idforma_pago_default;
        $DATOS_AJUSTE['iddespacho_pago_recupera'] = 'NULL';
        $DATOS_AJUSTE['usuario_creacion'] = $usuario_creacion;
        $DATOS_AJUSTE['imagen'] = 'NULL';

        if (! table::insert_record($DATOS_AJUSTE)) {
            $this->last_error = 'No se pudo registrar el movimiento adicional de ' . $etiqueta . '.';
            utils::report_error(bd_error, $DATOS_AJUSTE, $this->last_error);
            return false;
        }

        $id_ajuste = mysql::last_id();
        $security->registrar_bitacora($this->ACCIONES['crear_despacho_pago'], $id_ajuste, $DATOS_BASE['iddespacho'], $etiqueta . ': ' . $DATOS_AJUSTE['monto']);

        return $id_ajuste;
    }

    private function obtener_idtipo_pago_por_aliases($ALIAS)
    {
        if (!is_array($ALIAS) || count($ALIAS) === 0) {
            return false;
        }

        $where_alias = [];
        foreach ($ALIAS as $alias) {
            $alias = trim((string)$alias);
            if ($alias === '') {
                continue;
            }

            $where_alias[] = "UPPER(TRIM(descripcion)) = '" . addslashes($alias) . "'";
        }

        if (count($where_alias) === 0) {
            return false;
        }

        $idtipo_pago = mysql::getvalue("SELECT idtipo_pago
            FROM tipo_pago
            WHERE estado != 'INACTIVO'
                AND (" . implode(' OR ', $where_alias) . ")
            ORDER BY idtipo_pago ASC
            LIMIT 1", 'idtipo_pago');

        if (! $idtipo_pago) {
            return false;
        }

        return trim($idtipo_pago . '');
    }

    private function obtener_cheque_principal_recuperacion($numero_recuperado)
    {
        $numero_recuperado = strtoupper(trim($numero_recuperado . ''));

        return mysql::getrow("SELECT iddespacho_pago, monto
            FROM view_despacho_pago_recuperacion
            WHERE UPPER(TRIM(correlativo_documento)) = '" . addslashes($numero_recuperado) . "'
                AND estado <> 'ANULADO'
                AND tipo_documento NOT LIKE 'RECUPER%'
                AND tipo_pago LIKE '%CHEQUE%'
            ORDER BY iddespacho_pago ASC
            LIMIT 1");
    }

    private function obtener_total_recuperado_cheque_principal($iddespacho_pago_principal, $numero_cheque_principal, $iddespacho_pago_actual = '')
    {
        $iddespacho_pago_principal = trim($iddespacho_pago_principal . '');
        $numero_cheque_principal = strtoupper(trim($numero_cheque_principal . ''));
        $iddespacho_pago_actual = trim($iddespacho_pago_actual . '');
        $where_actual = '';

        if ($iddespacho_pago_actual != '') {
            $where_actual = " AND iddespacho_pago <> '" . addslashes($iddespacho_pago_actual) . "'";
        }

        $total_recuperado = mysql::getvalue("SELECT IFNULL(SUM(monto), 0) AS total_recuperado
            FROM view_despacho_pago_recuperacion
            WHERE estado <> 'ANULADO'
                AND tipo_documento LIKE 'RECUPER%'
                AND (
                    iddespacho_pago_recupera = '" . addslashes($iddespacho_pago_principal) . "'
                    OR UPPER(TRIM(IFNULL(numero_recuperado, ''))) = '" . addslashes($numero_cheque_principal) . "'
                )
                $where_actual", 'total_recuperado');

        return round((float)$total_recuperado, 2);
    }

    private function validar_correlativo_anulado_reutilizable($correlativo_documento, $iddespacho_pago_actual = '')
    {
        $correlativo_norm = strtoupper(trim((string)$correlativo_documento));
        if ($correlativo_norm === '') {
            return true;
        }

        $where_excluir = '';
        $iddespacho_pago_actual = trim((string)$iddespacho_pago_actual);
        if ($iddespacho_pago_actual !== '') {
            $where_excluir = " AND iddespacho_pago != '" . addslashes($iddespacho_pago_actual) . "'";
        }

        $id_anulado = mysql::getvalue("SELECT iddespacho_pago
            FROM despacho_pago
            WHERE UPPER(TRIM(correlativo_documento)) = '" . addslashes($correlativo_norm) . "'
                AND UPPER(TRIM(estado)) = 'ANULADO'" . $where_excluir . "
            ORDER BY iddespacho_pago DESC
            LIMIT 1", 'iddespacho_pago');

        if ($id_anulado) {
            $this->last_error = 'El numero de documento ' . $correlativo_documento . ' ya fue registrado como ANULADO y no puede reutilizarse.';
            utils::report_error(validation_error, ['correlativo_documento' => $correlativo_documento, 'iddespacho_pago_anulado' => $id_anulado], $this->last_error);
            return false;
        }

        return true;
    }

    private function obtener_despacho_pago($iddespacho_pago)
    {
        $security = new security($this->ACCIONES['consultar_despacho_pago']);
        $security->registrar_bitacora($this->ACCIONES['consultar_despacho_pago'], $iddespacho_pago, 'obtener_despacho_pago');

        $iddespacho_pago = trim($iddespacho_pago . '');

        $row_despacho_pago = mysql::getrow("SELECT iddespacho_pago, iddespacho, fecha, idtipo_pago, idcliente_anticipo, idtipo_documento, estado, monto, correlativo_documento, numero_recuperado, banco, referencia_pago, observaciones, imagen FROM despacho_pago WHERE iddespacho_pago = '$iddespacho_pago' LIMIT 1");

        if (! $row_despacho_pago) {
            $this->last_error = 'El documento de pago indicado no existe.';
            utils::report_error(validation_error, $iddespacho_pago, $this->last_error);
            return false;
        }

        return json_encode($row_despacho_pago);
    }

    private function aplicar_y_registrar_anticipo($iddespacho_pago, $idcliente_anticipo, $iddespacho, $monto, $PARAMETROS)
    {
        $_CLIENTE_ANTICIPO = new cliente_anticipo();
        $_CLIENTE_ANTICIPO_APLICACION = new cliente_anticipo_aplicacion();
        $security = new security($this->ACCIONES['crear_despacho_pago']);

        // Aplicar el anticipo (descuenta del saldo disponible)
        if (!$_CLIENTE_ANTICIPO->aplicar_anticipo($idcliente_anticipo, $monto)) {
            $this->last_error = $_CLIENTE_ANTICIPO->last_error;
            return false;
        }

        // Crear registro en cliente_anticipo_aplicacion
        $fecha = isset($PARAMETROS['fecha']) ? $PARAMETROS['fecha'] : date('Y-m-d');
        $observaciones = isset($PARAMETROS['observaciones']) ? $PARAMETROS['observaciones'] : null;

        $idcliente_anticipo_aplicacion = $_CLIENTE_ANTICIPO_APLICACION->crear_aplicacion(
            $idcliente_anticipo,
            $iddespacho,
            $fecha,
            $monto,
            $observaciones
        );

        if (!$idcliente_anticipo_aplicacion) {
            $this->last_error = $_CLIENTE_ANTICIPO_APLICACION->last_error;
            // Revertir la aplicación del anticipo si falla el registro
            $_CLIENTE_ANTICIPO->revertir_anticipo($idcliente_anticipo, $monto);
            return false;
        }

        return true;
    }

    private function actualizar_aplicacion_anticipo($iddespacho_pago, $iddespacho, $idcliente_anticipo_anterior, $idcliente_anticipo_nuevo, $monto_anterior, $monto_nuevo, $PARAMETROS)
    {
        $_CLIENTE_ANTICIPO = new cliente_anticipo();
        $_CLIENTE_ANTICIPO_APLICACION = new cliente_anticipo_aplicacion();

        // Si el anticipo cambió, revertir el anterior y aplicar el nuevo
        if ($idcliente_anticipo_anterior !== $idcliente_anticipo_nuevo) {
            $_CLIENTE_ANTICIPO->revertir_anticipo($idcliente_anticipo_anterior, $monto_anterior);
            $_CLIENTE_ANTICIPO->aplicar_anticipo($idcliente_anticipo_nuevo, $monto_nuevo);
        } else {
            // Mismo anticipo pero diferente monto
            $diferencia = $monto_nuevo - $monto_anterior;
            
            if ($diferencia > 0) {
                // Aumentó el monto, aplicar más
                $_CLIENTE_ANTICIPO->aplicar_anticipo($idcliente_anticipo_anterior, $diferencia);
            } elseif ($diferencia < 0) {
                // Disminuyó el monto, revertir la diferencia
                $_CLIENTE_ANTICIPO->revertir_anticipo($idcliente_anticipo_anterior, abs($diferencia));
            }
        }

        // Actualizar registro en cliente_anticipo_aplicacion
        $row_aplicacion = $_CLIENTE_ANTICIPO_APLICACION->obtener_aplicacion_por_despacho($iddespacho, $idcliente_anticipo_anterior);
        
        if ($row_aplicacion) {
            $_CLIENTE_ANTICIPO_APLICACION->actualizar_aplicacion(
                $row_aplicacion['idcliente_anticipo_aplicacion'],
                $idcliente_anticipo_nuevo,
                $monto_nuevo,
                isset($PARAMETROS['observaciones']) ? $PARAMETROS['observaciones'] : null
            );
        }

        return true;
    }

    private function obtener_idforma_pago_default()
    {
        $idforma_pago = mysql::getvalue("SELECT idforma_pago
            FROM forma_pago
            WHERE estado = 'ACTIVO'
            ORDER BY idforma_pago ASC
            LIMIT 1");

        if (! $idforma_pago) {
            $this->last_error = 'No existe forma de pago activa para registrar documentos.';
            utils::report_error(validation_error, 'forma_pago', $this->last_error);
            return false;
        }

        return trim($idforma_pago . '');
    }

    private function ejecutar_despacho_pago($iddespacho_pago)
    {
        $iddespacho_pago = trim($iddespacho_pago . '');

        $row_despacho_pago = mysql::getrow("SELECT iddespacho_pago, iddespacho, estado
            FROM despacho_pago
            WHERE iddespacho_pago = '$iddespacho_pago'
            LIMIT 1");

        if (! $row_despacho_pago) {
            $this->last_error = 'El documento de pago indicado no existe.';
            utils::report_error(validation_error, $iddespacho_pago, $this->last_error);
            return false;
        }

        if (($row_despacho_pago['estado'] . '') !== 'PROGRAMADO') {
            $this->last_error = 'Solo se pueden ejecutar documentos en estado PROGRAMADO.';
            utils::report_error(validation_error, $row_despacho_pago, $this->last_error);
            return false;
        }

        $security = new security($this->ACCIONES['ejecutar_despacho_pago']);
        $usuario = $security->get_actual_user();

        $DATOS = [];
        $DATOS['iddespacho_pago']         = $iddespacho_pago;
        $DATOS['estado']                  = 'EJECUTADO';
        $DATOS['fecha_modificacion']      = date('Y-m-d H:i:s');
        $DATOS['usuario_modificacion']    = $usuario;

        if (! $this->update_record($DATOS, ['iddespacho_pago'])) {
            $this->last_error = 'Error al ejecutar el documento de pago.';
            utils::report_error(bd_error, $DATOS, $this->last_error);
            return false;
        }

        $security->registrar_bitacora($this->ACCIONES['ejecutar_despacho_pago'], $iddespacho_pago, $row_despacho_pago['iddespacho'], 'PROGRAMADO->EJECUTADO');
        return true;
    }

    private function eliminar_despacho_pago($iddespacho_pago)
    {
        $iddespacho_pago = trim($iddespacho_pago . '');

        $row_despacho_pago = mysql::getrow("SELECT iddespacho_pago, iddespacho, estado, idtipo_pago, idcliente_anticipo, monto
            FROM despacho_pago
            WHERE iddespacho_pago = '$iddespacho_pago'
            LIMIT 1");

        if (! $row_despacho_pago) {
            $this->last_error = 'El documento de pago indicado no existe.';
            utils::report_error(validation_error, $iddespacho_pago, $this->last_error);
            return false;
        }

        $security = new security($this->ACCIONES['eliminar_despacho_pago']);

        $DATOS = [];
        $DATOS['iddespacho_pago'] = $iddespacho_pago;

        if (! table::delete_record($DATOS)) {
            $this->last_error = 'Error al eliminar el documento de pago.';
            utils::report_error(bd_error, $DATOS, $this->last_error);
            return false;
        }

        // Si era ANTICIPO, revertir
        $idtipo_pago = trim($row_despacho_pago['idtipo_pago'] . '');
        if ($idtipo_pago === '10') {
            $idcliente_anticipo = trim($row_despacho_pago['idcliente_anticipo'] . '');
            $monto = (float)$row_despacho_pago['monto'];
            
            $_CLIENTE_ANTICIPO = new cliente_anticipo();
            $_CLIENTE_ANTICIPO_APLICACION = new cliente_anticipo_aplicacion();
            
            $_CLIENTE_ANTICIPO->revertir_anticipo($idcliente_anticipo, $monto);
            
            // Obtener y cancelar la aplicación
            $row_aplicacion = $_CLIENTE_ANTICIPO_APLICACION->obtener_aplicacion_por_despacho($row_despacho_pago['iddespacho'], $idcliente_anticipo);
            if ($row_aplicacion) {
                $_CLIENTE_ANTICIPO_APLICACION->cancelar_aplicacion($row_aplicacion['idcliente_anticipo_aplicacion']);
            }
        }

        $security->registrar_bitacora($this->ACCIONES['eliminar_despacho_pago'], $iddespacho_pago, $row_despacho_pago['iddespacho'], $row_despacho_pago['estado']);
        return true;
    }

    private function imprimir_documento($iddespacho_pago)
    {
        $security = new security($this->ACCIONES['imprimir_despacho_pago']);

        $iddespacho_pago = trim($iddespacho_pago . '');

        $documento_base = mysql::getrow("SELECT iddespacho_pago, iddespacho, idtipo_documento, correlativo_documento, fecha
            FROM despacho_pago
            WHERE iddespacho_pago = '$iddespacho_pago'
            LIMIT 1");

        if (!$documento_base) {
            $this->last_error = 'El documento de pago indicado no existe.';
            utils::report_error(validation_error, $iddespacho_pago, $this->last_error);
            return false;
        }

        $iddespacho = trim($documento_base['iddespacho'] . '');
        $idtipo_documento = trim($documento_base['idtipo_documento'] . '');
        $correlativo_documento = trim($documento_base['correlativo_documento'] . '');
        $correlativo_documento_norm = strtoupper($correlativo_documento);

        $_TIPO_DOCUMENTO = new tipo_documento();
        $row_tipo_documento = $_TIPO_DOCUMENTO->obtener_por_id($idtipo_documento);
        if (! $row_tipo_documento) {
            $this->last_error = 'No se encontró configuración para el tipo de documento seleccionado.';
            utils::report_error(validation_error, $idtipo_documento, $this->last_error);
            return false;
        }

        $nombre_tipo_documento = trim($row_tipo_documento['nombre'] . '');
        $serie_documento = trim($row_tipo_documento['correlativo'] . '');

        $sql_documentos = mysql::getresult("SELECT dp.iddespacho_pago, dp.fecha, dp.correlativo_documento, dp.imagen,
                td.nombre AS tipo_documento
            FROM despacho_pago dp
            LEFT JOIN tipo_documento td ON td.idtipo_documento = dp.idtipo_documento
            WHERE dp.iddespacho = '$iddespacho'
                AND dp.idtipo_documento = '" . addslashes($idtipo_documento) . "'
                AND UPPER(TRIM(dp.correlativo_documento)) = '" . addslashes($correlativo_documento_norm) . "'
            ORDER BY dp.fecha ASC, dp.iddespacho_pago ASC");

        if (!$sql_documentos) {
            $this->last_error = 'Error al obtener los documentos del recibo.';
            utils::report_error(bd_error, $iddespacho_pago, $this->last_error);
            return false;
        }

        $html_imagenes = '';
        $hay_filas = false;
        $hay_imagenes = false;

        while ($row = mysql::getrowresult($sql_documentos)) {
            $hay_filas = true;
            $ruta_imagen = trim((string)$row['imagen']);
            if ($ruta_imagen == '') {
                continue;
            }

            $hay_imagenes = true;
            $src_imagen = '../' . htmlspecialchars($ruta_imagen, ENT_QUOTES, 'UTF-8');
            $fecha_doc = htmlspecialchars((string)$row['fecha'], ENT_QUOTES, 'UTF-8');
            $tipo_doc = htmlspecialchars((string)$row['tipo_documento'], ENT_QUOTES, 'UTF-8');
            $correlativo_doc = htmlspecialchars((string)$row['correlativo_documento'], ENT_QUOTES, 'UTF-8');

            $html_imagenes .= "
                <div class='pagina-documento'>
                    <div class='encabezado-documento'>
                        <h3>Documento de pago</h3>
                        <p><strong>Tipo:</strong> $tipo_doc - <strong>Correlativo:</strong> $correlativo_doc - <strong>Fecha:</strong> $fecha_doc</p>
                    </div>
                    <div class='contenedor-imagen'>
                        <img src='$src_imagen' alt='Documento de pago'>
                    </div>
                </div>
            ";
        }

        if (!$hay_filas) {
            $this->last_error = 'No se encontraron registros para el correlativo y tipo de documento seleccionados.';
            utils::report_error(validation_error, $iddespacho_pago, $this->last_error);
            return false;
        }

        if (!$hay_imagenes) {
            $this->last_error = 'No hay imagenes para imprimir en este correlativo y tipo de documento.';
            utils::report_error(validation_error, $iddespacho_pago, $this->last_error);
            return false;
        }

        $html = "
            <style>
                @page {
                    size: auto;
                    margin: 8mm;
                }
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    color: #111111;
                }
                .contenedor-documentos {
                    width: 100%;
                }
                .pagina-documento {
                    page-break-after: always;
                    box-sizing: border-box;
                    padding: 8mm 8mm 8mm 8mm;
                }
                .pagina-documento:last-child {
                    page-break-after: auto;
                }
                .encabezado-documento {
                    margin-bottom: 10px;
                    border-bottom: 1px solid #cccccc;
                    padding-bottom: 6px;
                }
                .encabezado-documento h3 {
                    margin: 0 0 4px 0;
                    font-size: 18px;
                }
                .encabezado-documento p {
                    margin: 0;
                    font-size: 13px;
                }
                .contenedor-imagen {
                    text-align: center;
                    margin-top: 10px;
                    page-break-inside: avoid;
                }
                .contenedor-imagen img {
                    width: auto;
                    height: auto;
                    max-width: 190mm;
                    max-height: 210mm;
                    border: 1px solid #dddddd;
                    object-fit: contain;
                }

                @media print {
                    .pagina-documento {
                        padding-top: 20mm;
                    }
                    .contenedor-imagen img {
                        max-width: 185mm;
                        max-height: 190mm;
                    }
                }
            </style>
            <div id='contenedor_documentos_impresion' class='contenedor-documentos'>
                $html_imagenes
            </div>
        ";

        $security->registrar_bitacora($this->ACCIONES['imprimir_despacho_pago'], $iddespacho_pago, $correlativo_documento, $nombre_tipo_documento);
        return $html;
    }

    private function guardar_imagen_documento($iddespacho, $correlativo_documento)
    {
        if (!isset($_FILES['file_uploaded']) || $_FILES['file_uploaded']['tmp_name'] == '') {
            return '';
        }

        $tipos_permitidos = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
        ];

        $tipo_archivo = isset($_FILES['file_uploaded']['type']) ? $_FILES['file_uploaded']['type'] : '';
        if (!isset($tipos_permitidos[$tipo_archivo])) {
            $this->last_error = 'Tipo de archivo no permitido. Debe cargar imagenes en formato JPEG, JPG o PNG';
            utils::report_error(validation_error, $tipo_archivo, $this->last_error);
            return false;
        }

        $extension = $tipos_permitidos[$tipo_archivo];
        $referencia = preg_replace('/[^A-Za-z0-9_-]+/', '_', trim((string)$correlativo_documento));
        $referencia = $referencia !== '' ? $referencia : 'documento';
        $ruta = '../../img/documento/';
        $nombre_temp = $_FILES['file_uploaded']['tmp_name'];
        $nombre_archivo = uniqid('despacho_' . $iddespacho . '_' . $referencia . '_') . '.' . $extension;

        if (!file_exists($ruta)) {
            mkdir($ruta, 0777, true);
        }

        if (!move_uploaded_file($nombre_temp, $ruta . $nombre_archivo)) {
            $this->last_error = 'Error al mover el archivo cargado';
            utils::report_error(validation_error, $correlativo_documento, $this->last_error);
            return false;
        }

        return 'img/documento/' . $nombre_archivo;
    }

}
