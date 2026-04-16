<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/objects.php';
require_once '../wisetech/utils.php';

class tipo_contacto extends table
{

    use utils;
    private $idtipo_contacto;
    private $ACCIONES  = [];
    public $last_error = '';

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'tipo_contacto');

        $this->ACCIONES['opcion_tipo_contacto']         = "Opcion_tipo_contacto";
        $this->ACCIONES['crear_tipo_contacto']          = "Crear_tipo_contacto";
        $this->ACCIONES['modificar_tipo_contacto']      = "Modificar_tipo_contacto";
        $this->ACCIONES['cambiar_estado_tipo_contacto'] = "Cambiar_estado_tipo_contacto";

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'guardar') {
                if ($resultado = $this->guardar_tipo_contacto($PARAMETROS)) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['idtipo_contacto'], $PARAMETROS, false)) {
                    if($resultado = self::cambiar_estado($PARAMETROS['idtipo_contacto'])){
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error($this->last_error);
                }
            }
        }

    }

    public function cargar_opcion()
    {
        $DATA   = [];
        $result = mysql::getresult("SELECT idtipo_contacto, descripcion, estado FROM tipo_contacto ORDER BY idtipo_contacto DESC");
        $tabla_tipo_contacto = '<table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
		<thead>
			<tr>
                <th style="text-align: center;">Acciones</th>
                <th style="text-align: center;">Descripcion</th>
                <th style="text-align: center;">Estado</th>
			</tr>
		</thead>
		<tbody id="tabla_todos">';

        while ($row = mysql::getrowresult($result)) {
            $descripcion = $row['descripcion'];
            $estado      = $row['estado'];
            $row_data    = $row;
            $str_data    = "";

            foreach ($row_data as $key => $value) {
                $str_data .= $key . "=" . $value . "&";
            }

            $boton_editar    = "<button  class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"editar_registro('$str_data',this.parentNode.parentNode);objeto('idtipo_contacto').readOnly = true;goTop();\"><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>";
            $tabla_tipo_contacto .= "<tr>
				<td>$boton_editar</td>
                <td>$descripcion</td>
                <td>$estado</td>
			</tr>";
        }
        $tabla_tipo_contacto .= "</tbody>
        </table>";

        $DATA['tabla_tipo_contacto'] = $tabla_tipo_contacto;
        $html                        = new html('tipo_contacto', $DATA);

        return $html->get_html();
    }

    public function guardar_tipo_contacto($PARAMETROS)
    {
        //valida que se cuente con los parametros necesarios.
        $parametros_necesarios = ["descripcion", "estado"];
        if (! table::validate_parameter_existence($parametros_necesarios, $PARAMETROS)) {
            $this->last_error = 'Datos incompletos.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);

            return false;
        }

        //valida que el usuario ingrese una descripcion para el tipo de contacto.
        if (empty($PARAMETROS['descripcion'])) {
            $this->last_error = 'Debe ingresar una descripcion para el tipo de contacto.';
            utils::report_error(validation_error, $PARAMETROS['descripcion'], $this->last_error);

            return false;
        }

        if ($PARAMETROS['idtipo_contacto'] == '') { //es un tipo de contacto nuevo
            $security = new security($this->ACCIONES['crear_tipo_contacto']);
            //valida que el tipo de contacto no este registrado ya.
            if (mysql::exists('tipo_contacto', " descripcion = '{$PARAMETROS['descripcion']}'")) { //verifica que tipo de contacto nuevo no exista ya 
                $this->last_error = 'El actual tipo de contacto ya esta registrado';
                utils::report_error(validation_error, $PARAMETROS['idtipo_contacto'], $this->last_error);

                return false;
            }

            $valores_necesarios        = ["descripcion", "estado"];
            $DATOS                     = table::create_subarray($valores_necesarios, $PARAMETROS);
            $DATOS['estado']           = "ACTIVO";
            $DATOS['usuario_creacion'] = $security->get_actual_user();

            if (table::insert_record($DATOS)) {
                $idtipo_contacto = mysql::last_id();
                $security->registrar_bitacora($this->ACCIONES['crear_tipo_contacto'], $idtipo_contacto);

                return "nuevo";
            } else {
                $this->last_error = "Error al guardar el registro";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }

        } else {
            $security = new security($this->ACCIONES['modificar_tipo_contacto']);
            if ($PARAMETROS['estado'] == 'ACTIVO') {
                if (mysql::exists('tipo_contacto', " descripcion = '{$PARAMETROS['descripcion']}' AND idtipo_contacto != '{$PARAMETROS['idtipo_contacto']}'")) {
                    $this->last_error = 'Un tipo de contacto existente ya tiene esa descripcion.';
                    utils::report_error(validation_error, $PARAMETROS=['idtipo_contacto'], $this->last_error);

                    return false;
                }

                $valores_necesarios            = ["idtipo_contacto", "descripcion", "estado"];
                $DATOS                         = table::create_subarray($valores_necesarios, $PARAMETROS);
                $DATOS['usuario_modificacion'] = $security->get_actual_user();
                $llaves                        = ["idtipo_contacto"];

                if (table::update_record($DATOS, $llaves)) {
                    $security->registrar_bitacora($this->ACCIONES['modificar_tipo_contacto'], $DATOS['idtipo_contacto']);

                    return "editado";
                } else {
                    $this->last_error = "Error al modificar el registro";
                    utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                    return false;
                }

            } else {
                $this->last_error = "Tipo de contacto inactivo, no se puede editar.";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        }
    }

    public function estado($idtipo_contacto){
        return mysql::getvalue("SELECT estado FROM tipo_contacto WHERE idtipo_contacto = '$idtipo_contacto' ");
    }

    public function cambiar_estado($idtipo_contacto){
        $security                 = new security($this->ACCIONES['cambiar_estado_tipo_contacto']);
        $estado_actual            = mysql::getvalue("SELECT estado FROM tipo_contacto WHERE idtipo_contacto = '$idtipo_contacto' ");;
        $DATOS['idtipo_contacto'] = $idtipo_contacto;

        if ($estado_actual == 'PROTEGIDO') {
            $this->last_error = 'Registro protegido, no puede modificarse';
            utils::report_error(validation_error, $idtipo_contacto, $this->last_error);

            return $this->estado($idtipo_contacto);;
        }

        $DATOS['estado']               = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $DATOS['usuario_modificacion'] = $security->get_actual_user();
        $llaves                        = ['idtipo_contacto'];
        
        
        if (table::update_record($DATOS, $llaves)) {
            $security->registrar_bitacora($this->ACCIONES['cambiar_estado_tipo_contacto'], $idtipo_contacto, $DATOS['estado']);

            return $this->estado($idtipo_contacto);
        } else {
            $this->last_error = "Error al cambiar de estado";
            utils::report_error(validation_error, $idtipo_contacto, $this->last_error);

            return false;
        }
    }

    public function option_activas()
    {
        return mysql::getoptions("SELECT idtipo_contacto as id, descripcion as descripcion FROM tipo_contacto WHERE estado = 'ACTIVO' ORDER BY descripcion ASC");
    }
}