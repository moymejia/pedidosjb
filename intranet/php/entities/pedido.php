<?php
require_once('../wisetech/table.php');
require_once('../wisetech/security.php');
require_once('../wisetech/html.php');
require_once('../wisetech/utils.php');
require_once('../entities/cliente.php');
require_once('../entities/marca.php');
require_once('../entities/temporada.php');
require_once('../entities/set_talla.php');
require_once('../entities/temporada.php');
require_once('../entities/transporte.php');

class pedido extends table{

    use utils;

    private $last_error;
    private $ACCIONES = array();

    public function __construct($PARAMETROS = null){

        parent::__construct(prefijo . '_pedidos', 'pedido');

        $this->ACCIONES['crear']            = 27;
        $this->ACCIONES['eliminar']         = 28;
        $this->ACCIONES['cerrar_pedido']    = 29;

        if(isset($PARAMETROS['operacion'])){

            if ($PARAMETROS['operacion'] == 'guardar') {
                if (table::validate_parameter_existence([ 'idcliente', 'idmarca', 'fecha_desde', 'fecha_hasta', 'idtemporada', 'idset_talla','idtransporte'], $PARAMETROS, false)) {

                    if ($resultado = $this->guardar($PARAMETROS)) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }

                } else {
                    self::end_error("Faltan parámetros");
                }
            }

            if ($PARAMETROS['operacion'] == 'eliminar') {
                if (table::validate_parameter_existence(['idpedido'], $PARAMETROS, false)) {
                    if ($options = $this->eliminar($PARAMETROS['idpedido'])) {
                        self::end_success($options);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Parámetros faltantes");
                }
            }

            if ($PARAMETROS['operacion'] == 'cerrar_pedido') {
                if (table::validate_parameter_existence(['idpedido'], $PARAMETROS, false)) {
                    if ($options = $this->cerrar_pedido($PARAMETROS['idpedido'])) {
                        self::end_success($options);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Parámetros faltantes");
                }
            }
        
        }
    }

    public function cargar_opcion()
    {
        $DATA = [];
        $DATA['clientes']       = (new cliente())->option_activas();
        $DATA['marcas']         = (new marca())->option_activas();
        $DATA['temporadas']     = (new temporada())->option_activos();
        $DATA['set_tallas']     = (new set_talla())->options_activos();
        $DATA['transportes']    = (new transporte())->option_activas();

        $result = mysql::getresult("SELECT idpedido, idcliente, idtemporada, idmarca, idset_talla, set_talla, cliente, temporada, marca, set_talla, estado, 
            fecha_desde,fecha_hasta, observaciones_pedido, idtransporte, transporte, monto_descuento, email
            FROM view_pedidos ORDER BY idpedido DESC");

        $tabla = '
        <table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Acciones</th>
                    <th>ID Pedido</th>
                    <th>Set talla</th>
                    <th>Cliente</th>
                    <th>Temporada</th>
                    <th>Marca</th>
                    <th>Fecha desde</th>
                    <th>Fecha hasta</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody id="tabla_todos">';

        while ($row = mysql::getrowresult($result)) {
            $idpedido        = $row['idpedido'];
            $idset_talla     = $row['idset_talla'];
            $set_talla       = $row['set_talla'];
            $cliente         = $row['cliente'];
            $idcliente       = $row['idcliente'];
            $temporada       = $row['temporada'];
            $idtemporada     = $row['idtemporada'];
            $marca           = $row['marca'];
            $idmarca         = $row['idmarca'];
            $transporte      = $row['transporte'];
            $monto_descuento = $row['monto_descuento'];
            $estado          = $row['estado'];
            $fecha_desde     = date('Y-m-d', strtotime($row['fecha_desde']));
            $fecha_hasta     = date('Y-m-d', strtotime($row['fecha_hasta']));
            $observaciones_pedido  = date('Y-m-d', strtotime($row['observaciones_pedido']));

            $str_data = "";
            foreach ($row as $key => $value) {
                $str_data .= $key . "=" . $value . "&";
            }

            $boton_editar = "<button class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"
                    editar_registro('$str_data',this.parentNode.parentNode);
                    showElements('detalles_del_pedido');
                    hideElements('lista_pedidos');
                    disableElements('idcliente,idmarca,fecha_desde,fecha_hasta,idtemporada,idset_talla,observaciones_pedido,btn_limpiar_pedido,btn_guardar_pedido,idtransporte,monto_descuento');
                    cargarTallas();
                    cargarDetallePedido();
                    goTop();\">
                    <span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar
                </button>";

            $tabla .= "
                <tr>
                    <td>$boton_editar</td>
                    <td>$idpedido</td>
                    <td>$set_talla</td>
                    <td>$cliente</td>
                    <td>$temporada</td>
                    <td style='text-align: center;'>$marca</td>
                    <td>$fecha_desde</td>
                    <td>$fecha_hasta</td>
                    <td style='text-align: center;'>$estado</td>
                </tr>";
        }

        $tabla .= "</tbody></table>";
        $DATA['tabla_pedidos'] = $tabla;
        $html = new html('pedido', $DATA);

        return $html->get_html();
    }

    public function guardar($PARAMETROS)
    {
        $security = new security($this->ACCIONES['crear']);
        $usuario  = $security->get_actual_user();

        $DATOS = [];
        $DATOS['idcliente']             = $PARAMETROS['idcliente'];
        $DATOS['idmarca']               = $PARAMETROS['idmarca'];
        $DATOS['fecha_desde']           = $PARAMETROS['fecha_desde'];
        $DATOS['fecha_hasta']           = $PARAMETROS['fecha_hasta'];
        $DATOS['idtemporada']           = $PARAMETROS['idtemporada'];
        $DATOS['email']                 = $PARAMETROS['email'];
        $DATOS['idset_talla']           = $PARAMETROS['idset_talla'];
        $DATOS['observaciones_pedido']  = $PARAMETROS['observaciones_pedido'];
        $DATOS['idtransporte']          = $PARAMETROS['idtransporte'];
        $DATOS['monto_descuento']       = $PARAMETROS['monto_descuento'];
        $DATOS['estado']                = 'BORRADOR';
        $DATOS['fecha_creacion']        = date("Y-m-d H:i:s");
        $DATOS['usuario_creacion']      = $usuario;

        if ($resultado = table::insert_record($DATOS)) {
            $idpedido = mysql::last_id();
            $security->registrar_bitacora($this->ACCIONES['crear'], $resultado, $idpedido);

            return $idpedido;

        } else {
            $this->last_error = "Error al guardar el pedido";
            utils::report_error(bd_error, $DATOS, $this->last_error);

            return false;
        }

    }

    
    public function eliminar($idpedido)
    {
        $security = new security($this->ACCIONES['eliminar']);
        $usuario  = $security->get_actual_user();


        $estado_actual = $this->estado($idpedido);
        if($estado_actual != 'BORRADOR'){
            $this->last_error = 'No se puede eliminar un pedido diferente a estado BORRADOR.';
            $this->report_error(validation_error,$idpedido,$this->last_error);

            return false;
        }

        $DATOS = [];
        $DATOS['idpedido']     = $idpedido;

        if(table::delete_record($DATOS)){
            $security->registrar_bitacora($this->ACCIONES['eliminar'],$idpedido,$usuario);
            
            return true;
        }else{
            $this->last_error = "Error al eliminar el pedido.";
            $this->report_error(bd_error, $idpedido, $this->last_error);

            return false;
        }
    }

    public function cerrar_pedido($idpedido)
    {
        $security = new security($this->ACCIONES['cerrar_pedido']);
        $usuario  = $security->get_actual_user();

        $estado_actual = $this->estado($idpedido);

        if($estado_actual != 'BORRADOR'){
            $this->last_error = 'Solo se pueden cerrar pedidos en estado BORRADOR.';
            $this->report_error(validation_error, $idpedido, $this->last_error);
            return false;
        }

        $result = mysql::getresult("SELECT SUM(cantidad) AS total_pares, SUM(subtotal) AS monto_total FROM pedido_detalle WHERE idpedido = $idpedido");

        if(!$result){
            $this->last_error = "Error al calcular totales del pedido.";
            $this->report_error(bd_error, $idpedido, $this->last_error);
            return false;
        }

        $row = mysql::getrowresult($result);

        $total_pares = $row['total_pares'] ? (int)$row['total_pares'] : 0;
        $monto_total = $row['monto_total'] ? (float)$row['monto_total'] : 0;

        if($total_pares <= 0){
            $this->last_error = "No se puede cerrar un pedido sin productos.";
            $this->report_error(validation_error, $idpedido, $this->last_error);
            return false;
        }

        $monto_subtotal = $monto_total;
        $pedido = mysql::getrow("SELECT monto_descuento FROM pedido WHERE idpedido = $idpedido");
        $monto_descuento = $pedido && $pedido['monto_descuento'] ? (float)$pedido['monto_descuento'] : 0;
        $monto_total = $monto_total - $monto_descuento;

        if($monto_total < 0){
            $monto_total = 0;
        }

        $DATOS = [];
        $DATOS['idpedido']             = $idpedido;
        $DATOS['estado']               = 'CERRADO';
        $DATOS['monto_subtotal']       = $monto_subtotal;
        $DATOS['total_pares']          = $total_pares;
        $DATOS['monto_total']          = $monto_total;
        $DATOS['fecha_modificacion']   = date('Y-m-d H:i:s');
        $DATOS['usuario_modificacion'] = $usuario;

        $llaves = ['idpedido'];

        if(table::update_record($DATOS, $llaves)){
            $security->registrar_bitacora($this->ACCIONES['cerrar_pedido'], $idpedido, "Pedido cerrado | Pares: $total_pares | Total: $monto_total | Descuento: $monto_descuento");
            return true;

        }else{
            $this->last_error = "Error al cerrar el pedido.";
            $this->report_error(bd_error, $DATOS, $this->last_error);
            return false;
        }
    }

    public function estado($idpedido)
    {
        return mysql::getvalue("SELECT estado FROM pedido WHERE idpedido = '$idpedido'");
    }
    
}
?>