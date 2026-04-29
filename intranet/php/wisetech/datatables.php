<?php
require_once 'mysql.php';
require_once 'security.php';

class datatables extends mysql {
    private $html = "";
    private $IDS = [];

    public function __construct($PARAMETROS = null) {
        if (isset($PARAMETROS) && isset($PARAMETROS['operacion'])) {
            $this->seleccionar_operacion($PARAMETROS);
        }
    }

    private function seleccionar_operacion($PARAMETROS) {
        switch ($PARAMETROS['operacion']) {
            case 'mostrar_tabla':
                if (isset($PARAMETROS['result'])) {
                    $CONFIGURACION = isset($PARAMETROS['configuracion']) ? $PARAMETROS['configuracion'] : [];
                    echo "|correcto|" . $this->addTable($PARAMETROS['result'], $CONFIGURACION);
                } else {
                    echo "|error|Datos incompletos|";
                }
                break;
            case 'guardar_estado_datatables':
                if (isset($PARAMETROS['idtabla']) && isset($PARAMETROS['estadotabla'])) {
                    echo "|correcto|" . $this->guardar_estado_datatables($PARAMETROS['idtabla'], $PARAMETROS['estadotabla']);
                } else {
                    echo "|error|Datos incompletos|";
                }
                break;
            case 'cargar_estado_datatables':
                echo "|correcto|" . $this->cargar_estado_datatables();
                break;
            default:
                echo "|error|Operacion no reconocida|";
                break;
        }
    }

    public function addTable($result, $PARAMETROS = [], $style = "", $special_columns = [], $aligments = [], $hidden_columns = [], $idtabla = "tabla_datos") {
        $PARAMETROS = is_array($PARAMETROS) ? $PARAMETROS : [];

        $columncontrol = isset($PARAMETROS['columncontrol']) ? $PARAMETROS['columncontrol'] : false;
        $responsive    = isset($PARAMETROS['responsive']) ? $PARAMETROS['responsive'] : false;
        $colreorder    = isset($PARAMETROS['colreorder']) ? $PARAMETROS['colreorder'] : false;
        $select        = isset($PARAMETROS['select']) ? $PARAMETROS['select'] : false;
        $buttons       = isset($PARAMETROS['buttons']) ? $PARAMETROS['buttons'] : false;
        $paging        = isset($PARAMETROS['paging']) ? $PARAMETROS['paging'] : false;
        $ordering      = isset($PARAMETROS['ordering']) ? $PARAMETROS['ordering'] : false;
        $order         = isset($PARAMETROS['order']) ? $PARAMETROS['order'] : true;
        $reset         = isset($PARAMETROS['reset']) ? $PARAMETROS['reset'] : false;
        $rowgroup      = isset($PARAMETROS['rowgroup']) ? $PARAMETROS['rowgroup'] : false;
        $acciones      = isset($PARAMETROS['acciones']) ? $PARAMETROS['acciones'] : false;
        $hidden_columns = !empty($hidden_columns)
            ? $hidden_columns
            : (isset($PARAMETROS['hidden_columns']) && is_array($PARAMETROS['hidden_columns'])
                ? $PARAMETROS['hidden_columns']
                : []);

        $titulo_tabla  = isset($PARAMETROS['titulotabla']) ? $PARAMETROS['titulotabla'] : false;
        $file_name     = isset($PARAMETROS['filename']) ? $PARAMETROS['filename'] : false;

        $titulo_tabla = ($titulo_tabla === false || $titulo_tabla === '') ? 'Listado' : $titulo_tabla;
        $file_name    = ($file_name === false || $file_name === '') ? 'Listado' : $file_name;
        $row_group    = ($rowgroup === false || $rowgroup === '') ? 'false' : $rowgroup;

        $data_ = "";
        $data_ .= " data-conf-columncontrol='" . ($columncontrol ? "true" : "false") . "' ";
        $data_ .= " data-conf-rowgroup='" . $row_group . "' ";
        $data_ .= " data-conf-titulotabla='" . $titulo_tabla . "' ";
        $data_ .= " data-conf-filename='" . $file_name . "' ";
        $data_ .= " data-conf-responsive='" . ($responsive ? "true" : "false") . "' ";
        $data_ .= " data-conf-colreorder='" . ($colreorder ? "true" : "false") . "' ";
        $data_ .= " data-conf-select='" . ($select ? "true" : "false") . "' ";
        $data_ .= " data-conf-buttons='" . ($buttons ? "true" : "false") . "' ";
        $data_ .= " data-conf-paging='" . ($paging ? "true" : "false") . "' ";
        $data_ .= " data-conf-ordering='" . ($ordering ? "true" : "false") . "' ";
        $data_ .= " data-conf-noorder='" . (!$order ? "true" : "false") . "' ";
        $data_ .= " data-conf-reset='" . ($reset ? "true" : "false") . "' ";

        $idtabla = ($idtabla === null || $idtabla === '') ? 'tabla_datos' : $idtabla;
        if (in_array($idtabla, $this->IDS, true)) {
            return false;
        }
        $this->IDS[] = $idtabla;
        $idtbody = $idtabla . '_todos';

        $tabla_marca = '<table id="' . $idtabla . '" '.$data_.' style="' . $style . '" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
        <thead>
            <tr>
                ';

        if ($acciones) {
            $tabla_marca .= '<th>Acciones</th>';
        }

        $columns_quantity = mysql::num_fields($result);
        for ($i = 0; $i < $columns_quantity; $i++) {
            $field_info = mysql::fetch_field($result, $i);
            $header     = $field_info->name;
            if (in_array($header, $hidden_columns)) {
                continue;
            }

            $header = str_replace('_', ' ', $header);
            $header = strtoupper($header);
            $tabla_marca .= "<th>$header</th>";
        }

        foreach ($special_columns as $column => $column_content) {
            $tabla_marca .= "<th>$column</th>";
        }

        $tabla_marca .= '
            </tr>
        </thead>
        <tbody id="' . $idtbody . '">';

        while ($row = mysql::getrowresult($result)) {
            $row_data = $row;
            $str_data = "";

            foreach ($row_data as $key => $value) {
                $str_data .= $key . "=" . $value . "&";
            }

            $tabla_marca .= "<tr>";

            if ($acciones) {
                $boton_editar = "
                    <button
                        class=\"btn btn-sm btn-primary waves-effect waves-light\"
                        type=\"button\"
                        onclick=\"
                            editar_registro('$str_data', this.parentNode.parentNode);
                            goTop();
                        \">
                        <span class=\"btn-label\">
                            <i class=\"far fa-edit\"></i>
                        </span>
                        Editar
                    </button>
                ";
                $tabla_marca .= "<td>$boton_editar</td>";
            }

            foreach ($row as $key => $value) {
                if (in_array($key, $hidden_columns)) {
                    continue;
                }
                $align = (isset($aligments[$key])) ? $aligments[$key] : "left";
                $tabla_marca .= "<td style='text-align:$align'>$value</td>";
            }

            foreach ($special_columns as $column => $column_content) {
                foreach ($row as $key => $value) {
                    $column_content = str_replace("[$key]", $value, $column_content);
                }
                $tabla_marca .= "<td>$column_content</td>";
            }

            $tabla_marca .= "</tr>";
        }

        $tabla_marca .= "</tbody>
        </table>";

        return $tabla_marca;
    }

    public function cargar_estado_datatables() {
        $db = new mysql();
        $usuario = (new security())->get_actual_user();
        $sql = "SELECT tabla, estado FROM solomoda_seguridad.datatables WHERE usuario = '$usuario' ";
        $result = $db->getresult($sql);
        $estados = [];
        while ($row = $db->getrowresult($result)) {
            $idtabla = $row['tabla'];
            $estado  = json_decode($row['estado'], true);
            $estados[$idtabla] = $estado;
        }
        return json_encode($estados);
    }

    public function guardar_estado_datatables($tabla, $estado) {
        $usuario = (new security())->get_actual_user();
        $estado  = urldecode($estado);
        $sql = "INSERT INTO solomoda_seguridad.datatables (usuario, tabla, estado)
                VALUES ('$usuario', '$tabla', '$estado')
                ON DUPLICATE KEY UPDATE estado = VALUES(estado)";
        $db = new mysql();
        return $db->getresult($sql);
    }

    // ========== MÉTODOS PARA CONSTRUCCIÓN DE REPORTES ==========

    public function addTitle($text)
    {
        $this->html .= "<h2 style='width:100%;display:block;text-align:center; color: black;'>$text</h2>";
    }

    public function addSubTitle($text)
    {
        $this->html .= "<h4 style='color: black;'>$text</h4>";
    }

    public function addBreakLine($cantidad = 1)
    {
        for ($i = 0; $i < $cantidad; $i++) {
            $this->html .= "<br>";
        }
    }

    public function addParagraph($text)
    {
        $this->html .= "<p>$text</p>";
    }

    public function addText($text)
    {
        $this->html .= "<span>$text</span>";
    }

    public function addLogo($url)
    {
        $this->html .= "<img src='$url' style='display: inline-block;position: relative;float: left;height: 20mm;margin: 5mm;margin-top:2mm;'>";
    }

    public function addTableToReport($result, $PARAMETROS = [], $style = "", $special_columns = [], $aligments = [], $hidden_columns = [], $idtabla = "tabla_datos")
    {
        $this->html .= $this->addTable($result, $PARAMETROS, $style, $special_columns, $aligments, $hidden_columns, $idtabla);
    }

    public function getReport()
    {
        return $this->html;
    }

    public function reset()
    {
        $this->html = "";
    }

}