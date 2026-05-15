<?php
require_once 'mysql.php';
require_once 'security.php';

class datatables extends mysql {
    private $base_datos;
    private $html = "";
    private $IDS = [];
    private $OPTIONS = [];

    public function __construct($PARAMETROS = null, $OPTIONS = []) {
        $this->OPTIONS = is_array($OPTIONS) ? $OPTIONS : [];
        $this->base_datos = prefijo . '_seguridad';
        
        if (isset($PARAMETROS) && isset($PARAMETROS['operacion'])) {
            $this->seleccionar_operacion($PARAMETROS);
        } else {
            $this->addButtonBar();
        }
    }

    private function addButtonBar() {
        if (empty($this->OPTIONS)) return;
        
        $buttons = "<span style='float:right;'>";
        
        if (isset($this->OPTIONS['print']) && $this->OPTIONS['print'] == true) {
            $buttons .= " <button type=\"button\" class=\"btn btn-secondary btn-circle btn-xl\" onclick=\"print_all_datatables();\"><i class=\"mdi mdi-printer\"></i> </button> ";
        }
        
        if (isset($this->OPTIONS['export_all']) && $this->OPTIONS['export_all'] == true) {
            $buttons .= " <button type=\"button\" class=\"btn btn-secondary btn-circle btn-xl\" onclick=\"export_all_datatables();\"><i class=\"mdi mdi-file-excel\"></i> </button> ";
        }
        
        $buttons .= "</span>";
        
        if (!empty($buttons) && $buttons !== "<span style='float:right;'></span>") {
            $this->html .= $buttons;
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
            case 'guardar_estado_datatables_staterestore':
                if (isset($PARAMETROS['idtabla']) && isset($PARAMETROS['nombre_estado']) && isset($PARAMETROS['estadotabla'])) {
                    echo "|correcto|" . $this->guardar_estado_datatables_staterestore($PARAMETROS['idtabla'], $PARAMETROS['nombre_estado'], $PARAMETROS['estadotabla']);
                } else {
                    echo "|error|Datos incompletos|";
                }
                break;
            case 'listar_estados_datatables_staterestore':
                if (isset($PARAMETROS['idtabla'])) {
                    echo "|correcto|" . $this->listar_estados_datatables_staterestore($PARAMETROS['idtabla']);
                } else {
                    echo "|error|Datos incompletos|";
                }
                break;
            case 'cargar_estados_datatables_staterestore':
                if (isset($PARAMETROS['idtabla'])) {
                    echo "|correcto|" . $this->cargar_estados_datatables_staterestore($PARAMETROS['idtabla']);
                } else {
                    echo "|error|Datos incompletos|";
                }
                break;
            case 'cargar_estado_datatables_staterestore':
                if (isset($PARAMETROS['idtabla']) && isset($PARAMETROS['nombre_estado'])) {
                    echo "|correcto|" . $this->cargar_estado_datatables_staterestore($PARAMETROS['idtabla'], $PARAMETROS['nombre_estado']);
                } else {
                    echo "|error|Datos incompletos|";
                }
                break;
            case 'actualizar_estado_datatables_staterestore':
                if (isset($PARAMETROS['idtabla']) && isset($PARAMETROS['nombre_estado']) && isset($PARAMETROS['estadotabla'])) {
                    echo "|correcto|" . $this->actualizar_estado_datatables_staterestore($PARAMETROS['idtabla'], $PARAMETROS['nombre_estado'], $PARAMETROS['estadotabla']);
                } else {
                    echo "|error|Datos incompletos|";
                }
                break;
            case 'renombrar_estado_datatables_staterestore':
                if (isset($PARAMETROS['idtabla']) && isset($PARAMETROS['nombre_estado_actual']) && isset($PARAMETROS['nombre_estado_nuevo'])) {
                    echo "|correcto|" . $this->renombrar_estado_datatables_staterestore($PARAMETROS['idtabla'], $PARAMETROS['nombre_estado_actual'], $PARAMETROS['nombre_estado_nuevo']);
                } else {
                    echo "|error|Datos incompletos|";
                }
                break;
            case 'eliminar_estado_datatables_staterestore':
                if (isset($PARAMETROS['idtabla']) && isset($PARAMETROS['nombre_estado'])) {
                    $resultado = $this->eliminar_estado_datatables_staterestore($PARAMETROS['idtabla'], $PARAMETROS['nombre_estado']);
                    if ($resultado === false) {
                        echo "|error|El estado está protegido y no puede eliminarse|";
                    } else {
                        echo "|correcto|" . $resultado;
                    }
                } else {
                    echo "|error|Datos incompletos|";
                }
                break;
            default:
                echo "|error|Operacion no reconocida|";
                break;
        }
    }
    public function cargar_status_datatables(string $tabla, string $nombre_estado) {
        if (!$this->has_state_name_column()) {
            return '';
        }

        $usuario = $this->escape_sql((new security())->get_actual_user());
        $nombre_estado = $this->escape_sql($this->normalizar_nombre_estado($nombre_estado));
        $db = new mysql();

        $sql = "SELECT status
            FROM $this->base_datos.datatables
                WHERE usuario = '$usuario' AND tabla = '$tabla' AND nombre_estado = '$nombre_estado'
                LIMIT 1";

        $status = $db->getvalue($sql, 'status');
        return $status !== null ? $status : '';
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
        $export_all    = isset($PARAMETROS['export_all']) ? $PARAMETROS['export_all'] : false;
        $staterestore  = isset($PARAMETROS['staterestore']) ? $PARAMETROS['staterestore'] : false;

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
        $data_ .= " data-conf-exportall='" . ($export_all ? "true" : "false") . "' ";
        $data_ .= " data-conf-staterestore='" . ($staterestore ? "true" : "false") . "' ";

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
                //si esta en columna especial, reemplazo su contenido
                if (isset($special_columns[$key])) {
                    $column_content = $special_columns[$key];
                    foreach ($row as $row_key => $row_value) {
                        $column_content = str_replace("[$row_key]", $row_value, $column_content);
                    }
                }else{
                    $column_content = $value;
                }

                $align = (isset($aligments[$key])) ? $aligments[$key] : "left";
                $tabla_marca .= "<td style='text-align:$align'>$column_content</td>";
            }

            // foreach ($special_columns as $column => $column_content) {
            //     foreach ($row as $key => $value) {
            //         $column_content = str_replace("[$key]", $value, $column_content);
            //     }
            //     $tabla_marca .= "<td>$column_content</td>";
            // }

            $tabla_marca .= "</tr>";
        }

        $tabla_marca .= "</tbody>
        </table>";

        return $tabla_marca;
    }

    public function cargar_estado_datatables() {
        $db = new mysql();
        $usuario = (new security())->get_actual_user();
        $usuario = $this->escape_sql($usuario);
        $estados = [];

        if ($this->has_state_name_column()) {
            $sql = "SELECT tabla, estado FROM {$this->base_datos}.datatables WHERE usuario = '$usuario' AND nombre_estado = 'default' ";
        } else {
            $sql = "SELECT tabla, estado FROM {$this->base_datos}.datatables WHERE usuario = '$usuario' ";
        }

        $result = $db->getresult($sql);
        while ($row = $db->getrowresult($result)) {
            $idtabla = $row['tabla'];
            $estado  = json_decode($row['estado'], true);
            $estados[$idtabla] = $estado;
        }
        return json_encode($estados);
    }

    public function guardar_estado_datatables($tabla, $estado) {
        $usuario = (new security())->get_actual_user();
        $usuario = $this->escape_sql($usuario);
        $tabla   = $this->escape_sql($tabla);
        $estado  = urldecode($estado);
        $estado  = $this->escape_sql($estado);

        if ($this->has_state_name_column()) {
            $sql = "INSERT INTO {$this->base_datos}.datatables (usuario, tabla, nombre_estado, estado)
                VALUES ('$usuario', '$tabla', 'default', '$estado')
                ON DUPLICATE KEY UPDATE estado = VALUES(estado)";
        } else {
            $sql = "INSERT INTO {$this->base_datos}.datatables (usuario, tabla, estado)
                VALUES ('$usuario', '$tabla', '$estado')
                ON DUPLICATE KEY UPDATE estado = VALUES(estado)";
        }

        $db = new mysql();
        return $db->getresult($sql);
    }

    public function guardar_estado_datatables_staterestore($tabla, $nombre_estado, $estado) {
        if (!$this->has_state_name_column()) {
            return false;
        }

        $usuario = $this->escape_sql((new security())->get_actual_user());
        $tabla = $this->escape_sql($tabla);
        $nombre_estado = $this->escape_sql($this->normalizar_nombre_estado($nombre_estado));
        $estado = $this->escape_sql(urldecode($estado));

        $sql = "INSERT INTO {$this->base_datos}.datatables (usuario, tabla, nombre_estado, estado)
            VALUES ('$usuario', '$tabla', '$nombre_estado', '$estado')
            ON DUPLICATE KEY UPDATE estado = VALUES(estado)";

        $db = new mysql();
        return $db->getresult($sql);
    }

    public function listar_estados_datatables_staterestore($tabla) {
        if (!$this->has_state_name_column()) {
            return json_encode([]);
        }

        $usuario = $this->escape_sql((new security())->get_actual_user());
        $tabla = $this->escape_sql($tabla);
        $db = new mysql();

        $sql = "SELECT nombre_estado
            FROM {$this->base_datos}.datatables
            WHERE usuario = '$usuario' AND tabla = '$tabla'
            ORDER BY nombre_estado";

        $result = $db->getresult($sql);
        $estados = [];
        while ($row = $db->getrowresult($result)) {
            $estados[] = $row['nombre_estado'];
        }

        return json_encode($estados);
    }

    public function cargar_estados_datatables_staterestore($tabla) {
        if (!$this->has_state_name_column()) {
            return json_encode((object)[]);
        }

        $usuario = $this->escape_sql((new security())->get_actual_user());
        $tabla = $this->escape_sql($tabla);
        $db = new mysql();

        $sql = "SELECT nombre_estado, estado
            FROM {$this->base_datos}.datatables
            WHERE usuario = '$usuario' AND tabla = '$tabla'
            ORDER BY nombre_estado";

        $result = $db->getresult($sql);
        $estados = [];
        while ($row = $db->getrowresult($result)) {
            $estados[$row['nombre_estado']] = json_decode($row['estado'], true);
        }

        return json_encode($estados);
    }

    public function cargar_estado_datatables_staterestore($tabla, $nombre_estado) {
        if (!$this->has_state_name_column()) {
            return json_encode([]);
        }

        $usuario = $this->escape_sql((new security())->get_actual_user());
        $tabla = $this->escape_sql($tabla);
        $nombre_estado = $this->escape_sql($this->normalizar_nombre_estado($nombre_estado));
        $db = new mysql();

        $sql = "SELECT estado
            FROM {$this->base_datos}.datatables
            WHERE usuario = '$usuario' AND tabla = '$tabla' AND nombre_estado = '$nombre_estado'
            LIMIT 1";

        $estado = $db->getvalue($sql, 'estado');
        return $estado ? $estado : json_encode([]);
    }

    public function actualizar_estado_datatables_staterestore($tabla, $nombre_estado, $estado) {
        return $this->guardar_estado_datatables_staterestore($tabla, $nombre_estado, $estado);
    }

    public function renombrar_estado_datatables_staterestore($tabla, $nombre_estado_actual, $nombre_estado_nuevo) {
        if (!$this->has_state_name_column()) {
            return false;
        }

        $usuario = $this->escape_sql((new security())->get_actual_user());
        $tabla = $this->escape_sql($tabla);
        $nombre_estado_actual = $this->escape_sql($this->normalizar_nombre_estado($nombre_estado_actual));
        $nombre_estado_nuevo = $this->escape_sql($this->normalizar_nombre_estado($nombre_estado_nuevo));

        if ($nombre_estado_actual === $nombre_estado_nuevo) {
            return true;
        }

        $db = new mysql();
        $sql = "UPDATE {$this->base_datos}.datatables
            SET nombre_estado = '$nombre_estado_nuevo'
            WHERE usuario = '$usuario' AND tabla = '$tabla' AND nombre_estado = '$nombre_estado_actual'";
        echo $sql;

        return $db->getresult($sql);
    }

    public function eliminar_estado_datatables_staterestore($tabla, $nombre_estado) {
        if (!$this->has_state_name_column()) {
            return false;
        }
        $status = $this->cargar_status_datatables($tabla, $nombre_estado);
        if($status == 'PROTEGIDO'){

            return false;
        }
        $usuario = $this->escape_sql((new security())->get_actual_user());
        $tabla = $this->escape_sql($tabla);
        $nombre_estado = $this->escape_sql($this->normalizar_nombre_estado($nombre_estado));

        $db = new mysql();
        $sql = "DELETE FROM {$this->base_datos}.datatables
            WHERE usuario = '$usuario' AND tabla = '$tabla' AND nombre_estado = '$nombre_estado'";

        return $db->getresult($sql);
    }

    private function normalizar_nombre_estado($nombre_estado) {
        $nombre = trim((string) $nombre_estado);
        if ($nombre === '') {
            return 'default';
        }
        return substr($nombre, 0, 100);
    }

    private function escape_sql($valor) {
        return str_replace("'", "\\'", (string) $valor);
    }

    private function has_state_name_column() {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $db = new mysql();
        $sql = "SELECT COUNT(1) existe
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = '{$this->base_datos}'
              AND TABLE_NAME = 'datatables'
              AND COLUMN_NAME = 'nombre_estado'";
        $cache = ((int) $db->getvalue($sql, 'existe')) > 0;
        return $cache;
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