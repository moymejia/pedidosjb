<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/utils.php';
require_once '../wisetech/datatables.php';

class datatable_configuracion extends table
{
    use utils;

    private $last_error = '';
    private $base_datos;
    private $ACCIONES = [];

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_seguridad', 'datatable_configuracion');
        $this->base_datos = prefijo . '_seguridad';
        $_MYSQL = new mysql($this->base_datos);
        $this->ACCIONES['opcion'] = $_MYSQL->getvalue("SELECT idaccion FROM accion WHERE nombre = 'opcion_configuracion_datatables' AND estado = 'ACTIVO' LIMIT 1", 'idaccion');
        $this->ACCIONES['modificar'] = $_MYSQL->getvalue("SELECT idaccion FROM accion WHERE nombre = 'modificar_configuracion_datatables' AND estado = 'ACTIVO' LIMIT 1", 'idaccion');

        if (!isset($PARAMETROS['operacion'])) {
            return;
        }

        if ($PARAMETROS['operacion'] === 'cargar_editor') {
            if (!isset($PARAMETROS['idtabla']) || trim((string)$PARAMETROS['idtabla']) === '') {
                self::end_error('Debe seleccionar una tabla.');
            }
            if (trim((string)$PARAMETROS['idtabla']) === 'tabla_datos') {
                self::end_error('tabla_datos no utiliza configuración almacenada.');
            }
            $_SECURITY = new security($this->ACCIONES['opcion']);
            echo '|correcto|' . $this->cargar_editor($PARAMETROS['idtabla'], $_SECURITY->get_actual_user());
            return;
        }

        if ($PARAMETROS['operacion'] === 'cargar_listado') {
            $_SECURITY = new security($this->ACCIONES['opcion']);
            echo '|correcto|' . $this->tabla_listado($_SECURITY->get_actual_user());
            return;
        }

        if ($PARAMETROS['operacion'] === 'guardar') {
            if ($this->guardar_seccion($PARAMETROS)) {
                self::end_success('editado');
            } else {
                self::end_error($this->last_error);
            }
        }
    }

    public function cargar_opcion()
    {
        $_SECURITY = new security($this->ACCIONES['opcion']);
        $DATA = ['tabla_configuraciones' => $this->tabla_listado($_SECURITY->get_actual_user())];
        $_HTML = new html('datatable_configuracion', $DATA);
        return $_HTML->get_html();
    }

    private function tabla_listado($usuario)
    {
        $_MYSQL = new mysql($this->base_datos);
        $usuario_sql = $this->escape_sql($usuario);
        $sql = "SELECT idtabla acciones, idtabla, creado_en, creado_por, actualizado_en, actualizado_por,
                (SELECT COUNT(1)
                 FROM {$this->base_datos}.datatable_configuracion_detalle d
                 WHERE d.idtabla = c.idtabla AND d.usuario = c.usuario) cantidad_parametros
            FROM {$this->base_datos}.datatable_configuracion c
            WHERE c.idtabla <> 'tabla_datos' AND c.usuario = '$usuario_sql'
            ORDER BY idtabla";
        $resultado = $_MYSQL->getresult($sql);
        if ($resultado === false) {
            utils::report_error(bd_error, $usuario, 'No fue posible cargar las configuraciones DataTables del usuario.');
            return "<div class='alert alert-danger'>No fue posible cargar las configuraciones.</div>";
        }

        $CONFIG_TABLA = [
            'responsive' => true,
            'colreorder' => true,
            'paging' => true,
            'ordering' => true,
            'reset' => true,
            'buttons' => false,
            'select' => false,
            'staterestore' => false,
            'titulotabla' => 'Configuración DataTables',
            'filename' => 'Configuracion_DataTables'
        ];
        $SPECIAL_COLUMNS = [
            'acciones' => "<button type='button' class='btn btn-primary btn-sm' onclick=\"cargarEditorConfiguracionDatatable('[idtabla]');\"><i class='far fa-edit'></i> Editar</button>"
        ];

        $_DATATABLES = new datatables();
        return $_DATATABLES->addTable(
            $resultado,
            $CONFIG_TABLA,
            '',
            $SPECIAL_COLUMNS,
            [],
            [],
            'tabla_configuracion_datatables'
        );
    }

    private function cargar_editor($idtabla, $usuario)
    {
        $CONFIGURACION = $this->obtener_configuracion($idtabla, $usuario);
        if ($CONFIGURACION === false) {
            return "<div class='alert alert-danger'>No fue posible cargar la configuración.</div>";
        }

        $CAMPOS = $this->lista($CONFIGURACION, 'query_fields');
        $idtabla_html = $this->html($idtabla);

        $html = "<div class='card datatable-config-editor'>
            <div class='card-header header-titulo'><strong style='color:#ffffff;'>Configuración: $idtabla_html</strong></div>
            <div class='card-body'>
                <ul class='nav nav-tabs' role='tablist'>
                    <li class='nav-item'><a class='nav-link active' data-toggle='tab' href='#dtconf_comportamiento' role='tab'>Comportamiento</a></li>
                    <li class='nav-item'><a class='nav-link' data-toggle='tab' href='#dtconf_columnas' role='tab'>Columnas y filtros</a></li>
                    <li class='nav-item'><a class='nav-link' data-toggle='tab' href='#dtconf_exportacion' role='tab'>Exportación</a></li>
                    <li class='nav-item'><a class='nav-link' data-toggle='tab' href='#dtconf_presentacion' role='tab'>Presentación</a></li>
                </ul>
                <div class='tab-content p-t-20'>";

        $html .= $this->formulario_comportamiento($idtabla, $CONFIGURACION, $CAMPOS);
        $html .= $this->formulario_columnas($idtabla, $CONFIGURACION, $CAMPOS);
        $html .= $this->formulario_exportacion($idtabla, $CONFIGURACION);
        $html .= $this->formulario_presentacion($idtabla, $CONFIGURACION);

        $html .= "</div>
                <div class='m-t-20'><button type='button' class='btn btn-inverse' onclick='cerrarEditorConfiguracionDatatable();'>Cerrar</button></div>
            </div>
        </div>";

        return $html;
    }

    private function formulario_comportamiento($idtabla, $CONFIGURACION, $CAMPOS)
    {
        $BOOLEANOS = [
            'colreorder' => 'Reordenar columnas',
            'select' => 'Seleccionar filas',
            'paging' => 'Paginación',
            'ordering' => 'Ordenamiento',
            'reset' => 'Botón reiniciar',
            'edit_button' => 'Botón editar',
            'staterestore' => 'Guardar vistas'
        ];
        $controles = '';
        foreach ($BOOLEANOS as $parametro => $etiqueta) {
            $controles .= $this->control_booleano($parametro, $etiqueta, $this->valor($CONFIGURACION, $parametro));
        }

        $responsive_valor = $this->valor($CONFIGURACION, 'responsive');
        $responsive_sin_prioridades = $responsive_valor === true || $responsive_valor === 1 || $responsive_valor === '1' || $responsive_valor === 'true';
        $RESPONSIVE = $responsive_sin_prioridades ? [] : $this->lista($CONFIGURACION, 'responsive');
        $responsive_activo = !($responsive_valor === false || $responsive_valor === 'false' || $responsive_valor === '' || $responsive_valor === null);
        $responsive_prioridades = implode(',', $RESPONSIVE);
        $controles .= "<div class='form-group col-md-4'>
            <label for='dtconf_responsive_activo' title='" . $this->html($this->descripcion_parametro('responsive')) . "'>Responsive</label>
            <select class='form-control' id='dtconf_responsive_activo' name='responsive_activo' onchange='mostrarPrioridadesResponsiveConfiguracionDatatable();'>
                <option value='true'" . ($responsive_activo ? ' selected' : '') . ">TRUE</option>
                <option value='false'" . (!$responsive_activo ? ' selected' : '') . ">FALSE</option>
            </select>
        </div>
        <div class='form-group col-md-8' id='dtconf_responsive_prioridades_contenedor'" . (!$responsive_activo ? " style='display:none;'" : '') . ">
            <label for='dtconf_responsive_prioridades'>Prioridades responsive</label>
            <input type='text' class='form-control' id='dtconf_responsive_prioridades' name='responsive_prioridades' value='" . $this->html($responsive_prioridades) . "'>
            <small>Ingrese campos separados por coma, en orden de mayor a menor prioridad.</small>
        </div>";

        $rowgroup = (string)$this->valor($CONFIGURACION, 'rowgroup');
        $opciones = "<option value='false'" . ($rowgroup === 'false' || $rowgroup === '' ? ' selected' : '') . ">Sin agrupación</option>";
        foreach ($CAMPOS as $campo) {
            $seleccionado = ($rowgroup === $campo) ? ' selected' : '';
            $opciones .= "<option value='" . $this->html($campo) . "'$seleccionado>" . $this->html($campo) . "</option>";
        }

        return "<div class='tab-pane active' id='dtconf_comportamiento' role='tabpanel'>
            <form onsubmit='guardarSeccionConfiguracionDatatable(this); return false;'>
                " . $this->campos_ocultos_formulario($idtabla, 'comportamiento') . "
                <div class='row'>$controles
                    <div class='form-group col-md-4'>
                        <label for='dtconf_rowgroup' title='" . $this->html($this->descripcion_parametro('rowgroup')) . "'>Agrupar por</label>
                        <select class='form-control' id='dtconf_rowgroup' name='rowgroup'>$opciones</select>
                    </div>
                </div>
                <div class='alert alert-info'>Active el botón Editar únicamente en mantenimientos que tengan formulario y función de edición compatibles.</div>
                <button type='submit' class='btn btn-success'>Guardar comportamiento</button>
            </form>
        </div>";
    }

    private function formulario_columnas($idtabla, $CONFIGURACION, $CAMPOS)
    {
        $filas_campos = '';
        $ALINEACIONES = $this->asociativo($CONFIGURACION, 'aligments');
        $COLUMNCONTROL_EXCLUIDOS = $this->lista($CONFIGURACION, 'columncontrolexclude');
        $COLUMNCONTROL_INCLUIDOS = array_values(array_diff($CAMPOS, $COLUMNCONTROL_EXCLUIDOS));
        foreach ($CAMPOS as $indice => $campo) {
            $alineacion = isset($ALINEACIONES[$campo]) ? $ALINEACIONES[$campo] : '';
            $filas_campos .= "<tr>
                <td>" . ($indice + 1) . "</td>
                <td>" . $this->html($campo) . "</td>
                <td><select class='form-control form-control-sm' name='aligments[" . $this->html($campo) . "]' title='" . $this->html($this->descripcion_parametro('aligments')) . "'>
                    <option value=''" . ($alineacion === '' ? ' selected' : '') . ">Predeterminada</option>
                    <option value='left'" . ($alineacion === 'left' ? ' selected' : '') . ">Izquierda</option>
                    <option value='center'" . ($alineacion === 'center' ? ' selected' : '') . ">Centro</option>
                    <option value='right'" . ($alineacion === 'right' ? ' selected' : '') . ">Derecha</option>
                </select></td>
            </tr>";
        }
        if ($filas_campos === '') {
            $filas_campos = "<tr><td colspan='3' class='text-center'>No hay campos registrados.</td></tr>";
        }

        return "<div class='tab-pane' id='dtconf_columnas' role='tabpanel'>
            <form onsubmit='guardarSeccionConfiguracionDatatable(this); return false;'>
                " . $this->campos_ocultos_formulario($idtabla, 'columnas') . "
                <div class='row'>
                    " . $this->control_booleano('columncontrol', 'ColumnControl', $this->valor($CONFIGURACION, 'columncontrol')) . "
                    " . $this->control_multiple('add_filter', 'Columnas con filtro', $CAMPOS, $this->lista($CONFIGURACION, 'add_filter')) . "
                    " . $this->control_multiple('columncontrolinclude', 'Columnas con ColumnControl', $CAMPOS, $COLUMNCONTROL_INCLUIDOS) . "
                    " . $this->control_multiple('hidden_columns', 'Columnas ocultas', $CAMPOS, $this->lista($CONFIGURACION, 'hidden_columns')) . "
                </div>
                <h5 class='m-t-20' title='" . $this->html($this->descripcion_parametro('query_fields')) . "'>Campos de la consulta y alineación</h5>
                <div class='table-responsive'><table class='table table-bordered table-sm'>
                    <thead><tr><th>Orden</th><th>Campo</th><th>Alineación</th></tr></thead>
                    <tbody>$filas_campos</tbody>
                </table></div>
                <button type='submit' class='btn btn-success'>Guardar columnas y filtros</button>
            </form>
        </div>";
    }

    private function formulario_exportacion($idtabla, $CONFIGURACION)
    {
        $BOTONES = ['copy', 'csv', 'excel', 'pdf', 'print'];
        $titulo_tabla = $this->texto($CONFIGURACION, 'titulotabla');

        return "<div class='tab-pane' id='dtconf_exportacion' role='tabpanel'>
            <form onsubmit='guardarSeccionConfiguracionDatatable(this); return false;'>
                " . $this->campos_ocultos_formulario($idtabla, 'exportacion') . "
                <div class='row'>
                    " . $this->control_multiple('buttons', 'Botones de exportación', $BOTONES, $this->lista($CONFIGURACION, 'buttons')) . "
                    <div class='form-group col-md-4'>
                        <label for='dtconf_filename' title='" . $this->html($this->descripcion_parametro('filename')) . "'>Nombre del archivo</label>
                        <input type='text' class='form-control' id='dtconf_filename' name='filename' value='" . $this->html($this->texto($CONFIGURACION, 'filename')) . "' maxlength='200'>
                    </div>
                    <div class='form-group col-md-4'>
                        <label for='dtconf_titulotabla' title='" . $this->html($this->descripcion_parametro('titulotabla')) . "'>Título de exportación</label>
                        <input type='text' class='form-control' id='dtconf_titulotabla' name='titulotabla' value='" . $this->html($titulo_tabla) . "' maxlength='250'>
                    </div>
                </div>
                <button type='submit' class='btn btn-success'>Guardar exportación</button>
            </form>
        </div>";
    }

    private function formulario_presentacion($idtabla, $CONFIGURACION)
    {
        $style = $this->texto($CONFIGURACION, 'style');
        $activo = ($style !== '' && strtolower($style) !== 'false');

        return "<div class='tab-pane' id='dtconf_presentacion' role='tabpanel'>
            <form onsubmit='guardarSeccionConfiguracionDatatable(this); return false;'>
                " . $this->campos_ocultos_formulario($idtabla, 'presentacion') . "
                <div class='row'>
                    <div class='form-group col-md-4'>
                        <label for='dtconf_style_activo' title='" . $this->html($this->descripcion_parametro('style_activo')) . "'>Aplicar estilo personalizado</label>
                        <select class='form-control' id='dtconf_style_activo' name='style_activo' onchange='mostrarStyleConfiguracionDatatable();'>
                            <option value='true'" . ($activo ? ' selected' : '') . ">TRUE</option>
                            <option value='false'" . (!$activo ? ' selected' : '') . ">FALSE</option>
                        </select>
                    </div>
                    <div class='form-group col-md-8' id='dtconf_style_contenedor'" . (!$activo ? " style='display:none;'" : '') . ">
                        <label for='dtconf_style' title='style son estilos de css que se agregan directamente en la etiqueta table'>Declaraciones CSS</label>
                        <textarea class='form-control' id='dtconf_style' name='style' rows='5'>" . $this->html($activo ? $style : '') . "</textarea>
                        <small>Ejemplo: border-collapse:collapse; margin-top:8mm; width:100%;</small>
                    </div>
                </div>
                <div class='alert alert-warning'>No se permiten selectores, llaves, HTML, JavaScript, url() ni expression().</div>
                <button type='submit' class='btn btn-success'>Guardar presentación</button>
            </form>
        </div>";
    }

    private function guardar_seccion($PARAMETROS)
    {
        $_SECURITY = new security($this->ACCIONES['modificar']);
        $usuario = $_SECURITY->get_actual_user();
        if (!isset($PARAMETROS['idtabla'], $PARAMETROS['seccion'])) {
            $this->last_error = 'Datos incompletos.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        $idtabla = trim((string)$PARAMETROS['idtabla']);
        $seccion = trim((string)$PARAMETROS['seccion']);
        if ($idtabla === 'tabla_datos') {
            $this->last_error = 'tabla_datos no utiliza configuración almacenada.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }
        $CONFIGURACION_ACTUAL = $this->obtener_configuracion($idtabla, $usuario);
        if ($idtabla === '' || $CONFIGURACION_ACTUAL === false) {
            $this->last_error = 'Configuración no encontrada.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        $CAMPOS = $this->lista($CONFIGURACION_ACTUAL, 'query_fields');
        $CAMBIOS = $this->preparar_cambios($seccion, $PARAMETROS, $CAMPOS);
        if ($CAMBIOS === false) {
            return false;
        }
        $CAMBIOS['export_all'] = false;

        $_MYSQL = new mysql($this->base_datos);
        if (!$_MYSQL->put('START TRANSACTION')) {
            $this->last_error = 'No fue posible iniciar la operación.';
            utils::report_error(bd_error, [$usuario, $idtabla], $this->last_error);
            return false;
        }

        $BITACORA = [];
        foreach ($CAMBIOS as $parametro => $NUEVO_VALOR) {
            $VALOR_ANTERIOR = isset($CONFIGURACION_ACTUAL[$parametro]) ? $CONFIGURACION_ACTUAL[$parametro] : false;
            if ($this->valores_iguales($VALOR_ANTERIOR, $NUEVO_VALOR)) {
                continue;
            }
            if (!$this->guardar_parametro($_MYSQL, $idtabla, $parametro, $NUEVO_VALOR, $usuario)) {
                $_MYSQL->put('ROLLBACK');
                return false;
            }
            $BITACORA[] = [
                'parametro' => $parametro,
                'cambio' => $this->resumen_valor($VALOR_ANTERIOR) . ' => ' . $this->resumen_valor($NUEVO_VALOR)
            ];
        }

        if (empty($BITACORA)) {
            if (!$_MYSQL->put('COMMIT')) {
                $_MYSQL->put('ROLLBACK');
                $this->last_error = 'No fue posible finalizar la operación.';
                utils::report_error(bd_error, [$usuario, $idtabla], $this->last_error);
                return false;
            }
            return true;
        }

        $idtabla_sql = $this->escape_sql($idtabla);
        $usuario_sql = $this->escape_sql($usuario);
        $sql = "UPDATE {$this->base_datos}.datatable_configuracion
            SET actualizado_en = NOW(), actualizado_por = '$usuario_sql'
            WHERE idtabla = '$idtabla_sql' AND usuario = '$usuario_sql'";
        if (!$_MYSQL->put($sql) || !$_MYSQL->put('COMMIT')) {
            $_MYSQL->put('ROLLBACK');
            $this->last_error = 'No fue posible guardar la configuración.';
            utils::report_error(bd_error, [$usuario, $idtabla], $this->last_error);
            return false;
        }

        foreach ($BITACORA as $REGISTRO_BITACORA) {
            $cambio_bitacora = str_replace("'", '’', $REGISTRO_BITACORA['cambio']);
            $_SECURITY->registrar_bitacora(
                $this->ACCIONES['modificar'],
                $idtabla,
                $REGISTRO_BITACORA['parametro'],
                substr($cambio_bitacora, 0, 100)
            );
        }

        return true;
    }

    private function preparar_cambios($seccion, $PARAMETROS, $CAMPOS)
    {
        if ($seccion === 'comportamiento') {
            $CAMBIOS = [];
            foreach (['colreorder', 'select', 'paging', 'ordering', 'reset', 'edit_button', 'staterestore'] as $parametro) {
                $CAMBIOS[$parametro] = $this->validar_booleano($PARAMETROS, $parametro);
                if ($CAMBIOS[$parametro] === null) return false;
            }
            $responsive_activo = $this->validar_booleano($PARAMETROS, 'responsive_activo');
            if ($responsive_activo === null) return false;
            $CAMBIOS['responsive'] = false;
            if ($responsive_activo) {
                $prioridades_texto = isset($PARAMETROS['responsive_prioridades']) ? trim((string)$PARAMETROS['responsive_prioridades']) : '';
                $PRIORIDADES = $prioridades_texto === '' ? [] : array_values(array_filter(array_map('trim', explode(',', $prioridades_texto)), function ($campo) {
                    return $campo !== '';
                }));
                $PRIORIDADES = $this->validar_lista_campos($PRIORIDADES, $CAMPOS);
                if ($PRIORIDADES === false) return false;
                $CAMBIOS['responsive'] = empty($PRIORIDADES) ? true : $PRIORIDADES;
            }
            $rowgroup = isset($PARAMETROS['rowgroup']) ? trim((string)$PARAMETROS['rowgroup']) : 'false';
            if ($rowgroup !== 'false' && !in_array($rowgroup, $CAMPOS, true)) {
                $this->last_error = 'La columna de agrupación no es válida.';
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);
                return false;
            }
            $CAMBIOS['rowgroup'] = $rowgroup;
            return $CAMBIOS;
        }

        if ($seccion === 'columnas') {
            $columncontrol = $this->validar_booleano($PARAMETROS, 'columncontrol');
            if ($columncontrol === null) return false;
            $COLUMNCONTROL_INCLUIDOS = $this->validar_lista_campos(
                isset($PARAMETROS['columncontrolinclude']) ? $PARAMETROS['columncontrolinclude'] : [],
                $CAMPOS
            );
            if ($COLUMNCONTROL_INCLUIDOS === false) return false;
            $CAMBIOS = [
                'columncontrol' => $columncontrol,
                'add_filter' => $this->validar_lista_campos(isset($PARAMETROS['add_filter']) ? $PARAMETROS['add_filter'] : [], $CAMPOS),
                'columncontrolexclude' => array_values(array_diff($CAMPOS, $COLUMNCONTROL_INCLUIDOS)),
                'hidden_columns' => $this->validar_lista_campos(isset($PARAMETROS['hidden_columns']) ? $PARAMETROS['hidden_columns'] : [], $CAMPOS)
            ];
            if ($CAMBIOS['add_filter'] === false || $CAMBIOS['hidden_columns'] === false) return false;
            $ALINEACIONES = [];
            $ALINEACIONES_RECIBIDAS = isset($PARAMETROS['aligments']) && is_array($PARAMETROS['aligments']) ? $PARAMETROS['aligments'] : [];
            foreach ($ALINEACIONES_RECIBIDAS as $campo => $alineacion) {
                if (!in_array($campo, $CAMPOS, true) || !in_array($alineacion, ['', 'left', 'center', 'right'], true)) {
                    $this->last_error = 'La configuración de alineación no es válida.';
                    utils::report_error(validation_error, $PARAMETROS, $this->last_error);
                    return false;
                }
                if ($alineacion !== '') $ALINEACIONES[$campo] = $alineacion;
            }
            $CAMBIOS['aligments'] = $ALINEACIONES;
            return $CAMBIOS;
        }

        if ($seccion === 'exportacion') {
            $BOTONES = isset($PARAMETROS['buttons']) ? $PARAMETROS['buttons'] : [];
            $BOTONES = $this->validar_lista_permitida($BOTONES, ['copy', 'csv', 'excel', 'pdf', 'print'], 'Los botones seleccionados no son válidos.');
            if ($BOTONES === false) return false;
            $filename = isset($PARAMETROS['filename']) ? trim((string)$PARAMETROS['filename']) : '';
            if (mb_strlen($filename, 'UTF-8') > 200 || ($filename !== '' && !preg_match('/^[\p{L}\p{N} _.-]+$/u', $filename))) {
                $this->last_error = 'El nombre del archivo contiene caracteres no permitidos.';
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);
                return false;
            }
            $titulo_tabla = isset($PARAMETROS['titulotabla']) ? trim(strip_tags((string)$PARAMETROS['titulotabla'])) : '';
            if (mb_strlen($titulo_tabla, 'UTF-8') > 250) {
                $this->last_error = 'El título debe tener como máximo 250 caracteres.';
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);
                return false;
            }
            return [
                'buttons' => $BOTONES,
                'filename' => ($filename === '' ? false : $filename),
                'titulotabla' => ($titulo_tabla === '' ? false : $titulo_tabla)
            ];
        }

        if ($seccion === 'presentacion') {
            $activo = $this->validar_booleano($PARAMETROS, 'style_activo');
            if ($activo === null) return false;
            if ($activo === false) return ['style' => false];
            $style = isset($PARAMETROS['style']) ? trim((string)$PARAMETROS['style']) : '';
            if (!$this->validar_style($style)) return false;
            return ['style' => $style];
        }

        $this->last_error = 'Sección no reconocida.';
        utils::report_error(validation_error, $PARAMETROS, $this->last_error);
        return false;
    }

    private function guardar_parametro($_MYSQL, $idtabla, $parametro, $VALOR, $usuario)
    {
        $idtabla_sql = $this->escape_sql($idtabla);
        $parametro_sql = $this->escape_sql($parametro);
        $usuario_sql = $this->escape_sql($usuario);
        $idtabla_detalle = $_MYSQL->getvalue("SELECT idtabla_detalle
            FROM {$this->base_datos}.datatable_configuracion_detalle
            WHERE idtabla = '$idtabla_sql' AND usuario = '$usuario_sql' AND parametro = '$parametro_sql' LIMIT 1", 'idtabla_detalle');

        $es_lista = is_array($VALOR);
        $tiene_valores = $es_lista && !empty($VALOR);
        $valor_sql = $tiene_valores ? 'NULL' : "'" . $this->escape_sql($this->valor_texto($VALOR)) . "'";
        if ($idtabla_detalle === false) {
            $sql = "INSERT INTO {$this->base_datos}.datatable_configuracion_detalle
                (usuario, idtabla, parametro, valor, creado_por)
                VALUES ('$usuario_sql', '$idtabla_sql', '$parametro_sql', $valor_sql, '$usuario_sql')";
            if (!$_MYSQL->put($sql)) {
                $this->last_error = 'No fue posible crear el parámetro ' . $parametro . '.';
                utils::report_error(bd_error, [$usuario, $idtabla, $parametro], $this->last_error);
                return false;
            }
            $idtabla_detalle = $_MYSQL->last_id();
        } else {
            $sql = "UPDATE {$this->base_datos}.datatable_configuracion_detalle
                SET valor = $valor_sql, actualizado_en = NOW(), actualizado_por = '$usuario_sql'
                WHERE idtabla_detalle = '$idtabla_detalle'";
            if (!$_MYSQL->put($sql)) {
                $this->last_error = 'No fue posible actualizar el parámetro ' . $parametro . '.';
                utils::report_error(bd_error, [$usuario, $idtabla, $parametro], $this->last_error);
                return false;
            }
        }

        if (!$_MYSQL->put("DELETE FROM {$this->base_datos}.datatable_configuracion_detalle_valor WHERE idtabla_detalle = '$idtabla_detalle'")) {
            $this->last_error = 'No fue posible actualizar los valores de ' . $parametro . '.';
            utils::report_error(bd_error, [$usuario, $idtabla, $parametro], $this->last_error);
            return false;
        }

        if (!$es_lista || empty($VALOR)) return true;

        $orden = 1;
        foreach ($VALOR as $clave => $valor_item) {
            $clave_sql = is_string($clave) ? "'" . $this->escape_sql($clave) . "'" : 'NULL';
            $valor_item_sql = $this->escape_sql($valor_item);
            $sql = "INSERT INTO {$this->base_datos}.datatable_configuracion_detalle_valor
                (idtabla_detalle, clave, valor, orden, creado_por)
                VALUES ('$idtabla_detalle', $clave_sql, '$valor_item_sql', '$orden', '$usuario_sql')";
            if (!$_MYSQL->put($sql)) {
                $this->last_error = 'No fue posible guardar los valores de ' . $parametro . '.';
                utils::report_error(bd_error, [$usuario, $idtabla, $parametro], $this->last_error);
                return false;
            }
            $orden++;
        }
        return true;
    }

    private function obtener_configuracion($idtabla, $usuario)
    {
        $_MYSQL = new mysql($this->base_datos);
        $idtabla_sql = $this->escape_sql($idtabla);
        $usuario_sql = $this->escape_sql($usuario);
        if (!$_MYSQL->getvalue("SELECT COUNT(1) existe FROM {$this->base_datos}.datatable_configuracion WHERE idtabla = '$idtabla_sql' AND usuario = '$usuario_sql'", 'existe')) {
            return false;
        }

        $resultado = $_MYSQL->getresult("SELECT idtabla_detalle, parametro, valor
            FROM {$this->base_datos}.datatable_configuracion_detalle
            WHERE idtabla = '$idtabla_sql' AND usuario = '$usuario_sql'
            ORDER BY idtabla_detalle");
        if ($resultado === false) {
            utils::report_error(bd_error, [$usuario, $idtabla], 'No fue posible consultar los parámetros DataTables.');
            return false;
        }

        $CONFIGURACION = [];
        $DETALLES = [];
        while ($FILA = $_MYSQL->getrowresult($resultado)) {
            $idtabla_detalle = (int)$FILA['idtabla_detalle'];
            $DETALLES[$idtabla_detalle] = $FILA['parametro'];
            $CONFIGURACION[$FILA['parametro']] = $this->normalizar($FILA['valor']);
        }

        if (empty($DETALLES)) return $CONFIGURACION;
        $IDS = implode(',', array_keys($DETALLES));
        $resultado = $_MYSQL->getresult("SELECT idtabla_detalle, clave, valor
            FROM {$this->base_datos}.datatable_configuracion_detalle_valor
            WHERE idtabla_detalle IN ($IDS)
            ORDER BY idtabla_detalle, orden");
        if ($resultado === false) {
            utils::report_error(bd_error, [$usuario, $idtabla], 'No fue posible consultar los valores DataTables.');
            return false;
        }

        $LISTAS = [];
        while ($FILA = $_MYSQL->getrowresult($resultado)) {
            $idtabla_detalle = (int)$FILA['idtabla_detalle'];
            if (!isset($LISTAS[$idtabla_detalle])) $LISTAS[$idtabla_detalle] = [];
            if ($FILA['clave'] === null || $FILA['clave'] === '') {
                $LISTAS[$idtabla_detalle][] = $FILA['valor'];
            } else {
                $LISTAS[$idtabla_detalle][$FILA['clave']] = $FILA['valor'];
            }
        }
        foreach ($LISTAS as $idtabla_detalle => $LISTA) {
            $CONFIGURACION[$DETALLES[$idtabla_detalle]] = $LISTA;
        }
        return $CONFIGURACION;
    }

    private function validar_booleano($PARAMETROS, $parametro)
    {
        if (!isset($PARAMETROS[$parametro]) || !in_array($PARAMETROS[$parametro], ['true', 'false'], true)) {
            $this->last_error = 'El parámetro ' . $parametro . ' debe ser TRUE o FALSE.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return null;
        }
        return $PARAMETROS[$parametro] === 'true';
    }

    private function validar_lista_campos($VALORES, $CAMPOS)
    {
        return $this->validar_lista_permitida($VALORES, $CAMPOS, 'La lista contiene columnas no válidas.');
    }

    private function validar_lista_permitida($VALORES, $PERMITIDOS, $mensaje)
    {
        $VALORES = is_array($VALORES) ? array_values(array_unique($VALORES)) : [];
        foreach ($VALORES as $valor) {
            if (!in_array($valor, $PERMITIDOS, true)) {
                $this->last_error = $mensaje;
                utils::report_error(validation_error, $VALORES, $this->last_error);
                return false;
            }
        }
        return $VALORES;
    }

    private function validar_style($style)
    {
        if ($style === '') {
            $this->last_error = 'Debe ingresar las declaraciones CSS.';
            utils::report_error(validation_error, $style, $this->last_error);
            return false;
        }
        if (preg_match('/[{}<>"\']|url\s*\(|expression\s*\(|javascript\s*:/i', $style)) {
            $this->last_error = 'El estilo contiene elementos no permitidos.';
            utils::report_error(validation_error, $style, $this->last_error);
            return false;
        }
        $PROPIEDADES = ['border-collapse', 'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left', 'width', 'max-width', 'min-width', 'font-size', 'table-layout', 'text-align'];
        foreach (explode(';', $style) as $declaracion) {
            $declaracion = trim($declaracion);
            if ($declaracion === '') continue;
            $PARTES = explode(':', $declaracion, 2);
            if (count($PARTES) !== 2 || !in_array(strtolower(trim($PARTES[0])), $PROPIEDADES, true) || trim($PARTES[1]) === '') {
                $this->last_error = 'El estilo contiene una propiedad o valor no permitido.';
                utils::report_error(validation_error, $style, $this->last_error);
                return false;
            }
        }
        return true;
    }

    private function control_booleano($nombre, $etiqueta, $valor)
    {
        $es_true = ($valor === true || $valor === 'true' || $valor === 1 || $valor === '1');
        $descripcion = $this->html($this->descripcion_parametro($nombre));
        return "<div class='form-group col-md-4'>
            <label for='dtconf_$nombre' title='$descripcion'>" . $this->html($etiqueta) . "</label>
            <select class='form-control' id='dtconf_$nombre' name='$nombre'>
                <option value='true'" . ($es_true ? ' selected' : '') . ">TRUE</option>
                <option value='false'" . (!$es_true ? ' selected' : '') . ">FALSE</option>
            </select>
        </div>";
    }

    private function control_multiple($nombre, $etiqueta, $OPCIONES, $SELECCIONADOS)
    {
        $options = '';
        $descripcion = $this->html($this->descripcion_parametro($nombre));
        foreach ($OPCIONES as $opcion) {
            $selected = in_array($opcion, $SELECCIONADOS, true) ? ' selected' : '';
            $options .= "<option value='" . $this->html($opcion) . "'$selected>" . $this->html($opcion) . "</option>";
        }
        return "<div class='form-group col-md-4'>
            <label for='dtconf_$nombre' title='$descripcion'>" . $this->html($etiqueta) . "</label>
            <select class='form-control select2' style='width:100%;' id='dtconf_$nombre' name='{$nombre}[]' multiple>$options</select>
        </div>";
    }

    private function descripcion_parametro($parametro)
    {
        $DESCRIPCIONES = [
            'responsive' => 'Adapta la tabla al espacio disponible y permite ordenar las columnas por prioridad.',
            'colreorder' => 'Permite cambiar el orden de las columnas arrastrándolas.',
            'select' => 'Permite seleccionar una o varias filas de la tabla.',
            'paging' => 'Divide los registros en páginas.',
            'ordering' => 'Permite ordenar los registros desde los encabezados.',
            'reset' => 'Muestra el botón para restablecer filtros, orden y visibilidad.',
            'edit_button' => 'Agrega el botón Editar en mantenimientos compatibles.',
            'staterestore' => 'Permite guardar y recuperar vistas personalizadas de la tabla.',
            'rowgroup' => 'Agrupa las filas utilizando la columna seleccionada.',
            'columncontrol' => 'Activa los controles adicionales disponibles en los encabezados.',
            'add_filter' => 'Indica las columnas que tendrán un filtro adicional.',
            'columncontrolinclude' => 'Indica las columnas que sí tendrán ColumnControl.',
            'hidden_columns' => 'Indica las columnas que se mostrarán ocultas inicialmente.',
            'query_fields' => 'Muestra los campos de la consulta y permite definir su alineación.',
            'aligments' => 'Define la alineación del contenido de cada columna.',
            'buttons' => 'Selecciona los botones de exportación disponibles para la tabla.',
            'filename' => 'Define el nombre utilizado al generar archivos de exportación.',
            'titulotabla' => 'Define el título utilizado en las exportaciones e impresión.',
            'style_activo' => 'Activa o desactiva los estilos CSS personalizados de la tabla.'
        ];
        return isset($DESCRIPCIONES[$parametro]) ? $DESCRIPCIONES[$parametro] : '';
    }

    private function campos_ocultos_formulario($idtabla, $seccion)
    {
        return "<input type='hidden' name='table' value='datatable_configuracion'>
            <input type='hidden' name='idtabla' value='" . $this->html($idtabla) . "'>
            <input type='hidden' name='seccion' value='" . $this->html($seccion) . "'>";
    }

    private function valor($CONFIGURACION, $parametro)
    {
        return isset($CONFIGURACION[$parametro]) ? $CONFIGURACION[$parametro] : false;
    }

    private function texto($CONFIGURACION, $parametro)
    {
        $valor = $this->valor($CONFIGURACION, $parametro);
        return ($valor === false || is_array($valor)) ? '' : (string)$valor;
    }

    private function lista($CONFIGURACION, $parametro)
    {
        $valor = $this->valor($CONFIGURACION, $parametro);
        if (is_array($valor)) return array_values($valor);
        if ($valor === false || $valor === '' || $valor === null) return [];
        return array_values(array_filter(array_map('trim', explode(',', (string)$valor)), function ($item) { return $item !== ''; }));
    }

    private function asociativo($CONFIGURACION, $parametro)
    {
        $valor = $this->valor($CONFIGURACION, $parametro);
        return is_array($valor) ? $valor : [];
    }

    private function normalizar($valor)
    {
        if ($valor === null) return false;
        $normalizado = strtolower(trim((string)$valor));
        if ($normalizado === 'true') return true;
        if ($normalizado === 'false') return false;
        return $valor;
    }

    private function valor_texto($valor)
    {
        if ($valor === true) return 'true';
        if ($valor === false || $valor === null || $valor === '') return 'false';
        return (string)$valor;
    }

    private function resumen_valor($VALOR)
    {
        if ($VALOR === true) return 'true';
        if ($VALOR === false || $VALOR === null || $VALOR === '') return 'false';
        if (!is_array($VALOR)) return (string)$VALOR;
        $PARTES = [];
        foreach ($VALOR as $clave => $valor) {
            $PARTES[] = is_string($clave) ? $clave . ':' . $valor : $valor;
        }
        return implode(',', $PARTES);
    }

    private function valores_iguales($VALOR_ANTERIOR, $NUEVO_VALOR)
    {
        return serialize($this->normalizar_comparacion($VALOR_ANTERIOR)) === serialize($this->normalizar_comparacion($NUEVO_VALOR));
    }

    private function normalizar_comparacion($VALOR)
    {
        if ($VALOR === false || $VALOR === null || $VALOR === '' || (is_array($VALOR) && empty($VALOR))) {
            return false;
        }
        if ($VALOR === true) return true;
        if (!is_array($VALOR)) return (string)$VALOR;

        $RESULTADO = [];
        foreach ($VALOR as $clave => $valor) {
            $RESULTADO[$clave] = $this->normalizar_comparacion($valor);
        }
        return $RESULTADO;
    }

    private function escape_sql($valor)
    {
        return str_replace("'", "\\'", (string)$valor);
    }

    private function html($valor)
    {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}


