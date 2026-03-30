<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/objects.php';
require_once '../wisetech/utils.php';
require_once '../entities/set_talla.php';

class talla extends table
{
    use utils;
    private $idtalla;
    private $ACCIONES  = [];
    public $last_error = '';

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'talla');

        $this->ACCIONES['opcion_talla']         = 198;
        $this->ACCIONES['crear_talla']          = 199;
        $this->ACCIONES['modificar_talla']      = 200;
        $this->ACCIONES['cambiar_estado_talla'] = 201;

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'guardar') {
                if ($resultado = $this->guardar_talla($PARAMETROS)) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['idtalla'], $PARAMETROS, false)) {
                    if ($resultado = self::cambiar_estado($PARAMETROS['idtalla'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error($this->last_error);
                }
            }
        }

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'cargar_tabla_talla') {
                if ($resultado = $this->cargar_tabla_talla()) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }
        }
    }

    public function cargar_tabla_talla()
    {
        $result = mysql::getresult("SELECT idtalla, numero, estado AS estado_talla FROM talla ORDER BY CAST(numero AS UNSIGNED) ASC");

        $tabla = '<table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" width="100%">
            <thead style="background-color: var(--datatable-color); color: white;">
                <tr>
                    <th>Acciones</th>
                    <th>Numero</th>
                    <th>Estado</th>
                </tr>
            </thead>
        <tbody>';

        while ($row = mysql::getrowresult($result)) {
            $numero   = $row['numero'];
            $estado   = $row['estado_talla'];
            $str_data = '';

            foreach ($row as $key => $value) {
                $str_data .= $key . '=' . $value . '&';
            }

            $boton_editar = "<button class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"
                    editar_registro('$str_data',this.parentNode.parentNode);
                    showElements('botones_edicion_talla');
                    goTop();\"
                ><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>";

            $tabla .= "<tr>
                <td>$boton_editar</td>
                <td style='text-align: center;'>$numero</td>
                <td>$estado</td>
            </tr>";
        }

        $tabla .= '</tbody></table>';

        return $tabla;
    }

    public function cargar_opcion()
    {
        $DATA        = [];
        $result      = mysql::getresult("SELECT idtalla, numero, estado FROM talla ORDER BY CAST(numero AS UNSIGNED) ASC");
        $tabla_talla = '<table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
		<thead>
			<tr>
                <th style="text-align: center;">Acciones</th>
                <th style="text-align: center;">Numero</th>
                <th style="text-align: center;">Estado</th>
			</tr>
		</thead>
		<tbody id="tabla_todos">';

        while ($row = mysql::getrowresult($result)) {
            $numero   = $row['numero'];
            $estado   = $row['estado'];
            $row_data = $row;
            $str_data = "";

            foreach ($row_data as $key => $value) {
                $str_data .= $key . "=" . $value . "&";
            }

            $boton_editar  = "<button  class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"editar_registro('$str_data',this.parentNode.parentNode);objeto('idtalla').readOnly = true;goTop();\"><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>";
            $tabla_talla  .= "<tr>
				<td>$boton_editar</td>
                <td>$numero</td>
                <td>$estado</td>
			</tr>";
        }
        $tabla_talla .= "</tbody>
        </table>";

        $DATA['tabla_talla'] = $tabla_talla;
        $html                = new html('set_talla', $DATA);

        return $html->get_html();
    }

    public function guardar_talla($PARAMETROS)
    {
        $parametros_necesarios = ["numero"]; //valida que se cuente con los parametros necesarios.
        if (! table::validate_parameter_existence($parametros_necesarios, $PARAMETROS)) {
            $this->last_error = 'Datos incompletos.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);

            return false;
        }

        if (empty($PARAMETROS['numero'])) { //valida que el usuario ingrese un numero de talla.
            $this->last_error = 'Debe ingresar un numero para la talla.';
            utils::report_error(validation_error, $PARAMETROS['numero'], $this->last_error);

            return false;
        }

        if (strlen($PARAMETROS['numero']) > 10) { //valida que el numero de talla no tenga mas de 10 caracteres
            $this->last_error = 'El numero de talla no puede tener mas de 10 caracteres.';
            utils::report_error(validation_error, $PARAMETROS['numero'], $this->last_error);

            return false;
        }

        if ($PARAMETROS['idtalla'] == '') { //es una nueva talla
            $security = new security($this->ACCIONES['crear_talla']);

            if (mysql::exists('talla', " numero = '{$PARAMETROS['numero']}'")) { //verifica que la talla nueva no exista ya
                $this->last_error = 'La talla ya esta registrada';
                utils::report_error(validation_error, $PARAMETROS['idtalla'], $this->last_error);

                return false;
            }

            $valores_necesarios        = ["numero"];
            $DATOS                     = table::create_subarray($valores_necesarios, $PARAMETROS);
            $DATOS['estado']           = "ACTIVO";
            $DATOS['usuario_creacion'] = $security->get_actual_user();

            if (table::insert_record($DATOS)) {
                $idtalla = mysql::last_id();
                $security->registrar_bitacora($this->ACCIONES['crear_talla'], $idtalla);

                return "nuevo";
            } else {
                $this->last_error = "Error al guardar la talla";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        } else {
            $security = new security($this->ACCIONES['modificar_talla']); //modificar registro de talla
            if ($PARAMETROS['estado_talla'] == 'ACTIVO') {
                if (mysql::exists('talla', " numero = '{$PARAMETROS['numero']}' AND idtalla != '{$PARAMETROS['idtalla']}'")) { //verifica que una talla existente no tenga el mismo numero que el que se quiere modificar
                    $this->last_error = 'Una talla existente ya tiene ese numero.';
                    utils::report_error(validation_error, $PARAMETROS = ['idtalla'], $this->last_error);

                    return false;
                }

                $valores_necesarios            = ["idtalla", "numero", "estado"];
                $DATOS                         = table::create_subarray($valores_necesarios, $PARAMETROS);
                $DATOS['usuario_modificacion'] = $security->get_actual_user();
                $llaves                        = ["idtalla"];

                if (table::update_record($DATOS, $llaves)) {
                    $security->registrar_bitacora($this->ACCIONES['modificar_talla'], $DATOS['idtalla']);

                    return "editado";
                } else {
                    $this->last_error = "Error al modificar el registro";
                    utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                    return false;
                }
            } else {
                $this->last_error = "Talla inactiva, no se pueden modificar sus datos.";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        }
    }

    public function cambiar_estado($idtalla)
    {
        $security                      = new security($this->ACCIONES['cambiar_estado_talla']);
        $estado_actual                 = mysql::getvalue("SELECT estado AS estado_talla FROM talla WHERE idtalla = '$idtalla' ");
        $DATOS['idtalla']              = $idtalla;
        $DATOS['estado']               = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $DATOS['usuario_modificacion'] = $security->get_actual_user();
        $llaves                        = ['idtalla'];

        if (table::update_record($DATOS, $llaves)) {
            $security->registrar_bitacora($this->ACCIONES['cambiar_estado_talla'], $idtalla, $DATOS['estado']);

            return mysql::getvalue("SELECT estado AS estado_talla FROM talla WHERE idtalla = '$idtalla' ");
        } else {
            $this->last_error = "Error al cambiar de estado";
            utils::report_error(validation_error, $idtalla, $this->last_error);

            return false;
        }
    }

    public function get_idtalla($numero)
    {   
        $row = mysql::getrow("SELECT idtalla FROM talla WHERE numero = '$numero'");
        if(empty($row['idtalla'])){
            $this->last_error = "Error no existe talla con el numero $numero.";
            utils::report_error(validation_error, $numero, $this->last_error);

            return false;
        }else{
            return $row['idtalla'];
        }
    }

    public function existen_tallas($talla_desde,$talla_hasta)
    {
        for($i = $talla_desde; $i <= $talla_hasta; $i++){
            $idtalla = $this->get_idtalla($i);

            if(!$idtalla){
                $this->last_error = "No existe talla para el numero $i";

                return false;
            }
        }
        return true;
    }


    public function option_activas()
    {

        return mysql::getoptions("SELECT idtalla as id, numero as descripcion FROM talla WHERE estado = 'ACTIVO' ORDER BY numero ASC");
    }
}
