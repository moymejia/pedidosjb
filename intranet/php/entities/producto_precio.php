<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/objects.php';
require_once '../wisetech/utils.php';
require_once '../entities/producto.php';

class producto_precio extends table
{

    use utils;
    public $last_error;
    private $ACCIONES = [];

    public function __construct($PARAMETROS = null)
    {

        parent::__construct(prefijo . '_pedidos', 'producto_precio');

        $this->ACCIONES['crear']            = 25;
        $this->ACCIONES['eliminar']         = 26;
        $this->ACCIONES['activar']          = 27;
        $this->ACCIONES['modificar']        = 29;
        $this->ACCIONES['crear_x_mant']     = 52;
        $this->ACCIONES['modificar_x_mant'] = 53;

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'tabla_producto_precio') {
                if (table::validate_parameter_existence(['modelo'], $PARAMETROS, false)) {
                    if ($resultado = $this->tabla_producto_precio($PARAMETROS['modelo'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Parametros faltantes");
                }
            }

            if ($PARAMETROS['operacion'] == 'guardar') {
                if (table::validate_parameter_existence(['idproducto','precio'], $PARAMETROS, false)) {
                    if ($resultado = $this->guardar($PARAMETROS['idproducto_precio'],$PARAMETROS['idproducto'],$PARAMETROS['precio'],$PARAMETROS['estado'],$PARAMETROS['mantenimiento'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Parametros faltantes");
                }
            }
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

    public function guardar($idproducto_precio,$idproducto,$precio,$idset_talla,$estado = '',$mantenimiento = 'NO') 
    {   
        if($mantenimiento !== 'SI'){
            if($idproducto_precio == ''){
                $idprod_existente = $this->get_idproducto_precio($idproducto);
    
                if($idprod_existente){
                    $idproducto_precio = $idprod_existente;
                }else{
                    $idproducto_precio = '';
                }
            }
        }
        
        if($idproducto_precio == ''){
            if($mantenimiento == 'SI'){
                $security = new security($this->ACCIONES['crear']);    
            }else{
                $security = new security($this->ACCIONES['crear_x_mant']);
            }

            if((new producto())->estado($idproducto) == 'INACTIVO'){
                $this->last_error = "Error al guardar el producto precio, el producto se encuentra inactivo.";
                utils::report_error(bd_error, $idproducto,$this->last_error);

                return false;
            }

            $and = '';

            if(mysql::exists('producto_precio',"idproducto = '$idproducto' $and")){
                $this->last_error = "Error al guardar el producto precio, ya existe un registro para el material del producto";
                utils::report_error(bd_error, $idproducto,$this->last_error);

                return false;
            }

            $DATOS = [];
            $DATOS['idproducto']       = $idproducto;
            $DATOS['precio']           = $precio;
            $DATOS['idset_talla']      = $idset_talla;
            $DATOS['estado']           = $estado != '' ?  "$estado" :'ACTIVO';
            $DATOS['usuario_creacion'] = $security->get_actual_user();

            if(table::insert_record($DATOS)){
                if($mantenimiento == 'SI'){
                    $security->registrar_bitacora($this->ACCIONES['crear_x_mant'], "guardar producto precio");
                }else{
                    $security->registrar_bitacora($this->ACCIONES['crear'], "guardar producto precio");
                }

                return true;
            }else{
                $this->last_error = "Error al guardar el producto precio";
                utils::report_error(bd_error, $DATOS,$this->last_error);

                return false;
            }
        }else{
            if($mantenimiento == 'SI'){
                $security = new security($this->ACCIONES['modificar_x_mant']);    
            }else{
                $security = new security($this->ACCIONES['modificar']);
            }
            

            if((new producto())->estado($idproducto) == 'INACTIVO'){
                $this->last_error = "Error al modificar el producto precio, el producto se encuentra inactivo.";
                utils::report_error(bd_error, $idproducto,$this->last_error);

                return false;
            }

            $and = '';

            $DATOS = [];
            $DATOS['idproducto_precio']    = $idproducto_precio;
            $DATOS['idproducto']           = $idproducto;
            $DATOS['precio']               = $precio;
            $DATOS['idset_talla']          = $idset_talla;
            $DATOS['usuario_modificacion'] = $security->get_actual_user();
            $DATOS['fecha_modificacion']   = date("Y-m-d H:i:s");
            $llaves                        = ['idproducto_precio'];

            if(table::update_record($DATOS,$llaves)){
                if($mantenimiento == 'SI'){
                    $security->registrar_bitacora($this->ACCIONES['modificar_x_mant'], "modificar producto precio");
                }else{
                    $security->registrar_bitacora($this->ACCIONES['modificar'], "modificar producto precio");
                }

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

    public function tabla_producto_precio($modelo)
    {   
        $modelo = addslashes($modelo);

        $sql = mysql::getresult("
            SELECT 
                idproducto_precio,
                idproducto,
                modelo,
                material,
                precio,
                estado
            FROM view_producto_precio
            WHERE modelo = '$modelo'
        ");

        $columnControl = true;
        $responsive    = true;
        $colReorder    = true;
        $select        = true;
        $buttons       = true;
        $paging        = true;
        $ordering      = true;
        $order         = true;
        $rowGroup      = false;

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

        $tabla = '<table id="tabla_datos" '.$data_.' class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Modelo</th>
                            <th>Material</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>';
                    
        while($row = mysql::getrowresult($sql)){
            $idproducto        = $row['idproducto'];
            $idproducto_precio = $row['idproducto_precio'];
            $modelo            = $row['modelo'];
            $material          = $row['material'];
            $precio            = $row['precio'];
            $estado            = $row['estado'];

            $btn = "<button class='btn btn-info btn-sm' onclick='
                    element(\"idproducto\").value = \"$idproducto\";
                    element(\"idproducto_precio\").value = \"$idproducto_precio\";
                    element(\"material\").value = \"$material\";
                    element(\"precio\").value = \"$precio\";
                    showElements(\"div_form_precio_producto\");
                    hideElements(\"div_form_busqueda,div_tabla_precios,div_botones_precio\");
                '>Editar</button>";

            $tabla .= "<tr>
                        <td>$idproducto_precio</td>
                        <td>$modelo</td>
                        <td>$material</td>
                        <td>$precio</td>
                        <td>$estado</td>
                        <td>$btn</td>
                    </tr>";
        }

        $tabla .= '</tbody></table>';

        return $tabla;
    }
}
