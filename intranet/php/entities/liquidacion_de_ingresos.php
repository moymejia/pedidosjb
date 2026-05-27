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
                IFNULL(nombre_cliente, '') AS cliente,
                IFNULL(tipo_pago, '') AS tipo_pago,
                IFNULL(estado_pago_individual, '') AS estado_pago_individual,
                                IFNULL(monto_flete, 0) AS monto_flete,
                IFNULL(monto_pago, 0) AS monto_pago
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
                DATE_FORMAT(fecha_pago, '%d/%m/%Y') AS fecha_pago,
                IFNULL(correlativo_documento, '') AS numero_documento,
                IFNULL(nombre_cliente, '') AS cliente,
                IFNULL(tipo_pago, '') AS tipo_pago,
                IFNULL(monto_pago, 0) AS monto_pago
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
                IFNULL(nombre_cliente, '') AS cliente,
                IFNULL(tipo_pago, '') AS tipo_pago,
                IFNULL(monto_pago, 0) AS monto_pago,
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
            'total_recibos_provisionales'            => '0.00',
            'total_cheques_posfecha'                 => '0.00',
            'total_cobrado'                          => '0.00',
            'detalle_total_flete'                    => '0.00',
            'detalle_total_deposito'                 => '0.00',
            'detalle_total_cheque_vista'             => '0.00',
            'detalle_total_cheque_posfechado'        => '0.00',
            'detalle_total_general'                  => '0.00',
            'total_programados'                      => '0.00',
            'total_recuperacion'                     => '0.00'
        ];

        $TOTALES_DETALLE = [
            'flete' => 0,
            'deposito' => 0,
            'cheque_vista' => 0,
            'cheque_posfechado' => 0
        ];

        $RECIBOS_ORDENABLES = [];
        $DESPACHOS_FLETE = [];
        $filas_ejecutados = '';
        while ($row = mysql::getrowresult($sql_ejecutados)) {
            $monto = (float)$row['monto_pago'];
            $tipo_pago = strtoupper(trim($row['tipo_pago']));
            $iddespacho = (int)$row['iddespacho'];

            $valor_flete = '';
            $valor_deposito = '';
            $valor_cheque_vista = '';
            $valor_cheque_posfechado = '';

            // El flete se toma del despacho y se aplica una sola vez por iddespacho.
            if (!isset($DESPACHOS_FLETE[$iddespacho])) {
                $monto_flete = (float)$row['monto_flete'];
                if ($monto_flete > 0) {
                    $valor_flete = $this->formatear_moneda($monto_flete);
                    $TOTALES_DETALLE['flete'] += $monto_flete;
                }
                $DESPACHOS_FLETE[$iddespacho] = true;
            }

            $estado_pago = strtoupper(trim($row['estado_pago_individual']));

            if ($estado_pago == 'PROGRAMADO') {
                $valor_cheque_posfechado = $this->formatear_moneda($monto);
                $TOTALES_DETALLE['cheque_posfechado'] += $monto;
            } elseif (strpos($tipo_pago, 'CHEQUE') !== false) {
                $valor_cheque_vista = $this->formatear_moneda($monto);
                $TOTALES_DETALLE['cheque_vista'] += $monto;
            } else {
                $valor_deposito = $this->formatear_moneda($monto);
                $TOTALES_DETALLE['deposito'] += $monto;
            }

            $numero_documento = trim((string)$row['numero_documento']);
            if ($numero_documento != '') {
                $RECIBOS_ORDENABLES[] = $numero_documento;
            }

            $filas_ejecutados .= '<tr>';
            $filas_ejecutados .= '<td class="text-center">' . htmlspecialchars($row['fecha_pago'], ENT_QUOTES, 'UTF-8') . '</td>';
            $filas_ejecutados .= '<td class="text-center">' . htmlspecialchars($row['numero_documento'], ENT_QUOTES, 'UTF-8') . '</td>';
            $filas_ejecutados .= '<td>' . htmlspecialchars($row['cliente'], ENT_QUOTES, 'UTF-8') . '</td>';
            $filas_ejecutados .= '<td class="text-right">' . (($valor_flete != '') ? ('Q ' . $valor_flete) : '') . '</td>';
            $filas_ejecutados .= '<td class="text-right">' . (($valor_deposito != '') ? ('Q ' . $valor_deposito) : '') . '</td>';
            $filas_ejecutados .= '<td class="text-right">' . (($valor_cheque_vista != '') ? ('Q ' . $valor_cheque_vista) : '') . '</td>';
            $filas_ejecutados .= '<td class="text-right">' . (($valor_cheque_posfechado != '') ? ('Q ' . $valor_cheque_posfechado) : '') . '</td>';
            $filas_ejecutados .= '<td class="text-right">Q ' . $this->formatear_moneda($monto) . '</td>';
            $filas_ejecutados .= '</tr>';
        }

        if ($filas_ejecutados == '') {
            $filas_ejecutados = '<tr><td colspan="8" class="text-center">No hay documentos ejecutados para el rango seleccionado.</td></tr>';
        }

        $filas_programados = '';
        $total_programados = 0;
        while ($row = mysql::getrowresult($sql_programados)) {
            $monto = (float)$row['monto_pago'];
            $total_programados += $monto;

            $filas_programados .= '<tr>';
            $filas_programados .= '<td class="text-center">' . (int)$row['iddespacho'] . '</td>';
            $filas_programados .= '<td>' . htmlspecialchars($row['cliente'], ENT_QUOTES, 'UTF-8') . '</td>';
            $filas_programados .= '<td class="text-center">' . htmlspecialchars($row['numero_documento'], ENT_QUOTES, 'UTF-8') . '</td>';
            $filas_programados .= '<td class="text-center">' . htmlspecialchars($row['tipo_pago'], ENT_QUOTES, 'UTF-8') . '</td>';
            $filas_programados .= '<td class="text-center">' . htmlspecialchars($row['fecha_pago'], ENT_QUOTES, 'UTF-8') . '</td>';
            $filas_programados .= '<td class="text-right">Q ' . $this->formatear_moneda($monto) . '</td>';
            $filas_programados .= '</tr>';
        }

        if ($filas_programados == '') {
            $filas_programados = '<tr><td colspan="6" class="text-center">No hay documentos programados para el rango seleccionado.</td></tr>';
        }

        $filas_recuperacion = '';
        $total_recuperacion = 0;
        while ($row = mysql::getrowresult($sql_recuperacion)) {
            $monto = (float)$row['monto_pago'];
            $total_recuperacion += $monto;

            $estado = strtoupper(trim($row['estado_pago_individual']));
            $valor_deposito = ($estado == 'EJECUTADO') ? ('Q ' . $this->formatear_moneda($monto)) : '';
            $valor_cheque = ($estado == 'PROGRAMADO') ? ('Q ' . $this->formatear_moneda($monto)) : '';

            $filas_recuperacion .= '<tr>';
            $filas_recuperacion .= '<td class="text-center">' . htmlspecialchars($row['fecha_pago'], ENT_QUOTES, 'UTF-8') . '</td>';
            $filas_recuperacion .= '<td class="text-center">' . htmlspecialchars($row['numero_documento'], ENT_QUOTES, 'UTF-8') . '</td>';
            $filas_recuperacion .= '<td>' . htmlspecialchars($row['cliente'], ENT_QUOTES, 'UTF-8') . '</td>';
            $filas_recuperacion .= '<td class="text-center">' . htmlspecialchars($row['numero_documento'], ENT_QUOTES, 'UTF-8') . '</td>';
            $filas_recuperacion .= '<td class="text-right">' . $valor_deposito . '</td>';
            $filas_recuperacion .= '<td class="text-right">' . $valor_cheque . '</td>';
            $filas_recuperacion .= '<td class="text-center">' . htmlspecialchars($row['numero_documento'], ENT_QUOTES, 'UTF-8') . '</td>';
            $filas_recuperacion .= '<td class="text-right">Q ' . $this->formatear_moneda($monto) . '</td>';
            $filas_recuperacion .= '</tr>';
        }

        if ($filas_recuperacion == '') {
            $filas_recuperacion = '<tr><td colspan="8" class="text-center">No hay documentos de recuperacion para el rango seleccionado.</td></tr>';
        }

        $DATOS['tabla_documentos_ejecutados_rows'] = $filas_ejecutados;
        $DATOS['tabla_documentos_programados_rows'] = $filas_programados;
        $DATOS['tabla_documentos_recuperacion_rows'] = $filas_recuperacion;

        $total_cobrado =
            $TOTALES_DETALLE['flete'] +
            $TOTALES_DETALLE['deposito'] +
            $TOTALES_DETALLE['cheque_vista'] +
            $total_programados;

        $DATOS['detalle_total_flete'] = $this->formatear_moneda($TOTALES_DETALLE['flete']);
        $DATOS['detalle_total_deposito'] = $this->formatear_moneda($TOTALES_DETALLE['deposito']);
        $DATOS['detalle_total_cheque_vista'] = $this->formatear_moneda($TOTALES_DETALLE['cheque_vista']);
        $DATOS['detalle_total_cheque_posfechado'] = $this->formatear_moneda($TOTALES_DETALLE['cheque_posfechado']);
        $DATOS['detalle_total_general'] = $this->formatear_moneda($total_cobrado);

        $DATOS['total_fletes'] = $this->formatear_moneda($TOTALES_DETALLE['flete']);
        $DATOS['total_depositos'] = $this->formatear_moneda($TOTALES_DETALLE['deposito']);
        $DATOS['total_recibos_provisionales'] = $this->formatear_moneda($TOTALES_DETALLE['cheque_vista']);
        $DATOS['total_cheques_posfecha'] = $this->formatear_moneda($TOTALES_DETALLE['cheque_posfechado']);
        $DATOS['total_cobrado'] = $this->formatear_moneda($total_cobrado);

        if (count($RECIBOS_ORDENABLES) > 0) {
            usort($RECIBOS_ORDENABLES, [$this, 'comparar_recibos']);
            $DATOS['recibos_del'] = (string)$RECIBOS_ORDENABLES[0];
            $DATOS['recibos_al'] = (string)$RECIBOS_ORDENABLES[count($RECIBOS_ORDENABLES) - 1];
        }

        $DATOS['total_programados'] = $this->formatear_moneda($total_programados);
        $DATOS['total_recuperacion'] = $this->formatear_moneda($total_recuperacion);

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
