<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/objects.php';
require_once '../wisetech/utils.php';
require_once '../entities/talla.php';
require_once '../entities/set_talla_detalle.php';

class set_talla extends table
{

    use utils;
    private $idset_talla;
    private $ACCIONES   = [];
    public $last_error = '';

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'set_talla');
        $this->ACCIONES['opcion']         = "Opcion_set_talla";
        $this->ACCIONES['crear']          = "Crear_set_talla";
        $this->ACCIONES['modificar']      = "Modificar_set_talla";
        $this->ACCIONES['cambiar_estado'] = "Cambiar_estado_set_talla";

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'guardar') {
                if ($resultado = $this->guardar($PARAMETROS)) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['idset_talla'], $PARAMETROS, false)) {
                    if ($resultado = self::cambiar_estado($PARAMETROS['idset_talla'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Faltan parámetros");
                }
            }

            if ($PARAMETROS['operacion'] == 'obtener_grupo') {
                if (table::validate_parameter_existence(['idset_talla'], $PARAMETROS, false)) {
                    if ($resultado = self::obtener_grupo($PARAMETROS['idset_talla'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Faltan parámetros");
                }
            }
        }
    }

    public function cargar_set_talla()
    {
        $result = mysql::getresult("SELECT idset_talla, grupo, descripcion, estado FROM set_talla ORDER BY idset_talla DESC");
        $tabla  = '<table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Acciones</th>
                    <th>Grupo</th>
                    <th>Descripcion</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody id="tabla_todos">';

        while ($row = mysql::getrowresult($result)) {
            $grupo       = $row['grupo'];
            $descripcion = $row['descripcion'];
            $estado      = $row['estado'];
            $str_data    = "";

            foreach ($row as $key => $value) {
                $str_data .= $key . "=" . $value . "&";
            }

            $boton_editar = "<button class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"
                    clearElements('formulario_registro_set_talla');
                    editar_registro('$str_data',this.parentNode.parentNode);
                    hideElements('div_tabla');
                    showElements('div_tabs,tab_set_talla,tab_set_talla_detalle,tab_nueva_talla');
                    $('#a_set_talla').tab('show');
                    goTop();
                \">
                <span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Seleccionar</button>";

            $tabla .= "<tr>
                    <td>$boton_editar</td>
                    <td>$grupo</td>
                    <td>$descripcion</td>
                    <td>$estado</td>
                </tr>";
        }

        $tabla .= "</tbody></table>";

        return $tabla;
    }

    public function cargar_opcion()
    {
        $DATA   = [];
        $result = mysql::getresult("SELECT idset_talla, grupo, descripcion, estado FROM set_talla ORDER BY idset_talla DESC");
        $tabla  = '<table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Acciones</th>
                        <th>Grupo</th>
                        <th>Descripcion</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="tabla_todos">';

        while ($row = mysql::getrowresult($result)) {
            $grupo       = $row['grupo'];
            $descripcion = $row['descripcion'];
            $estado      = $row['estado'];
            $str_data    = "";

            foreach ($row as $key => $value) {
                $str_data .= $key . "=" . $value . "&";
            }

            $boton_editar = "<button class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"
                clearElements('formulario_registro_set_talla');
                editar_registro('$str_data',this.parentNode.parentNode);
                hideElements('div_tabla');
                showElements('div_tabs,tab_set_talla,tab_nueva_talla,tab_set_talla_detalle,botones_edicion');
                $('#a_set_talla').tab('show');
                goTop();
                download_div_content('idset_talla','set_talla_detalle','cargar_talla','talla');
            \">
            <span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Seleccionar</button>";

            $tabla .= "<tr>
                <td>$boton_editar</td>
                <td>$grupo</td>
                <td>$descripcion</td>
                <td>$estado</td>
            </tr>";
        }

        $tabla                   .= "</tbody></table>";
        $DATA['tabla_set_talla'] = $tabla;
        $DATA['tabla_talla']     = (new talla())->cargar_tabla_talla();

        $html  = new html('set_talla', $DATA);

        return $html->get_html();
    }

    public function guardar($PARAMETROS)
    {
        $parametros_necesarios = ["grupo"]; //valida que se cuente con los parametros necesarios.
        if (! table::validate_parameter_existence($parametros_necesarios, $PARAMETROS)) {
            $this->last_error = 'Datos incompletos.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);

            return false;
        }

        if (empty($PARAMETROS['grupo'])) { //valida que el usuario ingrese un grupo para el set de tallas.
            $this->last_error = 'Debe ingresar un grupo para el set de tallas.';
            utils::report_error(validation_error, $PARAMETROS['grupo'], $this->last_error);

            return false;
        }

        if (strlen($PARAMETROS['grupo']) > 50) { //valida que el grupo de talla no tenga mas de 10 caracteres
            $this->last_error = 'El grupo de talla no puede tener mas de 10 caracteres.';
            utils::report_error(validation_error, $PARAMETROS['grupo'], $this->last_error);

            return false;
        }

        if (empty($PARAMETROS['descripcion'])) { //si la descripcion esta vacia, se guarda como null en la base de datos
            $PARAMETROS['descripcion'] = 'NULL';
        }

        if ($PARAMETROS['idset_talla'] == '') { //es un nuevo set de tallas
            $security = new security($this->ACCIONES['crear']);

            if (mysql::exists('set_talla', " grupo = '{$PARAMETROS['grupo']}'")) { //verifica que el grupo nuevo no exista ya
                $this->last_error = 'El grupo ya esta registrado';
                utils::report_error(validation_error, $PARAMETROS['grupo'], $this->last_error);

                return false;
            }

            $DATOS                     = [];
            $DATOS['grupo']            = $PARAMETROS['grupo'];
            $DATOS['descripcion']      = $PARAMETROS['descripcion'];
            $DATOS['estado']           = 'ACTIVO';
            $DATOS['usuario_creacion'] = $security->get_actual_user();

            if ($resultado = table::insert_record($DATOS)) {
                $referencia = mysql::last_id();
                $security->registrar_bitacora($this->ACCIONES['crear'], $resultado, $referencia);

                return $referencia;

            } else {
                $this->last_error = "Error al guardar el registro";
                utils::report_error(bd_error, $DATOS, $this->last_error);
                return false;
            }

        } else {
            $security = new security($this->ACCIONES['modificar']);

            if ($PARAMETROS['estado'] == 'INACTIVO') {
                $this->last_error = "Set de tallas inactivo, no se pueden modificar sus datos.";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }

            if (mysql::exists('set_talla', " grupo = '{$PARAMETROS['grupo']}' AND idset_talla != '{$PARAMETROS['idset_talla']}'")) { //verifica que un set de tallas existente no tenga el mismo grupo que el que se quiere modificar
                $this->last_error = 'Un set de tallas existente ya tiene ese grupo.';
                utils::report_error(validation_error, $PARAMETROS = ['idset_talla'], $this->last_error);

                return false;
            }

            $valores_necesarios            = ["idset_talla", "grupo", "descripcion", "estado"];
            $DATOS                         = table::create_subarray($valores_necesarios, $PARAMETROS);
            $DATOS['usuario_modificacion'] = $security->get_actual_user();
            $llaves                        = ["idset_talla"];

            if (table::update_record($DATOS, $llaves)) {
                $security->registrar_bitacora($this->ACCIONES['modificar'], $DATOS['idset_talla']);

                return "editado";
            } else {
                $this->last_error = "Error al modificar el registro";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        }
    }

    public function obtener_grupo($idset_talla)
    {

        $result = mysql::getresult("SELECT idset_talla, descripcion, idtalla, talla
            FROM view_set_talla_detalle
            WHERE idset_talla = '" . intval($idset_talla) . "'
            ORDER BY CAST(talla AS UNSIGNED) ASC, talla ASC
        ");

        if (mysql::num_rows($result) == 0) {
            $this->last_error = "No se encontraron tallas para el set.";
            utils::report_error(bd_error, $idset_talla, $this->last_error);
            return false;
        }

        $data = [
            'id'     => 0,
            'nombre' => '',
            'tallas' => [],
        ];

        while ($row = mysql::getrowresult($result)) {

            if ($data['id'] == 0) {
                $data['id']     = (int) $row['idset_talla'];
                $data['nombre'] = $row['descripcion'];
            }

            $data['tallas'][] = [
                'id'     => $row['idtalla'],
                'numero' => $this->formatear_numero_talla($row['talla']),
            ];
        }

        return json_encode($data);
    }

    private function formatear_numero_talla($numero)
    {
        $numero = trim((string)$numero);

        if ($numero !== '' && preg_match('/^-?\d+\.0+$/', $numero)) {
            return preg_replace('/\.0+$/', '', $numero);
        }

        return $numero;
    }

    public function cambiar_estado($idset_talla)
    {
        $security             = new security($this->ACCIONES['cambiar_estado']);
        $estado_actual        = mysql::getvalue("SELECT estado FROM set_talla WHERE idset_talla = '$idset_talla' ");
        $DATOS['idset_talla'] = $idset_talla;

        $DATOS['estado']               = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $DATOS['usuario_modificacion'] = $security->get_actual_user();
        $llaves                        = ['idset_talla'];

        if (table::update_record($DATOS, $llaves)) {
            $security->registrar_bitacora($this->ACCIONES['cambiar_estado'], $idset_talla, $DATOS['estado']);

            return mysql::getvalue("SELECT estado FROM set_talla WHERE idset_talla = '$idset_talla' ");

        } else {

            $this->last_error = "Error al cambiar de estado";
            utils::report_error(validation_error, $idset_talla, $this->last_error);

            return false;
        }
    }

    public function get_idset_talla($talla_desde, $talla_hasta)
    {
        $SET_TALLA_DETALLE = new set_talla_detalle();
        $idset_talla_d     = $SET_TALLA_DETALLE->get_set_talla($talla_desde, $talla_hasta);

        if (!empty($idset_talla_d) && $idset_talla_d !== '') {

            return $idset_talla_d;
        } else {
            $TALLA = new talla();

            if(!$TALLA->existen_tallas($talla_desde,$talla_hasta)){
                $this->last_error = $TALLA->last_error;

                return false;
            }

            $grupo       = $talla_desde . '-' . $talla_hasta;
            $DATOS       = ['idset_talla' => '', 'grupo' => $grupo, 'descripcion' => $grupo];
            $idset_talla = $this->guardar($DATOS);

            if (! $idset_talla) {
                return false;
            } else {
                if (! $SET_TALLA_DETALLE->guardar_tallas($idset_talla, $talla_desde, $talla_hasta)) {
                    $this->last_error = $SET_TALLA_DETALLE->last_error;

                    return false;
                }

                return $idset_talla;
            }
        }
    }

    public function estado($idset_talla)
    {
        return mysql::getvalue("SELECT estado FROM set_talla WHERE idset_talla = '$idset_talla'");
    }

    public function options_activos()
    {
        return mysql::getoptions("SELECT idset_talla AS id,
                CASE 
                    WHEN descripcion IS NULL OR descripcion = '' THEN grupo
                    WHEN grupo IS NULL OR grupo = '' THEN descripcion
                    ELSE CONCAT(grupo, ' - ', descripcion)
                END AS descripcion
            FROM set_talla
            WHERE estado = 'ACTIVO'
            ORDER BY grupo, descripcion
        ");
    }
}
