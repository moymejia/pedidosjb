<?php //
$path = getcwd();
if (strpos($path, 'entities')) {
    require_once '../wisetech/table.php';
    require_once '../wisetech/security.php';
    require_once '../wisetech/html.php';
    require_once '../wisetech/objects.php';
    require_once '../wisetech/utils.php';
} else if (strpos($path, 'wisetech')) {
    require_once 'table.php';
    require_once 'security.php';
    require_once 'html.php';
    require_once 'objects.php';
    require_once 'utils.php';
} else {
    require_once 'wisetech/table.php';
    require_once 'wisetech/security.php';
    require_once 'wisetech/html.php';
    require_once 'wisetech/objects.php';
    require_once 'wisetech/utils.php';
}

class usuario extends table
{
    use utils;
    private $usuario;
    private $ACCIONES  = [];
    public $last_error = '';

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_seguridad', 'usuario');

        $this->ACCIONES['crear']          = 2;
        $this->ACCIONES['modificar']      = 3;
        $this->ACCIONES['cambiar_clave']  = 4;
        $this->ACCIONES['cambiar_estado'] = 12;

        if (isset($PARAMETROS['operacion'])) {

            if ($PARAMETROS['operacion'] == 'guardar') {
                self::end_success($this->guardar_usuario($PARAMETROS));
            }
            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['usuario'], $PARAMETROS, false)) {
                    self::end_success($this->cambiar_estado($PARAMETROS['usuario']));
                } else {
                    self::end_error("nombre de usuario no encontrado");
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_clave') {
                if (table::validate_parameter_existence(['usuario', 'clave'], $PARAMETROS, false)) {
                    if ($resultado = $this->cambiar_clave($PARAMETROS['usuario'], $PARAMETROS['clave'])) {
                        self::end_success("Clave de acceso modificada correctamente");
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("nombre de usuario no encontrado");
                }
            }

            if ($PARAMETROS['operacion'] == 'tabla_todos_usuarios') {
                if ($resultado = $this->tabla_todos_usuarios()) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'get_usuario_actual') {
                if ($resultado = $this->get_usuario_actual()) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }
        }
    }

    public function options_usuarios_activos()
    {
        return mysql::getoptions("SELECT usuario id, nombre descripcion FROM usuario WHERE estado = 'ACTIVO' ");
    }

    public function tabla_todos_usuarios()
    {
        $result         = mysql::getresult("SELECT usuario, '' clave, nombre, email, estado, idrol, color, pin FROM usuario   ");
        $tabla_usuarios = '<table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th>Acciones</th>
				<th>Usuario</th>
				<th>Nombre</th>
				<th>Email</th>
				<th>Rol</th>
				<th>Color</th>
				<th>Pin de venta</th>
				<th>Estado</th>
			</tr>
		</thead>
		<tbody id="tabla_todos">';
        while ($row = mysql::getrowresult($result)) {
            $usuario = $row['usuario'];
            $nombre  = $row['nombre'];
            $clave   = $row['clave'];
            $email   = $row['email'];
            $idrol   = $row['idrol'];
            $color   = $row['color'];
            $pin     = $row['pin'];
            $color   = "<span style='background:$color;display:inline-block;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
            $estado  = $row['estado'];
            //$row_data = getrowmysql("SELECT * FROM area WHERE idarea = '$idarea' ");
            $row_data = $row;
            $str_data = "";
            foreach ($row_data as $key => $value) {
                $str_data .= $key . "=" . $value . "&";
            }
            $boton_editar = "<button  class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"editar_registro('$str_data',this.parentNode.parentNode);objeto('usuario').readOnly = true;goTop();\"><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>";
            $tabla_usuarios .= "<tr>
				<td>$boton_editar</td>
				<td>$usuario</td>
				<td>$nombre</td>
				<td>$email</td>
				<td>$idrol</td>
				<td>$color</td>
				<td>$pin</td>
				<td>$estado</td>
			</tr>";
        }

        return $tabla_usuarios;
    }

    public function cargar_opcion()
    {
        $DATA                  = [];
        $DATA['roles_activos'] = objects::get_object('../entities/', 'rol')->options_roles_activos();
        $result                = mysql::getresult("SELECT usuario, '' clave, nombre, email, estado, idrol, color, pin FROM usuario  WHERE estado = 'ACTIVO' ");
        $tabla_usuarios        = '<table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th>Acciones</th>
				<th>Usuario</th>
				<th>Nombre</th>
				<th>Email</th>
				<th>Rol</th>
				<th>Color</th>
				<th>Pin de venta</th>
				<th>Estado</th>
			</tr>
		</thead>
		<tbody id="tabla_todos">';

        while ($row = mysql::getrowresult($result)) {
            $usuario = $row['usuario'];
            $nombre  = $row['nombre'];
            $clave   = $row['clave'];
            $email   = $row['email'];
            $idrol   = $row['idrol'];
            $color   = $row['color'];
            $pin     = $row['pin'];
            $color   = "<span style='background:$color;display:inline-block;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
            $estado  = $row['estado'];
            //$row_data = getrowmysql("SELECT * FROM area WHERE idarea = '$idarea' ");
            $row_data = $row;
            $str_data = "";
            foreach ($row_data as $key => $value) {
                $str_data .= $key . "=" . $value . "&";
            }
            $boton_editar = "<button  class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"editar_registro('$str_data',this.parentNode.parentNode);objeto('usuario').readOnly = true;goTop();\"><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>";
            $tabla_usuarios .= "<tr>
				<td>$boton_editar</td>
				<td>$usuario</td>
				<td>$nombre</td>
				<td>$email</td>
				<td>$idrol</td>
				<td>$color</td>
				<td>$pin</td>
				<td>$estado</td>
			</tr>";
        }
        $tabla_usuarios .= "</tbody>
        </table>";
        $DATA['tabla_usuarios'] = $tabla_usuarios;

        $html = new html('usuario', $DATA);

        return $html->get_html();
    }

    /**
     * @param array $PARAMETROS
     * si el usuario existe, lo modifica, de lo contraro crea uno nuevo
     */
    private function guardar_usuario($PARAMETROS)
    {

        $parametros_necesarios = ["usuario", "nombre", "clave", "email", "estado", "idrol", "color", "pin"];
        if (!table::validate_parameter_existence($parametros_necesarios, $PARAMETROS)) {
            die("|ERROR|Datos incompletos.");
        }

        $PARAMETROS = table::sanitize_array($PARAMETROS);
        // validar que el pin sea numerico
        if (!is_numeric($PARAMETROS['pin'])) {
            self::report_error(validation_error, $PARAMETROS, "Pin no es numerico.");
            self::end_error("Pin no es numerico, no se guardó el registro");
        }

        if ($PARAMETROS['idrol'] == "") {
            self::report_error(validation_error, $PARAMETROS, "Rol no colocado.");
            self::end_error("Rol de usuario no colocado");
        }

        // validacioes para el pin de venta.
        $existe_pin = mysql::getvalue("SELECT count(1) cantidad FROM usuario WHERE pin = '{$PARAMETROS['pin']}' AND usuario != '{$PARAMETROS['usuario']}' ");
        if ($existe_pin > 0) {
            self::report_error(validation_error, $PARAMETROS, "Pin existente con otro usuario");
            self::end_error("Pin invalido, no se guardó el registro");
        }

        if ($PARAMETROS['pin'] > 9999 || $PARAMETROS['pin'] < 1) {
            self::report_error(validation_error, $PARAMETROS, "Pin fuera de rango.");
            self::end_error("Pin invalido, no se guardó el registro");
        }
        if (!mysql::exists('usuario', " usuario = '{$PARAMETROS['usuario']}' ")) { //es usuario nuevo
            $security = new security($this->ACCIONES['crear']);

            $valores_necesarios = ["usuario", "nombre", "clave", "email", "estado", "idrol", "color", "pin"];
            $DATOS              = table::create_subarray($valores_necesarios, $PARAMETROS);
            $clave              = $DATOS['clave'];
            if ($resultado = table::insert_record($DATOS)) {
                mysql::put("UPDATE usuario SET clave = sha2('$clave',256) WHERE usuario = '{$DATOS['usuario']}' ");
                $referencia = $DATOS['usuario'];
                $security->registrar_bitacora($this->ACCIONES['crear'], $referencia);

                return "|correcto|nuevo|";
            } else {
                return "|error|Error al guardar el registro|";
            }

        } else {
            $security = new security($this->ACCIONES['modificar']); //modificar registro de usuario

            $valores_necesarios = ["usuario", "nombre", "email", "estado", "idrol", "color", "pin"];
            $DATOS              = table::create_subarray($valores_necesarios, $PARAMETROS);
            $llaves             = ["usuario"];
            if ($resultado = table::update_record($DATOS, $llaves)) {
                $referencia = $DATOS['usuario'];
                $security->registrar_bitacora($this->ACCIONES['modificar'], $referencia);

                return "|correcto|editado|";
            } else {
                return "|error|Error al modificar el registro|";
            }
        }
    }

    private function cambiar_estado($usuario)
    {
        $security      = new security($this->ACCIONES['cambiar_estado']);
        $estado_actual = mysql::getvalue("SELECT estado FROM usuario WHERE usuario = '$usuario' ");

        $DATOS['usuario'] = $usuario;
        $DATOS['estado']  = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $llaves           = ['usuario'];
        $resultado        = table::update_record($DATOS, $llaves);
        $security->registrar_bitacora($this->ACCIONES['cambiar_estado'], $usuario, $DATOS['estado']);

        return mysql::getvalue("SELECT estado FROM usuario WHERE usuario = '$usuario' ");
    }

    private function cambiar_clave($usuario, $clave_nueva)
    {
        $security      = new security($this->ACCIONES['cambiar_clave']);
        $estado_actual = mysql::getvalue("SELECT estado FROM usuario WHERE usuario = '$usuario' ");
        if ($estado_actual == 'INACTIVO') {
            $this->last_error = 'usuario en estado inactivo, no puede cambiarse la clave';

            return false;
        }

        mysql::put("UPDATE usuario SET clave = sha2('$clave_nueva',256) WHERE usuario = '$usuario' ");

        $security->registrar_bitacora($this->ACCIONES['cambiar_clave'], $usuario);

        return true;
    }

    //get nombre usuario.
    public function get_nombre($usuario)
    {
        return mysql::getvalue("SELECT nombre FROM multiclich_seguridad.usuario WHERE usuario = '$usuario' ");
    }

    public function get_usuario_actual()
    {
        $security = new security();
        $usuario  = $security->get_actual_user();
        
        return $this->get_nombre($usuario);
    }

    public function options_activas()
    {
        return mysql::getoptions("SELECT usuario id, nombre descripcion FROM usuario WHERE estado = 'ACTIVO'");
    }
}
