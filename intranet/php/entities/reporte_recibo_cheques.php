<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/utils.php';
require_once '../entities/cliente.php';

class reporte_recibo_cheques extends table
{
    use utils;

    private $ACCIONES = [];
    public $last_error = '';

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'reporte_recibo_cheques');

        $this->ACCIONES['opcion_reporte_recibo_cheques'] = 'Opcion_reporte_recibo_cheques';

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'generar_reporte_recibo_cheques') {
                if (table::validate_parameter_existence(['fecha_desde', 'fecha_hasta'], $PARAMETROS, false)) {
                    if ($resultado = $this->generar_reporte_recibo_cheques($PARAMETROS)) {
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
        $_SECURITY = new security($this->ACCIONES['opcion_reporte_recibo_cheques']);
        $usuario_actual = $_SECURITY->get_actual_user();
        $_SECURITY->registrar_bitacora($this->ACCIONES['opcion_reporte_recibo_cheques'], 'cargar_opcion', $usuario_actual);

        $DATA = [];
        $_CLIENTE = new cliente();
        $DATA['clientes_activos'] = $_CLIENTE->option_activas();

        $_HTML = new html('reporte_recibo_cheques', $DATA);

        return $_HTML->get_html();
    }

    public function generar_reporte_recibo_cheques($PARAMETROS = [])
    {
        $_SECURITY = new security($this->ACCIONES['opcion_reporte_recibo_cheques']);
        $_SECURITY->get_actual_user();

        $fecha_desde = isset($PARAMETROS['fecha_desde']) ? trim($PARAMETROS['fecha_desde']) : '';
        $fecha_hasta = isset($PARAMETROS['fecha_hasta']) ? trim($PARAMETROS['fecha_hasta']) : '';
        $idcliente = isset($PARAMETROS['idcliente']) ? trim($PARAMETROS['idcliente']) : '';

        if (!$this->validar_rango_fechas($fecha_desde, $fecha_hasta)) {
            return false;
        }

        $fecha_desde_sql = addslashes($fecha_desde);
        $fecha_hasta_sql = addslashes($fecha_hasta);
        $where_cliente = ($idcliente != '') ? " AND idcliente = '" . (int)$idcliente . "'" : '';
        $cliente_reporte = $this->obtener_nombre_cliente_reporte($idcliente);

        $sql = mysql::getresult("SELECT
                cheque_no,
                banco,
                DATE_FORMAT(fecha_cheque_raw, '%d/%m/%Y') AS fecha_cheque,
                fecha_cheque_raw AS fecha_orden,
                recibo_caja,
                tipo_recibo,
                estado_cheque,
                valor_cheque
            FROM view_reporte_recibo_cheques
            WHERE DATE(fecha_cheque_raw) >= '$fecha_desde_sql'
                AND DATE(fecha_cheque_raw) <= '$fecha_hasta_sql'
                $where_cliente
            ORDER BY recibo_caja ASC, tipo_recibo ASC, fecha_orden ASC, cheque_no ASC");

        if (!$sql) {
            $this->last_error = 'No fue posible obtener recibos con cheques.';
            utils::report_error(bd_error, ['fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta, 'idcliente' => $idcliente], $this->last_error);
            return false;
        }

        $GRUPOS_RECIBO = [];
        while ($row = mysql::getrowresult($sql)) {
            $recibo_caja = trim((string)$row['recibo_caja']);
            if ($recibo_caja == '') {
                $recibo_caja = 'SIN RECIBO';
            }

            $tipo_recibo = $this->formatear_tipo_recibo($row['tipo_recibo']);
            $grupo_recibo = $tipo_recibo . '|' . $recibo_caja;

            if (!isset($GRUPOS_RECIBO[$grupo_recibo])) {
                $GRUPOS_RECIBO[$grupo_recibo] = [
                    'recibo_caja' => $recibo_caja,
                    'tipo_recibo' => $tipo_recibo,
                    'total_recibo' => 0,
                    'cheques' => []
                ];
            }

            $valor_pago_recibo = (float)$row['valor_cheque'];
            $GRUPOS_RECIBO[$grupo_recibo]['total_recibo'] += $valor_pago_recibo;
            $estado_cheque = $this->formatear_estado_cheque($row['estado_cheque']);
            $cheque_no = trim((string)$row['cheque_no']);

            $GRUPOS_RECIBO[$grupo_recibo]['cheques'][] = [
                'cheque_no' => $cheque_no,
                'banco' => $row['banco'],
                'fecha_cheque' => $row['fecha_cheque'],
                'estado_cheque' => $estado_cheque,
                'valor_cheque' => $valor_pago_recibo
            ];
        }

        $filas = '';
        $total_general = 0;
        foreach ($GRUPOS_RECIBO as $GRUPO) {
            $cantidad_cheques = count($GRUPO['cheques']);
            $total_recibo = (float)$GRUPO['total_recibo'];
            $total_general += $total_recibo;

            foreach ($GRUPO['cheques'] as $indice => $CHEQUE) {
                $filas .= '<tr>';
                $filas .= '<td class="text-center">' . htmlspecialchars($CHEQUE['cheque_no'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas .= '<td class="text-center">' . htmlspecialchars($CHEQUE['banco'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas .= '<td class="text-center">' . htmlspecialchars($CHEQUE['fecha_cheque'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas .= '<td class="text-center">' . htmlspecialchars($GRUPO['recibo_caja'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas .= '<td class="text-center">' . htmlspecialchars($GRUPO['tipo_recibo'], ENT_QUOTES, 'UTF-8') . '</td>';
                $filas .= '<td class="text-center">' . htmlspecialchars($CHEQUE['estado_cheque'], ENT_QUOTES, 'UTF-8') . '</td>';

                if ($indice == 0) {
                    $filas .= '<td class="recibo-total" rowspan="' . $cantidad_cheques . '"><span class="amount-box"><span>Q</span><span>' . $this->formatear_moneda($total_recibo) . '</span></span></td>';
                }

                $filas .= '<td class="amount"><span class="amount-box"><span>Q</span><span>' . $this->formatear_moneda($CHEQUE['valor_cheque']) . '</span></span></td>';
                $filas .= '</tr>';
            }
        }

        if ($filas == '') {
            $filas = '<tr><td colspan="8" class="text-center">No hay recibos con cheques cobrados o por cobrar para el rango seleccionado.</td></tr>';
        } else {
            $filas .= '<tr class="fila-total">';
            $filas .= '<td colspan="6" class="text-right">TOTAL</td>';
            $filas .= '<td colspan="2" class="amount"><span class="amount-box"><span>Q</span><span>' . $this->formatear_moneda($total_general) . '</span></span></td>';
            $filas .= '</tr>';
        }

        $DATOS = [
            'cliente_reporte' => htmlspecialchars($cliente_reporte, ENT_QUOTES, 'UTF-8'),
            'fecha_desde' => $this->formatear_fecha_texto($fecha_desde),
            'fecha_hasta' => $this->formatear_fecha_texto($fecha_hasta),
            'filas_cheques' => $filas
        ];

        $_HTML = new html('template_reporte_recibo_cheques', $DATOS);
        $contenido = $_HTML->get_html();

        $usuario_actual = $_SECURITY->get_actual_user();
        $_SECURITY->registrar_bitacora($this->ACCIONES['opcion_reporte_recibo_cheques'], 'BUSQUEDA', $usuario_actual);

        return $contenido;
    }


    private function formatear_tipo_recibo($tipo_recibo)
    {
        $tipo_normalizado = $this->normalizar_texto($tipo_recibo);

        if (strpos($tipo_normalizado, 'PROVISIONAL') !== false) {
            return 'Provisional';
        }

        if (strpos($tipo_normalizado, 'RECIBO') !== false) {
            return 'Recibo de caja';
        }

        $tipo_recibo = trim((string)$tipo_recibo);
        return ($tipo_recibo != '') ? $tipo_recibo : 'Sin tipo';
    }

    private function formatear_estado_cheque($estado_cheque)
    {
        $estado_normalizado = $this->normalizar_texto($estado_cheque);

        if ($estado_normalizado == 'EJECUTADO') {
            return 'Cobrado';
        }

        if ($estado_normalizado == 'PROGRAMADO') {
            return 'Por cobrar';
        }

        return trim((string)$estado_cheque);
    }

    private function normalizar_texto($texto)
    {
        $texto = strtoupper(trim((string)$texto));
        $texto = str_replace(['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ'], ['A', 'E', 'I', 'O', 'U', 'U', 'N'], $texto);

        return $texto;
    }

    private function obtener_nombre_cliente_reporte($idcliente)
    {
        if (trim($idcliente) == '') {
            return 'Todos';
        }

        $idcliente = (int)$idcliente;
        $cliente = mysql::getvalue("SELECT CONCAT(codigo, ' - ', nombre) AS cliente FROM cliente WHERE idcliente = '$idcliente' LIMIT 1", 'cliente');

        return ($cliente) ? $cliente : 'Cliente seleccionado';
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

    private function formatear_fecha_texto($fecha)
    {
        if (strtotime($fecha) === false) {
            return '';
        }

        return date('d/m/Y', strtotime($fecha));
    }

    private function formatear_moneda($monto)
    {
        return number_format((float)$monto, 2, '.', ',');
    }
}
