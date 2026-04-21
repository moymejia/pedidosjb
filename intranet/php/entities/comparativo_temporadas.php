<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/objects.php';
require_once '../wisetech/utils.php';
require_once('../entities/temporada.php');
require_once('../entities/cliente.php');
require_once('../entities/marca.php');

class comparativo_temporadas extends table
{

    use utils;
    public $last_error = '';
    private $ACCIONES   = [];

    public function __construct($PARAMETROS = null)
    {

        parent::__construct(prefijo . '_pedidos', 'comparativo_temporadas');

        $this->ACCIONES['opcion_ventas_temporada'] = 'opcion_comparativo_temporadas';

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'options_clientes_temporadas') {
                if ($resultado = $this->options_clientes_temporadas($PARAMETROS['idtemporada'])) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'options_marcas_temporadas') {
                if ($resultado = $this->options_marcas_temporadas($PARAMETROS['idtemporada'])) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'generar_comparativo') {
                if ($resultado = $this->generar_comparativo($PARAMETROS['idtemporada'], $PARAMETROS['idcliente'], $PARAMETROS['idmarca'])) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }
        }
    }

    public function cargar_opcion($PARAMETROS = null)
    {
        $DATA = [];
        $DATA['temporadas_activas'] = (new temporada())->option_activos();
        $DATA['clientes_activos'] = "<option value=''>Seleccione cliente</option>";
        $DATA['marcas_activas'] = "<option value=''>Seleccione marca</option>";
        $DATA['tabla'] = '';
        
        $html = new html('comparativo_temporadas', $DATA);

        return $html->get_html();
    }

    public function options_clientes_temporadas($idtemporada)
    {
        $idtemporada = str_replace("|", ",", $idtemporada);

        return "<option value=''>Seleccione cliente</option>" . mysql::getoptions("SELECT DISTINCT p.idcliente AS id, c.nombre AS descripcion
            FROM pedido p
            LEFT JOIN cliente c ON c.idcliente = p.idcliente
            WHERE p.estado = 'CERRADO'
                AND p.idtemporada IN ($idtemporada)
            ORDER BY c.nombre ASC");
    }

    public function options_marcas_temporadas($idtemporada)
    {
        $idtemporada = str_replace("|", ",", $idtemporada);

        return "<option value=''>Seleccione marca</option>" . mysql::getoptions("SELECT DISTINCT p.idmarca AS id, m.nombre AS descripcion
            FROM pedido p
            LEFT JOIN marca m ON m.idmarca = p.idmarca
            WHERE p.estado = 'CERRADO'
                AND p.idtemporada IN ($idtemporada)
            ORDER BY m.nombre ASC");
    }

    public function generar_comparativo($temporadas, $idcliente = 0, $idmarca = 0)
    {
        if ($temporadas == '') { 
            $this->last_error = 'Debe seleccionar al menos una temporada.'; 
            utils::report_error(validation_error, $temporadas, $this->last_error); 
            return false; 
        }
        
        if ($idcliente == '' && $idmarca == '') { 
            $this->last_error = 'Debe seleccionar un cliente o una marca.'; 
            utils::report_error(validation_error, ['idcliente' => $idcliente, 'idmarca' => $idmarca], $this->last_error); 
            return false; 
        }
        if ($idcliente != '' && $idmarca != '') { 
            $this->last_error = 'Seleccione solo cliente o solo marca.'; 
            utils::report_error(validation_error, ['idcliente' => $idcliente, 'idmarca' => $idmarca], $this->last_error); 
            return false; 
        }

        $arreglo_temporadas = explode('|', $temporadas);
        $temporadas_seleccionadas = str_replace("|", ",", $temporadas);

        $columnas = [];
        foreach ($arreglo_temporadas as $t) {
            $columnas[] = "SUM(CASE WHEN v.idtemporada = $t THEN v.total_pares ELSE 0 END) AS total_pares_$t";
            $columnas[] = "CONCAT('Q ', FORMAT(SUM(CASE WHEN v.idtemporada = $t THEN v.monto_total ELSE 0 END), 2)) AS monto_total_$t";
        }
        $columnas_sql = implode(",\n", $columnas);

        if ($idmarca) {
            $donde = "v.idmarca = $idmarca";
            $filtro_cliente_o_marca = "v.idcliente";
            $nombre = "v.cliente_nombre";
            $titulo = "Cliente";
        } else {
            $donde = "v.idcliente = $idcliente";
            $filtro_cliente_o_marca = "v.idmarca";
            $nombre = "v.marca_nombre";
            $titulo = "Marca";
        }

        $arreglo_temporadas_nombre = [];
        $resarreglo_temporadas = mysql::getresult("SELECT idtemporada, nombre FROM temporada WHERE idtemporada IN ($temporadas_seleccionadas)");
        while ($r = mysql::getrowresult($resarreglo_temporadas)) {
            $arreglo_temporadas_nombre[$r['idtemporada']] = $r['nombre'];
        }

        $sql = mysql::getresult("
            SELECT 
                $filtro_cliente_o_marca AS filtro_cliente_o_marca,
                $nombre AS nombre,
                $columnas_sql
            FROM view_pedido_comparativo v
            WHERE v.estado = 'CERRADO'
            AND v.idtemporada IN ($temporadas_seleccionadas)
            AND $donde
            GROUP BY $filtro_cliente_o_marca
            ORDER BY nombre ASC
        ");

        $columnControl = true;
        $responsive    = true;
        $colReorder    = true;
        $select        = false;
        $buttons       = true;
        $paging        = true;
        $ordering      = true;
        $order         = true;
        $rowGroup      = false;
        $reset         = true;
        $tituloTabla   = "Comparativo de temporadas por $titulo";
        $fileName      = "Comparativo_temporadas";

        $data_ = "";
        $data_  = " data-conf-columncontrol='" . ($columnControl ? "true" : "false") . "' ";
        $data_ .= " data-conf-rowgroup=''";
        $data_ .= " data-conf-titulotabla='" . $tituloTabla . "' ";
        $data_ .= " data-conf-filename='" . $fileName . "' ";
        $data_ .= " data-conf-responsive='"    . ($responsive    ? "true" : "false") . "' ";
        $data_ .= " data-conf-colreorder='"    . ($colReorder    ? "true" : "false") . "' ";
        $data_ .= " data-conf-select='"        . ($select        ? "true" : "false") . "' ";
        $data_ .= " data-conf-buttons='"       . ($buttons       ? "true" : "false") . "' ";
        $data_ .= " data-conf-paging='"        . ($paging        ? "true" : "false") . "' ";
        $data_ .= " data-conf-ordering='"      . ($ordering      ? "true" : "false") . "' ";
        $data_ .= " data-conf-noorder='"       . (!$order        ? "true" : "false") . "' ";
        $data_ .= " data-conf-reset='"         . ($reset         ? "true" : "false") . "' ";

        $tabla_comparativa = "<input type='hidden' id='datatableid' name='datatableid' value='tabla_comparativo_temporadas'>";
        $tabla_comparativa .= "<table id='tabla_datos' " . $data_ . " class='display nowrap table table-hover table-bordered datatable' cellspacing='0' width='100%'><thead>";

        $tabla_comparativa .= "<tr><th rowspan='2'>$titulo</th>";
        foreach ($arreglo_temporadas as $t) {
            $tabla_comparativa .= "
                <th colspan='2'>
                    <button class=\"btn btn-primary btn-sm\" onclick=\"abrir_reporte_ventas_por_temporada('".($arreglo_temporadas_nombre[$t] ?? $t)."');\" style=\"cursor:pointer; margin-right:5px;\">
                        <i class=\"fas fa-eye\"></i>
                    </button>" . ($arreglo_temporadas_nombre[$t] ?? $t) . "</th>";
        }
        $tabla_comparativa .= "</tr>";

        $tabla_comparativa .= "<tr>";
        foreach ($arreglo_temporadas as $t) {
            $tabla_comparativa .= "<th>Pares</th><th>Monto</th>";
        }
        
        $tabla_comparativa .= "</tr></thead><tbody id='tabla_todos'>";


        while ($row = mysql::getrowresult($sql)) {
            $tabla_comparativa .= "<tr><td>{$row['nombre']}</td>";

            foreach ($arreglo_temporadas as $t) {
                $pares = (isset($row["total_pares_$t"]) && $row["total_pares_$t"] !== '') ? $row["total_pares_$t"] : '0';
                $monto = (isset($row["monto_total_$t"]) && $row["monto_total_$t"] !== '') ? $row["monto_total_$t"] : 'Q 0.00';

                $tabla_comparativa .= "<td style='text-align: center;'>" . $pares . "</td>";
                $tabla_comparativa .= "<td>" . $monto . "</td>";
            }

            $tabla_comparativa .= "</tr>";
        }

        $tabla_comparativa .= "</tbody></table>";

        return $tabla_comparativa;
    }

    
}