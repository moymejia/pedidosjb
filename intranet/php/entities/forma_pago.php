<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/objects.php';
require_once '../wisetech/utils.php';

class forma_pago extends table
{
    use utils;
    private $idforma_pago;
    private $ACCIONES  = [];
    public $last_error = '';

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'forma_pago');

        $this->ACCIONES['opcion_forma_pago']         = 206;
        $this->ACCIONES['crear_forma_pago']          = 207;
        $this->ACCIONES['modificar_forma_pago']      = 208;
        $this->ACCIONES['cambiar_estado_forma_pago'] = 209;

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'guardar') {
                if ($resultado = $this->guardar_forma_pago($PARAMETROS)) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['idforma_pago'], $PARAMETROS, false)) {
                    if($resultado = self::cambiar_estado($PARAMETROS['idforma_pago'])){
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
        $result = mysql::getresult("SELECT idforma_pago, descripcion, estado FROM forma_pago ORDER BY idforma_pago DESC");
        $tabla_forma_pago = '<table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
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

            $boton_editar    = "<button  class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"editar_registro('$str_data',this.parentNode.parentNode);objeto('idforma_pago').readOnly = true;goTop();\"><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>";
            $tabla_forma_pago .= "<tr>
				<td>$boton_editar</td>
                <td>$descripcion</td>
                <td>$estado</td>
			</tr>";
        }
        $tabla_forma_pago .= "</tbody>
        </table>";

        $DATA['tabla_forma_pago'] = $tabla_forma_pago;
        $html                        = new html('forma_pago', $DATA);

        return $html->get_html();
    }

    public function guardar_forma_pago($PARAMETROS)
    {
        $parametros_necesarios = ["descripcion"]; //valida que se cuente con los parametros necesarios.
        if (!table::validate_parameter_existence($parametros_necesarios, $PARAMETROS)) {
            $this->last_error = 'Datos incompletos.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);

            return false;
        }

        if (empty($PARAMETROS['descripcion'])) { //valida que se ingrese un descripcion para la forma de pago
            $this->last_error = 'Debe ingresar un descripcion para la forma de pago.';
            utils::report_error(validation_error, $PARAMETROS['descripcion'], $this->last_error);

            return false;
        }

        if (strlen($PARAMETROS['descripcion']) > 100) { //valida que la descripcion no tenga mas de 100 caracteres
            $this->last_error = 'La descripcion de la forma de pago no puede tener mas de 100 caracteres.';
            utils::report_error(validation_error, $PARAMETROS['descripcion'], $this->last_error);

            return false;
        }

        if ($PARAMETROS['idforma_pago'] == '') { //es una nueva forma de pago
            if (mysql::exists('forma_pago', " descripcion = '{$PARAMETROS['descripcion']}'")) { //verifica que la forma de pago nueva no exista ya
                $this->last_error = 'La forma de pago ya esta registrada';
                utils::report_error(validation_error, $PARAMETROS['idforma_pago'], $this->last_error);

                return false;
            }

            $security                  = new security($this->ACCIONES['crear_forma_pago']);
            $valores_necesarios        = ["descripcion"];
            $DATOS                     = table::create_subarray($valores_necesarios, $PARAMETROS);
            $DATOS['estado']           = "ACTIVO";
            $DATOS['usuario_creacion'] = $security->get_actual_user();

            if (table::insert_record($DATOS)) {
                $idforma_pago = mysql::last_id();
                $security->registrar_bitacora($this->ACCIONES['crear_forma_pago'], $idforma_pago);

                return "nuevo";
            } else {
                $this->last_error = "Error al guardar el tipo de pago";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        } else {
            if ($PARAMETROS['estado'] == 'ACTIVO') {
                if (mysql::exists('forma_pago', " descripcion = '{$PARAMETROS['descripcion']}' AND idforma_pago != '{$PARAMETROS['idforma_pago']}'")) { //verifica que una forma de pago existente no tenga la misma descripcion que la que se quiere modificar
                    $this->last_error = 'La forma de pago ya esta registrada';
                    utils::report_error(validation_error, $PARAMETROS=['idforma_pago'], $this->last_error);

                    return false;
                }

                $security                      = new security($this->ACCIONES['modificar_forma_pago']); //modificar registro de forma de pago
                $valores_necesarios            = ["idforma_pago", "descripcion", "estado"];
                $DATOS                         = table::create_subarray($valores_necesarios, $PARAMETROS);
                $DATOS['usuario_modificacion'] = $security->get_actual_user();
                $llaves                        = ["idforma_pago"];

                if (table::update_record($DATOS, $llaves)) {
                    $security->registrar_bitacora($this->ACCIONES['modificar_forma_pago'], $DATOS['idforma_pago']);

                    return "editado";
                } else {
                    $this->last_error = "Error al modificar el registro";
                    utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                    return false;
                }
            } else {
                $this->last_error = "Forma de pago inactiva, no se pueden modificar sus datos.";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        }
    }

    public function estado($idforma_pago){
        return mysql::getvalue("SELECT estado FROM forma_pago WHERE idforma_pago = '$idforma_pago' ");
    }

    public function cambiar_estado($idforma_pago){
        $security             = new security($this->ACCIONES['cambiar_estado_forma_pago']);
        $estado_actual        = mysql::getvalue("SELECT estado FROM forma_pago WHERE idforma_pago = '$idforma_pago' ");;
        $DATOS['idforma_pago'] = $idforma_pago;

        if ($estado_actual == 'PROTEGIDO') { //si el estado actual es protegido, no se permite cambiar el estado
            $this->last_error = 'Registro protegido, no puede modificarse';
            utils::report_error(validation_error, $idforma_pago, $this->last_error);

            return $this->estado($idforma_pago);;
        }

        $DATOS['estado']               = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $DATOS['usuario_modificacion'] = $security->get_actual_user();
        $llaves                        = ['idforma_pago'];
        
        if (table::update_record($DATOS, $llaves)) {
            $security->registrar_bitacora($this->ACCIONES['cambiar_estado_forma_pago'], $idforma_pago, $DATOS['estado']);

            return $this->estado($idforma_pago);
        } else {
            $this->last_error = "Error al cambiar de estado";
            utils::report_error(validation_error, $idforma_pago, $this->last_error);

            return false;
        }
    }
}