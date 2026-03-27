<?php
require_once('../wisetech/table.php');
require_once('../wisetech/security.php');
require_once('../wisetech/html.php');
require_once('../wisetech/utils.php');
require_once('../entities/pedido.php');


class pedido_detalle extends table{

    use utils;

    private $last_error;
    private $ACCIONES = array();

    public function __construct($PARAMETROS = null){

        parent::__construct(prefijo . '_pedidos', 'pedido_detalle');

        $this->ACCIONES['crear_detalle']        = 30;
        $this->ACCIONES['eliminar_detalle']     = 31;
        $this->ACCIONES['modificar_detalle']    = 32;

        if(isset($PARAMETROS['operacion'])){

            if ($PARAMETROS['operacion'] == 'guardar') {

                if (table::validate_parameter_existence(['idpedido','idproducto','precio','idproducto_precio'],$PARAMETROS,false)) {
                    if ($resultado = $this->guardar($PARAMETROS)) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Faltan parámetros");
                }
            }

            if ($PARAMETROS['operacion'] == 'obtener_por_pedido') {

                if (table::validate_parameter_existence(['idpedido'],$PARAMETROS,false)) {      
                    if ($resultado = $this->obtener_por_pedido($PARAMETROS['idpedido'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
            
                } else {
                    self::end_error("Faltan parámetros");
                }
            }

            if ($PARAMETROS['operacion'] == 'eliminar') {
                if (table::validate_parameter_existence(['idpedido_detalle','idpedido'],$PARAMETROS,false)) {      
                    if ($resultado = $this->eliminar($PARAMETROS['idpedido_detalle'],$PARAMETROS['idpedido'])) {
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

    public function obtener_por_pedido($idpedido)
    {
        $result = mysql::getresult("SELECT idpedido_detalle, idpedido, idproducto, codigo, descripcion, idcolor, color, marca, material, precio_venta, cantidad, subtotal, idtalla, 
            talla, idproducto_precio, imagen 
            FROM view_pedido_detalle WHERE idpedido = $idpedido");

        if (!$result) {
            $this->last_error = "Error al obtener el detalle del pedido.";
            utils::report_error(bd_error, $idpedido, $this->last_error);
            return false;
        }

        $data = [];

        while ($row = mysql::getrowresult($result)) {
            $data[] = $row;
        }

        return json_encode($data);
    }

    public function guardar($PARAMETROS)
    {
        $security = new security($this->ACCIONES['crear_detalle']);
        $usuario  = $security->get_actual_user();

        $idpedido           = (int)$PARAMETROS['idpedido'];
        $idproducto         = (int)$PARAMETROS['idproducto'];
        $precio             = (float)$PARAMETROS['precio'];
        $idproducto_precio  = (int)$PARAMETROS['idproducto_precio'];

        $estado_actual = (new pedido())->estado($idpedido);

        if($estado_actual != 'BORRADOR'){
            $this->last_error = 'No se puede editar un pedido en estado "CERRADO"';
            $this->report_error(validation_error, $idpedido, $this->last_error);
            return false;
        }

        $idcolor = isset($PARAMETROS['idcolor']) ? (int)$PARAMETROS['idcolor'] : 0;

        $ruta_bd = null;

        if(
            isset($_FILES['file_uploaded']) && 
            $_FILES['file_uploaded']['tmp_name'] != '' &&
            $idcolor > 0
        ){
            if ($_FILES['file_uploaded']['type'] != 'image/jpeg') {
                $this->last_error = "Tipo de archivo no permitido. Debe cargar imagenes en formato JPG";
                utils::report_error(validation_error, $idproducto, $this->last_error);
                return false;
            }

            $ruta           = "../../img/producto/";
            $nombre_temp    = $_FILES['file_uploaded']['tmp_name'];
            $nombre_archivo = $idproducto . "_" . $idproducto_precio . "_" . $idcolor . ".jpg";

            if (!file_exists($ruta)) {
                mkdir($ruta, 0777, true);
            }

            if (!move_uploaded_file($nombre_temp, $ruta . $nombre_archivo)) {
                $this->last_error = "Error al mover el archivo cargado";
                utils::report_error(validation_error, $idproducto, $this->last_error);
                return false;
            }
            $ruta_bd = "img/producto/" . $nombre_archivo;
            $security->registrar_bitacora($this->ACCIONES['crear_detalle'],$idproducto,"Imagen guardada correctamente");
        }

        foreach ($PARAMETROS as $key => $valor) {

            if (strpos($key, 'talla_') !== 0) continue;
            $idtalla  = (int) str_replace('talla_', '', $key);
            $cantidad = (int)$valor;

            $existe = mysql::exists(
                'pedido_detalle',
                "idpedido = $idpedido AND idproducto_precio = $idproducto_precio AND idtalla = $idtalla"
            );

            if (!$existe) {

                $security = new security($this->ACCIONES['crear_detalle']);

                $DATOS = [];
                $DATOS['idpedido']             = $idpedido;
                $DATOS['idproducto_precio']    = $idproducto_precio;
                $DATOS['idproducto']           = $idproducto;
                $DATOS['idtalla']              = $idtalla;
                $DATOS['cantidad']             = $cantidad;
                $DATOS['precio_lista']         = $precio;
                $DATOS['precio_venta']         = $precio;
                $DATOS['imagen']               = $ruta_bd ? $ruta_bd : 'NULL';
                $DATOS['subtotal']             = $cantidad * $precio;
                $DATOS['cantidad_despachada']  = 0;
                $DATOS['cantidad_pendiente']   = $cantidad;
                $DATOS['estado']               = 'ACTIVO';
                $DATOS['usuario_creacion']     = $usuario;

                if (table::insert_record($DATOS)) {
                    $id = mysql::last_id();
                    $security->registrar_bitacora($this->ACCIONES['crear_detalle'], $id);
                } else {
                    $this->last_error = "Error al guardar talla $idtalla.";
                    utils::report_error(bd_error, $DATOS, $this->last_error);
                    return false;
                }

            } else {

                $security = new security($this->ACCIONES['modificar_detalle']);

                $DATOS = [];
                $DATOS['idpedido']              = $idpedido;
                $DATOS['idproducto']            = $idproducto;
                $DATOS['idproducto_precio']     = $idproducto_precio;
                $DATOS['idtalla']               = $idtalla;
                $DATOS['cantidad']              = $cantidad;
                $DATOS['precio_lista']          = $precio;
                $DATOS['precio_venta']          = $precio;
                $DATOS['subtotal']              = $cantidad * $precio;
                $DATOS['cantidad_pendiente']    = $cantidad;
                $DATOS['fecha_modificacion']    = date('Y-m-d H:i:s');
                $DATOS['usuario_modificacion']  = $usuario;

                if($ruta_bd){
                    $DATOS['imagen'] = $ruta_bd;
                }

                $llaves = ['idpedido', 'idproducto_precio', 'idtalla'];

                if (table::update_record($DATOS, $llaves)) {
                    $security->registrar_bitacora($this->ACCIONES['modificar_detalle'],$idpedido . '-' . $idproducto . '-' . $idtalla);

                } else {
                    $this->last_error = "Error al actualizar talla $idtalla.";
                    utils::report_error(bd_error, $DATOS, $this->last_error);
                    return false;
                }
            }
        }

        return true;
    }

    public function eliminar($ids,$idpedido)
    {
        $security = new security($this->ACCIONES['eliminar_detalle']);

        $estado_actual = (new pedido())->estado($idpedido);

        if($estado_actual != 'BORRADOR'){
            $this->last_error = 'No se puede editar un pedido en estado "CERRADO"';
            $this->report_error(validation_error, $idpedido, $this->last_error);
            return false;
        }

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        foreach ($ids as $idpedido_detalle) {

            $DATOS = [];
            $DATOS['idpedido_detalle'] = $idpedido_detalle;

            if (!table::delete_record($DATOS)) {
                $this->last_error = "Error al eliminar el detalle del pedido.";
                utils::report_error(bd_error, $DATOS, $this->last_error);
                return false;
            }

            $security->registrar_bitacora($this->ACCIONES['eliminar_detalle'], $idpedido_detalle);
        }

        return "eliminado";
    }
    
}
?>