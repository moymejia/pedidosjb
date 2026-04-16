<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/objects.php';
require_once '../wisetech/utils.php';

class temporada extends table
{
    use utils;
    private $idtemporada;
    private $ACCIONES  = [];
    public $last_error = '';

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'temporada');

        $this->ACCIONES['opcion_temporada']         = "Opcion_temporada";
        $this->ACCIONES['crear_temporada']          = "Crear_temporada";
        $this->ACCIONES['modificar_temporada']      = "Modificar_temporada";
        $this->ACCIONES['cambiar_estado_temporada'] = "Cambiar_estado_temporada";

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'guardar') {
                if ($resultado = $this->guardar_temporada($PARAMETROS)) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['idtemporada'], $PARAMETROS, false)) {
                    if($resultado = self::cambiar_estado($PARAMETROS['idtemporada'])){
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'obtener_fechas_temporada') {
                if (table::validate_parameter_existence(['idtemporada'], $PARAMETROS, false)) {
                    if($resultado = self::obtener_fechas_temporada($PARAMETROS['idtemporada'])){
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
        $result = mysql::getresult("SELECT idtemporada, nombre, fecha_inicio, fecha_fin, estado FROM temporada ORDER BY idtemporada DESC");
        $tabla_temporada = '<table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
		<thead>
			<tr>
                <th style="text-align: center;">Acciones</th>
                <th style="text-align: center;">Nombre</th>
                <th style="text-align: center;">Fecha de inicio</th>
                <th style="text-align: center;">Fecha de final</th>
                <th style="text-align: center;">Estado</th>
			</tr>
		</thead>
		<tbody id="tabla_todos">';

        while ($row = mysql::getrowresult($result)) {
            $nombre = $row['nombre'];
            $fecha_inicio = $row['fecha_inicio'];
            $fecha_fin = $row['fecha_fin'];
            $estado = $row['estado'];
            $row_data = $row;
            $str_data = "";

            foreach ($row_data as $key => $value) {
                $str_data .= $key . "=" . $value . "&";
            }

            $boton_editar    = "<button  class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"editar_registro('$str_data',this.parentNode.parentNode);objeto('idtemporada').readOnly = true;goTop();\"><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>";
            $tabla_temporada .= "<tr>
				<td>$boton_editar</td>
                <td>$nombre</td>
                <td style='text-align: left;'>$fecha_inicio</td>
                <td style='text-align: left;'>$fecha_fin</td>
                <td>$estado</td>
			</tr>";
        }
        $tabla_temporada .= "</tbody>
        </table>";

        $DATA['tabla_temporada'] = $tabla_temporada;
        $html                        = new html('temporada', $DATA);

        return $html->get_html();
    }

    public function guardar_temporada($PARAMETROS)
    {
        $parametros_necesarios = ["nombre", "fecha_inicio", "estado"]; //valida que se cuente con los parametros necesarios.
        if (!table::validate_parameter_existence($parametros_necesarios, $PARAMETROS)) {
            $this->last_error = 'Datos incompletos.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);

            return false;
        }

        if (empty($PARAMETROS['nombre'])) { //valida que el usuario ingrese un nombre para la temporada.
            $this->last_error = 'Debe ingresar un nombre para la temporada.';
            utils::report_error(validation_error, $PARAMETROS['nombre'], $this->last_error);

            return false;
        }

        if (empty($PARAMETROS['fecha_inicio'])) { //valida que el usuario ingrese un fecha de inicio para la temporada.
            $this->last_error = 'Debe ingresar un fecha de inicio para la temporada.';
            utils::report_error(validation_error, $PARAMETROS['fecha_inicio'], $this->last_error);

            return false;
        }

        $fecha_de_inicio = new DateTime($PARAMETROS['fecha_inicio']);

        if (!empty($PARAMETROS['fecha_fin'])) { //valida que la fecha de inicio sea menor a la fecha de fin, en caso de que se ingrese una fecha de fin para la temporada.
            $fecha_de_fin = new DateTime($PARAMETROS['fecha_fin']);

            if ($fecha_de_inicio > $fecha_de_fin) { 
                $this->last_error = 'La fecha de inicio debe ser menor a la fecha de fin.';
                utils::report_error(validation_error, $PARAMETROS['idtemporada'], $this->last_error);

                return false;
            }
        } else { //si no se ingresa fecha de fin, se guarda como null en la base de datos
            $PARAMETROS['fecha_fin'] = null;
        }
        

        if ($PARAMETROS['idtemporada'] == '') { //es una nueva temporada
            $security = new security($this->ACCIONES['crear_temporada']);
            if (mysql::exists('temporada', " nombre = '{$PARAMETROS['nombre']}'")) { //verifica que la temporada nueva no exista ya
                $this->last_error = 'La temporada ya esta registrada';
                utils::report_error(validation_error, $PARAMETROS['idtemporada'], $this->last_error);

                return false;
            }

            $valores_necesarios        = ["nombre", "fecha_inicio", "fecha_fin", "estado"];
            $DATOS                     = table::create_subarray($valores_necesarios, $PARAMETROS);
            $DATOS['estado']           = "ACTIVO";
            $DATOS['usuario_creacion'] = $security->get_actual_user();

            if (table::insert_record($DATOS)) {
                $idtemporada = mysql::last_id();
                $security->registrar_bitacora($this->ACCIONES['crear_temporada'], $idtemporada);

                return "nuevo";
            } else {
                $this->last_error = "Error al guardar la temporada";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        } else {
            $security = new security($this->ACCIONES['modificar_temporada']);
            if ($PARAMETROS['estado'] == 'ACTIVO') {
                if (mysql::exists('temporada', " nombre = '{$PARAMETROS['nombre']}' AND idtemporada != '{$PARAMETROS['idtemporada']}'")) { //verifica que una temporada existente no tenga el mismo nombre que el que se quiere modificar
                    $this->last_error = 'Una temporada existente ya tiene ese nombre.';
                    utils::report_error(validation_error, $PARAMETROS=['idtemporada'], $this->last_error);

                    return false;
                }

                $valores_necesarios            = ["idtemporada", "nombre", "fecha_inicio", "fecha_fin", "estado"];
                $DATOS                         = table::create_subarray($valores_necesarios, $PARAMETROS);
                $DATOS['usuario_modificacion'] = $security->get_actual_user();
                $llaves                        = ["idtemporada"];

                if (table::update_record($DATOS, $llaves)) {
                    $security->registrar_bitacora($this->ACCIONES['modificar_temporada'], $DATOS['idtemporada']);

                    return "editado";
                } else {
                    $this->last_error = "Error al modificar el registro";
                    utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                    return false;
                }
            } else {
                $this->last_error = "Temporada inactiva, no se pueden modificar sus datos.";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        }
    }

    public function estado($idtemporada){
        return mysql::getvalue("SELECT estado FROM temporada WHERE idtemporada = '$idtemporada' ");
    }

    public function cambiar_estado($idtemporada){
        $security             = new security($this->ACCIONES['cambiar_estado_temporada']);
        $estado_actual        = mysql::getvalue("SELECT estado FROM temporada WHERE idtemporada = '$idtemporada' ");;
        $DATOS['idtemporada'] = $idtemporada;

        if ($estado_actual == 'PROTEGIDO') { //si el estado actual es protegido, no se permite cambiar el estado
            $this->last_error = 'Registro protegido, no puede modificarse';
            utils::report_error(validation_error, $idtemporada, $this->last_error);

            return $this->estado($idtemporada);;
        }

        $DATOS['estado']               = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $DATOS['usuario_modificacion'] = $security->get_actual_user();
        $llaves                        = ['idtemporada'];
        
        
        if (table::update_record($DATOS, $llaves)) {
            $security->registrar_bitacora($this->ACCIONES['cambiar_estado_temporada'], $idtemporada, $DATOS['estado']);

            return $this->estado($idtemporada);
        } else {
            $this->last_error = "Error al cambiar de estado";
            utils::report_error(validation_error, $idtemporada, $this->last_error);

            return false;
        }
    }

    public function option_activos(){ 
        
        return mysql::getoptions("SELECT idtemporada as id, nombre as descripcion FROM temporada WHERE estado IN ('ACTIVO','PROTEGIDO') ORDER BY nombre ASC");
    }

    public function obtener_fechas_temporada($idtemporada){
        $data = mysql::getrow("SELECT fecha_inicio, fecha_fin FROM temporada WHERE idtemporada = $idtemporada LIMIT 1");
    
        return json_encode([
            'fecha_desde' => $data['fecha_inicio'],
            'fecha_hasta' => $data['fecha_fin']
        ]);
    }

    
}
