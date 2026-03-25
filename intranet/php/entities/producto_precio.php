<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/objects.php';
require_once '../wisetech/utils.php';

class producto_precio extends table
{

    use utils;
    public $last_error;
    private $ACCIONES = [];

    public function __construct($PARAMETROS = null)
    {

        parent::__construct(prefijo . '_pedidos', 'producto_precio');

        $this->ACCIONES['crear']     = 25;
        $this->ACCIONES['eliminar']  = 26;
        $this->ACCIONES['activar']   = 27;
        $this->ACCIONES['modificar'] = 29;

        if (isset($PARAMETROS['operacion'])) {

        }
    }

    public function get_idproducto_precio($idproducto)
    {
        $row = mysql::getrow("SELECT idproducto_precio FROM producto_precio WHERE idproducto = '$idproducto'");

        if(!empty($row['idproducto_precio'])){
            return $row['idproducto_precio'];
        }else{
            return false;
        }
    }

    public function guardar($idproducto_precio,$idproducto,$material,$precio,$estado = '') 
    {   
        if($idproducto_precio == ''){
            $idprod_existente = $this->get_idproducto_precio($idproducto);

            if($idprod_existente){
                $idproducto_precio = $idprod_existente;
            }else{
                $idproducto_precio = '';
            }
        }

        if($idproducto_precio == ''){
            $security = new security($this->ACCIONES['crear']);

            $and = '';

            if($material != '' && !empty($material)){
                $and = "AND material = '$material'";
            }else{
                $and = "AND material is not null";
            }
            if(mysql::exists('producto_precio',"idproducto = '$idproducto' $and")){
                $this->last_error = "Error al guardar el producto precio, ya existe un registro para el material del producto";
                utils::report_error(bd_error, $idproducto."-".$material,$this->last_error);

                return false;
            }

            $DATOS = [];
            $DATOS['idproducto']       = $idproducto;
            if($material != ''){
                $DATOS['material']         = $material;
            }
            $DATOS['precio']           = $precio;
            $DATOS['estado']           = $estado != '' ?  "$estado" :'ACTIVO';
            $DATOS['usuario_creacion'] = $security->get_actual_user();

            if(table::insert_record($DATOS)){
                $security->registrar_bitacora($this->ACCIONES['crear'], "guardar producto precio");

                return true;
            }else{
                $this->last_error = "Error al guardar el producto precio";
                utils::report_error(bd_error, $DATOS,$this->last_error);

                return false;
            }
        }else{
            $security = new security($this->ACCIONES['modificar']);

            $and = '';

            if($material != ''){
                $and = "AND material = '$material'";
            }else{
                $and = "AND material is not null";
            }
            if(mysql::exists('producto_precio',"idproducto = '$idproducto' $and")){
                $this->last_error = "Error al modificar el precio del producto, ya existe un registro para el material del producto";
                utils::report_error(bd_error, $idproducto."-".$material,$this->last_error);

                return false;
            }

            $DATOS = [];
            $DATOS['idproducto_precio']    = $idproducto_precio;
            $DATOS['idproducto']           = $idproducto;
            if($material != ''){
                $DATOS['material']         = $material;
            }
            $DATOS['precio']               = $precio;
            $DATOS['usuario_modificacion'] = $security->get_actual_user();
            $DATOS['fecha_modificacion']   = date("Y-m-d H:i:s");
            $llaves                        = ['idproducto_precio'];

            if(table::update_record($DATOS,$llaves)){
                $security->registrar_bitacora($this->ACCIONES['modificar'], "modificar producto precio");

                return true;
            }else{
                $this->last_error = "Error al modificar el producto precio";
                utils::report_error(bd_error, $DATOS,$this->last_error);

                return false;
            }
        }
    }

    public function eliminar($DATOS)
    {   
        $security = new security($this->ACCIONES['eliminar']);

        if(table::delete_record($DATOS)){
            $security->registrar_bitacora($this->ACCIONES['eliminar'], "Eliminar precios por falla.");

            return true;
        }else{
            $this->last_error = "Error al eliminar los precios.";
            utils::report_error(bd_error, $DATOS,$this->last_error);

            return false;
        }
    }

    public function activar()
    {   
        $security = new security($this->ACCIONES['activar']);

        $usuario = $security->get_actual_user();
        
        $DATOS = [];
        $DATOS['usuario_creacion'] = $usuario;
        $DATOS['estado']           = 'ACTIVO';
        $llaves                    = ['usuario_creacion'];

        if(table::update_record($DATOS,$llaves)){
            $security->registrar_bitacora($this->ACCIONES['activar'], "activar precios por falla.");

            return true;
        }else{
            $this->last_error = "Error al activar los precios.";
            utils::report_error(bd_error, $DATOS,$this->last_error);

            return false;
        }
    }
}
