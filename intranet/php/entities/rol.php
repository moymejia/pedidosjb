<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/utils.php';
class rol extends table
{
    use utils;
    private $idrol;
    private $last_error;
    private $ACCIONES = [];

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_seguridad', 'rol');

        $this->ACCIONES['crear']          = "Crear_rol";
        $this->ACCIONES['modificar']      = "Modificar_rol";
        $this->ACCIONES['cambiar_estado'] = "Cambiar_estado_rol";

        if (isset($PARAMETROS['operacion'])) {

            if ($PARAMETROS['operacion'] == 'guardar') {
                if ($resultado = self::guardar_rol($PARAMETROS)) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['idrol'], $PARAMETROS, false)) {
                    self::end_success(self::cambiar_estado($PARAMETROS['idrol']));
                } else {
                    self::end_error("rol no encontrado");
                }
            }

        }
    }

    public function options_roles_activos()
    {
        return mysql::getoptions("SELECT idrol id, nombre descripcion FROM rol WHERE estado = 'ACTIVO' ");
    }

    public function cargar_opcion()
    {
        $DATA        = [];
        $result      = mysql::getresult("SELECT idrol, nombre, estado FROM rol ");
        $tabla_roles = '';
        while ($row = mysql::getrowresult($result)) {
            $idrol  = $row['idrol'];
            $nombre = $row['nombre'];
            $estado = $row['estado'];
            //$row_data = getrowmysql("SELECT * FROM area WHERE idarea = '$idarea' ");
            $row_data = $row;
            $str_data = "";
            foreach ($row_data as $key => $value) {
                $str_data .= $key . "=" . $value . "&";
            }

            
            $boton_editar = "<button  class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"editar_registro('$str_data',this.parentNode.parentNode);goTop();\"><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>";
            
            $tabla_roles .= "<tr>
                <td>$boton_editar</td>
                <td>$idrol</td>
				<td>$nombre</td>
				<td>$estado</td>
			</tr>";
        }
        $DATA['tabla_roles'] = $tabla_roles;

        $html = new html('rol', $DATA);

        return $html->get_html();
    }

    /**
     * @param array $PARAMETROS
     * si el rol existe, lo modifica, de lo contraro crea uno nuevo
     */
    public function guardar_rol($PARAMETROS)
    {
        $parametros_necesarios = ["idrol", "nombre"];
        if (!table::validate_parameter_existence($parametros_necesarios, $PARAMETROS)) {
            self::end_error("Datos incompletos.");
        }

        $PARAMETROS = table::sanitize_array($PARAMETROS);

        if (!mysql::exists('rol', " idrol = '{$PARAMETROS['idrol']}' ")) { //es rol nuevo
            $security = new security($this->ACCIONES['crear']);

            $valores_necesarios = ["nombre"];
            $DATOS              = table::create_subarray($valores_necesarios, $PARAMETROS);
            $DATOS['estado']    = 'ACTIVO';
            if ($resultado = table::insert_record($DATOS)) {
                $idrol = mysql::last_id();
                $security->registrar_bitacora($this->ACCIONES['crear'], $idrol);

                return "nuevo";
            } else {
                $this->last_error = "Error al guardar el registro";

                return false;
            }

        } else {
            $security           = new security($this->ACCIONES['modificar']); //modificar registro de rol
            $valores_necesarios = ["idrol", "nombre"];
            $DATOS              = table::create_subarray($valores_necesarios, $PARAMETROS);
            $llaves             = ["idrol"];
            if ($resultado = table::update_record($DATOS, $llaves)) {
                $referencia = $DATOS['idrol'];
                $security->registrar_bitacora($this->ACCIONES['modificar'], $referencia);

                return "editado";
            } else {
                $this->last_error = "|Error al modificar el registro|";

                return false;
            }
        }
    }

    public function cambiar_estado($idrol)
    {
        $security      = new security($this->ACCIONES['cambiar_estado']);
        $estado_actual = mysql::getvalue("SELECT estado FROM rol WHERE idrol = '$idrol' ");

        $DATOS['idrol']  = $idrol;
        $DATOS['estado'] = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $llaves          = ['idrol'];
        $resultado       = table::update_record($DATOS, $llaves);

        return mysql::getvalue("SELECT estado FROM rol WHERE idrol = '$idrol' ");
    }
}
