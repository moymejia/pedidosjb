<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/objects.php';
require_once '../wisetech/utils.php';

class color extends table
{

    use utils;
    private $idcolor;
    public $last_error = '';
    private $ACCIONES   = [];

    public function __construct($PARAMETROS = null)
    {

        parent::__construct(prefijo . '_pedidos', 'color');

        $this->ACCIONES['opcion_color']         = 227;
        $this->ACCIONES['crear_color']          = "Crear_color";
        $this->ACCIONES['modificar_color']      = "Modificar_color";
        $this->ACCIONES['cambiar_estado_color'] = "Cambiar_estado_color";

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'guardar') {
                if ($resultado = $this->guardar_color($PARAMETROS)) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['idcolor'], $PARAMETROS, false)) {
                    if ($resultado = self::cambiar_estado($PARAMETROS['idcolor'])) {
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

    public function cargar_opcion($PARAMETROS)
    {
        $DATA        = [];
        $result      = mysql::getresult("SELECT idcolor, nombre, estado FROM color ORDER BY idcolor DESC");
        $tabla_color = '<table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
		<thead>
			<tr>
                <th style="text-align: center;">Acciones</th>
                <th style="text-align: center;">Nombre</th>
                <th style="text-align: center;">Estado</th>
			</tr>
		</thead>
		<tbody id="tabla_todos">';

        while ($row = mysql::getrowresult($result)) {
            $nombre   = $row['nombre'];
            $estado   = $row['estado'];
            $row_data = $row;
            $str_data = "";

            foreach ($row_data as $key => $value) {
                $str_data .= $key . "=" . $value . "&";
            }

            $boton_editar  = "<button  class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"editar_registro('$str_data',this.parentNode.parentNode);objeto('idcolor').readOnly = true;goTop();\"><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>";
            $tabla_color  .= "<tr>
				<td>$boton_editar</td>
                <td>$nombre</td>
                <td>$estado</td>
			</tr>";
        }
        $tabla_color .= "</tbody>
        </table>";

        $DATA['tabla_color'] = $tabla_color;
        $html                = new html('color', $DATA);

        return $html->get_html();
    }

    public function guardar_color($PARAMETROS)
    {
        $parametros_necesarios = ["nombre"];
        if (! table::validate_parameter_existence($parametros_necesarios, $PARAMETROS)) {
            $this->last_error = 'Datos incompletos.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);

            return false;
        }

        if (empty($PARAMETROS['nombre'])) {
            $this->last_error = 'Debe ingresar un nombre para el color.';
            utils::report_error(validation_error, $PARAMETROS['nombre'], $this->last_error);

            return false;
        }

        if ($PARAMETROS['idcolor'] == '') {
            $security = new security($this->ACCIONES['crear_color']);
            if (mysql::exists('color', " nombre = '{$PARAMETROS['nombre']}'")) {
                $this->last_error = 'El color ya esta registrado';
                utils::report_error(validation_error, $PARAMETROS['idcolor'], $this->last_error);

                return false;
            }

            $valores_necesarios        = ["nombre"];
            $DATOS                     = table::create_subarray($valores_necesarios, $PARAMETROS);
            $DATOS['estado']           = "ACTIVO";
            $DATOS['usuario_creacion'] = $security->get_actual_user();

            if (table::insert_record($DATOS)) {
                $idcolor = mysql::last_id();
                $security->registrar_bitacora($this->ACCIONES['crear_color'], $idcolor);

                return $idcolor;
            } else {
                $this->last_error = "Error al guardar el color";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        } else {
            $security = new security($this->ACCIONES['modificar_color']);
            if ($PARAMETROS['estado'] == 'ACTIVO') {
                if (mysql::exists('color', " nombre = '{$PARAMETROS['nombre']}' AND idcolor != '{$PARAMETROS['idcolor']}'")) {
                    $this->last_error = 'Un color existente ya tiene ese nombre.';
                    utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                    return false;
                }

                $valores_necesarios            = ["idcolor", "nombre"];
                $DATOS                         = table::create_subarray($valores_necesarios, $PARAMETROS);
                $DATOS['usuario_modificacion'] = $security->get_actual_user();
                $llaves                        = ["idcolor"];

                if (table::update_record($DATOS, $llaves)) {
                    $security->registrar_bitacora($this->ACCIONES['modificar_color'], $DATOS['idcolor']);

                    return "editado";
                } else {
                    $this->last_error = "Error al modificar el registro";
                    utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                    return false;
                }
            } else {
                $this->last_error = "Color inactivo, no se pueden modificar sus datos.";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        }
    }

    public function cambiar_estado($idcolor)
    {
        $security         = new security($this->ACCIONES['cambiar_estado_color']);
        $estado_actual    = mysql::getvalue("SELECT estado FROM color WHERE idcolor = '$idcolor' ");
        $DATOS['idcolor'] = $idcolor;

        if ($estado_actual == 'PROTEGIDO') {
            $this->last_error = 'Registro protegido, no puede modificarse';
            utils::report_error(validation_error, $idcolor, $this->last_error);

            return $this->estado($idcolor);
        }

        $DATOS['estado']               = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $DATOS['usuario_modificacion'] = $security->get_actual_user();
        $llaves                        = ['idcolor'];

        if (table::update_record($DATOS, $llaves)) {
            $security->registrar_bitacora($this->ACCIONES['cambiar_estado_color'], $idcolor, $DATOS['estado']);

            return $this->estado($idcolor);
        } else {
            $this->last_error = "Error al cambiar de estado";
            utils::report_error(validation_error, $idcolor, $this->last_error);

            return false;
        }
    }

    public function estado($idcolor)
    {
        return mysql::getvalue("SELECT estado FROM color WHERE idcolor = '$idcolor' ");
    }

    public function get_idcolor($nombre){
        if(mysql::exists("color","nombre = '$nombre'")){
            return mysql::getvalue("SELECT idcolor from color WHERE nombre = '$nombre'");
        }else{
            $DATOS = ['idcolor'=>'','nombre'=>$nombre];
            $idcolor = $this->guardar_color($DATOS);
            return $idcolor;
        }
    }

    public function option_activos()
    {
        return mysql::getoptions("SELECT idcolor id, nombre descripcion FROM color WHERE estado = 'ACTIVO' ORDER BY nombre ASC");
    }

}
