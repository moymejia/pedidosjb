<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/objects.php';
require_once '../wisetech/utils.php';

class marca extends table
{
    use utils;
    private $idmarca;
    private $ACCIONES  = [];
    public $last_error = '';

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'marca');

        $this->ACCIONES['opcion_marca']         = 210;
        $this->ACCIONES['crear_marca']          = 211;
        $this->ACCIONES['modificar_marca']      = 212;
        $this->ACCIONES['cambiar_estado_marca'] = 213;

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'guardar') {
                if ($resultado = $this->guardar_marca($PARAMETROS)) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['idmarca'], $PARAMETROS, false)) {
                    if ($resultado = self::cambiar_estado($PARAMETROS['idmarca'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'obtener_descripcion') {
                if (table::validate_parameter_existence(['idmarca'], $PARAMETROS, false)) {
                    if ($resultado = $this->obtener_descripcion($PARAMETROS['idmarca'])) {
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

    public function options_set_tallas_activos()
    {
        return mysql::getoptions("SELECT idset_talla id, grupo descripcion FROM set_talla WHERE estado = 'ACTIVO' ");
    }

    public function option_activos()
    {
        return mysql::getoptions("SELECT idmarca AS id, nombre AS descripcion FROM marca WHERE estado = 'ACTIVO' ");
    }

    public function cargar_opcion()
    {
        $DATA                       = [];
        $result                     = mysql::getresult("SELECT idmarca, nombre, estado, descripcion FROM marca");
        $tabla_marca                = '<table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
		<thead>
			<tr>
                <th style="text-align: center;">Acciones</th>
                <th style="text-align: center;">Nombre</th>
                <th style="text-align: center;">Descripcion</th>
                <th style="text-align: center;">Estado</th>
			</tr>
		</thead>
		<tbody id="tabla_todos">';

        while ($row = mysql::getrowresult($result)) {
            $nombre         = $row['nombre'];
            $descripcion    = $row['descripcion'];
            $estado         = $row['estado'];
            $row_data = $row;
            $str_data = "";

            foreach ($row_data as $key => $value) {
                $str_data .= $key . "=" . $value . "&";
            }

            $boton_editar  = "<button  class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"editar_registro('$str_data',this.parentNode.parentNode);objeto('idmarca').readOnly = true;goTop();\"><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>";
            $tabla_marca  .= "<tr>
				<td>$boton_editar</td>
                <td>$nombre</td>
                <td>$descripcion</td>
                <td>$estado</td>
			</tr>";
        }
        $tabla_marca .= "</tbody>
        </table>";

        $DATA['tabla_marca'] = $tabla_marca;
        $html                = new html('marca', $DATA);

        return $html->get_html();
    }

    public function guardar_marca($PARAMETROS)
    {
        $PARAMETROS['nombre'] = preg_replace('/\s+/', ' ', trim($PARAMETROS['nombre']));
        
        $parametros_necesarios = ["nombre"]; //valida que se cuente con los parametros necesarios.
        if (! table::validate_parameter_existence($parametros_necesarios, $PARAMETROS)) {
            $this->last_error = 'Datos incompletos.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);

            return false;
        }

        if (empty($PARAMETROS['nombre'])) { //valida que el usuario ingrese un nombre para la marca.
            $this->last_error = 'Debe ingresar un nombre para la marca.';
            utils::report_error(validation_error, $PARAMETROS['nombre'], $this->last_error);

            return false;
        }

        if (strlen($PARAMETROS['nombre']) > 50) { //valida que el nombre de marca no tenga mas de 50 caracteres
            $this->last_error = 'El nombre de marca no puede tener mas de 50 caracteres.';
            utils::report_error(validation_error, $PARAMETROS['nombre'], $this->last_error);

            return false;
        }

        if ($PARAMETROS['idmarca'] == '') {                                  //es una nueva marca
            if (mysql::exists('marca', " nombre = '{$PARAMETROS['nombre']}'")) { //verifica que la marca nueva no exista ya
                $this->last_error = 'La marca ya esta registrada';
                utils::report_error(validation_error, $PARAMETROS['idmarca'], $this->last_error);

                return false;
            }

            $security                  = new security($this->ACCIONES['crear_marca']);
            $valores_necesarios        = ["nombre","descripcion"];
            $DATOS                     = table::create_subarray($valores_necesarios, $PARAMETROS);
            $DATOS['estado']           = "ACTIVO";
            $DATOS['usuario_creacion'] = $security->get_actual_user();

            if (table::insert_record($DATOS)) {
                $idmarca = mysql::last_id();
                $security->registrar_bitacora($this->ACCIONES['crear_marca'], $idmarca);

                return "nuevo";
            } else {
                $this->last_error = "Error al guardar la marca";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        } else {
            if ($PARAMETROS['estado'] == 'ACTIVO') {
                if (mysql::exists('marca', " nombre = '{$PARAMETROS['nombre']}' AND idmarca != '{$PARAMETROS['idmarca']}'")) { //verifica que una marca existente no tenga el mismo nombre que el que se quiere modificar
                    $this->last_error = 'Una marca existente ya tiene ese nombre.';
                    utils::report_error(validation_error, $PARAMETROS = ['idmarca'], $this->last_error);

                    return false;
                }

                $security                      = new security($this->ACCIONES['modificar_marca']); //modificar registro de marca
                $valores_necesarios            = ["idmarca", "nombre", "descripcion", "estado"];
                $DATOS                         = table::create_subarray($valores_necesarios, $PARAMETROS);
                $DATOS['usuario_modificacion'] = $security->get_actual_user();
                $llaves                        = ["idmarca"];

                if (table::update_record($DATOS, $llaves)) {
                    $security->registrar_bitacora($this->ACCIONES['modificar_marca'], $DATOS['idmarca']);

                    return "editado";
                } else {
                    $this->last_error = "Error al modificar el registro";
                    utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                    return false;
                }
            } else {
                $this->last_error = "marca inactiva, no se pueden modificar sus datos.";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        }
    }

    public function estado($idmarca)
    {
        return mysql::getvalue("SELECT estado FROM marca WHERE idmarca = '$idmarca' ");
    }

    public function cambiar_estado($idmarca)
    {
        $security         = new security($this->ACCIONES['cambiar_estado_marca']);
        $estado_actual    = mysql::getvalue("SELECT estado FROM marca WHERE idmarca = '$idmarca' ");
        $DATOS['idmarca'] = $idmarca;

        if ($estado_actual == 'PROTEGIDO') { //si el estado actual es protegido, no se permite cambiar el estado
            $this->last_error = 'Registro protegido, no puede modificarse';
            utils::report_error(validation_error, $idmarca, $this->last_error);

            return $this->estado($idmarca);
        }

        $DATOS['estado']               = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $DATOS['usuario_modificacion'] = $security->get_actual_user();
        $llaves                        = ['idmarca'];

        if (table::update_record($DATOS, $llaves)) {
            $security->registrar_bitacora($this->ACCIONES['cambiar_estado_marca'], $idmarca, $DATOS['estado']);

            return $this->estado($idmarca);
        } else {
            $this->last_error = "Error al cambiar de estado";
            utils::report_error(validation_error, $idmarca, $this->last_error);

            return false;
        }
    }

    public function option_activas()
    {

        return mysql::getoptions("SELECT idmarca as id, nombre as descripcion FROM marca WHERE estado = 'ACTIVO' ORDER BY nombre ASC");
    }

    public function obtener_descripcion($idmarca)
    {
        $idmarca = (int)$idmarca;
        $descripcion = mysql::getvalue("SELECT descripcion FROM marca WHERE idmarca = '$idmarca'");

        if ($descripcion === false || $descripcion === null) {
            $this->last_error = "No se encontro la descripcion de la marca.";
            utils::report_error(validation_error, $idmarca, $this->last_error);
            return false;
        }

        return $descripcion !== '' ? $descripcion : 'Sin descripcion';
    }

    public function get_idmarca($nombre)
    {
        $nombre = trim($nombre);

        return mysql::getvalue("SELECT idmarca FROM marca WHERE UPPER(TRIM(nombre)) = UPPER('{$nombre}')");
    }
}
