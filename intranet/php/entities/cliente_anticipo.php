<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/objects.php';
require_once '../wisetech/utils.php';
require_once '../entities/cliente.php';
require_once '../entities/tipo_pago.php';

class cliente_anticipo extends table
{
    use utils;

    private $ACCIONES  = [];
    public $last_error = '';

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'cliente_anticipo');

        $this->ACCIONES['opcion_cliente_anticipo']         = 'Opcion_cliente_anticipo';
        $this->ACCIONES['consultar_cliente_anticipo']      = 'Consultar_cliente_anticipo';
        $this->ACCIONES['crear_cliente_anticipo']          = 'Crear_cliente_anticipo';
        $this->ACCIONES['modificar_cliente_anticipo']      = 'Modificar_cliente_anticipo';
        $this->ACCIONES['cambiar_estado_cliente_anticipo'] = 'Cambiar_estado_cliente_anticipo';

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'guardar') {
                if ($resultado = $this->guardar_cliente_anticipo($PARAMETROS)) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['idcliente_anticipo'], $PARAMETROS, false)) {
                    if ($resultado = self::cambiar_estado($PARAMETROS['idcliente_anticipo'])) {
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
        $security = new security($this->ACCIONES['opcion_cliente_anticipo']);
        $security->registrar_bitacora($this->ACCIONES['opcion_cliente_anticipo'], 'cargar_opcion');

        $DATA = [];
        $_CLIENTE   = new cliente();
        $_TIPO_PAGO = new tipo_pago();

        $DATA['options_clientes'] = $_CLIENTE->option_activas();
        $DATA['options_tipos_pago'] = $_TIPO_PAGO->option_activas();
        $DATA['fecha_hoy'] = date('Y-m-d');

        $result = mysql::getresult("SELECT idcliente_anticipo, idcliente, fecha, idtipo_pago, monto, saldo_disponible, referencia_pago, observaciones,
                estado, cliente, tipo_pago
            FROM view_cliente_anticipo
            ORDER BY idcliente_anticipo DESC");

        if (! $result) {
            $this->last_error = 'No se pudo cargar la lista de anticipos.';
            utils::report_error(bd_error, 'cliente_anticipo', $this->last_error);
            return false;
        }

        $tabla_cliente_anticipo = '<table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
		<thead>
			<tr>
                <th style="text-align: center;">Acciones</th>
                <th style="text-align: center;">Cliente</th>
                <th style="text-align: center;">Fecha</th>
                <th style="text-align: center;">Tipo pago</th>
                <th style="text-align: center;">Monto</th>
                <th style="text-align: center;">Saldo disponible</th>
                <th style="text-align: center;">Referencia</th>
                <th style="text-align: center;">Estado</th>
			</tr>
		</thead>
		<tbody id="tabla_todos">';

        while ($row = mysql::getrowresult($result)) {
            $cliente = $row['cliente'];
            $fecha = $row['fecha'];
            $tipo_pago = $row['tipo_pago'];
            $monto = number_format((float)$row['monto'], 2, '.', ',');
            $saldo_disponible = number_format((float)$row['saldo_disponible'], 2, '.', ',');
            $referencia_pago = $row['referencia_pago'];
            $estado = $row['estado'];
            $row_data = $row;
            $str_data = '';

            foreach ($row_data as $key => $value) {
                $str_data .= $key . '=' . $value . '&';
            }

            $boton_editar = "<button class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"editar_registro('$str_data',this.parentNode.parentNode);objeto('idcliente_anticipo').readOnly = true;goTop();\"><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>";
            $tabla_cliente_anticipo .= "<tr>
				<td>$boton_editar</td>
                <td>$cliente</td>
                <td>$fecha</td>
                <td>$tipo_pago</td>
                <td style='text-align: right;'>$monto</td>
                <td style='text-align: right;'>$saldo_disponible</td>
                <td>$referencia_pago</td>
                <td>$estado</td>
			</tr>";
        }

        $tabla_cliente_anticipo .= '</tbody>
        </table>';

        $DATA['tabla_cliente_anticipo'] = $tabla_cliente_anticipo;
        $html = new html('cliente_anticipo', $DATA);

        return $html->get_html();
    }

    public function guardar_cliente_anticipo($PARAMETROS)
    {
        $parametros_necesarios = ['idcliente', 'fecha', 'idtipo_pago', 'monto'];
        if (! table::validate_parameter_existence($parametros_necesarios, $PARAMETROS)) {
            $this->last_error = 'Datos incompletos.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        if (trim($PARAMETROS['idcliente']) == '') {
            $this->last_error = 'Debe seleccionar un cliente.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        if (trim($PARAMETROS['fecha']) == '') {
            $this->last_error = 'Debe ingresar una fecha valida.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        if (trim($PARAMETROS['idtipo_pago']) == '') {
            $this->last_error = 'Debe seleccionar un tipo de pago.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        $monto = (float)$PARAMETROS['monto'];
        if ($monto <= 0) {
            $this->last_error = 'El monto debe ser mayor a cero.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        $referencia_pago = isset($PARAMETROS['referencia_pago']) ? trim($PARAMETROS['referencia_pago']) : '';
        $observaciones   = isset($PARAMETROS['observaciones']) ? trim($PARAMETROS['observaciones']) : '';

        if (strlen($referencia_pago) > 100) {
            $this->last_error = 'La referencia de pago no puede tener mas de 100 caracteres.';
            utils::report_error(validation_error, $referencia_pago, $this->last_error);
            return false;
        }

        if (strlen($observaciones) > 500) {
            $this->last_error = 'Las observaciones no pueden tener mas de 500 caracteres.';
            utils::report_error(validation_error, $observaciones, $this->last_error);
            return false;
        }

        if (! isset($PARAMETROS['idcliente_anticipo']) || trim($PARAMETROS['idcliente_anticipo']) == '') {
            $security = new security($this->ACCIONES['crear_cliente_anticipo']);

            $DATOS = [];
            $DATOS['idcliente']         = $PARAMETROS['idcliente'];
            $DATOS['fecha']             = $PARAMETROS['fecha'];
            $DATOS['idtipo_pago']       = $PARAMETROS['idtipo_pago'];
            $DATOS['monto']             = number_format($monto, 2, '.', '');
            $DATOS['saldo_disponible']  = number_format($monto, 2, '.', '');
            $DATOS['referencia_pago']   = $referencia_pago == '' ? 'NULL' : $referencia_pago;
            $DATOS['observaciones']     = $observaciones == '' ? 'NULL' : $observaciones;
            $DATOS['estado']            = 'ACTIVO';
            $DATOS['usuario_creacion']  = $security->get_actual_user();

            if (table::insert_record($DATOS)) {
                $idcliente_anticipo = mysql::last_id();
                $security->registrar_bitacora($this->ACCIONES['crear_cliente_anticipo'], $idcliente_anticipo, $DATOS['idcliente'], $DATOS['monto']);
                return 'nuevo';
            }

            $this->last_error = 'Error al guardar el anticipo.';
            utils::report_error(bd_error, $PARAMETROS, $this->last_error);
            return false;
        }

        $security = new security($this->ACCIONES['modificar_cliente_anticipo']);

        $row_actual = mysql::getrow("SELECT idcliente_anticipo, monto, saldo_disponible, estado FROM cliente_anticipo WHERE idcliente_anticipo = '{$PARAMETROS['idcliente_anticipo']}' LIMIT 1");
        if (! $row_actual) {
            $this->last_error = 'El anticipo indicado no existe.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        if (($row_actual['estado'] . '') != 'ACTIVO') {
            $this->last_error = 'El anticipo esta inactivo y no puede modificarse.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        if ((float)$row_actual['monto'] != (float)$row_actual['saldo_disponible']) {
            $this->last_error = 'El anticipo ya tiene aplicaciones y no puede modificarse.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        $DATOS = [];
        $DATOS['idcliente_anticipo']            = $PARAMETROS['idcliente_anticipo'];
        $DATOS['idcliente']                     = $PARAMETROS['idcliente'];
        $DATOS['fecha']                         = $PARAMETROS['fecha'];
        $DATOS['idtipo_pago']                   = $PARAMETROS['idtipo_pago'];
        $DATOS['monto']                         = number_format($monto, 2, '.', '');
        $DATOS['saldo_disponible']              = number_format($monto, 2, '.', '');
        $DATOS['referencia_pago']               = $referencia_pago == '' ? 'NULL' : $referencia_pago;
        $DATOS['observaciones']                 = $observaciones == '' ? 'NULL' : $observaciones;
        $DATOS['estado']                        = isset($PARAMETROS['estado']) ? $PARAMETROS['estado'] : 'ACTIVO';
        $DATOS['usuario_modificacion']          = $security->get_actual_user();
        $llaves = ['idcliente_anticipo'];

        if (table::update_record($DATOS, $llaves)) {
            $security->registrar_bitacora($this->ACCIONES['modificar_cliente_anticipo'], $DATOS['idcliente_anticipo'], $DATOS['idcliente'], $DATOS['monto']);
            return 'editado';
        }

        $this->last_error = 'Error al modificar el anticipo.';
        utils::report_error(bd_error, $PARAMETROS, $this->last_error);
        return false;
    }

    public function estado($idcliente_anticipo)
    {
        return mysql::getvalue("SELECT estado FROM cliente_anticipo WHERE idcliente_anticipo = '$idcliente_anticipo'");
    }

    public function cambiar_estado($idcliente_anticipo)
    {
        $security = new security($this->ACCIONES['cambiar_estado_cliente_anticipo']);

        $row_anticipo = mysql::getrow("SELECT idcliente_anticipo, estado, saldo_disponible FROM cliente_anticipo WHERE idcliente_anticipo = '$idcliente_anticipo' LIMIT 1");
        if (! $row_anticipo) {
            $this->last_error = 'El anticipo indicado no existe.';
            utils::report_error(validation_error, $idcliente_anticipo, $this->last_error);
            return false;
        }

        if (($row_anticipo['estado'] . '') == 'PROTEGIDO') {
            $this->last_error = 'Registro protegido, no puede modificarse.';
            utils::report_error(validation_error, $idcliente_anticipo, $this->last_error);
            return $this->estado($idcliente_anticipo);
        }

        $DATOS = [];
        $DATOS['idcliente_anticipo'] = $idcliente_anticipo;
        $DATOS['estado'] = (($row_anticipo['estado'] . '') == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $DATOS['usuario_modificacion'] = $security->get_actual_user();

        if (table::update_record($DATOS, ['idcliente_anticipo'])) {
            $security->registrar_bitacora($this->ACCIONES['cambiar_estado_cliente_anticipo'], $idcliente_anticipo, $DATOS['estado'], $row_anticipo['saldo_disponible']);
            return $this->estado($idcliente_anticipo);
        }

        $this->last_error = 'Error al cambiar estado del anticipo.';
        utils::report_error(bd_error, $idcliente_anticipo, $this->last_error);
        return false;
    }

    public function obtener_anticipos_cliente($idcliente)
    {
        $idcliente = trim($idcliente . '');

        return mysql::getoptions("SELECT idcliente_anticipo AS id, CONCAT('Q ', FORMAT(saldo_disponible, 2), ' - ', tipo_pago) AS descripcion 
            FROM view_cliente_anticipo 
            WHERE idcliente = '$idcliente' 
                AND estado = 'ACTIVO' 
                AND saldo_disponible > 0 
            ORDER BY fecha DESC");
    }

    public function obtener_saldo_disponible($idcliente_anticipo)
    {
        $idcliente_anticipo = trim($idcliente_anticipo . '');

        $saldo = mysql::getvalue("SELECT saldo_disponible FROM cliente_anticipo WHERE idcliente_anticipo = '$idcliente_anticipo' LIMIT 1");

        if ($saldo === false) {
            $this->last_error = 'Anticipo no encontrado.';
            return false;
        }

        return (float)$saldo;
    }

    public function aplicar_anticipo($idcliente_anticipo, $monto_aplicar)
    {
        $idcliente_anticipo = trim($idcliente_anticipo . '');
        $monto_aplicar = (float)$monto_aplicar;

        if ($monto_aplicar <= 0) {
            $this->last_error = 'El monto a aplicar debe ser mayor a cero.';
            return false;
        }

        $saldo_actual = $this->obtener_saldo_disponible($idcliente_anticipo);
        if ($saldo_actual === false) {
            return false;
        }

        if ($monto_aplicar > $saldo_actual) {
            $this->last_error = 'El monto a aplicar supera el saldo disponible del anticipo.';
            return false;
        }

        $nuevo_saldo = $saldo_actual - $monto_aplicar;
        
        $DATOS = [];
        $DATOS['idcliente_anticipo'] = $idcliente_anticipo;
        $DATOS['saldo_disponible'] = number_format($nuevo_saldo, 2, '.', '');

        if (table::update_record($DATOS, ['idcliente_anticipo'])) {
            return true;
        }

        $this->last_error = 'Error al aplicar el anticipo.';
        utils::report_error(bd_error, $DATOS, $this->last_error);
        return false;
    }

    public function revertir_anticipo($idcliente_anticipo, $monto_revertir)
    {
        $idcliente_anticipo = trim($idcliente_anticipo . '');
        $monto_revertir = (float)$monto_revertir;

        if ($monto_revertir <= 0) {
            $this->last_error = 'El monto a revertir debe ser mayor a cero.';
            return false;
        }

        $saldo_actual = $this->obtener_saldo_disponible($idcliente_anticipo);
        if ($saldo_actual === false) {
            return false;
        }

        $row_anticipo = mysql::getrow("SELECT monto FROM cliente_anticipo WHERE idcliente_anticipo = '$idcliente_anticipo' LIMIT 1");
        if (!$row_anticipo) {
            $this->last_error = 'Anticipo no encontrado.';
            return false;
        }

        $nuevo_saldo = $saldo_actual + $monto_revertir;
        $monto_original = (float)$row_anticipo['monto'];

        if ($nuevo_saldo > $monto_original) {
            $this->last_error = 'No se puede revertir más de lo que fue aplicado.';
            return false;
        }

        $DATOS = [];
        $DATOS['idcliente_anticipo'] = $idcliente_anticipo;
        $DATOS['saldo_disponible'] = number_format($nuevo_saldo, 2, '.', '');

        if (table::update_record($DATOS, ['idcliente_anticipo'])) {
            return true;
        }

        $this->last_error = 'Error al revertir el anticipo.';
        utils::report_error(bd_error, $DATOS, $this->last_error);
        return false;
    }

}
