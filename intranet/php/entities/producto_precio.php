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

        $this->ACCIONES['crear']            = "Crear_color_producto_precio_carga_productos";
        $this->ACCIONES['eliminar']         = "Eliminar_productos_borrador_carga_productos";
        $this->ACCIONES['activar']          = "Activar_productos_carga_productos";
        $this->ACCIONES['modificar']        = "Modificar_producto_carga_productos";
        $this->ACCIONES['crear_x_mant']     = "Crear_producto_precio_mantenimiento";
        $this->ACCIONES['modificar_x_mant'] = "Modificar_producto_precio_mantenimiento";
        $this->ACCIONES['cambiar_estado']   = "Cambiar_estado_producto_precio";

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'tabla_producto_precio') {
                if (table::validate_parameter_existence(['modelo','idmarca','idtemporada'], $PARAMETROS, false)) {
                    if ($resultado = $this->tabla_producto_precio($PARAMETROS['modelo'], $PARAMETROS['idmarca'], $PARAMETROS['idtemporada'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Faltan datos requeridos.");
                }
            }

            if ($PARAMETROS['operacion'] == 'guardar') {
                if (table::validate_parameter_existence(['idproducto','precio','idset_talla'], $PARAMETROS, false)) {
                    if ($resultado = $this->guardar($PARAMETROS['idproducto_precio'],$PARAMETROS['idproducto'],$PARAMETROS['precio'],$PARAMETROS['idset_talla'],$PARAMETROS['estado'],$PARAMETROS['mantenimiento'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Parametros faltantes");
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['idproducto_precio'], $PARAMETROS, false)) {
                    if ($resultado = $this->cambiar_estado($PARAMETROS['idproducto_precio'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Faltan datos requeridos.");
                }
            }

            if ($PARAMETROS['operacion'] == 'obtener_precio_set') {
                if (table::validate_parameter_existence(['idproducto','idset_talla'], $PARAMETROS, false)) {
                    if ($resultado = $this->obtener_precio_set($PARAMETROS['idproducto'], $PARAMETROS['idset_talla'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Faltan datos requeridos.");
                }
            }
        }
    }

    public function get_idproducto_precio($idproducto, $idset_talla)
    {
        return mysql::getvalue("SELECT idproducto_precio FROM producto_precio WHERE idproducto = '$idproducto' AND idset_talla = '$idset_talla'");
    }

    public function guardar($idproducto_precio,$idproducto,$precio,$idset_talla,$estado = '',$mantenimiento = 'NO') 
    {   
        if($mantenimiento !== 'SI'){
            if($idproducto_precio == ''){
                $idprod_existente = $this->get_idproducto_precio($idproducto, $idset_talla);
    
                if($idprod_existente){
                    $idproducto_precio = $idprod_existente;
                }else{
                    $idproducto_precio = '';
                }
            }
        }

        if ($precio <= 0) {
            $this->last_error = "Precio inválido. Debe ser mayor a 0.";
            utils::report_error(validation_error, $precio, $this->last_error);

            return false;
        }
        
        if($idproducto_precio == ''){
            if($mantenimiento == 'SI'){
                $security = new security($this->ACCIONES['crear']);    
            }else{
                $security = new security($this->ACCIONES['crear_x_mant']);
            }

            if((new producto())->estado($idproducto) == 'INACTIVO'){
                $this->last_error = "El producto está inactivo.";
                utils::report_error(bd_error, $idproducto,$this->last_error);

                return false;
            }

            $condicion_duplicado = "idproducto = '$idproducto' AND idset_talla = '$idset_talla'";

            if ($idproducto_precio != '') {
                $condicion_duplicado .= " AND idproducto_precio <> '$idproducto_precio'";
            }

            if(mysql::exists('producto_precio', $condicion_duplicado)){
                $this->last_error = "El precio ya existe para este producto.";
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
                $this->last_error = "No se pudo guardar el precio.";
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
                $this->last_error = "El producto está inactivo.";
                utils::report_error(bd_error, $idproducto,$this->last_error);

                return false;
            }

            $condicion_duplicado = "idproducto = '$idproducto' AND idset_talla = '$idset_talla' AND idproducto_precio <> '$idproducto_precio'";

            if(mysql::exists('producto_precio', $condicion_duplicado)){
                $this->last_error = "El precio ya existe para este producto.";
                utils::report_error(bd_error, $idproducto,$this->last_error);

                return false;
            }

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
                $this->last_error = "No se pudo actualizar el precio.";
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
            $this->last_error = "No se pudieron eliminar los precios.";
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
            $this->last_error = "No se pudieron activar los precios.";
            utils::report_error(bd_error, $DATOS,$this->last_error);

            return false;
        }
    }

    public function tabla_producto_precio($modelo, $idmarca, $idtemporada)
    {   
        $modelo = addslashes($modelo);

        $sql = mysql::getresult("SELECT idproducto_precio, idproducto, modelo, idset_talla, set_talla, precio, estado
            FROM view_producto_precio
            WHERE modelo = '$modelo'
            AND idmarca = '$idmarca'
            AND idtemporada = '$idtemporada'
        ");

        $columnControl = true;
        $responsive    = true;
        $colReorder    = true;
        $select        = false;
        $buttons       = true;
        $paging        = true;
        $ordering      = true;
        $order         = true;
        $rowGroup      = false;
        $reset         = true;

        $data_ = "";
        $data_  = " data-conf-columncontrol='" . ($columnControl ? "true" : "false") . "' ";
        $data_ .= " data-conf-rowgroup=''";
        $data_ .= " data-conf-titulotabla='Listado de modelo: $modelo' ";
        $data_ .= " data-conf-filename='Modelo' ";
        $data_ .= " data-conf-responsive='"    . ($responsive    ? "true" : "false") . "' ";
        $data_ .= " data-conf-colreorder='"    . ($colReorder    ? "true" : "false") . "' ";
        $data_ .= " data-conf-select='"        . ($select        ? "true" : "false") . "' ";
        $data_ .= " data-conf-buttons='"       . ($buttons       ? "true" : "false") . "' ";
        $data_ .= " data-conf-paging='"        . ($paging        ? "true" : "false") . "' ";
        $data_ .= " data-conf-ordering='"      . ($ordering      ? "true" : "false") . "' ";
        $data_ .= " data-conf-noorder='"       . (!$order        ? "true" : "false") . "' ";
        $data_ .= " data-conf-reset='"         . ($reset         ? "true" : "false") . "' ";

        $tabla = '<table id="tabla_datos" '.$data_.' class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Modelo</th>
                            <th>Set talla</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>';
                    
        while($row = mysql::getrowresult($sql)){
            $idproducto        = $row['idproducto'];
            $idproducto_precio = $row['idproducto_precio'];
            $idset_talla       = $row['idset_talla'];
            $set_talla       = $row['set_talla'];
            $precio            = $row['precio'];
            $estado            = $row['estado'];

            $btn = "<button class='btn btn-info btn-sm' onclick='
                    element(\"idproducto\").value = \"$idproducto\";
                    element(\"idproducto_precio\").value = \"$idproducto_precio\";
                    element(\"idset_talla\").value = \"$idset_talla\";
                    element(\"precio\").value = \"$precio\";
                    element(\"estado_precio\").value = \"$estado\";
                    showElements(\"div_form_precio_producto,btn_cambiar_estado_precio\");
                    showElements(\"div_form_busqueda,div_tabla_precios\");
                '>Editar</button>";

            $tabla .= "<tr>
                        <td>$idproducto_precio</td>
                        <td>$modelo</td>
                        <td>$set_talla</td>
                        <td>$precio</td>
                        <td>$estado</td>
                        <td>$btn</td>
                    </tr>";
        }

        $tabla .= '</tbody></table>';

        return $tabla;
    }

    public function obtener_precio_set($idproducto, $idset_talla)
    {

        $row = mysql::getrow("SELECT idproducto_precio, precio
            FROM producto_precio
            WHERE idproducto = '$idproducto'
            AND idset_talla = '$idset_talla'
            AND estado = 'ACTIVO'
            LIMIT 1");

        if (!$row) {
            $this->last_error = "No se encontro precio para el producto y set de tallas seleccionados.";
            utils::report_error(validation_error, ['idproducto' => $idproducto, 'idset_talla' => $idset_talla], $this->last_error);
            return false;
        }

        return json_encode([
            'idproducto_precio' => (int)$row['idproducto_precio'],
            'precio' => (float)$row['precio']
        ]);
    }

    public function estado($idproducto_precio)
    {
        return mysql::getvalue("SELECT estado FROM producto_precio WHERE idproducto_precio = '$idproducto_precio'");
    }

    public function cambiar_estado($idproducto_precio)
    {
        $security = new security($this->ACCIONES['cambiar_estado']);
        $estado_actual = $this->estado($idproducto_precio);

        $DATOS = [];
        $DATOS['idproducto_precio']           = $idproducto_precio;
        $DATOS['estado']               = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $DATOS['usuario_modificacion'] = $security->get_actual_user();
        $DATOS['fecha_modificacion']   = date("Y-m-d H:i:s");
        $llaves                        = ['idproducto_precio'];

        if (table::update_record($DATOS, $llaves)) {
            $security->registrar_bitacora($this->ACCIONES['cambiar_estado'], $idproducto_precio, $DATOS['estado']);

            return $this->estado($idproducto_precio);
        } else {
            $this->last_error = "No se pudo cambiar el estado.";
            utils::report_error(validation_error, $idproducto_precio, $this->last_error);

            return false;
        }
    }
}
