<?php
require_once 'mysql.php';
require_once 'security.php';
require_once 'utils.php';

class datatables extends mysql {
    use utils;
    private $base_datos;
    private $html = "";
    private $IDS = [];
    private $OPTIONS = [];
    private $ACCIONES   = [];

    public function __construct($PARAMETROS = null, $OPTIONS = []) {
        $this->OPTIONS = is_array($OPTIONS) ? $OPTIONS : [];
        $this->base_datos = prefijo . '_seguridad';
        $_MYSQL = new mysql($this->base_datos);
        $this->ACCIONES['actualizar_vista'] = $_MYSQL->getvalue("SELECT idaccion FROM accion WHERE nombre = 'actualizar_vista' AND estado = 'ACTIVO' LIMIT 1", 'idaccion');
        $this->ACCIONES['compartir_vista']  = $_MYSQL->getvalue("SELECT idaccion FROM accion WHERE nombre = 'compartir_vista' AND estado = 'ACTIVO' LIMIT 1", 'idaccion');

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
            case 'opciones_estado_columna':
                echo "|correcto|" . $this->opciones_estado_columna();
                break;
            case 'compartir_estados':
                if (isset($PARAMETROS['idtabla']) && isset($PARAMETROS['nombre_estado']) && isset($PARAMETROS['usuarios'])) {
                    echo "|correcto|" . $this->compartir_estados($PARAMETROS['idtabla'], $PARAMETROS['nombre_estado'], $PARAMETROS['usuarios']);
                } else {
                    echo "|error|Datos incompletos|";
                }
                break;
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
    private function opciones_estado_columna(){
        return json_encode(['ACTIVO', 'INACTIVO', 'PROTEGIDO']);
    }
    private function compartir_estados($idtabla, $nombre_estado, $usuarios){
        
        $security = new security($this->ACCIONES['compartir_vista']);
        $usuario_actual = $security->get_actual_user();
        $usuario_actual = $this->escape_sql($usuario_actual);

        $idtabla       = $this->escape_sql($idtabla);
        $nombre_estado = $this->escape_sql($nombre_estado);

        // Obtener el estado original del usuario actual
        $sql_origen = "SELECT estado FROM {$this->base_datos}.datatables WHERE usuario = '$usuario_actual' AND tabla = '$idtabla' AND nombre_estado = '$nombre_estado'";

        $db = new mysql();
        $estado_original = $db->getvalue($sql_origen);

        if (!$estado_original) {
            return "Estado no encontrado";
        }

        $estado_escapado = $this->escape_sql($estado_original);

        $usuarios = urldecode($usuarios);
        $lista_usuarios = explode(',', $usuarios);

        $resultados = [];
        foreach ($lista_usuarios as $usuario_destino) {
            $usuario_destino = trim($usuario_destino);
            if ($usuario_destino === '') {
                continue;
            }
            $usuario_destino_escapado = $this->escape_sql($usuario_destino);

            $sql = "INSERT INTO {$this->base_datos}.datatables (usuario, tabla, nombre_estado, estado)
                    VALUES ('$usuario_destino_escapado', '$idtabla', '$nombre_estado', '$estado_escapado')
                    ON DUPLICATE KEY UPDATE estado = VALUES(estado)";

            $db->getresult($sql);
            $resultados[] = $usuario_destino;
        }

        return "Estado compartido con: " . implode(', ', $resultados);
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
    private function cargar_configuracion_datatable($idtabla, $usuario) {
        $idtabla_sql = $this->escape_sql($idtabla);
        $usuario_sql = $this->escape_sql($usuario);
        $_MYSQL = new mysql($this->base_datos);

        $sql = "SELECT idtabla
            FROM {$this->base_datos}.datatable_configuracion
            WHERE idtabla = '$idtabla_sql' AND usuario = '$usuario_sql'
            LIMIT 1";
        $resultado = $_MYSQL->getresult($sql);
        if ($resultado === false) {
            utils::report_error(bd_error, [$usuario, $idtabla], 'No fue posible consultar la configuración DataTables.');
            return false;
        }

        $FILA = $_MYSQL->getrowresult($resultado);
        if (!$FILA) {
            return null;
        }

        $sql = "SELECT idtabla_detalle, parametro, valor
            FROM {$this->base_datos}.datatable_configuracion_detalle
            WHERE idtabla = '$idtabla_sql' AND usuario = '$usuario_sql'
            ORDER BY idtabla_detalle";
        $resultado = $_MYSQL->getresult($sql);
        if ($resultado === false) {
            utils::report_error(bd_error, [$usuario, $idtabla], 'No fue posible consultar los parámetros DataTables.');
            return false;
        }

        $DETALLES = [];
        while ($FILA = $_MYSQL->getrowresult($resultado)) {
            $parametro = trim((string)$FILA['parametro']);
            if ($parametro === '') {
                continue;
            }
            $DETALLES[(int)$FILA['idtabla_detalle']] = [
                'parametro' => $parametro,
                'valor' => $FILA['valor']
            ];
        }

        $VALORES_DETALLE = [];
        if (!empty($DETALLES)) {
            $sql = "SELECT idtabla_detalle, clave, valor
                FROM {$this->base_datos}.datatable_configuracion_detalle_valor
                WHERE idtabla_detalle IN (
                    SELECT idtabla_detalle
                    FROM {$this->base_datos}.datatable_configuracion_detalle
                    WHERE idtabla = '$idtabla_sql' AND usuario = '$usuario_sql'
                )
                ORDER BY idtabla_detalle, orden";
            $resultado = $_MYSQL->getresult($sql);
            if ($resultado === false) {
                utils::report_error(bd_error, [$usuario, $idtabla], 'No fue posible consultar los valores de configuración DataTables.');
                return false;
            }

            while ($FILA = $_MYSQL->getrowresult($resultado)) {
                $idtabla_detalle = (int)$FILA['idtabla_detalle'];
                if (!isset($VALORES_DETALLE[$idtabla_detalle])) {
                    $VALORES_DETALLE[$idtabla_detalle] = [];
                }
                $VALORES_DETALLE[$idtabla_detalle][] = [
                    'clave' => $FILA['clave'],
                    'valor' => $FILA['valor']
                ];
            }
        }

        $CONFIGURACION = [];
        foreach ($DETALLES as $idtabla_detalle => $DETALLE) {
            $parametro = $DETALLE['parametro'];
            if (!isset($VALORES_DETALLE[$idtabla_detalle])) {
                $CONFIGURACION[$parametro] = $this->normalizar_valor_configuracion($DETALLE['valor']);
                continue;
            }

            $VALORES = [];
            $es_asociativo = false;
            foreach ($VALORES_DETALLE[$idtabla_detalle] as $VALOR_DETALLE) {
                if ($VALOR_DETALLE['clave'] !== null && $VALOR_DETALLE['clave'] !== '') {
                    $es_asociativo = true;
                    $VALORES[$VALOR_DETALLE['clave']] = $VALOR_DETALLE['valor'];
                } else {
                    $VALORES[] = $VALOR_DETALLE['valor'];
                }
            }

            if (!$es_asociativo && in_array($parametro, ['add_filter', 'columncontrolexclude', 'buttons', 'responsive'], true)) {
                $CONFIGURACION[$parametro] = implode(',', $VALORES);
            } else {
                $CONFIGURACION[$parametro] = $VALORES;
            }
        }

        return $CONFIGURACION;
    }
    private function guardar_configuracion_datatable($idtabla, $usuario, $PARAMETROS, $style, $special_columns, $aligments, $hidden_columns) {
        $PARAMETROS = is_array($PARAMETROS) ? $PARAMETROS : [];
        $CONFIGURACION = array_fill_keys($this->obtener_parametros_configuracion_datatable(), false);
        foreach ($PARAMETROS as $parametro => $valor) {
            $CONFIGURACION[$parametro] = $valor;
        }
        $CONFIGURACION['style'] = ($style === null || $style === '') ? false : $style;
        $CONFIGURACION['special_columns'] = empty($special_columns) ? false : $special_columns;
        $CONFIGURACION['aligments'] = empty($aligments) ? false : $aligments;
        $CONFIGURACION['hidden_columns'] = empty($hidden_columns) ? false : $hidden_columns;

        $_SECURITY = new security($this->ACCIONES['actualizar_vista']);
        $usuario = $this->escape_sql($usuario);
        $idtabla_sql = $this->escape_sql($idtabla);
        $_MYSQL = new mysql($this->base_datos);

        if (!$_MYSQL->put('START TRANSACTION')) {
            utils::report_error(bd_error, [$usuario, $idtabla], 'No fue posible iniciar la creación de la configuración DataTables.');
            return false;
        }

        $sql = "INSERT INTO {$this->base_datos}.datatable_configuracion
            (usuario, idtabla, creado_por)
            VALUES ('$usuario', '$idtabla_sql', '$usuario')";
        if (!$_MYSQL->put($sql)) {
            $_MYSQL->put('ROLLBACK');
            utils::report_error(bd_error, [$usuario, $idtabla], 'No fue posible crear la configuración DataTables.');
            return false;
        }

        foreach ($CONFIGURACION as $parametro => $valor) {
            $LISTADO = $this->obtener_listado_valor_configuracion($parametro, $valor);
            $parametro_sql = $this->escape_sql($parametro);
            $valor_sql = ($LISTADO !== null)
                ? 'NULL'
                : "'" . $this->escape_sql($this->convertir_valor_configuracion_texto($valor)) . "'";

            $sql = "INSERT INTO {$this->base_datos}.datatable_configuracion_detalle
                (usuario, idtabla, parametro, valor, creado_por)
                VALUES ('$usuario', '$idtabla_sql', '$parametro_sql', $valor_sql, '$usuario')";
            if (!$_MYSQL->put($sql)) {
                $_MYSQL->put('ROLLBACK');
                utils::report_error(bd_error, [$usuario, $idtabla, $parametro], 'No fue posible crear el parámetro DataTables.');
                return false;
            }

            if ($LISTADO === null) {
                continue;
            }

            $idtabla_detalle = $_MYSQL->last_id();
            $orden = 1;
            foreach ($LISTADO as $VALOR_LISTADO) {
                $clave_sql = ($VALOR_LISTADO['clave'] === null)
                    ? 'NULL'
                    : "'" . $this->escape_sql($VALOR_LISTADO['clave']) . "'";
                $valor_listado_sql = $this->escape_sql($VALOR_LISTADO['valor']);
                $sql = "INSERT INTO {$this->base_datos}.datatable_configuracion_detalle_valor
                    (idtabla_detalle, clave, valor, orden, creado_por)
                    VALUES ('$idtabla_detalle', $clave_sql, '$valor_listado_sql', '$orden', '$usuario')";
                if (!$_MYSQL->put($sql)) {
                    $_MYSQL->put('ROLLBACK');
                    utils::report_error(bd_error, [$usuario, $idtabla, $parametro], 'No fue posible crear el valor del parámetro DataTables.');
                    return false;
                }
                $orden++;
            }
        }

        if (!$_MYSQL->put('COMMIT')) {
            $_MYSQL->put('ROLLBACK');
            utils::report_error(bd_error, [$usuario, $idtabla], 'No fue posible finalizar la creación de la configuración DataTables.');
            return false;
        }

        $_SECURITY->registrar_bitacora(
            $this->ACCIONES['actualizar_vista'],
            $idtabla,
            'CREAR CONFIGURACION DATATABLE'
        );

        return true;
    }
    private function obtener_parametros_configuracion_datatable() {
        return [
            'add_filter',
            'columncontrol',
            'columncontrolexclude',
            'responsive',
            'colreorder',
            'select',
            'buttons',
            'paging',
            'ordering',
            'reset',
            'rowgroup',
            'edit_button',
            'export_all',
            'staterestore',
            'titulotabla',
            'filename'
        ];
    }
    private function obtener_listado_valor_configuracion($parametro, $valor) {
        if ($valor === false || $valor === null || $valor === '' || (is_array($valor) && empty($valor))) {
            return null;
        }
        if ($parametro === 'responsive' && !is_array($valor) && ($valor === true || $valor === 1 || $valor === '1' || strtolower(trim((string)$valor)) === 'true')) {
            return null;
        }

        $PARAMETROS_LISTA_SIMPLE = ['add_filter', 'columncontrolexclude', 'buttons', 'responsive', 'hidden_columns'];
        $PARAMETROS_LISTA_ASOCIATIVA = ['special_columns', 'aligments'];
        if (!in_array($parametro, $PARAMETROS_LISTA_SIMPLE, true) && !in_array($parametro, $PARAMETROS_LISTA_ASOCIATIVA, true) && !is_array($valor)) {
            return null;
        }

        if (in_array($parametro, $PARAMETROS_LISTA_ASOCIATIVA, true)) {
            if (!is_array($valor)) {
                return null;
            }
            $LISTADO = [];
            foreach ($valor as $clave => $contenido) {
                $LISTADO[] = [
                    'clave' => (string)$clave,
                    'valor' => $this->convertir_valor_configuracion_texto($contenido)
                ];
            }
            return $LISTADO;
        }

        if (is_array($valor)) {
            $VALORES = array_values($valor);
        } elseif ($parametro === 'buttons' && $valor === true) {
            $VALORES = ['all'];
        } elseif (in_array($parametro, ['add_filter', 'columncontrolexclude', 'buttons', 'responsive'], true)) {
            $VALORES = explode(',', (string)$valor);
        } else {
            $VALORES = [$valor];
        }

        $LISTADO = [];
        foreach ($VALORES as $valor_listado) {
            $valor_listado = trim($this->convertir_valor_configuracion_texto($valor_listado));
            if ($valor_listado === '') {
                continue;
            }
            $LISTADO[] = ['clave' => null, 'valor' => $valor_listado];
        }

        return empty($LISTADO) ? null : $LISTADO;
    }
    private function convertir_valor_configuracion_texto($valor) {
        if ($valor === true) {
            return 'true';
        }
        if ($valor === false || $valor === null || $valor === '' || is_array($valor)) {
            return 'false';
        }
        return (string)$valor;
    }
    private function sincronizar_campos_query_datatable($idtabla, $usuario, $CAMPOS_QUERY) {
        if (empty($CAMPOS_QUERY)) {
            utils::report_error(validation_error, [$usuario, $idtabla], 'No se recibieron campos para sincronizar la configuración DataTables.');
            return false;
        }

        $_SECURITY = new security($this->ACCIONES['actualizar_vista']);
        $usuario = $this->escape_sql($usuario);
        $idtabla_sql = $this->escape_sql($idtabla);
        $_MYSQL = new mysql($this->base_datos);

        if (!$_MYSQL->put('START TRANSACTION')) {
            utils::report_error(bd_error, [$usuario, $idtabla], 'No fue posible iniciar la sincronización de campos DataTables.');
            return false;
        }

        $sql = "SELECT idtabla_detalle
            FROM {$this->base_datos}.datatable_configuracion_detalle
            WHERE idtabla = '$idtabla_sql' AND usuario = '$usuario' AND parametro = 'query_fields'
            LIMIT 1";
        $idtabla_detalle = $_MYSQL->getvalue($sql, 'idtabla_detalle');

        if ($idtabla_detalle === false) {
            $sql = "INSERT INTO {$this->base_datos}.datatable_configuracion_detalle
                (usuario, idtabla, parametro, valor, creado_por)
                VALUES ('$usuario', '$idtabla_sql', 'query_fields', NULL, '$usuario')";
            if (!$_MYSQL->put($sql)) {
                $_MYSQL->put('ROLLBACK');
                utils::report_error(bd_error, [$usuario, $idtabla], 'No fue posible crear query_fields para DataTables.');
                return false;
            }
            $idtabla_detalle = $_MYSQL->last_id();
        } else {
            $sql = "UPDATE {$this->base_datos}.datatable_configuracion_detalle
                SET valor = NULL, actualizado_en = NOW(), actualizado_por = '$usuario'
                WHERE idtabla_detalle = '$idtabla_detalle'";
            if (!$_MYSQL->put($sql)) {
                $_MYSQL->put('ROLLBACK');
                utils::report_error(bd_error, [$usuario, $idtabla], 'No fue posible actualizar query_fields para DataTables.');
                return false;
            }

            $sql = "DELETE FROM {$this->base_datos}.datatable_configuracion_detalle_valor
                WHERE idtabla_detalle = '$idtabla_detalle'";
            if (!$_MYSQL->put($sql)) {
                $_MYSQL->put('ROLLBACK');
                utils::report_error(bd_error, [$usuario, $idtabla], 'No fue posible reemplazar los campos de query_fields.');
                return false;
            }
        }

        $orden = 1;
        foreach ($CAMPOS_QUERY as $campo) {
            $campo_sql = $this->escape_sql($campo);
            $sql = "INSERT INTO {$this->base_datos}.datatable_configuracion_detalle_valor
                (idtabla_detalle, clave, valor, orden, creado_por)
                VALUES ('$idtabla_detalle', NULL, '$campo_sql', '$orden', '$usuario')";
            if (!$_MYSQL->put($sql)) {
                $_MYSQL->put('ROLLBACK');
                utils::report_error(bd_error, [$usuario, $idtabla, $campo], 'No fue posible guardar un campo de query_fields.');
                return false;
            }
            $orden++;
        }

        if (!$_MYSQL->put('COMMIT')) {
            $_MYSQL->put('ROLLBACK');
            utils::report_error(bd_error, [$usuario, $idtabla], 'No fue posible finalizar la sincronización de campos DataTables.');
            return false;
        }

        $_SECURITY->registrar_bitacora(
            $this->ACCIONES['actualizar_vista'],
            $idtabla,
            'ACTUALIZAR CAMPOS QUERY DATATABLE'
        );

        return true;
    }
    private function normalizar_valor_configuracion($valor) {
        if ($valor === null) {
            return true;
        }

        $valor = trim((string)$valor);
        $valor_normalizado = strtolower($valor);
        if ($valor_normalizado === 'true') {
            return true;
        }
        if ($valor_normalizado === 'false') {
            return false;
        }

        return $valor;
    }
    public function addTable($result, $PARAMETROS = [], $style = "", $special_columns = [], $aligments = [], $hidden_columns = [], $idtabla = "tabla_datos") {
        $idtabla = ($idtabla === null || $idtabla === '') ? 'tabla_datos' : $idtabla;
        if ($idtabla !== 'tabla_datos') {
            $_SECURITY_CONFIGURACION = new security();
            $usuario_configuracion = $_SECURITY_CONFIGURACION->get_actual_user();
            $CAMPOS_QUERY = [];
            $cantidad_campos = mysql::num_fields($result);
            for ($indice_campo = 0; $indice_campo < $cantidad_campos; $indice_campo++) {
                $informacion_campo = mysql::fetch_field($result, $indice_campo);
                $CAMPOS_QUERY[] = $informacion_campo->name;
            }

            $CONFIGURACION = $this->cargar_configuracion_datatable($idtabla, $usuario_configuracion);
            if (is_array($CONFIGURACION)) {
                $CAMPOS_GUARDADOS = isset($CONFIGURACION['query_fields']) && is_array($CONFIGURACION['query_fields'])
                    ? array_values($CONFIGURACION['query_fields'])
                    : [];
                if ($CAMPOS_GUARDADOS !== $CAMPOS_QUERY) {
                    $this->sincronizar_campos_query_datatable($idtabla, $usuario_configuracion, $CAMPOS_QUERY);
                    $CONFIGURACION['query_fields'] = $CAMPOS_QUERY;
                }
                $PARAMETROS = $CONFIGURACION;
                $style = (isset($CONFIGURACION['style']) && $CONFIGURACION['style'] !== false) ? $CONFIGURACION['style'] : '';
                $special_columns = (isset($CONFIGURACION['special_columns']) && is_array($CONFIGURACION['special_columns'])) ? $CONFIGURACION['special_columns'] : [];
                $aligments = (isset($CONFIGURACION['aligments']) && is_array($CONFIGURACION['aligments'])) ? $CONFIGURACION['aligments'] : [];
                $hidden_columns = (isset($CONFIGURACION['hidden_columns']) && is_array($CONFIGURACION['hidden_columns'])) ? $CONFIGURACION['hidden_columns'] : [];
            } else {
                $PARAMETROS = is_array($PARAMETROS) ? $PARAMETROS : [];
                $PARAMETROS_GUARDAR = $PARAMETROS;
                $PARAMETROS_GUARDAR['query_fields'] = $CAMPOS_QUERY;
                if ($CONFIGURACION === null && !empty($PARAMETROS)) {
                    $this->guardar_configuracion_datatable($idtabla, $usuario_configuracion, $PARAMETROS_GUARDAR, $style, $special_columns, $aligments, $hidden_columns);
                } elseif ($CONFIGURACION === null) {
                    $this->guardar_configuracion_datatable($idtabla, $usuario_configuracion, $PARAMETROS_GUARDAR, $style, $special_columns, $aligments, $hidden_columns);
                }
            }
        }

        //
        $add_filter    = isset($PARAMETROS['add_filter'])    ? $PARAMETROS['add_filter']    : false;
        $add_filter_value = 'false';
        if (!($add_filter === false || $add_filter === '' || $add_filter === null)) {
            $add_filter_value = trim((string)$add_filter);
            if ($add_filter_value !== '') {
                $normalized_parts = [];
                foreach (explode(',', $add_filter_value) as $part) {
                    $part = strtoupper(str_replace('_', ' ', trim((string)$part)));
                    if ($part !== '') {
                        $normalized_parts[] = $part;
                    }
                }
                $add_filter_value = empty($normalized_parts) ? 'false' : implode(',', $normalized_parts);
            } else {
                $add_filter_value = 'false';
            }
        }
        //
        $columncontrol = isset($PARAMETROS['columncontrol']) ? $PARAMETROS['columncontrol'] : false;
        $responsive = isset($PARAMETROS['responsive']) ? $PARAMETROS['responsive'] : false;
        $responsive = isset($PARAMETROS['responsive']) ? $PARAMETROS['responsive'] : false;
        $responsive_value = 'false';
        if (!($responsive === false || $responsive === '' || $responsive === null)) {
            $responsive_value = trim((string)$responsive);
            if ($responsive_value !== '') {
                $normalized_parts = [];
                foreach (explode(',', $responsive_value) as $part) {
                    $part = strtoupper(str_replace('_', ' ', trim((string)$part)));
                    if ($part !== '') {
                        $normalized_parts[] = $part;
                    }
                }
                $responsive_value = empty($normalized_parts) ? 'false' : implode(',', $normalized_parts);
            } else {
                $responsive_value = 'false';
            }
        }
        $colreorder    = isset($PARAMETROS['colreorder'])    ? $PARAMETROS['colreorder']    : false;
        $select        = isset($PARAMETROS['select'])        ? $PARAMETROS['select']        : false;
        $buttons       = isset($PARAMETROS['buttons'])       ? $PARAMETROS['buttons']       : false;
        $paging        = isset($PARAMETROS['paging'])        ? $PARAMETROS['paging']        : false;
        $ordering      = isset($PARAMETROS['ordering'])      ? $PARAMETROS['ordering']      : false;
        $reset         = isset($PARAMETROS['reset'])         ? $PARAMETROS['reset']         : false;
        $rowgroup      = isset($PARAMETROS['rowgroup'])      ? $PARAMETROS['rowgroup']      : false;
        $edit_button   = isset($PARAMETROS['edit_button'])   ? $PARAMETROS['edit_button']   : false;
        $export_all    = isset($PARAMETROS['export_all'])    ? $PARAMETROS['export_all']    : false;
        $staterestore  = isset($PARAMETROS['staterestore'])  ? $PARAMETROS['staterestore']  : false;
        $titulo_tabla  = isset($PARAMETROS['titulotabla'])   ? $PARAMETROS['titulotabla']   : false;
        $file_name     = isset($PARAMETROS['filename'])      ? $PARAMETROS['filename']      : false;
        $titulo_tabla = ($titulo_tabla === false || $titulo_tabla === '') ? 'Listado' : $titulo_tabla;
        $titulo_tabla_value = htmlspecialchars((string)$titulo_tabla, ENT_QUOTES, 'UTF-8');
        $file_name    = ($file_name    === false || $file_name === '')    ? 'Listado' : $file_name;
        $row_group    = ($rowgroup     === false || $rowgroup === '')     ? 'false'   : strtoupper(str_replace('_', ' ', (string)$rowgroup));
        $columncontrol_exclude = isset($PARAMETROS['columncontrolexclude']) ? $PARAMETROS['columncontrolexclude'] : false;
        $columncontrol_exclude_value = 'false';
        if (!($columncontrol_exclude === false || $columncontrol_exclude === '' || $columncontrol_exclude === null)) {
            $columncontrol_exclude_value = trim((string)$columncontrol_exclude);
            if ($columncontrol_exclude_value !== '') {
                $normalized_parts = [];
                foreach (explode(',', $columncontrol_exclude_value) as $part) {
                    $part = strtoupper(str_replace('_', ' ', trim((string)$part)));
                    if ($part !== '') {
                        $normalized_parts[] = $part;
                    }
                }
                $columncontrol_exclude_value = empty($normalized_parts) ? 'false' : implode(',', $normalized_parts);
            } else {
                $columncontrol_exclude_value = 'false';
            }
        }

        $row_group = htmlspecialchars($row_group, ENT_QUOTES, 'UTF-8');

        $buttons_value = 'false';
        if ($buttons === true || $buttons === 1 || $buttons === '1') {
            $buttons_value = 'all';
        } elseif ($buttons === false || $buttons === 0 || $buttons === '0' || $buttons === null) {
            $buttons_value = 'false';
        } else {
            $buttons_value = trim((string)$buttons);
            if ($buttons_value === '' || strtolower($buttons_value) === 'false') {
                $buttons_value = 'false';
            } elseif (strtolower($buttons_value) === 'true') {
                $buttons_value = 'all';
            }
        }
        $buttons_value = htmlspecialchars($buttons_value, ENT_QUOTES, 'UTF-8');

        $data_ = "";
        $data_ .= " data-conf-columncontrol='" . ($columncontrol ? "true" : "false") . "' ";
        $data_ .= " data-conf-rowgroup='"      . $row_group . "' ";
        $data_ .= " data-conf-titulotabla='"   . $titulo_tabla_value . "' ";
        $data_ .= " data-conf-filename='"      . $file_name . "' ";
        // $data_ .= " data-conf-responsive='"    . ($responsive ? "true" : "false") . "' ";
        $data_ .= " data-conf-responsive='"    . htmlspecialchars($responsive_value, ENT_QUOTES, 'UTF-8') . "' ";
        $data_ .= " data-conf-colreorder='"    . ($colreorder ? "true" : "false") . "' ";
        $data_ .= " data-conf-select='"        . ($select ? "true" : "false") . "' ";
        $data_ .= " data-conf-buttons='"       . $buttons_value . "' ";
        $data_ .= " data-conf-paging='"        . ($paging ? "true" : "false") . "' ";
        $data_ .= " data-conf-ordering='"      . ($ordering ? "true" : "false") . "' ";
        $data_ .= " data-conf-reset='"         . ($reset ? "true" : "false") . "' ";
        $data_ .= " data-conf-exportall='"     . ($export_all ? "true" : "false") . "' ";
        $data_ .= " data-conf-staterestore='"  . ($staterestore ? "true" : "false") . "' ";
        $data_ .= " data-conf-addfilter='"            . htmlspecialchars($add_filter_value, ENT_QUOTES, 'UTF-8') . "' ";
        $data_ .= " data-conf-columncontrolexclude='" . htmlspecialchars($columncontrol_exclude_value, ENT_QUOTES, 'UTF-8') . "' ";

        if (in_array($idtabla, $this->IDS, true)) {
            return false;
        }
        $this->IDS[] = $idtabla;
        $idtbody = $idtabla . '_todos';

        $tabla_marca = '<table id="' . $idtabla . '" '.$data_.' style="' . $style . '" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
        <thead>
            <tr>
                ';

        if ($edit_button) {
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

            if ($edit_button) {
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
                        //$column_content = str_replace("[$row_key]", $row_value, $column_content);
                        $column_content = str_replace("[$row_key]", ($row_value === null ? '' : (string)$row_value), $column_content);

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
            if ($idtabla === 'tabla_datos' && isset($estado['search']['search'])) {
                $estado['search']['search'] = '';
            }
            $estados[$idtabla] = $estado;
        }
        $json = json_encode($estados);
        $json = str_replace('|', '##PIPE##', $json);
        return $json; 
    }
    public function guardar_estado_datatables($tabla, $estado) {
        
        $usuario = (new security())->get_actual_user();
        $usuario = $this->escape_sql($usuario);
        $tabla   = $this->escape_sql($tabla);
        if ($tabla === 'tabla_datos') {
            return true;
        }
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
        $json = json_encode($estados);
        $json = str_replace('|', '##PIPE##', $json);
        return $json;
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

