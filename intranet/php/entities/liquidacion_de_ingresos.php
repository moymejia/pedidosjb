<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/utils.php';

class liquidacion_de_ingresos extends table
{
    use utils;

    public $last_error = '';
    private $ACCIONES = [];

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'liquidacion_de_ingresos');

        $this->ACCIONES['opcion_liquidacion_de_ingresos'] = 'Opcion_liquidacion_de_ingresos';

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'generar_reporte_liquidacion_de_ingresos') {
                if ($this->validate_parameter_existence(['fecha_desde', 'fecha_hasta'], $PARAMETROS, false)) {
                    if ($resultado = $this->generar_reporte_liquidacion_de_ingresos($PARAMETROS['fecha_desde'], $PARAMETROS['fecha_hasta'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error('Debe seleccionar fecha desde y fecha hasta.');
                }
            }
        }
    }

    public function cargar_opcion()
    {
        $_SECURITY = new security($this->ACCIONES['opcion_liquidacion_de_ingresos']);
        $_SECURITY->get_actual_user();

        $_HTML = new html('liquidacion_de_ingresos');

        return $_HTML->get_html();
    }

    public function generar_reporte_liquidacion_de_ingresos($fecha_desde, $fecha_hasta)
    {
        $_SECURITY = new security($this->ACCIONES['opcion_liquidacion_de_ingresos']);
        $_SECURITY->get_actual_user();

        if (!$this->validar_rango_fechas($fecha_desde, $fecha_hasta)) {
            return false;
        }

        $where_fechas = "DATE(fecha_pago) >= '$fecha_desde' AND DATE(fecha_pago) <= '$fecha_hasta'";

        $sql_ejecutados = mysql::getresult("SELECT
                iddespacho,
                DATE_FORMAT(fecha_pago, '%d/%m/%Y') AS fecha_pago,
                IFNULL(correlativo_documento, '') AS numero_documento,
                IFNULL(numero_recuperado, '') AS numero_recuperado,
                IFNULL(nombre_cliente, '') AS cliente,
                IFNULL(tipo_pago, '') AS tipo_pago,
                IFNULL(tipo_documento, '') AS tipo_documento,
                IFNULL(estado_pago_individual, '') AS estado_pago_individual,
                IFNULL(monto_flete, 0) AS monto_flete,
                IFNULL(observaciones_anticipo, '') AS observaciones_anticipo,
                CASE
                    WHEN UPPER(TRIM(IFNULL(estado_pago_individual, ''))) = 'ANULADO' THEN 0
                    ELSE IFNULL(monto_pago, 0)
                END AS monto_pago
            FROM view_estado_cuenta_despacho_detallado
            WHERE fecha_pago IS NOT NULL
              AND $where_fechas
            ORDER BY fecha_pago ASC, iddespacho ASC");

        if (!$sql_ejecutados) {
            $this->last_error = 'No fue posible obtener documentos ejecutados para la liquidacion de ingresos.';
            utils::report_error(bd_error, $where_fechas, $this->last_error);
            return false;
        }

        $sql_programados = mysql::getresult("SELECT
                iddespacho,
                IFNULL(codigo_cliente, '') AS codigo_cliente,
                IFNULL(referencia_pago, '') AS numero_cheque,
                TRIM(IFNULL(banco, '')) AS banco,
                DATE_FORMAT(fecha_pago, '%d/%m/%Y') AS fecha_pago,
                IFNULL(correlativo_documento, '') AS numero_documento,
                IFNULL(numero_recuperado, '') AS numero_recuperado,
                IFNULL(nombre_cliente, '') AS cliente,
                IFNULL(tipo_pago, '') AS tipo_pago,
                IFNULL(tipo_documento, '') AS tipo_documento,
                IFNULL(estado_pago_individual, '') AS estado_pago_individual,
                CASE
                    WHEN UPPER(TRIM(IFNULL(estado_pago_individual, ''))) = 'ANULADO' THEN 0
                    ELSE IFNULL(monto_pago, 0)
                END AS monto_pago
            FROM view_estado_cuenta_despacho_detallado
            WHERE fecha_pago IS NOT NULL
                AND $where_fechas
                AND UPPER(TRIM(estado_pago_individual)) = 'PROGRAMADO'
            ORDER BY fecha_pago ASC, iddespacho ASC");

        if (!$sql_programados) {
            $this->last_error = 'No fue posible obtener documentos programados para la liquidacion de ingresos.';
            utils::report_error(bd_error, $where_fechas, $this->last_error);
            return false;
        }

        $sql_recuperacion = mysql::getresult("SELECT
                iddespacho,
                DATE_FORMAT(fecha_pago, '%d/%m/%Y') AS fecha_pago,
                IFNULL(correlativo_documento, '') AS numero_documento,
                IFNULL(numero_recuperado, '') AS numero_recuperado,
                IFNULL(nombre_cliente, '') AS cliente,
                IFNULL(tipo_pago, '') AS tipo_pago,
                IFNULL(tipo_documento, '') AS tipo_documento,
                CASE
                    WHEN UPPER(TRIM(IFNULL(estado_pago_individual, ''))) = 'ANULADO' THEN 0
                    ELSE IFNULL(monto_pago, 0)
                END AS monto_pago,
                IFNULL(estado_pago_individual, '') AS estado_pago_individual
            FROM view_estado_cuenta_despacho_detallado
            WHERE fecha_pago IS NOT NULL
                AND $where_fechas
                AND UPPER(TRIM(IFNULL(tipo_documento, ''))) = 'RECUPERACION'
            ORDER BY fecha_pago ASC, iddespacho ASC");

        if (!$sql_recuperacion) {
            $this->last_error = 'No fue posible obtener documentos de recuperacion para la liquidacion de ingresos.';
            utils::report_error(bd_error, $where_fechas, $this->last_error);
            return false;
        }

        $DATOS = [
            'vendedor'                               => strtoupper($_SESSION['usuario']),
            'fecha_desde'                            => $this->formatear_fecha_texto($fecha_desde),
            'fecha_hasta'                            => $this->formatear_fecha_texto($fecha_hasta),
            'hl_no'                                  => '',
            'recibos_serie'                          => 'D',
            'recibos_del'                            => '',
            'recibos_al'                             => '',
            'tabla_documentos_ejecutados_rows'       => '',
            'tabla_documentos_programados_rows'      => '',
            'tabla_documentos_recuperacion_rows'     => '',
            'total_fletes'                           => '0.00',
            'total_depositos'                        => '0.00',
            'total_cheques_vista'                    => '0.00',
            'total_cheques_posfecha'                 => '0.00',
            'total_anticipos'                        => '0.00',
            'total_cobrado'                          => '0.00',
            'detalle_total_flete'                    => '0.00',
            'detalle_total_deposito'                 => '0.00',
            'detalle_total_cheque_vista'             => '0.00',
            'detalle_total_cheque_posfechado'        => '0.00',
            'detalle_total_anticipo'                 => '0.00',
            'detalle_total_general'                  => '0.00',
            'total_programados'                      => '0.00',
            'total_recuperacion'                     => '0.00',
            'provisional_recibos_del'                => '',
            'provisional_recibos_al'                 => '',
            'provisional_tabla_documentos_ejecutados_rows' => '',
            'provisional_tabla_documentos_programados_rows' => '',
            'provisional_tabla_documentos_recuperacion_rows' => '',
            'provisional_total_fletes'               => '0.00',
            'provisional_total_depositos'            => '0.00',
            'provisional_total_cheques_vista'        => '0.00',
            'provisional_total_cheques_posfecha'     => '0.00',
            'provisional_total_anticipos'            => '0.00',
            'provisional_total_cobrado'              => '0.00',
            'provisional_detalle_total_flete'        => '0.00',
            'provisional_detalle_total_deposito'     => '0.00',
            'provisional_detalle_total_cheque_vista' => '0.00',
            'provisional_detalle_total_cheque_posfechado' => '0.00',
            'provisional_detalle_total_anticipo'     => '0.00',
            'provisional_detalle_total_general'      => '0.00',
            'provisional_total_programados'          => '0.00',
            'provisional_total_recuperacion'         => '0.00'
        ];

        $TOTALES_DETALLE = [
            'flete' => 0,
            'deposito' => 0,
            'cheque_vista' => 0,
            'cheque_posfechado' => 0,
            'anticipo' => 0,
            'descuento' => 0,
            'devolucion' => 0
        ];

        $TOTALES_PROVISIONALES = [
            'flete' => 0,
            'deposito' => 0,
            'cheque_vista' => 0,
            'cheque_posfechado' => 0,
            'anticipo' => 0,
            'descuento' => 0,
            'devolucion' => 0
        ];

        $RECIBOS_ORDENABLES = [];
        $RECIBOS_PROVISIONALES_ORDENABLES = [];
        $DESPACHOS_FLETE = [];
        $DESPACHOS_FLETE_PROVISIONALES = [];
        $filas_ejecutados = '';
        $filas_provisionales = '';
        while ($row = mysql::getrowresult($sql_ejecutados)) {
            $iddespacho = (int)$row['iddespacho'];
            $estado_pago = strtoupper(trim($row['estado_pago_individual']));
            $categoria_pago = $this->obtener_categoria_liquidacion($row['tipo_pago'], $estado_pago);
            if ($categoria_pago == '') {
                continue;
            }
            $es_recibo_provisional = $this->es_recibo_provisional($row['tipo_documento']);

            $monto = (float)$row['monto_pago'];
            $cliente = $row['cliente'];

            $valor_flete = '';
            $valor_deposito = '';
            $valor_cheque_vista = '';
            $valor_cheque_posfechado = '';
            $valor_anticipo = '';
            $informacion_anticipo = '';
            $valor_flete_provisional = '';
            $valor_deposito_provisional = '';
            $valor_cheque_vista_provisional = '';
            $valor_cheque_posfechado_provisional = '';
            $valor_anticipo_provisional = '';
            $informacion_anticipo_provisional = '';

            // El flete se toma del despacho y se aplica una sola vez por iddespacho.
            if (!$es_recibo_provisional && !isset($DESPACHOS_FLETE[$iddespacho])) {
                $monto_flete = (float)$row['monto_flete'];
                if ($monto_flete > 0) {
                    $valor_flete = $this->formatear_moneda($monto_flete);
                    $TOTALES_DETALLE['flete'] += $monto_flete;
                }
                $DESPACHOS_FLETE[$iddespacho] = true;
            }
            if ($es_recibo_provisional && !isset($DESPACHOS_FLETE_PROVISIONALES[$iddespacho])) {
                $monto_flete = (float)$row['monto_flete'];
                if ($monto_flete > 0) {
                    $valor_flete_provisional = $this->formatear_moneda($monto_flete);
                    $TOTALES_PROVISIONALES['flete'] += $monto_flete;
                }
                $DESPACHOS_FLETE_PROVISIONALES[$iddespacho] = true;
            }

            if ($categoria_pago == 'CHEQUE_POSFECHADO') {
                if ($es_recibo_provisional) {
                    $valor_cheque_posfechado_provisional = $this->formatear_moneda($monto);
                    $TOTALES_PROVISIONALES['cheque_posfechado'] += $monto;
                } else {
                    $valor_cheque_posfechado = $this->formatear_moneda($monto);
                    $TOTALES_DETALLE['cheque_posfechado'] += $monto;
                }
            } elseif ($categoria_pago == 'CHEQUE_VISTA') {
                if ($es_recibo_provisional) {
                    $valor_cheque_vista_provisional = $this->formatear_moneda($monto);
                    $TOTALES_PROVISIONALES['cheque_vista'] += $monto;
                } else {
                    $valor_cheque_vista = $this->formatear_moneda($monto);
                    $TOTALES_DETALLE['cheque_vista'] += $monto;
                }
            } elseif ($categoria_pago == 'DEPOSITO') {
                if ($es_recibo_provisional) {
                    $valor_deposito_provisional = $this->formatear_moneda($monto);
                    $TOTALES_PROVISIONALES['deposito'] += $monto;
                } else {
                    $valor_deposito = $this->formatear_moneda($monto);
                    $TOTALES_DETALLE['deposito'] += $monto;
                }
            } elseif ($categoria_pago == 'ANTICIPO') {
                if ($es_recibo_provisional) {
                    $valor_anticipo_provisional = $this->formatear_moneda($monto);
                    $informacion_anticipo_provisional = $row['observaciones_anticipo'];
                    $TOTALES_PROVISIONALES['anticipo'] += $monto;
                } else {
                    $valor_anticipo = $this->formatear_moneda($monto);
                    $informacion_anticipo = $row['observaciones_anticipo'];
                    $TOTALES_DETALLE['anticipo'] += $monto;
                }
            } elseif ($categoria_pago == 'DESCUENTO') {
                if ($es_recibo_provisional) {
                    $TOTALES_PROVISIONALES['descuento'] += $monto;
                } else {
                    $TOTALES_DETALLE['descuento'] += $monto;
                }
            } elseif ($categoria_pago == 'DEVOLUCION') {
                if ($es_recibo_provisional) {
                    $TOTALES_PROVISIONALES['devolucion'] += $monto;
                } else {
                    $TOTALES_DETALLE['devolucion'] += $monto;
                }
            }

            $numero_documento = trim((string)$row['numero_documento']);
            if (!$es_recibo_provisional && $numero_documento != '') {
                $RECIBOS_ORDENABLES[] = $numero_documento;
            }
            if ($es_recibo_provisional && $numero_documento != '') {
                $RECIBOS_PROVISIONALES_ORDENABLES[] = $numero_documento;
            }

            if (!$es_recibo_provisional) {
                $filas_ejecutados .= '<tr>';
                $filas_ejecutados .= '<td class="text-center">' . htmlspecialchars($row['fecha_pago'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_ejecutados .= '<td class="text-center">' . htmlspecialchars($row['numero_documento'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_ejecutados .= '<td>' . htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_ejecutados .= '<td class="text-right">' . (($valor_flete != '') ? ('Q ' . $valor_flete) : '') . '</td>';
                $filas_ejecutados .= '<td class="text-right">' . (($valor_deposito != '') ? ('Q ' . $valor_deposito) : '') . '</td>';
                $filas_ejecutados .= '<td class="text-right">' . (($valor_cheque_vista != '') ? ('Q ' . $valor_cheque_vista) : '') . '</td>';
                $filas_ejecutados .= '<td class="text-right">' . (($valor_cheque_posfechado != '') ? ('Q ' . $valor_cheque_posfechado) : '') . '</td>';
                $filas_ejecutados .= '<td class="text-right">Q ' . $this->formatear_moneda($monto) . '</td>';
                $filas_ejecutados .= '<td class="text-right">' . (($valor_anticipo != '') ? ('Q ' . $valor_anticipo) : '') . '</td>';
                $filas_ejecutados .= '<td>' . nl2br(htmlspecialchars($informacion_anticipo, ENT_QUOTES, 'UTF-8')) . '</td>';
                $filas_ejecutados .= '</tr>';
            }

            if ($es_recibo_provisional) {
                $filas_provisionales .= '<tr>';
                $filas_provisionales .= '<td class="text-center">' . htmlspecialchars($row['fecha_pago'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_provisionales .= '<td class="text-center">' . htmlspecialchars($row['numero_documento'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_provisionales .= '<td>' . htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_provisionales .= '<td class="text-right">' . (($valor_flete_provisional != '') ? ('Q ' . $valor_flete_provisional) : '') . '</td>';
                $filas_provisionales .= '<td class="text-right">' . (($valor_deposito_provisional != '') ? ('Q ' . $valor_deposito_provisional) : '') . '</td>';
                $filas_provisionales .= '<td class="text-right">' . (($valor_cheque_vista_provisional != '') ? ('Q ' . $valor_cheque_vista_provisional) : '') . '</td>';
                $filas_provisionales .= '<td class="text-right">' . (($valor_cheque_posfechado_provisional != '') ? ('Q ' . $valor_cheque_posfechado_provisional) : '') . '</td>';
                $filas_provisionales .= '<td class="text-right">Q ' . $this->formatear_moneda($monto) . '</td>';
                $filas_provisionales .= '</tr>';
            }
        }

        if ($filas_ejecutados == '') {
            $filas_ejecutados = '<tr><td colspan="10" class="text-center">No hay documentos ejecutados para el rango seleccionado.</td></tr>';
        }
        if ($filas_provisionales == '') {
            $filas_provisionales = '<tr><td colspan="8" class="text-center">No hay recibos provisionales para el rango seleccionado.</td></tr>';
        }

        $filas_programados = '';
        $filas_programados_provisionales = '';
        $total_programados = 0;
        $total_programados_provisionales = 0;
        while ($row = mysql::getrowresult($sql_programados)) {
            $estado_pago = isset($row['estado_pago_individual']) ? strtoupper(trim($row['estado_pago_individual'])) : '';
            $categoria_pago = $this->obtener_categoria_liquidacion($row['tipo_pago'], $estado_pago);
            if ($categoria_pago != 'CHEQUE_POSFECHADO') {
                continue;
            }

            $monto = (float)$row['monto_pago'];
            $cliente = $row['cliente'];

            if ($this->es_recibo_provisional($row['tipo_documento'])) {
                $total_programados_provisionales += $monto;

                $filas_programados_provisionales .= '<tr>';
                $filas_programados_provisionales .= '<td class="text-center">' . htmlspecialchars($row['codigo_cliente'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_programados_provisionales .= '<td>' . htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_programados_provisionales .= '<td class="text-center">' . htmlspecialchars($row['numero_cheque'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_programados_provisionales .= '<td class="text-center">' . htmlspecialchars($row['banco'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_programados_provisionales .= '<td class="text-center">' . htmlspecialchars($row['fecha_pago'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_programados_provisionales .= '<td class="text-right">Q ' . $this->formatear_moneda($monto) . '</td>';
                $filas_programados_provisionales .= '</tr>';
            } else {
                $total_programados += $monto;

                $filas_programados .= '<tr>';
                $filas_programados .= '<td class="text-center">' . htmlspecialchars($row['codigo_cliente'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_programados .= '<td>' . htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_programados .= '<td class="text-center">' . htmlspecialchars($row['numero_cheque'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_programados .= '<td class="text-center">' . htmlspecialchars($row['banco'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_programados .= '<td class="text-center">' . htmlspecialchars($row['fecha_pago'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_programados .= '<td class="text-right">Q ' . $this->formatear_moneda($monto) . '</td>';
                $filas_programados .= '</tr>';
            }
        }

        if ($filas_programados == '') {
            $filas_programados = '<tr><td colspan="6" class="text-center">No hay documentos programados para el rango seleccionado.</td></tr>';
        }
        if ($filas_programados_provisionales == '') {
            $filas_programados_provisionales = '<tr><td colspan="6" class="text-center">No hay cheques posfechados provisionales para el rango seleccionado.</td></tr>';
        }

        $filas_recuperacion = '';
        $filas_recuperacion_provisionales = '';
        $total_recuperacion = 0;
        $total_recuperacion_provisionales = 0;
        while ($row = mysql::getrowresult($sql_recuperacion)) {
            $estado = strtoupper(trim($row['estado_pago_individual']));
            $categoria_pago = $this->obtener_categoria_liquidacion($row['tipo_pago'], $estado);
            if ($categoria_pago == '') {
                continue;
            }
            if ($categoria_pago != 'DEPOSITO' && $categoria_pago != 'CHEQUE_POSFECHADO' && $categoria_pago != 'CHEQUE_VISTA') {
                continue;
            }

            $monto = (float)$row['monto_pago'];
            $cliente = $row['cliente'];
            $valor_deposito = ($categoria_pago == 'DEPOSITO') ? ('Q ' . $this->formatear_moneda($monto)) : '';
            $valor_cheque = ($categoria_pago == 'CHEQUE_POSFECHADO' || $categoria_pago == 'CHEQUE_VISTA') ? ('Q ' . $this->formatear_moneda($monto)) : '';

            if ($this->es_recibo_provisional($row['tipo_documento'])) {
                $total_recuperacion_provisionales += $monto;

                $filas_recuperacion_provisionales .= '<tr>';
                $filas_recuperacion_provisionales .= '<td class="text-center">' . htmlspecialchars($row['fecha_pago'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_recuperacion_provisionales .= '<td class="text-center">' . htmlspecialchars($row['numero_documento'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_recuperacion_provisionales .= '<td>' . htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_recuperacion_provisionales .= '<td class="text-center">' . htmlspecialchars($row['numero_documento'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_recuperacion_provisionales .= '<td class="text-right">' . $valor_deposito . '</td>';
                $filas_recuperacion_provisionales .= '<td class="text-right">' . $valor_cheque . '</td>';
                $filas_recuperacion_provisionales .= '<td class="text-center">' . htmlspecialchars($row['numero_recuperado'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_recuperacion_provisionales .= '<td class="text-right">Q ' . $this->formatear_moneda($monto) . '</td>';
                $filas_recuperacion_provisionales .= '</tr>';
            } else {
                $total_recuperacion += $monto;

                $filas_recuperacion .= '<tr>';
                $filas_recuperacion .= '<td class="text-center">' . htmlspecialchars($row['fecha_pago'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_recuperacion .= '<td class="text-center">' . htmlspecialchars($row['numero_documento'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_recuperacion .= '<td>' . htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_recuperacion .= '<td class="text-center">' . htmlspecialchars($row['numero_documento'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_recuperacion .= '<td class="text-right">' . $valor_deposito . '</td>';
                $filas_recuperacion .= '<td class="text-right">' . $valor_cheque . '</td>';
                $filas_recuperacion .= '<td class="text-center">' . htmlspecialchars($row['numero_recuperado'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas_recuperacion .= '<td class="text-right">Q ' . $this->formatear_moneda($monto) . '</td>';
                $filas_recuperacion .= '</tr>';
            }
        }

        if ($filas_recuperacion == '') {
            $filas_recuperacion = '<tr><td colspan="8" class="text-center">No hay documentos de recuperacion para el rango seleccionado.</td></tr>';
        }
        if ($filas_recuperacion_provisionales == '') {
            $filas_recuperacion_provisionales = '<tr><td colspan="8" class="text-center">No hay recuperacion de cheques provisionales para el rango seleccionado.</td></tr>';
        }

        $DATOS['tabla_documentos_ejecutados_rows'] = $filas_ejecutados;
        $DATOS['tabla_documentos_programados_rows'] = $filas_programados;
        $DATOS['tabla_documentos_recuperacion_rows'] = $filas_recuperacion;
        $DATOS['provisional_tabla_documentos_ejecutados_rows'] = $filas_provisionales;
        $DATOS['provisional_tabla_documentos_programados_rows'] = $filas_programados_provisionales;
        $DATOS['provisional_tabla_documentos_recuperacion_rows'] = $filas_recuperacion_provisionales;

        $total_cobrado =
            $TOTALES_DETALLE['flete'] +
            $TOTALES_DETALLE['deposito'] +
            $TOTALES_DETALLE['cheque_vista'] +
            $TOTALES_DETALLE['cheque_posfechado'] +
            $TOTALES_DETALLE['anticipo'] +
            $TOTALES_DETALLE['descuento'] +
            $TOTALES_DETALLE['devolucion'];

        $total_cobrado_provisional =
            $TOTALES_PROVISIONALES['flete'] +
            $TOTALES_PROVISIONALES['deposito'] +
            $TOTALES_PROVISIONALES['cheque_vista'] +
            $TOTALES_PROVISIONALES['cheque_posfechado'] +
            $TOTALES_PROVISIONALES['anticipo'] +
            $TOTALES_PROVISIONALES['descuento'] +
            $TOTALES_PROVISIONALES['devolucion'];

        $DATOS['detalle_total_flete'] = $this->formatear_moneda($TOTALES_DETALLE['flete']);
        $DATOS['detalle_total_deposito'] = $this->formatear_moneda($TOTALES_DETALLE['deposito']);
        $DATOS['detalle_total_cheque_vista'] = $this->formatear_moneda($TOTALES_DETALLE['cheque_vista']);
        $DATOS['detalle_total_cheque_posfechado'] = $this->formatear_moneda($TOTALES_DETALLE['cheque_posfechado']);
        $DATOS['detalle_total_anticipo'] = $this->formatear_moneda($TOTALES_DETALLE['anticipo']);
        $DATOS['detalle_total_general'] = $this->formatear_moneda($total_cobrado);

        $DATOS['total_fletes'] = $this->formatear_moneda($TOTALES_DETALLE['flete']);
        $DATOS['total_depositos'] = $this->formatear_moneda($TOTALES_DETALLE['deposito']);
        $DATOS['total_cheques_vista'] = $this->formatear_moneda($TOTALES_DETALLE['cheque_vista']);
        $DATOS['total_cheques_posfecha'] = $this->formatear_moneda($TOTALES_DETALLE['cheque_posfechado']);
        $DATOS['total_anticipos'] = $this->formatear_moneda($TOTALES_DETALLE['anticipo']);
        $DATOS['total_cobrado'] = $this->formatear_moneda($total_cobrado);

        $DATOS['provisional_detalle_total_flete'] = $this->formatear_moneda($TOTALES_PROVISIONALES['flete']);
        $DATOS['provisional_detalle_total_deposito'] = $this->formatear_moneda($TOTALES_PROVISIONALES['deposito']);
        $DATOS['provisional_detalle_total_cheque_vista'] = $this->formatear_moneda($TOTALES_PROVISIONALES['cheque_vista']);
        $DATOS['provisional_detalle_total_cheque_posfechado'] = $this->formatear_moneda($TOTALES_PROVISIONALES['cheque_posfechado']);
        $DATOS['provisional_detalle_total_anticipo'] = $this->formatear_moneda($TOTALES_PROVISIONALES['anticipo']);
        $DATOS['provisional_detalle_total_general'] = $this->formatear_moneda($total_cobrado_provisional);

        $DATOS['provisional_total_fletes'] = $this->formatear_moneda($TOTALES_PROVISIONALES['flete']);
        $DATOS['provisional_total_depositos'] = $this->formatear_moneda($TOTALES_PROVISIONALES['deposito']);
        $DATOS['provisional_total_cheques_vista'] = $this->formatear_moneda($TOTALES_PROVISIONALES['cheque_vista']);
        $DATOS['provisional_total_cheques_posfecha'] = $this->formatear_moneda($TOTALES_PROVISIONALES['cheque_posfechado']);
        $DATOS['provisional_total_anticipos'] = $this->formatear_moneda($TOTALES_PROVISIONALES['anticipo']);
        $DATOS['provisional_total_cobrado'] = $this->formatear_moneda($total_cobrado_provisional);

        if (count($RECIBOS_ORDENABLES) > 0) {
            usort($RECIBOS_ORDENABLES, [$this, 'comparar_recibos']);
            $DATOS['recibos_del'] = (string)$RECIBOS_ORDENABLES[0];
            $DATOS['recibos_al'] = (string)$RECIBOS_ORDENABLES[count($RECIBOS_ORDENABLES) - 1];
        }
        if (count($RECIBOS_PROVISIONALES_ORDENABLES) > 0) {
            usort($RECIBOS_PROVISIONALES_ORDENABLES, [$this, 'comparar_recibos']);
            $DATOS['provisional_recibos_del'] = (string)$RECIBOS_PROVISIONALES_ORDENABLES[0];
            $DATOS['provisional_recibos_al'] = (string)$RECIBOS_PROVISIONALES_ORDENABLES[count($RECIBOS_PROVISIONALES_ORDENABLES) - 1];
        }

        $DATOS['total_programados'] = $this->formatear_moneda($total_programados);
        $DATOS['total_recuperacion'] = $this->formatear_moneda($total_recuperacion);
        $DATOS['provisional_total_programados'] = $this->formatear_moneda($total_programados_provisionales);
        $DATOS['provisional_total_recuperacion'] = $this->formatear_moneda($total_recuperacion_provisionales);

        $_HTML = new html('template_liquidacion_documentos', $DATOS);
        $contenido = $_HTML->get_html();

        $usuario_actual = $_SECURITY->get_actual_user();
        $_SECURITY->registrar_bitacora($this->ACCIONES['opcion_liquidacion_de_ingresos'],'BUSQUEDA',$usuario_actual);

        return $contenido;
    }

    private function validar_rango_fechas($fecha_desde, $fecha_hasta)
    {
        if (trim($fecha_desde) == '' || trim($fecha_hasta) == '') {
            $this->last_error = 'Debe seleccionar fecha desde y fecha hasta.';
            utils::report_error(validation_error, ['fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta], $this->last_error);
            return false;
        }

        if (strtotime($fecha_desde) === false) {
            $this->last_error = 'La fecha desde no es valida.';
            utils::report_error(validation_error, $fecha_desde, $this->last_error);
            return false;
        }

        if (strtotime($fecha_hasta) === false) {
            $this->last_error = 'La fecha hasta no es valida.';
            utils::report_error(validation_error, $fecha_hasta, $this->last_error);
            return false;
        }

        if (strtotime($fecha_desde) > strtotime($fecha_hasta)) {
            $this->last_error = 'La fecha desde no puede ser mayor que la fecha hasta.';
            utils::report_error(validation_error, ['fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta], $this->last_error);
            return false;
        }

        return true;
    }

    private function comparar_recibos($recibo_a, $recibo_b)
    {
        $clave_a = preg_replace('/\D+/', '', (string)$recibo_a);
        $clave_b = preg_replace('/\D+/', '', (string)$recibo_b);

        if ($clave_a != '' && $clave_b != '') {
            if (strlen($clave_a) < strlen($clave_b)) {
                return -1;
            }
            if (strlen($clave_a) > strlen($clave_b)) {
                return 1;
            }

            $comparacion_clave = strcmp($clave_a, $clave_b);
            if ($comparacion_clave != 0) {
                return $comparacion_clave;
            }
        }

        return strcasecmp((string)$recibo_a, (string)$recibo_b);
    }

    private function es_recibo_provisional($tipo_documento)
    {
        $tipo_documento_normalizado = $this->normalizar_texto_liquidacion($tipo_documento);

        return (strpos($tipo_documento_normalizado, 'PROVISIONAL') !== false);
    }

    private function obtener_categoria_liquidacion($tipo_pago, $estado_pago)
    {
        $tipo_pago_normalizado = $this->normalizar_texto_liquidacion($tipo_pago);
        $estado_pago_normalizado = $this->normalizar_texto_liquidacion($estado_pago);

        if ($tipo_pago_normalizado == '' || $estado_pago_normalizado == 'ANULADO') {
            return '';
        }

        if (strpos($tipo_pago_normalizado, 'CHEQUE') !== false) {
            return ($estado_pago_normalizado == 'PROGRAMADO') ? 'CHEQUE_POSFECHADO' : 'CHEQUE_VISTA';
        }

        if (strpos($tipo_pago_normalizado, 'ANTICIPO') !== false) {
            return 'ANTICIPO';
        }

        if (strpos($tipo_pago_normalizado, 'DESCUENTO') !== false) {
            return 'DESCUENTO';
        }

        if (strpos($tipo_pago_normalizado, 'DEVOLUCION') !== false) {
            return 'DEVOLUCION';
        }

        if (
            strpos($tipo_pago_normalizado, 'TRANSFER') !== false ||
            strpos($tipo_pago_normalizado, 'DEPOSITO') !== false ||
            strpos($tipo_pago_normalizado, 'EFECTIVO') !== false ||
            strpos($tipo_pago_normalizado, 'CONTADO') !== false ||
            strpos($tipo_pago_normalizado, 'MIXTO') !== false
        ) {
            return ($estado_pago_normalizado == 'EJECUTADO') ? 'DEPOSITO' : '';
        }

        return '';
    }

    private function normalizar_texto_liquidacion($texto)
    {
        $texto = strtoupper(trim((string)$texto));
        $texto = str_replace(['Á', 'É', 'Í', 'Ó', 'Ú'], ['A', 'E', 'I', 'O', 'U'], $texto);

        return $texto;
    }

    private function formatear_moneda($monto)
    {
        return number_format((float)$monto, 2, '.', ',');
    }

    private function formatear_fecha_texto($fecha)
    {
        if (strtotime($fecha) === false) {
            return '';
        }

        return date('d/m/Y', strtotime($fecha));
    }

}
