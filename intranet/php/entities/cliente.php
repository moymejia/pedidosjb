<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/objects.php';
require_once '../wisetech/utils.php';
require_once '../entities/tipo_contacto.php';

class cliente extends table
{

    use utils;
    private $idcliente;
    private $ACCIONES  = [];
    public $last_error = '';

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'cliente');

        $this->ACCIONES['opcion_cliente']         = "Opcion_cliente";
        $this->ACCIONES['crear_cliente']          = "Crear_cliente";
        $this->ACCIONES['modificar_cliente']      = "Modificar_cliente";
        $this->ACCIONES['cambiar_estado_cliente'] = "Cambiar_estado_cliente";

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'guardar') {
                if ($resultado = $this->guardar_cliente($PARAMETROS)) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['idcliente'], $PARAMETROS, false)) {
                    if($resultado = self::cambiar_estado($PARAMETROS['idcliente'])){
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'obtener_correo') {
                if (table::validate_parameter_existence(['idcliente'], $PARAMETROS, false)) {
                    if($resultado = self::obtener_correo($PARAMETROS['idcliente'])){
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
        $DATA['tipo_contacto'] = (new tipo_contacto())->option_activas();

        $result = mysql::getresult("SELECT idcliente, nombre, codigo, direccion, establecimiento,
                telefono, nit, limite_credito, dias_credito, observaciones, estado, correo
            FROM cliente");

        $columnControl = true;
        $responsive    = true;
        $colReorder    = true;
        $select        = true;
        $buttons       = true;
        $paging        = true;
        $ordering      = true;
        $order         = true;
        $rowGroup      = false;
        $reset         = true;

        $data_ = "";
        $data_  = " data-conf-columncontrol='" . ($columnControl ? "true" : "false") . "' ";
        $data_ .= " data-conf-rowgroup=''";
        $data_ .= " data-conf-responsive='"    . ($responsive    ? "true" : "false") . "' ";
        $data_ .= " data-conf-colreorder='"    . ($colReorder    ? "true" : "false") . "' ";
        $data_ .= " data-conf-select='"        . ($select        ? "true" : "false") . "' ";
        $data_ .= " data-conf-buttons='"       . ($buttons       ? "true" : "false") . "' ";
        $data_ .= " data-conf-paging='"        . ($paging        ? "true" : "false") . "' ";
        $data_ .= " data-conf-ordering='"      . ($ordering      ? "true" : "false") . "' ";
        $data_ .= " data-conf-noorder='"       . (!$order        ? "true" : "false") . "' ";
        $data_ .= " data-conf-reset='"         . ($reset         ? "true" : "false") . "' ";

        $tabla_clientes = '<table id="tabla_datos"'.$data_.' class="display nowrap table table-hover table-bordered datatable " cellspacing="0" width="100%">
		<thead>
			<tr>
                <th style="text-align: center;">Acciones</th>
                <th style="text-align: center;">Codigo</th>
				<th style="text-align: center;">Nombre</th>
                <th style="text-align: center;">Establecimiento</th>
                <th style="text-align: center;">Direccion</th>
                <th style="text-align: center;">Telefono</th>
                <th style="text-align: center;">NIT</th>
                <th style="text-align: center;">Observaciones</th>
                <th style="text-align: center;">Limite de credito</th>
                <th style="text-align: center;">Dias de credito</th>
                <th style="text-align: center;">Estado</th>
			</tr>
		</thead>
		<tbody id="tabla_todos">';

        while ($row = mysql::getrowresult($result)) {
            $nombre          = $row['nombre'];
            $codigo          = $row['codigo'];
            $direccion       = $row['direccion'];
            $establecimiento = $row['establecimiento'];
            $telefono        = $row['telefono'];
            $nit             = $row['nit'];
            $limite_credito  = $row['limite_credito'];
            $limite_credito  = number_format($limite_credito, 2, '.', ',');
            $dias_credito    = $row['dias_credito'];
            $observaciones   = $row['observaciones'];
            $estado          = $row['estado'];
            $row_data        = $row;
            $str_data        = "";

            foreach ($row_data as $key => $value) {
                $str_data .= $key . "=" . $value . "&";
            }

            $boton_editar = "<button class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"
                        editar_registro('$str_data', this.parentNode.parentNode);
                        objeto('idcliente').readOnly = true;
                        goTop();
                        hideElements('div_tabla');
                        showElements('div_tabs,tab_contactos,tab_clientes');
                        download_div_content('idcliente','cliente_contacto','tabla_contactos','div_tabla_cliente_contactos');
                    \"
                >
                    <span class=\"btn-label\">
                        <i class=\"far fa-edit\"></i>
                    </span>
                    Editar
                </button>
            ";

            $tabla_clientes .= "<tr>
				<td>$boton_editar</td>
                <td>$codigo</td>
				<td>$nombre</td>
                <td>$establecimiento</td>
                <td>$direccion</td>
                <td>$telefono</td>
                <td>$nit</td>
                <td>$observaciones</td>
                <td style='text-align: right;'>$limite_credito</td>
                <td style='text-align: center;'>$dias_credito</td>
                <td>$estado</td>
			</tr>";
        }
        $tabla_clientes .= "</tbody>
        </table>";

        $DATA['tabla_clientes'] = $tabla_clientes;
        $html                   = new html('cliente', $DATA);

        return $html->get_html();
    }

    public function guardar_cliente($PARAMETROS)
    {
        //valida que se cuente con los parametros necesarios.
        $parametros_necesarios = ["nombre", "codigo", "direccion", "establecimiento", "telefono", "nit", "limite_credito", "dias_credito", "observaciones","correo"];
        if (! table::validate_parameter_existence($parametros_necesarios, $PARAMETROS)) {
            $this->last_error = 'Datos incompletos.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);

            return false;
        }

        //valida que el usuario ingrese un codigo para el cliente
        if (empty($PARAMETROS['codigo'])) {
            $this->last_error = 'Debe ingresar un codigo para el cliente';
            utils::report_error(validation_error, $PARAMETROS['codigo'], $this->last_error);

            return false;
        }

        //valida que el usuario ingrese un nombre para el cliente.
        if (empty($PARAMETROS['nombre'])) {
            $this->last_error = 'Debe ingresar el nombre del cliente';
            utils::report_error(validation_error, $PARAMETROS['nombre'], $this->last_error);

            return false;
        }

        if (empty($PARAMETROS['nit'])) {
            $this->last_error = 'Debe ingresar el NIT del cliente';
            utils::report_error(validation_error, $PARAMETROS['nit'], $this->last_error);

            return false;
        }

        //valida que el usuario ingrese un limite de credito para el cliente.
        if (empty($PARAMETROS['limite_credito'])) {
            $this->last_error = 'Debe ingresar limite de credito del cliente';
            utils::report_error(validation_error, $PARAMETROS['limite_credito'], $this->last_error);

            return false;
        }

        //valida que el usuario ingrese los dias de credito del cliente.
        if (empty($PARAMETROS['dias_credito'])) {
            $this->last_error = 'Debe ingresar los dias de credito del cliente';
            utils::report_error(validation_error, $PARAMETROS['dias_credito'], $this->last_error);

            return false;
        }

        if ($PARAMETROS['idcliente'] == '') { //es cliente nuevo
            $security                  = new security($this->ACCIONES['crear_cliente']);
            //valida que el codigo asignado no este siendo usado por otro cliente.
            if (mysql::exists('cliente', " codigo = '{$PARAMETROS['codigo']}'")) { //verifica que el codigo de cliente nuevo no exista ya 
                $this->last_error = 'Ya existe un cliente con el codigo: ' . $PARAMETROS['codigo'];
                utils::report_error(validation_error, $PARAMETROS['idcliente'], $this->last_error);

                return false;
            }


            $valores_necesarios        = [ "nombre", "codigo", "direccion", "establecimiento", "telefono", "nit", "limite_credito", "dias_credito", "observaciones","correo"];
            $DATOS                     = table::create_subarray($valores_necesarios, $PARAMETROS);
            $DATOS['estado']           = "ACTIVO";
            $DATOS['usuario_creacion'] = $security->get_actual_user();

            if (table::insert_record($DATOS)) {
                $idcliente = mysql::last_id();
                $security->registrar_bitacora($this->ACCIONES['crear_cliente'], $idcliente);

                return "nuevo";
            } else {
                $this->last_error = "Error al guardar el registro";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }

        } else {
            $security = new security($this->ACCIONES['modificar_cliente']);
            if ($PARAMETROS['estado'] == 'ACTIVO') {

                if (mysql::exists('cliente', " codigo = '{$PARAMETROS['codigo']}' AND idcliente != '{$PARAMETROS['idcliente']}'")) {
                    $this->last_error = 'Uno de los clientes ya tiene asignado el codigo: ' . $PARAMETROS['codigo'];
                    utils::report_error(validation_error, $PARAMETROS=['idcliente'], $this->last_error);

                    return false;
                }

                 //modificar registro de cliente
                $valores_necesarios            = ["idcliente", "nombre", "codigo", "direccion", "establecimiento", "telefono", "nit", "limite_credito", "dias_credito", "observaciones", "correo"];
                $DATOS                         = table::create_subarray($valores_necesarios, $PARAMETROS);
                $DATOS['usuario_modificacion'] = $security->get_actual_user();
                $llaves                        = ["idcliente"];

                if (table::update_record($DATOS, $llaves)) {
                    $security->registrar_bitacora($this->ACCIONES['modificar_cliente'], $DATOS['idcliente']);

                    return "editado";
                } else {
                    $this->last_error = "Error al modificar el registro";
                    utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                    return false;
                }

            } else {
                $this->last_error = "Usuario inactivo, no se puede editar.";
                utils::report_error(validation_error, $PARAMETROS, $this->last_error);

                return false;
            }
        }
    }

    public function estado($idcliente){
        return mysql::getvalue("SELECT estado FROM cliente WHERE idcliente = '$idcliente' ");
    }

    private function cambiar_estado($idcliente)
    {

        $security           = new security($this->ACCIONES['cambiar_estado_cliente']);
        $estado_actual      = mysql::getvalue("SELECT estado FROM cliente WHERE idcliente = '$idcliente' ");
        $DATOS['idcliente'] = $idcliente;

        if ($estado_actual == 'PROTEGIDO') {
            $this->last_error = 'Registro protegido, no puede modificarse';
            utils::report_error(validation_error, $idcliente, $this->last_error);

            return $this->estado($idcliente);;
        }

        $DATOS['estado']               = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $DATOS['usuario_modificacion'] = $security->get_actual_user();
        $llaves                        = ['idcliente'];
        
        
        if (table::update_record($DATOS, $llaves)) {
            $security->registrar_bitacora($this->ACCIONES['cambiar_estado_cliente'], $idcliente, $DATOS['estado']);

            return $this->estado($idcliente);
        } else {
            $this->last_error = "Error al cambiar de estado";
            utils::report_error(validation_error, $idcliente, $this->last_error);

            return false;
        }
    }

    public function option_activas(){

        return mysql::getoptions("SELECT idcliente AS id, CONCAT(codigo, ' - ', nombre) AS descripcion FROM cliente  WHERE estado = 'ACTIVO' ORDER BY nombre ASC");
    }

    public function obtener_correo($idcliente){
    
        $correo = mysql::getvalue("SELECT correo FROM cliente WHERE idcliente = $idcliente LIMIT 1");
    
        if (empty($correo)) {
            return 'sin_correo';
        }
    
        return $correo;
    }
}
