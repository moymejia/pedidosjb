<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/utils.php';

class transporte extends table
{

    use utils;

    private $last_error;
    private $ACCIONES = [];

    public function __construct($PARAMETROS = null)
    {

        parent::__construct(prefijo . '_pedidos', 'transporte');

        $this->ACCIONES['opcion_transporte']         = 223;
        $this->ACCIONES['crear_transporte']          = 224;
        $this->ACCIONES['modificar_transporte']      = 225;
        $this->ACCIONES['cambiar_estado_transporte'] = 226;

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'guardar') {
                if ($resultado = $this->guardar_transporte($PARAMETROS)) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['idtransporte'], $PARAMETROS, false)) {
                    if ($resultado = self::cambiar_estado($PARAMETROS['idtransporte'])) {
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
        $result           = mysql::getresult("SELECT idtransporte, nombre, estado FROM transporte ORDER BY idtransporte DESC");
        $tabla_transporte = '<table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
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

            $boton_editar      = "<button  class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"editar_registro('$str_data',this.parentNode.parentNode);objeto('idtransporte').readOnly = true;goTop();\"><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>";
            $tabla_transporte .= "<tr>
				<td>$boton_editar</td>
                <td>$nombre</td>
                <td>$estado</td>
			</tr>";
        }
        $tabla_transporte .= "</tbody>
        </table>";

        $DATA['tabla_transporte'] = $tabla_transporte;
        $html                     = new html('transporte', $DATA);

        return $html->get_html();
    }

    public function guardar_transporte($PARAMETROS)
    {
        $parametros_necesarios = ["nombre"];
        if (! table::validate_parameter_existence($parametros_necesarios, $PARAMETROS)) {
            $this->last_error = 'Datos incompletos.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);

            return false;
        }

        if (empty($PARAMETROS['nombre'])) {
            $this->last_error = 'Debe ingresar un nombre para el transporte.';
            utils::report_error(validation_error, $PARAMETROS['nombre'], $this->last_error);

            return false;
        }

        if ($PARAMETROS['idtransporte'] == '') {
            $security = new security($this->ACCIONES['crear_transporte']);
            if (mysql::exists('transporte', " nombre = '{$PARAMETROS['nombre']}'")) {
                $this->last_error = 'El transporte ya esta registrado';
                utils::report_error(validation_error, $PARAMETROS['idtransporte'], $this->last_error);

                return false;
            }

            $valores_necesarios        = ["nombre"];
            $DATOS                     = table::create_subarray($valores_necesarios, $PARAMETROS);
            $DATOS['estado']           = "ACTIVO";
            $DATOS['usuario_creacion'] = $security->get_actual_user();

            if (table::insert_record($DATOS)) {
                $idtransporte = mysql::last_id();
                $security->registrar_bitacora($this->ACCIONES['crear_transporte'], $idtransporte);

                return "nuevo";
            } else {
                $this->last_error = "Error al guardar el transporte";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        } else {
            $security = new security($this->ACCIONES['modificar_transporte']);
            if ($PARAMETROS['estado'] == 'ACTIVO') {
                if (mysql::exists('transporte', " nombre = '{$PARAMETROS['nombre']}' AND idtransporte != '{$PARAMETROS['idtransporte']}'")) {
                    $this->last_error = 'Un transporte existente ya tiene ese nombre.';
                    utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                    return false;
                }

                $valores_necesarios            = ["idtransporte", "nombre"];
                $DATOS                         = table::create_subarray($valores_necesarios, $PARAMETROS);
                $DATOS['usuario_modificacion'] = $security->get_actual_user();
                $llaves                        = ["idtransporte"];

                if (table::update_record($DATOS, $llaves)) {
                    $security->registrar_bitacora($this->ACCIONES['modificar_transporte'], $DATOS['idtransporte']);

                    return "editado";
                } else {
                    $this->last_error = "Error al modificar el registro";
                    utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                    return false;
                }
            } else {
                $this->last_error = "Transporte inactivo, no se pueden modificar sus datos.";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        }
    }

    public function cambiar_estado($idtransporte)
    {
        $security              = new security($this->ACCIONES['cambiar_estado_transporte']);
        $estado_actual         = mysql::getvalue("SELECT estado FROM transporte WHERE idtransporte = '$idtransporte' ");
        $DATOS['idtransporte'] = $idtransporte;

        if ($estado_actual == 'PROTEGIDO') {
            $this->last_error = 'Registro protegido, no puede modificarse';
            utils::report_error(validation_error, $idtransporte, $this->last_error);

            return $this->estado($idtransporte);
        }

        $DATOS['estado']               = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $DATOS['usuario_modificacion'] = $security->get_actual_user();
        $llaves                        = ['idtransporte'];

        if (table::update_record($DATOS, $llaves)) {
            $security->registrar_bitacora($this->ACCIONES['cambiar_estado_transporte'], $idtransporte, $DATOS['estado']);

            return $this->estado($idtransporte);
        } else {
            $this->last_error = "Error al cambiar de estado";
            utils::report_error(validation_error, $idtransporte, $this->last_error);

            return false;
        }
    }

    public function option_activas()
    {

        return mysql::getoptions("SELECT idtransporte as id, nombre as descripcion FROM transporte WHERE estado = 'ACTIVO' ORDER BY nombre ASC");
    }

    public function estado($idtransporte)
    {
        return mysql::getvalue("SELECT estado FROM transporte WHERE idtransporte = '$idtransporte' ");
    }

}
