<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/objects.php';
require_once '../wisetech/utils.php';

class tipo_pago extends table
{
    use utils;
    private $idtipo_pago;
    private $ACCIONES  = [];
    public $last_error = '';

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'tipo_pago');

        $this->ACCIONES['opcion_tipo_pago']         = 202;
        $this->ACCIONES['crear_tipo_pago']          = 203;
        $this->ACCIONES['modificar_tipo_pago']      = 204;
        $this->ACCIONES['cambiar_estado_tipo_pago'] = 205;

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'guardar') {
                if ($resultado = $this->guardar_tipo_pago($PARAMETROS)) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['idtipo_pago'], $PARAMETROS, false)) {
                    if($resultado = self::cambiar_estado($PARAMETROS['idtipo_pago'])){
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
        $result = mysql::getresult("SELECT idtipo_pago, descripcion, estado FROM tipo_pago ORDER BY idtipo_pago DESC");
        $tabla_tipo_pago = '<table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
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
            $estado = $row['estado'];
            $row_data = $row;
            $str_data = "";

            foreach ($row_data as $key => $value) {
                $str_data .= $key . "=" . $value . "&";
            }

            $boton_editar    = "<button  class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"editar_registro('$str_data',this.parentNode.parentNode);objeto('idtipo_pago').readOnly = true;goTop();\"><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>";
            $tabla_tipo_pago .= "<tr>
				<td>$boton_editar</td>
                <td>$descripcion</td>
                <td>$estado</td>
			</tr>";
        }
        $tabla_tipo_pago .= "</tbody>
        </table>";

        $DATA['tabla_tipo_pago'] = $tabla_tipo_pago;
        $html                        = new html('tipo_pago', $DATA);

        return $html->get_html();
    }

    public function guardar_tipo_pago($PARAMETROS)
    {
        $parametros_necesarios = ["descripcion"]; //valida que se cuente con los parametros necesarios.
        if (!table::validate_parameter_existence($parametros_necesarios, $PARAMETROS)) {
            $this->last_error = 'Datos incompletos.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);

            return false;
        }

        if (empty($PARAMETROS['descripcion'])) { //valida que se ingrese un descripcion para el tipo de pago
            $this->last_error = 'Debe ingresar un descripcion para el tipo de pago.';
            utils::report_error(validation_error, $PARAMETROS['descripcion'], $this->last_error);

            return false;
        }

        if (strlen($PARAMETROS['descripcion']) > 100) { //valida que la descripcion no tenga mas de 100 caracteres
            $this->last_error = 'El descripcion de tipo de pago no puede tener mas de 100 caracteres.';
            utils::report_error(validation_error, $PARAMETROS['descripcion'], $this->last_error);

            return false;
        }

        if ($PARAMETROS['idtipo_pago'] == '') { //es un nuevo tipo de pago
            if (mysql::exists('tipo_pago', " descripcion = '{$PARAMETROS['descripcion']}'")) { //verifica que el tipo de pago nuevo no exista ya
                $this->last_error = 'El tipo de pago ya esta registrado';
                utils::report_error(validation_error, $PARAMETROS['idtipo_pago'], $this->last_error);

                return false;
            }

            $security                  = new security($this->ACCIONES['crear_tipo_pago']);
            $valores_necesarios        = ["descripcion"];
            $DATOS                     = table::create_subarray($valores_necesarios, $PARAMETROS);
            $DATOS['estado']           = "ACTIVO";
            $DATOS['usuario_creacion'] = $security->get_actual_user();

            if (table::insert_record($DATOS)) {
                $idtipo_pago = mysql::last_id();
                $security->registrar_bitacora($this->ACCIONES['crear_tipo_pago'], $idtipo_pago);

                return "nuevo";
            } else {
                $this->last_error = "Error al guardar el tipo de pago";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        } else {
            if ($PARAMETROS['estado'] == 'ACTIVO') {
                if (mysql::exists('tipo_pago', " descripcion = '{$PARAMETROS['descripcion']}' AND idtipo_pago != '{$PARAMETROS['idtipo_pago']}'")) { //verifica que un tipo de pago existente no tenga el mismo descripcion que el que se quiere modificar
                    $this->last_error = 'Un tipo de pago existente ya tiene ese descripcion.';
                    utils::report_error(validation_error, $PARAMETROS=['idtipo_pago'], $this->last_error);

                    return false;
                }

                $security                      = new security($this->ACCIONES['modificar_tipo_pago']); //modificar registro de tipo de pago
                $valores_necesarios            = ["idtipo_pago", "descripcion", "estado"];
                $DATOS                         = table::create_subarray($valores_necesarios, $PARAMETROS);
                $DATOS['usuario_modificacion'] = $security->get_actual_user();
                $llaves                        = ["idtipo_pago"];

                if (table::update_record($DATOS, $llaves)) {
                    $security->registrar_bitacora($this->ACCIONES['modificar_tipo_pago'], $DATOS['idtipo_pago']);

                    return "editado";
                } else {
                    $this->last_error = "Error al modificar el registro";
                    utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                    return false;
                }
            } else {
                $this->last_error = "Tipo de pago inactivo, no se pueden modificar sus datos.";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        }
    }

    public function estado($idtipo_pago){
        return mysql::getvalue("SELECT estado FROM tipo_pago WHERE idtipo_pago = '$idtipo_pago' ");
    }

    public function cambiar_estado($idtipo_pago){
        $security             = new security($this->ACCIONES['cambiar_estado_tipo_pago']);
        $estado_actual        = mysql::getvalue("SELECT estado FROM tipo_pago WHERE idtipo_pago = '$idtipo_pago' ");;
        $DATOS['idtipo_pago'] = $idtipo_pago;

        if ($estado_actual == 'PROTEGIDO') { //si el estado actual es protegido, no se permite cambiar el estado
            $this->last_error = 'Registro protegido, no puede modificarse';
            utils::report_error(validation_error, $idtipo_pago, $this->last_error);

            return $this->estado($idtipo_pago);;
        }

        $DATOS['estado']               = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $DATOS['usuario_modificacion'] = $security->get_actual_user();
        $llaves                        = ['idtipo_pago'];
        
        if (table::update_record($DATOS, $llaves)) {
            $security->registrar_bitacora($this->ACCIONES['cambiar_estado_tipo_pago'], $idtipo_pago, $DATOS['estado']);

            return $this->estado($idtipo_pago);
        } else {
            $this->last_error = "Error al cambiar de estado";
            utils::report_error(validation_error, $idtipo_pago, $this->last_error);

            return false;
        }
    }
}