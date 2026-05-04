<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/objects.php';
require_once '../wisetech/utils.php';
require_once('../entities/temporada.php');
require_once('../entities/cliente.php');
require_once('../entities/marca.php');

class ventas_temporada extends table
{

    use utils;
    private $idventas_temporada;
    public $last_error = '';
    private $ACCIONES   = [];

    public function __construct($PARAMETROS = null)
    {

        parent::__construct(prefijo . '_pedidos', 'ventas_temporada');

        $this->ACCIONES['opcion_ventas_temporada'] = "Opcion_ventas_temporada";

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'reporte_ventas_temporada') {
                if ($resultado = $this->reporte_ventas_temporada(
                    $PARAMETROS['idtemporada'],
                    $PARAMETROS['idcliente'],
                    $PARAMETROS['idmarca'],
                    (isset($PARAMETROS['fecha_desde']) ? $PARAMETROS['fecha_desde'] : ''),
                    (isset($PARAMETROS['fecha_hasta']) ? $PARAMETROS['fecha_hasta'] : '')
                )) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'options_clientes_temporada_marca') {
                if ($resultado = $this->options_clientes_temporada_marca($PARAMETROS['idtemporada'], $PARAMETROS['idmarca'])) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'options_marcas_temporada_cliente') {
                if ($resultado = $this->options_marcas_temporada_cliente($PARAMETROS['idtemporada'], $PARAMETROS['idcliente'])) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }
        }

    }

    public function cargar_opcion()
    {
        $DATA                       = [];
        $DATA['temporadas_activas'] = (new temporada())->option_activos();
        $html                = new html('ventas_temporada', $DATA);

        return $html->get_html();
    }

    public function options_clientes_temporada_marca($idtemporada, $idmarca)
    {
        $where_marca = ($idmarca > 0) ? "AND idmarca = '$idmarca'" : '';

        return mysql::getoptions("SELECT DISTINCT idcliente AS id, nombre_cliente AS descripcion
            FROM view_ventas_temporada
            WHERE idtemporada = '$idtemporada' $where_marca
            ORDER BY nombre_cliente ASC");
    }

    public function options_marcas_temporada_cliente($idtemporada, $idcliente)
    {   

        $idcliente_condicion = ($idcliente > 0) ? "AND idcliente = '$idcliente'" : '';

        return mysql::getoptions("SELECT DISTINCT idmarca AS id, nombre_marca AS descripcion
            FROM view_ventas_temporada
            WHERE idtemporada = '$idtemporada' $idcliente_condicion
            ORDER BY nombre_marca ASC");
    }

    public function reporte_ventas_temporada($idtemporada, $idcliente, $idmarca, $fecha_desde = '', $fecha_hasta = '')
    {
        $security = new security($this->ACCIONES['opcion_ventas_temporada']);

        if ($idtemporada == '') {
            $this->last_error = 'Debe seleccionar una temporada';
            utils::report_error(validation_error, $idtemporada, $this->last_error);
            return false;
        }

        if ($fecha_desde != '' && strtotime($fecha_desde) === false) {
            $this->last_error = 'La fecha desde no es valida';
            utils::report_error(validation_error, $fecha_desde, $this->last_error);
            return false;
        }

        if ($fecha_hasta != '' && strtotime($fecha_hasta) === false) {
            $this->last_error = 'La fecha hasta no es valida';
            utils::report_error(validation_error, $fecha_hasta, $this->last_error);
            return false;
        }

        if ($fecha_desde != '' && $fecha_hasta != '' && strtotime($fecha_desde) > strtotime($fecha_hasta)) {
            $this->last_error = 'La fecha desde no puede ser mayor que la fecha hasta';
            utils::report_error(validation_error, ['fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta], $this->last_error);
            return false;
        }

        $where_marca = ($idmarca > 0) ? "AND idmarca = '$idmarca'" : '';
        $where_cliente = ($idcliente > 0) ? "AND idcliente = '$idcliente'" : '';
        $where_fechas = '';
        if ($fecha_desde != '' && $fecha_hasta != '') {
            $where_fechas = "AND fecha_creacion >= '$fecha_desde 00:00:00' AND fecha_creacion <= '$fecha_hasta 23:59:59'";
        } else if ($fecha_desde != '') {
            $where_fechas = "AND fecha_creacion >= '$fecha_desde 00:00:00'";
        } else if ($fecha_hasta != '') {
            $where_fechas = "AND fecha_creacion <= '$fecha_hasta 23:59:59'";
        }

        $condicion_sql = " idtemporada = '$idtemporada' AND estado = 'CERRADO' $where_marca $where_cliente $where_fechas";

        $marca_seleccionada = '';
        if ($idmarca > 0) {
            $marca_seleccionada = mysql::getvalue("SELECT nombre FROM marca WHERE idmarca = '$idmarca'", 'nombre');
        }

        $texto_filtro_fecha = '';
        if ($fecha_desde != '' || $fecha_hasta != '') {
            $fecha_desde_texto = ($fecha_desde != '') ? date('d-m-Y', strtotime($fecha_desde)) : 'inicio';
            $fecha_hasta_texto = ($fecha_hasta != '') ? date('d-m-Y', strtotime($fecha_hasta)) : 'fin';
            $texto_filtro_fecha = " (Rango por fecha de creacion: $fecha_desde_texto a $fecha_hasta_texto)";
        }
        
        $sql = mysql::getresult("
            SELECT 
                CONCAT(
                    '<button class=\"btn btn-primary btn-sm\" onclick=\"abrir_pedido(', idpedido, ')\" style=\"cursor:pointer; margin-right:5px;\">',
                        '<i class=\"fas fa-eye\"></i>',
                    '</button>',
                    nopedido
                ) AS `No. Pedido`,
                nombre_cliente AS cliente,
                nombre_marca AS marca,
                cantidad_pares AS `cantidad de pares`,
                cantidad_modelos AS `cantidad de modelos`,
                CONCAT('Q ', FORMAT(monto_total, 2)) AS `monto total`
            FROM view_ventas_temporada 
            WHERE $condicion_sql
        ");

        $columnControl = true;
        $responsive    = true;
        $colReorder    = true;
        $select        = false;
        $buttons       = true;
        $paging        = true;
        $ordering      = true;
        $order         = true;
        $reset         = true;

        $alineacion = [
            'cantidad de pares' => 'center',
            'cantidad de modelos' => 'center',
            'total de pedidos' => 'center',
            'total de clientes' => 'center',
            'total de marcas' => 'center',
            'total de pares' => 'center',
            'total de modelos' => 'center',
            'monto total' => 'right'
        ];

        $titulo_detalle = 'Reporte de pedidos cerrados';
        if ($marca_seleccionada != '') {
            $titulo_detalle .= " - Marca: $marca_seleccionada";
        }
        $titulo_detalle .= $texto_filtro_fecha;

        $data_detalle  = " data-conf-columncontrol='" . ($columnControl ? "true" : "false") . "' ";
        $data_detalle .= " data-conf-rowgroup=''";
        $data_detalle .= " data-conf-titulotabla='" . htmlspecialchars($titulo_detalle, ENT_QUOTES, 'UTF-8') . "' ";
        $data_detalle .= " data-conf-filename='Ventas_temporada_detalle' ";
        $data_detalle .= " data-conf-responsive='"    . ($responsive    ? "true" : "false") . "' ";
        $data_detalle .= " data-conf-colreorder='"    . ($colReorder    ? "true" : "false") . "' ";
        $data_detalle .= " data-conf-select='"        . ($select        ? "true" : "false") . "' ";
        $data_detalle .= " data-conf-buttons='"       . ($buttons       ? "true" : "false") . "' ";
        $data_detalle .= " data-conf-paging='"        . ($paging        ? "true" : "false") . "' ";
        $data_detalle .= " data-conf-ordering='"      . ($ordering      ? "true" : "false") . "' ";
        $data_detalle .= " data-conf-noorder='"       . (!$order        ? "true" : "false") . "' ";
        $data_detalle .= " data-conf-reset='"         . ($reset         ? "true" : "false") . "' ";

        $contenido = '';
        $contenido .= $this->render_report_title($titulo_detalle);
        $contenido .= $this->render_datatable(
            $sql,
            'tabla_ventas_temporada_detalle',
            $data_detalle,
            $alineacion
        );
        $contenido .= "<br/>";


        $resumen = mysql::getresult("
                SELECT 'Cantidad total de pedidos' AS concepto, COUNT(DISTINCT idpedido) AS valor
                FROM view_ventas_temporada
                WHERE $condicion_sql
                UNION ALL
                SELECT 'Cantidad total de clientes', COUNT(DISTINCT idcliente)
                FROM view_ventas_temporada
                WHERE $condicion_sql
                UNION ALL
                SELECT 'Cantidad total de marcas', COUNT(DISTINCT idmarca)
                FROM view_ventas_temporada
                WHERE $condicion_sql
                UNION ALL
                SELECT 'Cantidad total de pares', SUM(cantidad_pares)
                FROM view_ventas_temporada
                WHERE $condicion_sql
                UNION ALL
                SELECT 'Cantidad total de modelos diferentes', SUM(cantidad_modelos)
                FROM view_ventas_temporada
                WHERE $condicion_sql
                UNION ALL
                SELECT 'Monto total', CONCAT('Q ', FORMAT(SUM(monto_total), 2))
                FROM view_ventas_temporada
                WHERE $condicion_sql
            ");

            $alineacion = [
                'concepto' => 'right',
                'valor' => 'right',
        ];

        $titulo_resumen_general = 'Resumen general de ventas por temporada';
        $data_resumen_general  = " data-conf-columncontrol='" . ($columnControl ? "true" : "false") . "' ";
        $data_resumen_general .= " data-conf-rowgroup=''";
        $data_resumen_general .= " data-conf-titulotabla='" . htmlspecialchars($titulo_resumen_general, ENT_QUOTES, 'UTF-8') . "' ";
        $data_resumen_general .= " data-conf-filename='Ventas_temporada_resumen_general' ";
        $data_resumen_general .= " data-conf-responsive='"    . ($responsive    ? "true" : "false") . "' ";
        $data_resumen_general .= " data-conf-colreorder='"    . ($colReorder    ? "true" : "false") . "' ";
        $data_resumen_general .= " data-conf-select='"        . ($select        ? "true" : "false") . "' ";
        $data_resumen_general .= " data-conf-buttons='"       . ($buttons       ? "true" : "false") . "' ";
        $data_resumen_general .= " data-conf-paging='"        . ($paging        ? "true" : "false") . "' ";
        $data_resumen_general .= " data-conf-ordering='"      . ($ordering      ? "true" : "false") . "' ";
        $data_resumen_general .= " data-conf-noorder='"       . (!$order        ? "true" : "false") . "' ";
        $data_resumen_general .= " data-conf-reset='"         . ($reset         ? "true" : "false") . "' ";
            
        $contenido .= $this->render_report_title($titulo_resumen_general);
        $contenido .= $this->render_datatable(
            $resumen,
            'tabla_ventas_temporada_resumen',
            $data_resumen_general,
            $alineacion
        );
        $contenido .= "<br/>";

        $resumen_marca = mysql::getresult("
            SELECT 
                nombre_marca AS marca,
                COUNT(DISTINCT idpedido) AS `total de pedidos`,
                SUM(cantidad_pares) AS `total de pares`,
                SUM(cantidad_modelos) AS `total de modelos`,
                CONCAT('Q ', FORMAT(SUM(monto_total), 2)) AS `monto total`
            FROM view_ventas_temporada
            WHERE $condicion_sql
            GROUP BY idmarca, nombre_marca
            ORDER BY nombre_marca ASC
        ");

        $alineacion_resumen_marca = [
            'marca' => 'left',
            'total de pedidos' => 'center',
            'total de pares' => 'center',
            'total de modelos' => 'center',
            'monto total' => 'right'
        ];

        $titulo_resumen_marca = 'Resumen por marca';
        $data_resumen_marca  = " data-conf-columncontrol='" . ($columnControl ? "true" : "false") . "' ";
        $data_resumen_marca .= " data-conf-rowgroup=''";
        $data_resumen_marca .= " data-conf-titulotabla='" . htmlspecialchars($titulo_resumen_marca, ENT_QUOTES, 'UTF-8') . "' ";
        $data_resumen_marca .= " data-conf-filename='Ventas_temporada_resumen_marca' ";
        $data_resumen_marca .= " data-conf-responsive='"    . ($responsive    ? "true" : "false") . "' ";
        $data_resumen_marca .= " data-conf-colreorder='"    . ($colReorder    ? "true" : "false") . "' ";
        $data_resumen_marca .= " data-conf-select='"        . ($select        ? "true" : "false") . "' ";
        $data_resumen_marca .= " data-conf-buttons='"       . ($buttons       ? "true" : "false") . "' ";
        $data_resumen_marca .= " data-conf-paging='"        . ($paging        ? "true" : "false") . "' ";
        $data_resumen_marca .= " data-conf-ordering='"      . ($ordering      ? "true" : "false") . "' ";
        $data_resumen_marca .= " data-conf-noorder='"       . (!$order        ? "true" : "false") . "' ";
        $data_resumen_marca .= " data-conf-reset='"         . ($reset         ? "true" : "false") . "' ";

        $contenido .= $this->render_report_title($titulo_resumen_marca);
        $contenido .= $this->render_datatable(
            $resumen_marca,
            'tabla_ventas_temporada_resumen_marca',
            $data_resumen_marca,
            $alineacion_resumen_marca
        );

        $security->registrar_bitacora(
            $this->ACCIONES['opcion_ventas_temporada'],
            'REPORTE_VENTAS_TEMPORADA',
            'temporada:' . $idtemporada,
            'cliente:' . $idcliente . '|marca:' . $idmarca . '|desde:' . $fecha_desde . '|hasta:' . $fecha_hasta
        );

        return $contenido;
    }

    private function render_report_title($titulo)
    {
        return '<h4 style="margin: 10px 0; text-align:center;">' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</h4>';
    }

    private function render_datatable($result, $table_id, $data_conf, $alineaciones = [])
    {
        $tabla = '<div class="table-responsive">';
        $tabla .= '<table id="' . $table_id . '" ' . $data_conf . ' class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">';

        $headers = [];
        $cantidad_columnas = mysql::num_fields($result);

        for ($i = 0; $i < $cantidad_columnas; $i++) {
            $campo = mysql::fetch_field($result, $i);
            $headers[] = $campo->name;
        }

        $tabla .= '<thead><tr>';
        foreach ($headers as $header) {
            $tabla .= '<th>' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        $tabla .= '</tr></thead>';

        $tabla .= '<tbody>';
        while ($row = mysql::getrowresult($result)) {
            $tabla .= '<tr>';
            foreach ($headers as $header) {
                $clave_alineacion = strtolower($header);
                $alineacion = isset($alineaciones[$clave_alineacion]) ? $alineaciones[$clave_alineacion] : 'left';
                $tabla .= '<td style="text-align:' . $alineacion . ';">' . $row[$header] . '</td>';
            }
            $tabla .= '</tr>';
        }
        $tabla .= '</tbody>';

        $tabla .= '</table>';
        $tabla .= '</div>';

        return $tabla;
    }

}