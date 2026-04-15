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

                if (table::validate_parameter_existence(['idpedido','idproducto','precio','idset_talla','modelo','color','material'],$PARAMETROS,false)) {
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
        $result = mysql::getresult("SELECT idpedido_detalle, idpedido, idproducto, codigo, descripcion, idcolor, color, marca, material, precio_venta, cantidad, subtotal, 
            idtalla, talla, idproducto_precio, imagen, idset_talla, set_talla
        FROM view_pedido_detalle 
        WHERE idpedido = $idpedido
        ORDER BY idset_talla ASC, codigo ASC, color ASC, material ASC, precio_venta ASC, idtalla ASC;");

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
        $es_edicion = isset($PARAMETROS['idpedido_detalle']);
        $security = new security($es_edicion ? $this->ACCIONES['modificar_detalle'] : $this->ACCIONES['crear_detalle']);
        $usuario  = $security->get_actual_user();

        $idpedido           = (int)$PARAMETROS['idpedido'];
        $idproducto         = (int)$PARAMETROS['idproducto'];
        $idset_talla        = (int)$PARAMETROS['idset_talla'];
        $precio             = (float)$PARAMETROS['precio'];
        $codigo_estilo      = trim((string)$PARAMETROS['modelo']);
        $color_texto        = trim((string)$PARAMETROS['color']);
        $material_texto     = trim((string)$PARAMETROS['material']);

        $ruta_bd = null;
        $ids_editar = [];
        $imagen_actual = null;

        if ($es_edicion) {
            $ids_editar = is_array($PARAMETROS['idpedido_detalle']) ? $PARAMETROS['idpedido_detalle'] : [$PARAMETROS['idpedido_detalle']];
            $ids_editar = array_values(array_filter(array_map('intval', $ids_editar)));
        }

        if (empty($codigo_estilo)) {
            $this->last_error = "Debe ingresar un codigo de estilo.";
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        if ($idproducto <= 0) {
            $this->last_error = "Debe cargar un estilo valido.";
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        if (empty($color_texto)) {
            $this->last_error = "Debe ingresar un color.";
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        if (empty($material_texto)) {
            $this->last_error = "Debe ingresar un material.";
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        if ($precio <= 0) {
            $this->last_error = "El precio debe ser mayor a cero.";
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        if (!empty($ids_editar)) {
            $imagen_actual = mysql::getvalue("SELECT imagen FROM pedido_detalle WHERE idpedido_detalle = " . (int)$ids_editar[0] . " LIMIT 1");
        }

        if(
            isset($_FILES['file_uploaded']) && 
            $_FILES['file_uploaded']['tmp_name'] != ''
        ){
            $tipos_permitidos = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
            ];

            $tipo_archivo = isset($_FILES['file_uploaded']['type']) ? $_FILES['file_uploaded']['type'] : '';

            if (!isset($tipos_permitidos[$tipo_archivo])) {
                $this->last_error = "Tipo de archivo no permitido. Debe cargar imagenes en formato JPEG, JPG o PNG";
                utils::report_error(validation_error, $codigo_estilo, $this->last_error);
                return false;
            }

            $extension      = $tipos_permitidos[$tipo_archivo];
            $referencia     = preg_replace('/[^A-Za-z0-9_-]+/', '_', $codigo_estilo);
            $referencia     = $referencia !== '' ? $referencia : 'detalle';
            $ruta           = "../../img/producto/";
            $nombre_temp    = $_FILES['file_uploaded']['tmp_name'];
            $nombre_archivo = uniqid("pedido_" . $idpedido . "_" . $idset_talla . "_" . $referencia . "_") . "." . $extension;

            if (!file_exists($ruta)) {
                mkdir($ruta, 0777, true);
            }

            if (!move_uploaded_file($nombre_temp, $ruta . $nombre_archivo)) {
                $this->last_error = "Error al mover el archivo cargado";
                utils::report_error(validation_error, $codigo_estilo, $this->last_error);
                return false;
            }
            $ruta_bd = "img/producto/" . $nombre_archivo;
            $security->registrar_bitacora($this->ACCIONES['crear_detalle'],$codigo_estilo,"Imagen guardada correctamente");
        }

        $TALLAS = [];
        foreach ($PARAMETROS as $key => $valor) {
            if (strpos($key, 'talla_') !== 0) {
                continue;
            }

            $idtalla = (int) str_replace('talla_', '', $key);
            if ($idtalla <= 0) {
                continue;
            }

            $TALLAS[$idtalla] = (int)$valor;
        }

        if (empty($TALLAS)) {
            $this->last_error = "Debe ingresar al menos una talla.";
            utils::report_error(validation_error, $PARAMETROS, $this->last_error);
            return false;
        }

        if (!empty($ids_editar)) {
            $sql_detalle_actual = mysql::getresult("SELECT idpedido_detalle, idtalla, cantidad_despachada, cantidad_pendiente, imagen
                FROM pedido_detalle
                WHERE idpedido_detalle IN (" . implode(',', $ids_editar) . ")");

            if (!$sql_detalle_actual) {
                $this->last_error = "Error al obtener el detalle actual del pedido.";
                utils::report_error(bd_error, $ids_editar, $this->last_error);
                return false;
            }

            $DETALLE_ACTUAL = [];
            while ($row_detalle_actual = mysql::getrowresult($sql_detalle_actual)) {
                $DETALLE_ACTUAL[(int)$row_detalle_actual['idtalla']] = $row_detalle_actual;
            }

            foreach ($TALLAS as $idtalla => $cantidad) {
                if (isset($DETALLE_ACTUAL[$idtalla])) {
                    $detalle_actual = $DETALLE_ACTUAL[$idtalla];
                    $cantidad_despachada = (int)$detalle_actual['cantidad_despachada'];

                    if ($cantidad < $cantidad_despachada) {
                        $this->last_error = "La cantidad no puede ser menor a la cantidad ya despachada en la talla $idtalla.";
                        utils::report_error(validation_error, ['idtalla' => $idtalla, 'cantidad' => $cantidad, 'cantidad_despachada' => $cantidad_despachada], $this->last_error);
                        return false;
                    }

                    $DATOS = [];
                    $DATOS['idpedido_detalle']      = (int)$detalle_actual['idpedido_detalle'];
                    $DATOS['idproducto_precio']     = 'NULL';
                    $DATOS['idproducto']            = $idproducto;
                    $DATOS['idset_talla']           = $idset_talla;
                    $DATOS['cantidad']              = $cantidad;
                    $DATOS['precio_lista']          = $precio;
                    $DATOS['precio_venta']          = $precio;
                    $DATOS['imagen']                = $ruta_bd ? $ruta_bd : (!empty($imagen_actual) ? $imagen_actual : (!empty($detalle_actual['imagen']) ? $detalle_actual['imagen'] : 'NULL'));
                    $DATOS['color_texto']           = $color_texto;
                    $DATOS['material_texto']        = $material_texto;
                    $DATOS['subtotal']              = $cantidad * $precio;
                    $DATOS['cantidad_pendiente']    = $cantidad - $cantidad_despachada;
                    $DATOS['usuario_modificacion']  = $usuario;

                    if (!table::update_record($DATOS, ['idpedido_detalle'])) {
                        $this->last_error = "Error al actualizar el detalle del pedido.";
                        utils::report_error(bd_error, $DATOS, $this->last_error);
                        return false;
                    }

                    $security->registrar_bitacora($this->ACCIONES['modificar_detalle'], $DATOS['idpedido_detalle']);
                    unset($DETALLE_ACTUAL[$idtalla]);
                    continue;
                }

                $DATOS = [];
                $DATOS['idpedido']             = $idpedido;
                $DATOS['idproducto_precio']    = 'NULL';
                $DATOS['idproducto']           = $idproducto;
                $DATOS['idset_talla']          = $idset_talla;
                $DATOS['idtalla']              = $idtalla;
                $DATOS['cantidad']             = $cantidad;
                $DATOS['precio_lista']         = $precio;
                $DATOS['precio_venta']         = $precio;
                $DATOS['imagen']               = $ruta_bd ? $ruta_bd : (!empty($imagen_actual) ? $imagen_actual : 'NULL');
                $DATOS['color_texto']          = $color_texto;
                $DATOS['material_texto']       = $material_texto;
                $DATOS['subtotal']             = $cantidad * $precio;
                $DATOS['cantidad_despachada']  = 0;
                $DATOS['cantidad_pendiente']   = $cantidad;
                $DATOS['estado']               = 'ACTIVO';
                $DATOS['usuario_creacion']     = $usuario;

                if (table::insert_record($DATOS)) {
                    $id = mysql::last_id();
                    $security->registrar_bitacora($this->ACCIONES['modificar_detalle'], $id);
                } else {
                    $this->last_error = "Error al guardar talla $idtalla.";
                    utils::report_error(bd_error, $DATOS, $this->last_error);
                    return false;
                }
            }

            foreach ($DETALLE_ACTUAL as $idtalla => $detalle_actual) {
                $cantidad_despachada = (int)$detalle_actual['cantidad_despachada'];
                if ($cantidad_despachada > 0) {
                    $this->last_error = "No se puede eliminar la talla $idtalla porque ya tiene despacho registrado.";
                    utils::report_error(validation_error, $detalle_actual, $this->last_error);
                    return false;
                }

                $DATOS = [];
                $DATOS['idpedido_detalle'] = (int)$detalle_actual['idpedido_detalle'];

                if (!table::delete_record($DATOS)) {
                    $this->last_error = "Error al actualizar el detalle del pedido.";
                    utils::report_error(bd_error, $DATOS, $this->last_error);
                    return false;
                }

                $security->registrar_bitacora($this->ACCIONES['modificar_detalle'], $DATOS['idpedido_detalle'], 'Eliminar talla no incluida en edición');
            }

            return true;
        }

        foreach ($TALLAS as $idtalla => $cantidad) {

            $DATOS = [];
            $DATOS['idpedido']             = $idpedido;
            $DATOS['idproducto_precio']    = 'NULL';
            $DATOS['idproducto']           = $idproducto;
            $DATOS['idset_talla']          = $idset_talla;
            $DATOS['idtalla']              = $idtalla;
            $DATOS['cantidad']             = $cantidad;
            $DATOS['precio_lista']         = $precio;
            $DATOS['precio_venta']         = $precio;
            $DATOS['imagen']               = $ruta_bd ? $ruta_bd : (!empty($imagen_actual) ? $imagen_actual : 'NULL');
            $DATOS['color_texto']          = $color_texto;
            $DATOS['material_texto']       = $material_texto;
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
        }

        return true;
    }

    public function eliminar($ids,$idpedido)
    {
        $security = new security($this->ACCIONES['eliminar_detalle']);

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
