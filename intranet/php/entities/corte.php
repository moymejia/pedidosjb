<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/utils.php';

class corte extends table
{
    use utils;
    private $idcorte;
    public $last_error = '';
    private $ACCIONES   = [];

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'corte');

        $this->ACCIONES['opcion_corte']         = "Opcion_corte";
        $this->ACCIONES['crear_corte']          = "Crear_corte";
        $this->ACCIONES['modificar_corte']      = "Modificar_corte";
        $this->ACCIONES['cambiar_estado_corte'] = "Cambiar_estado_corte";

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'guardar') {
                if ($resultado = $this->guardar_corte($PARAMETROS)) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['idcorte'], $PARAMETROS, false)) {
                    if ($resultado = self::cambiar_estado($PARAMETROS['idcorte'])) {
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
        $result      = mysql::getresult("SELECT idcorte, nombre, estado FROM corte ORDER BY idcorte DESC");
        $tabla_corte = '<table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
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

            $boton_editar  = "<button  class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"editar_registro('$str_data',this.parentNode.parentNode);objeto('idcorte').readOnly = true;goTop();\"><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>";
            $tabla_corte  .= "<tr>
				<td>$boton_editar</td>
                <td>$nombre</td>
                <td>$estado</td>
			</tr>";
        }
        $tabla_corte .= "</tbody>
        </table>";

        $DATA['tabla_corte'] = $tabla_corte;
        $html                = new html('corte', $DATA);

        return $html->get_html();
    }

    public function guardar_corte($PARAMETROS)
    {
        $parametros_necesarios = ["nombre"];
        if (! table::validate_parameter_existence($parametros_necesarios, $PARAMETROS)) {
            $this->last_error = 'Datos incompletos.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);

            return false;
        }

        if (empty($PARAMETROS['nombre'])) {
            $this->last_error = 'Debe ingresar un nombre para el corte.';
            utils::report_error(validation_error, $PARAMETROS['nombre'], $this->last_error);

            return false;
        }

        if ($PARAMETROS['idcorte'] == '') {
            $security = new security($this->ACCIONES['crear_corte']);
            if (mysql::exists('corte', " nombre = '{$PARAMETROS['nombre']}'")) {
                $this->last_error = 'El corte ya esta registrado';
                utils::report_error(validation_error, $PARAMETROS['idcorte'], $this->last_error);

                return false;
            }

            $valores_necesarios        = ["nombre"];
            $DATOS                     = table::create_subarray($valores_necesarios, $PARAMETROS);
            $DATOS['estado']           = "ACTIVO";
            $DATOS['usuario_creacion'] = $security->get_actual_user();

            if (table::insert_record($DATOS)) {
                $idcorte = mysql::last_id();
                $security->registrar_bitacora($this->ACCIONES['crear_corte'], $idcorte);

                return "nuevo";
            } else {
                $this->last_error = "Error al guardar el corte";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        } else {
            $security = new security($this->ACCIONES['modificar_corte']);
            if ($PARAMETROS['estado'] == 'ACTIVO') {
                if (mysql::exists('corte', " nombre = '{$PARAMETROS['nombre']}' AND idcorte != '{$PARAMETROS['idcorte']}'")) {
                    $this->last_error = 'Un corte existente ya tiene ese nombre.';
                    utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                    return false;
                }

                $valores_necesarios            = ["idcorte", "nombre"];
                $DATOS                         = table::create_subarray($valores_necesarios, $PARAMETROS);
                $DATOS['usuario_modificacion'] = $security->get_actual_user();
                $llaves                        = ["idcorte"];

                if (table::update_record($DATOS, $llaves)) {
                    $security->registrar_bitacora($this->ACCIONES['modificar_corte'], $DATOS['idcorte']);

                    return "editado";
                } else {
                    $this->last_error = "Error al modificar el registro";
                    utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                    return false;
                }
            } else {
                $this->last_error = "Corte inactivo, no se pueden modificar sus datos.";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        }
    }

    public function cambiar_estado($idcorte)
    {
        $security         = new security($this->ACCIONES['cambiar_estado_corte']);
        $estado_actual    = mysql::getvalue("SELECT estado FROM corte WHERE idcorte = '$idcorte' ");
        $DATOS['idcorte'] = $idcorte;

        if ($estado_actual == 'PROTEGIDO') {
            $this->last_error = 'Registro protegido, no puede modificarse';
            utils::report_error(validation_error, $idcorte, $this->last_error);

            return $this->estado($idcorte);
        }

        $DATOS['estado']               = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $DATOS['usuario_modificacion'] = $security->get_actual_user();
        $llaves                        = ['idcorte'];

        if (table::update_record($DATOS, $llaves)) {
            $security->registrar_bitacora($this->ACCIONES['cambiar_estado_corte'], $idcorte, $DATOS['estado']);

            return $this->estado($idcorte);
        } else {
            $this->last_error = "Error al cambiar de estado";
            utils::report_error(validation_error, $idcorte, $this->last_error);

            return false;
        }
    }

    public function estado($idcorte)
    {
        return mysql::getvalue("SELECT estado FROM corte WHERE idcorte = '$idcorte' ");
    }

    public function get_idcorte($nombre)
    {
        if(mysql::exists('corte',"nombre = '$nombre'")){
            return mysql::getvalue("SELECT idcorte FROM corte WHERE nombre = '$nombre'");
        }else{
            $DATOS = [];
            $DATOS['idcorte'] = '';
            $DATOS['nombre']  = $nombre;
            return $this->guardar_corte($DATOS);
        }
    }

    public function option_activos()
    {
        return mysql::getoptions("SELECT idcorte id, nombre descripcion FROM corte WHERE estado = 'ACTIVO' ORDER BY nombre ASC");
    }
}
