<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/utils.php';

class cliente_anticipo_aplicacion extends table
{
    use utils;

    private $ACCIONES = [];
    public $last_error = '';

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'cliente_anticipo_aplicacion');

        $this->ACCIONES['crear_aplicacion']      = 'Crear_cliente_anticipo_aplicacion';
        $this->ACCIONES['actualizar_aplicacion'] = 'Actualizar_cliente_anticipo_aplicacion';
        $this->ACCIONES['cancelar_aplicacion']   = 'Cancelar_cliente_anticipo_aplicacion';
        $this->ACCIONES['consultar_aplicacion']  = 'Consultar_cliente_anticipo_aplicacion';
    }

    public function crear_aplicacion($idcliente_anticipo, $iddespacho, $fecha, $monto_aplicado, $observaciones = null)
    {
        $idcliente_anticipo = trim($idcliente_anticipo . '');
        $iddespacho = trim($iddespacho . '');
        $fecha = trim($fecha . '');
        $monto_aplicado = (float)$monto_aplicado;
        $PARAMETROS = [
            'idcliente_anticipo' => $idcliente_anticipo,
            'iddespacho' => $iddespacho,
            'fecha' => $fecha,
            'monto_aplicado' => $monto_aplicado
        ];

        if (empty($idcliente_anticipo) || empty($iddespacho) || empty($fecha)) {
            $this->last_error = 'Datos incompletos para crear aplicación de anticipo.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        if ($monto_aplicado <= 0) {
            $this->last_error = 'El monto a aplicar debe ser mayor a cero.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        $security = new security($this->ACCIONES['crear_aplicacion']);
        
        $DATOS = [];
        $DATOS['idcliente_anticipo'] = $idcliente_anticipo;
        $DATOS['iddespacho']         = $iddespacho;
        $DATOS['fecha']              = $fecha;
        $DATOS['monto_aplicado']     = number_format($monto_aplicado, 2, '.', '');
        $DATOS['observaciones']      = empty($observaciones) ? 'NULL' : $observaciones;
        $DATOS['estado']             = 'ACTIVO';
        $DATOS['usuario_creacion']   = $security->get_actual_user();

        if (table::insert_record($DATOS)) {
            $idcliente_anticipo_aplicacion = mysql::last_id();
            $security->registrar_bitacora($this->ACCIONES['crear_aplicacion'], $idcliente_anticipo_aplicacion, $idcliente_anticipo, $monto_aplicado);
            return $idcliente_anticipo_aplicacion;
        }

        $this->last_error = 'Error al crear aplicación de anticipo.';
        utils::report_error(bd_error, $DATOS, $this->last_error);
        return false;
    }

    public function actualizar_aplicacion($idcliente_anticipo_aplicacion, $idcliente_anticipo, $monto_aplicado, $observaciones = null)
    {
        $idcliente_anticipo_aplicacion = trim($idcliente_anticipo_aplicacion . '');
        $idcliente_anticipo = trim($idcliente_anticipo . '');
        $monto_aplicado = (float)$monto_aplicado;
        $PARAMETROS = [
            'idcliente_anticipo_aplicacion' => $idcliente_anticipo_aplicacion,
            'idcliente_anticipo' => $idcliente_anticipo,
            'monto_aplicado' => $monto_aplicado
        ];

        if (empty($idcliente_anticipo_aplicacion)) {
            $this->last_error = 'ID de aplicación no válido.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        if ($monto_aplicado <= 0) {
            $this->last_error = 'El monto a aplicar debe ser mayor a cero.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        $security = new security($this->ACCIONES['actualizar_aplicacion']);

        $DATOS = [];
        $DATOS['idcliente_anticipo_aplicacion'] = $idcliente_anticipo_aplicacion;
        $DATOS['idcliente_anticipo']            = $idcliente_anticipo;
        $DATOS['monto_aplicado']                = number_format($monto_aplicado, 2, '.', '');
        $DATOS['observaciones']                 = empty($observaciones) ? 'NULL' : $observaciones;
        $DATOS['usuario_modificacion']          = $security->get_actual_user();
        $DATOS['fecha_modificacion']            = date('Y-m-d H:i:s');

        if (table::update_record($DATOS, ['idcliente_anticipo_aplicacion'])) {
            $security->registrar_bitacora($this->ACCIONES['actualizar_aplicacion'], $idcliente_anticipo_aplicacion, $idcliente_anticipo, $monto_aplicado);
            return true;
        }

        $this->last_error = 'Error al actualizar aplicación de anticipo.';
        utils::report_error(bd_error, $DATOS, $this->last_error);
        return false;
    }

    public function cancelar_aplicacion($idcliente_anticipo_aplicacion)
    {
        $idcliente_anticipo_aplicacion = trim($idcliente_anticipo_aplicacion . '');
        $PARAMETROS = ['idcliente_anticipo_aplicacion' => $idcliente_anticipo_aplicacion];

        if (empty($idcliente_anticipo_aplicacion)) {
            $this->last_error = 'ID de aplicación no válido.';
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        $security = new security($this->ACCIONES['cancelar_aplicacion']);

        $DATOS = [];
        $DATOS['idcliente_anticipo_aplicacion'] = $idcliente_anticipo_aplicacion;
        $DATOS['estado']                        = 'CANCELADO';
        $DATOS['usuario_modificacion']          = $security->get_actual_user();
        $DATOS['fecha_modificacion']            = date('Y-m-d H:i:s');

        if (table::update_record($DATOS, ['idcliente_anticipo_aplicacion'])) {
            $security->registrar_bitacora($this->ACCIONES['cancelar_aplicacion'], $idcliente_anticipo_aplicacion, 'CANCELADO', '');
            return true;
        }

        $this->last_error = 'Error al cancelar aplicación de anticipo.';
        utils::report_error(bd_error, $DATOS, $this->last_error);
        return false;
    }

    public function obtener_aplicacion_por_despacho($iddespacho, $idcliente_anticipo = null)
    {
        $iddespacho = trim($iddespacho . '');
        $idcliente_anticipo = trim($idcliente_anticipo . '');

        if (empty($iddespacho)) {
            return false;
        }

        $filtro_anticipo = '';
        if ($idcliente_anticipo !== '') {
            $filtro_anticipo = " AND idcliente_anticipo = '$idcliente_anticipo' ";
        }

        $row = mysql::getrow("SELECT idcliente_anticipo_aplicacion, idcliente_anticipo, iddespacho, monto_aplicado, estado 
            FROM cliente_anticipo_aplicacion 
            WHERE iddespacho = '$iddespacho' 
              AND estado = 'ACTIVO'
              $filtro_anticipo
            ORDER BY idcliente_anticipo_aplicacion DESC
            LIMIT 1");

        return $row ? $row : false;
    }

    public function obtener_aplicaciones_por_anticipo($idcliente_anticipo)
    {
        $idcliente_anticipo = trim($idcliente_anticipo . '');

        $result = mysql::getresult("SELECT idcliente_anticipo_aplicacion, iddespacho, monto_aplicado, fecha, estado 
            FROM cliente_anticipo_aplicacion 
            WHERE idcliente_anticipo = '$idcliente_anticipo' 
            ORDER BY fecha DESC, idcliente_anticipo_aplicacion DESC");

        return $result ? $result : false;
    }

}
