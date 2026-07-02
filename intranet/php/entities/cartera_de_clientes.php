<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/utils.php';
require_once '../entities/cliente.php';
require_once '../entities/usuario.php';

class cartera_de_clientes extends table
{
    use utils;

    public $last_error = '';
    private $ACCIONES = [];

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'cartera_de_clientes');

        $this->ACCIONES['opcion_cartera_de_clientes'] = 'Opcion_cartera_de_clientes';

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'generar_reporte_cartera_de_clientes') {
                if ($resultado = $this->generar_reporte_cartera_de_clientes($PARAMETROS)) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }
        }
    }

    public function cargar_opcion()
    {
        $_SECURITY = new security($this->ACCIONES['opcion_cartera_de_clientes']);
        $_SECURITY->get_actual_user();

        $DATA = [];
        $DATA['vendedores_activos'] = (new usuario())->options_activas();
        $DATA['clientes_activos']   = (new cliente())->option_activas();

        $_HTML = new html('cartera_de_clientes', $DATA);
        return $_HTML->get_html();
    }

    public function generar_reporte_cartera_de_clientes($PARAMETROS = [])
    {
        $_SECURITY = new security($this->ACCIONES['opcion_cartera_de_clientes']);
        $_SECURITY->get_actual_user();

        $idvendedor = isset($PARAMETROS['idvendedor']) ? trim($PARAMETROS['idvendedor']) : '';
        $idcliente = isset($PARAMETROS['idcliente']) ? trim($PARAMETROS['idcliente']) : '';
        $fecha_desde = isset($PARAMETROS['fecha_desde']) ? trim($PARAMETROS['fecha_desde']) : '';
        $fecha_hasta = isset($PARAMETROS['fecha_hasta']) ? trim($PARAMETROS['fecha_hasta']) : '';
        $idmostrar_cheques = isset($PARAMETROS['idmostrar_cheques']) ? strtoupper(trim($PARAMETROS['idmostrar_cheques'])) : 'NO';
        $mostrar_cheques = ($idmostrar_cheques == 'SI');

        if (!$this->validar_rango_fechas_opcional($fecha_desde, $fecha_hasta)) {
            return false;
        }

        $where_vendedor = '';
        if ($idvendedor != '') {
            $where_vendedor = " AND usuario_vendedor = '" . addslashes($idvendedor) . "'";
        }

        $where_cliente = '';
        if ($idcliente != '') {
            $where_cliente = " AND idcliente = '" . (int)$idcliente . "'";
        }

        $where_fechas = '';
        if ($fecha_desde != '' && $fecha_hasta != '') {
            $where_fechas = " AND DATE(fecha_factura) >= '" . addslashes($fecha_desde) . "' AND DATE(fecha_factura) <= '" . addslashes($fecha_hasta) . "'";
        } else if ($fecha_desde != '') {
            $where_fechas = " AND DATE(fecha_factura) >= '" . addslashes($fecha_desde) . "'";
        } else if ($fecha_hasta != '') {
            $where_fechas = " AND DATE(fecha_factura) <= '" . addslashes($fecha_hasta) . "'";
        }

        $sql = mysql::getresult("SELECT
                IFNULL(nombre_vendedor, usuario_vendedor) AS vendedor,
                IFNULL(usuario_vendedor, '') AS usuario_vendedor,
                IFNULL(codigo_cliente, '') AS codigo_cliente,
                IFNULL(nombre_cliente, '') AS nombre_cliente,
                IFNULL(numero_factura, '') AS numero_factura,
                DATE_FORMAT(fecha_factura, '%d/%m/%Y') AS fecha_factura,
                fecha_factura AS fecha_factura_orden,
                IFNULL(dias_transcurridos, 0) AS dias_transcurridos,
                IFNULL(saldo_0_30, 0) AS saldo_0_30,
                IFNULL(saldo_31_60, 0) AS saldo_31_60,
                IFNULL(saldo_61_90, 0) AS saldo_61_90,
                IFNULL(saldo_91_mas, 0) AS saldo_91_mas,
                IFNULL(saldo_cartera, 0) AS saldo_total,
                IFNULL(monto_no_ejecutado, 0) AS monto_no_ejecutado
            FROM view_cartera_de_clientes
            WHERE 1 = 1
                $where_vendedor
                $where_cliente
                $where_fechas
                AND saldo_cartera > 0
            ORDER BY vendedor ASC, codigo_cliente ASC, fecha_factura_orden ASC, numero_factura ASC");

        if (!$sql) {
            $this->last_error = 'No fue posible obtener datos de cartera de clientes.';
            utils::report_error(bd_error, [$where_vendedor, $where_cliente, $where_fechas], $this->last_error);
            return false;
        }

        $filas = '';
        $vendedor_actual = '';

        $TOTALES_RUTA = [
            'saldo_0_30' => 0,
            'saldo_31_60' => 0,
            'saldo_61_90' => 0,
            'saldo_91_mas' => 0,
            'saldo_total' => 0,
            'monto_no_ejecutado' => 0
        ];

        $cantidad_registros = 0;
        while ($row = mysql::getrowresult($sql)) {
            $cantidad_registros++;

            $vendedor_fila = trim((string)$row['vendedor']);
            if ($vendedor_fila == '') {
                $vendedor_fila = 'SIN VENDEDOR';
            }

            if ($vendedor_actual != $vendedor_fila) {
                if ($cantidad_registros > 1) {
                    $filas .= $this->fila_total_ruta($TOTALES_RUTA, $mostrar_cheques);
                    $filas .= $this->fila_porcentaje_ruta($TOTALES_RUTA, $mostrar_cheques);
                }

                if ($vendedor_actual != '') {
                    $filas .= '<tr class="fila-espaciador"><td colspan="' . ($mostrar_cheques ? '11' : '10') . '"></td></tr>';
                }

                $vendedor_actual = $vendedor_fila;
                $TOTALES_RUTA = [
                    'saldo_0_30' => 0,
                    'saldo_31_60' => 0,
                    'saldo_61_90' => 0,
                    'saldo_91_mas' => 0,
                    'saldo_total' => 0,
                    'monto_no_ejecutado' => 0
                ];

                $filas .= '<tr class="fila-vendedor">';
                $filas .= '<td colspan="' . ($mostrar_cheques ? '11' : '10') . '">Vendedor: ' . htmlspecialchars($vendedor_actual, ENT_QUOTES, 'UTF-8') . '</td>';
                $filas .= '</tr>';
            }

            $saldo_0_30 = (float)$row['saldo_0_30'];
            $saldo_31_60 = (float)$row['saldo_31_60'];
            $saldo_61_90 = (float)$row['saldo_61_90'];
            $saldo_91_mas = (float)$row['saldo_91_mas'];
            $saldo_total = (float)$row['saldo_total'];
            $monto_no_ejecutado = (float)$row['monto_no_ejecutado'];

            $TOTALES_RUTA['saldo_0_30'] += $saldo_0_30;
            $TOTALES_RUTA['saldo_31_60'] += $saldo_31_60;
            $TOTALES_RUTA['saldo_61_90'] += $saldo_61_90;
            $TOTALES_RUTA['saldo_91_mas'] += $saldo_91_mas;
            $TOTALES_RUTA['saldo_total'] += $saldo_total;
            $TOTALES_RUTA['monto_no_ejecutado'] += $monto_no_ejecutado;

            $filas .= '<tr class="fila-detalle">';
            $filas .= '<td class="text-center">' . htmlspecialchars($row['codigo_cliente'], ENT_QUOTES, 'UTF-8') . '</td>';
            $filas .= '<td>' . htmlspecialchars($row['nombre_cliente'], ENT_QUOTES, 'UTF-8') . '</td>';
            $filas .= '<td class="text-center">' . htmlspecialchars($row['fecha_factura'], ENT_QUOTES, 'UTF-8') . '</td>';
            $filas .= '<td class="text-center">' . htmlspecialchars($row['numero_factura'], ENT_QUOTES, 'UTF-8') . '</td>';
            $filas .= '<td class="text-center">' . (int)$row['dias_transcurridos'] . '</td>';
            $filas .= '<td class="text-right">' . $this->monto_columna($saldo_0_30) . '</td>';
            $filas .= '<td class="text-right">' . $this->monto_columna($saldo_31_60) . '</td>';
            $filas .= '<td class="text-right">' . $this->monto_columna($saldo_61_90) . '</td>';
            $filas .= '<td class="text-right">' . $this->monto_columna($saldo_91_mas) . '</td>';
            $filas .= '<td class="text-right">' . $this->monto_columna($saldo_total) . '</td>';
            if ($mostrar_cheques) {
                $filas .= '<td class="text-right">' . $this->monto_columna($monto_no_ejecutado) . '</td>';
            }
            $filas .= '</tr>';
        }

        if ($cantidad_registros > 0) {
            $filas .= $this->fila_total_ruta($TOTALES_RUTA, $mostrar_cheques);
            $filas .= $this->fila_porcentaje_ruta($TOTALES_RUTA, $mostrar_cheques);
        } else {
            $filas = '<tr><td colspan="' . ($mostrar_cheques ? '11' : '10') . '" class="text-center">No hay facturas pendientes para los filtros seleccionados.</td></tr>';
        }

        $fecha_corte = ($fecha_hasta != '') ? $this->formatear_fecha_texto($fecha_hasta) : date('d/m/Y');

        $DATOS = [
            'fecha_reporte' => date('d/m/Y'),
            'fecha_corte' => $fecha_corte,
            'columna_cheques_header' => $mostrar_cheques ? '<th style="width: 10%;">Cheques</th>' : '',
            'filas_detalle' => $filas
        ];

        $_HTML = new html('template_cartera_de_clientes', $DATOS);
        $contenido = $_HTML->get_html();

        $usuario_actual = $_SECURITY->get_actual_user();
        $_SECURITY->registrar_bitacora($this->ACCIONES['opcion_cartera_de_clientes'], 'BUSQUEDA', $usuario_actual);

        return $contenido;
    }

    private function fila_total_ruta($TOTALES_RUTA, $mostrar_cheques = true)
    {
        $fila = '<tr class="fila-total-ruta">';
        $fila .= '<td colspan="5" class="text-right">TOTALES</td>';
        $fila .= '<td class="text-right caja-total caja-l">' . $this->formatear_moneda($TOTALES_RUTA['saldo_0_30']) . '</td>';
        $fila .= '<td class="text-right caja-total">' . $this->formatear_moneda($TOTALES_RUTA['saldo_31_60']) . '</td>';
        $fila .= '<td class="text-right caja-total">' . $this->formatear_moneda($TOTALES_RUTA['saldo_61_90']) . '</td>';
        $fila .= '<td class="text-right caja-total">' . $this->formatear_moneda($TOTALES_RUTA['saldo_91_mas']) . '</td>';
        $fila .= '<td class="text-right caja-total' . ($mostrar_cheques ? '' : ' caja-r') . '">' . $this->formatear_moneda($TOTALES_RUTA['saldo_total']) . '</td>';
        if ($mostrar_cheques) {
            $fila .= '<td class="text-right caja-total caja-r">' . $this->formatear_moneda($TOTALES_RUTA['monto_no_ejecutado']) . '</td>';
        }
        $fila .= '</tr>';

        return $fila;
    }

    private function fila_porcentaje_ruta($TOTALES_RUTA, $mostrar_cheques = true)
    {
        $base = (float)$TOTALES_RUTA['saldo_total'];
        $porcentaje_0_30 = ($base > 0) ? (($TOTALES_RUTA['saldo_0_30'] / $base) * 100) : 0;
        $porcentaje_31_60 = ($base > 0) ? (($TOTALES_RUTA['saldo_31_60'] / $base) * 100) : 0;
        $porcentaje_61_90 = ($base > 0) ? (($TOTALES_RUTA['saldo_61_90'] / $base) * 100) : 0;
        $porcentaje_91_mas = ($base > 0) ? (($TOTALES_RUTA['saldo_91_mas'] / $base) * 100) : 0;

        $fila = '<tr class="fila-porcentaje-ruta">';
        $fila .= '<td colspan="5"></td>';
        $fila .= '<td class="text-center caja-total-b caja-l">' . $this->formatear_porcentaje($porcentaje_0_30) . '</td>';
        $fila .= '<td class="text-center caja-total-b">' . $this->formatear_porcentaje($porcentaje_31_60) . '</td>';
        $fila .= '<td class="text-center caja-total-b">' . $this->formatear_porcentaje($porcentaje_61_90) . '</td>';
        $fila .= '<td class="text-center caja-total-b">' . $this->formatear_porcentaje($porcentaje_91_mas) . '</td>';
        $fila .= '<td class="text-center caja-total-b' . ($mostrar_cheques ? '' : ' caja-r') . '"></td>';
        if ($mostrar_cheques) {
            $fila .= '<td class="text-center caja-total-b caja-r"></td>';
        }
        $fila .= '</tr>';

        return $fila;
    }

    private function validar_rango_fechas_opcional($fecha_desde, $fecha_hasta)
    {
        if ($fecha_desde != '' && strtotime($fecha_desde) === false) {
            $this->last_error = 'La fecha desde no es valida.';
            utils::report_error(validation_error, $fecha_desde, $this->last_error);
            return false;
        }

        if ($fecha_hasta != '' && strtotime($fecha_hasta) === false) {
            $this->last_error = 'La fecha hasta no es valida.';
            utils::report_error(validation_error, $fecha_hasta, $this->last_error);
            return false;
        }

        if ($fecha_desde != '' && $fecha_hasta != '' && strtotime($fecha_desde) > strtotime($fecha_hasta)) {
            $this->last_error = 'La fecha desde no puede ser mayor que la fecha hasta.';
            utils::report_error(validation_error, ['fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta], $this->last_error);
            return false;
        }

        return true;
    }

    private function monto_columna($monto)
    {
        if ((float)$monto <= 0) {
            return '';
        }

        return $this->formatear_moneda($monto);
    }

    private function formatear_moneda($monto)
    {
        return number_format((float)$monto, 2, '.', ',');
    }

    private function formatear_porcentaje($valor)
    {
        return number_format((float)$valor, 2, '.', ',') . '%';
    }

    private function formatear_fecha_texto($fecha)
    {
        if (strtotime($fecha) === false) {
            return '';
        }

        return date('d/m/Y', strtotime($fecha));
    }
}
