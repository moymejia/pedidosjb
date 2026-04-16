<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/utils.php';

class tipo_suela extends table
{
    use utils;
    private $idtipo_suela;
    public $last_error = '';
    private $ACCIONES   = [];

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'tipo_suela');

        $this->ACCIONES['opcion_tipo_suela']         = "Opcion_tipo_suela";
        $this->ACCIONES['crear_tipo_suela']          = "Crear_tipo_suela";
        $this->ACCIONES['modificar_tipo_suela']      = "Modificar_tipo_suela";
        $this->ACCIONES['cambiar_estado_tipo_suela'] = "Cambiar_estado_tipo_suela";

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'guardar') {
                if ($resultado = $this->guardar_tipo_suela($PARAMETROS)) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['idtipo_suela'], $PARAMETROS, false)) {
                    if ($resultado = self::cambiar_estado($PARAMETROS['idtipo_suela'])) {
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
        $DATA             = [];
        $result           = mysql::getresult("SELECT idtipo_suela, nombre, estado FROM tipo_suela ORDER BY idtipo_suela DESC");
        $tabla_tipo_suela = '<table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
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

            $boton_editar      = "<button  class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"editar_registro('$str_data',this.parentNode.parentNode);objeto('idtipo_suela').readOnly = true;goTop();\"><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>";
            $tabla_tipo_suela .= "<tr>
				<td>$boton_editar</td>
                <td>$nombre</td>
                <td>$estado</td>
			</tr>";
        }
        $tabla_tipo_suela .= "</tbody>
        </table>";

        $DATA['tabla_tipo_suela'] = $tabla_tipo_suela;
        $html                     = new html('tipo_suela', $DATA);

        return $html->get_html();
    }

    public function guardar_tipo_suela($PARAMETROS)
    {
        $parametros_necesarios = ["nombre"];
        if (! table::validate_parameter_existence($parametros_necesarios, $PARAMETROS)) {
            $this->last_error = 'Datos incompletos.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);

            return false;
        }

        if (empty($PARAMETROS['nombre'])) {
            $this->last_error = 'Debe ingresar un nombre para el tipo de suela.';
            utils::report_error(validation_error, $PARAMETROS['nombre'], $this->last_error);

            return false;
        }

        if ($PARAMETROS['idtipo_suela'] == '') {
            $security = new security($this->ACCIONES['crear_tipo_suela']);
            if (mysql::exists('tipo_suela', " nombre = '{$PARAMETROS['nombre']}'")) {
                $this->last_error = 'El tipo de suela ya esta registrado';
                utils::report_error(validation_error, $PARAMETROS['idtipo_suela'], $this->last_error);

                return false;
            }

            $valores_necesarios        = ["nombre"];
            $DATOS                     = table::create_subarray($valores_necesarios, $PARAMETROS);
            $DATOS['estado']           = "ACTIVO";
            $DATOS['usuario_creacion'] = $security->get_actual_user();

            if (table::insert_record($DATOS)) {
                $idtipo_suela = mysql::last_id();
                $security->registrar_bitacora($this->ACCIONES['crear_tipo_suela'], $idtipo_suela);

                return "nuevo";
            } else {
                $this->last_error = "Error al guardar el tipo_suela";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        } else {
            $security = new security($this->ACCIONES['modificar_tipo_suela']);
            if ($PARAMETROS['estado'] == 'ACTIVO') {
                if (mysql::exists('tipo_suela', " nombre = '{$PARAMETROS['nombre']}' AND idtipo_suela != '{$PARAMETROS['idtipo_suela']}'")) {
                    $this->last_error = 'Un tipo de suela existente ya tiene ese nombre.';
                    utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                    return false;
                }

                $valores_necesarios            = ["idtipo_suela", "nombre"];
                $DATOS                         = table::create_subarray($valores_necesarios, $PARAMETROS);
                $DATOS['usuario_modificacion'] = $security->get_actual_user();
                $llaves                        = ["idtipo_suela"];

                if (table::update_record($DATOS, $llaves)) {
                    $security->registrar_bitacora($this->ACCIONES['modificar_tipo_suela'], $DATOS['idtipo_suela']);

                    return "editado";
                } else {
                    $this->last_error = "Error al modificar el registro";
                    utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                    return false;
                }
            } else {
                $this->last_error = "Tipo de suela inactivo, no se pueden modificar sus datos.";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        }
    }

    public function cambiar_estado($idtipo_suela)
    {
        $security              = new security($this->ACCIONES['cambiar_estado_tipo_suela']);
        $estado_actual         = mysql::getvalue("SELECT estado FROM tipo_suela WHERE idtipo_suela = '$idtipo_suela' ");
        $DATOS['idtipo_suela'] = $idtipo_suela;

        if ($estado_actual == 'PROTEGIDO') {
            $this->last_error = 'Registro protegido, no puede modificarse';
            utils::report_error(validation_error, $idtipo_suela, $this->last_error);

            return $this->estado($idtipo_suela);
        }

        $DATOS['estado']               = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $DATOS['usuario_modificacion'] = $security->get_actual_user();
        $llaves                        = ['idtipo_suela'];

        if (table::update_record($DATOS, $llaves)) {
            $security->registrar_bitacora($this->ACCIONES['cambiar_estado_tipo_suela'], $idtipo_suela, $DATOS['estado']);

            return $this->estado($idtipo_suela);
        } else {
            $this->last_error = "Error al cambiar de estado";
            utils::report_error(validation_error, $idtipo_suela, $this->last_error);

            return false;
        }
    }

    public function estado($idtipo_suela)
    {
        return mysql::getvalue("SELECT estado FROM tipo_suela WHERE idtipo_suela = '$idtipo_suela' ");
    }

    public function get_idtipo_suela($nombre)
    {
        if(mysql::exists('tipo_suela',"nombre = '$nombre'")){
            return mysql::getvalue("SELECT idtipo_suela FROM tipo_suela WHERE nombre = '$nombre'");
        }else{
            $DATOS = [];
            $DATOS['idtipo_suela'] = '';
            $DATOS['nombre'] = $nombre;
            return $this->guardar_tipo_suela($DATOS);
        }
    }

    public function option_activos()
    {
        return mysql::getoptions("SELECT idtipo_suela id, nombre descripcion FROM tipo_suela WHERE estado = 'ACTIVO' ORDER BY nombre ASC");
    }
}
