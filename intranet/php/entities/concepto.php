<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/utils.php';

class concepto extends table
{
    use utils;
    private $idconcepto;
    public $last_error = '';
    private $ACCIONES   = [];

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'concepto');

        $this->ACCIONES['opcion_concepto']         = "Opcion_concepto";
        $this->ACCIONES['crear_concepto']          = "Crear_concepto";
        $this->ACCIONES['modificar_concepto']      = "Modificar_concepto";
        $this->ACCIONES['cambiar_estado_concepto'] = "Cambiar_estado_concepto";

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'guardar') {
                if ($resultado = $this->guardar_concepto($PARAMETROS)) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['idconcepto'], $PARAMETROS, false)) {
                    if ($resultado = self::cambiar_estado($PARAMETROS['idconcepto'])) {
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
        $DATA           = [];
        $result         = mysql::getresult("SELECT idconcepto, nombre, estado FROM concepto ORDER BY idconcepto DESC");
        $tabla_concepto = '<table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
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

            $boton_editar    = "<button  class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"editar_registro('$str_data',this.parentNode.parentNode);objeto('idconcepto').readOnly = true;goTop();\"><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>";
            $tabla_concepto .= "<tr>
				<td>$boton_editar</td>
                <td>$nombre</td>
                <td>$estado</td>
			</tr>";
        }
        $tabla_concepto .= "</tbody>
        </table>";

        $DATA['tabla_concepto'] = $tabla_concepto;
        $html                   = new html('concepto', $DATA);

        return $html->get_html();
    }

    public function guardar_concepto($PARAMETROS)
    {
        $parametros_necesarios = ["nombre"];
        if (! table::validate_parameter_existence($parametros_necesarios, $PARAMETROS)) {
            $this->last_error = 'Datos incompletos.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);

            return false;
        }

        if (empty($PARAMETROS['nombre'])) {
            $this->last_error = 'Debe ingresar un nombre para el concepto.';
            utils::report_error(validation_error, $PARAMETROS['nombre'], $this->last_error);

            return false;
        }

        if ($PARAMETROS['idconcepto'] == '') {
            $security = new security($this->ACCIONES['crear_concepto']);
            if (mysql::exists('concepto', " nombre = '{$PARAMETROS['nombre']}'")) {
                $this->last_error = 'El concepto ya esta registrado';
                utils::report_error(validation_error, $PARAMETROS['idconcepto'], $this->last_error);

                return false;
            }

            $valores_necesarios        = ["nombre"];
            $DATOS                     = table::create_subarray($valores_necesarios, $PARAMETROS);
            $DATOS['estado']           = "ACTIVO";
            $DATOS['usuario_creacion'] = $security->get_actual_user();

            if (table::insert_record($DATOS)) {
                $idconcepto = mysql::last_id();
                $security->registrar_bitacora($this->ACCIONES['crear_concepto'], $idconcepto);

                return "nuevo";
            } else {
                $this->last_error = "Error al guardar el concepto";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        } else {
            $security = new security($this->ACCIONES['modificar_concepto']);
            if ($PARAMETROS['estado'] == 'ACTIVO') {
                if (mysql::exists('concepto', " nombre = '{$PARAMETROS['nombre']}' AND idconcepto != '{$PARAMETROS['idconcepto']}'")) {
                    $this->last_error = 'Un concepto existente ya tiene ese nombre.';
                    utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                    return false;
                }

                $valores_necesarios            = ["idconcepto", "nombre"];
                $DATOS                         = table::create_subarray($valores_necesarios, $PARAMETROS);
                $DATOS['usuario_modificacion'] = $security->get_actual_user();
                $llaves                        = ["idconcepto"];

                if (table::update_record($DATOS, $llaves)) {
                    $security->registrar_bitacora($this->ACCIONES['modificar_concepto'], $DATOS['idconcepto']);

                    return "editado";
                } else {
                    $this->last_error = "Error al modificar el registro";
                    utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                    return false;
                }
            } else {
                $this->last_error = "Concepto inactivo, no se pueden modificar sus datos.";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        }
    }

    public function cambiar_estado($idconcepto)
    {
        $security            = new security($this->ACCIONES['cambiar_estado_concepto']);
        $estado_actual       = mysql::getvalue("SELECT estado FROM concepto WHERE idconcepto = '$idconcepto' ");
        $DATOS['idconcepto'] = $idconcepto;

        if ($estado_actual == 'PROTEGIDO') {
            $this->last_error = 'Registro protegido, no puede modificarse';
            utils::report_error(validation_error, $idconcepto, $this->last_error);

            return $this->estado($idconcepto);
        }

        $DATOS['estado']               = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $DATOS['usuario_modificacion'] = $security->get_actual_user();
        $llaves                        = ['idconcepto'];

        if (table::update_record($DATOS, $llaves)) {
            $security->registrar_bitacora($this->ACCIONES['cambiar_estado_concepto'], $idconcepto, $DATOS['estado']);

            return $this->estado($idconcepto);
        } else {
            $this->last_error = "Error al cambiar de estado";
            utils::report_error(validation_error, $idconcepto, $this->last_error);

            return false;
        }
    }

    public function estado($idconcepto)
    {
        return mysql::getvalue("SELECT estado FROM concepto WHERE idconcepto = '$idconcepto' ");
    }

    public function get_idconcepto($nombre)
    {
        if(mysql::exists('concepto',"nombre = '$nombre'")){
            return mysql::getvalue("SELECT idconcepto FROM concepto WHERE nombre = '$nombre'");
        }else{
            $DATOS = [];
            $DATOS['idconcepto'] = '';
            $DATOS['nombre']     = $nombre;
            return $this->guardar_concepto($DATOS);
        }
    }

    public function option_activos()
    {
        return mysql::getoptions("SELECT idconcepto id, nombre descripcion FROM concepto WHERE estado = 'ACTIVO' ORDER BY nombre ASC");
    }

}
