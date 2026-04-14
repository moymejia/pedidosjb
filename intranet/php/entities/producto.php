<?php
require_once('../wisetech/table.php');
require_once('../wisetech/security.php');
require_once('../wisetech/html.php');
require_once('../wisetech/utils.php');
require_once('../entities/marca.php');
require_once('../entities/temporada.php');
require_once('../entities/producto_precio.php');
require_once('../entities/tipo_suela.php');
require_once('../entities/corte.php');
require_once('../entities/color.php');
require_once('../entities/concepto.php');
require_once('../entities/set_talla.php');


class producto extends table{

    use utils;

    private $last_error;
    private $ACCIONES = array();

    public function __construct($PARAMETROS = null){

        parent::__construct(prefijo . '_pedidos', 'producto');

        $this->ACCIONES['cargar_productos']    = 23;
        $this->ACCIONES['crear']               = 24;
        $this->ACCIONES['eliminar']            = 26;
        $this->ACCIONES['activar']             = 27;
        $this->ACCIONES['modificar']           = 28;
        $this->ACCIONES['crear_x_mante']       = 49;
        $this->ACCIONES['modificar_x_mante']   = 50;
        $this->ACCIONES['cambiar_est_x_mante'] = 51;
        $this->ACCIONES['cambiar_estado']      = $this->ACCIONES['cambiar_est_x_mante'];

        if(isset($PARAMETROS['operacion'])){

            if ($PARAMETROS['operacion'] == 'obtener_modelo') {
                if (table::validate_parameter_existence(['modelo','idmarca','idtemporada'], $PARAMETROS, false)) {
                    if($resultado = self::obtener_modelo($PARAMETROS['modelo'],$PARAMETROS['idmarca'],$PARAMETROS['idtemporada'])){
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Faltan datos requeridos.");
                }
            }

            if ($PARAMETROS['operacion'] == 'cargar_productos') {
                if (table::validate_parameter_existence(['idtemporada'], $PARAMETROS, false)) {
                    if ($resultado = $this->cargar_productos($PARAMETROS['idtemporada'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Faltan datos requeridos.");
                }
            }

            if ($PARAMETROS['operacion'] == 'guardar') {
                if (table::validate_parameter_existence(['idmarca','modelo'], $PARAMETROS, false)) {
                    if ($resultado = $this->guardar($PARAMETROS['idproducto'],$PARAMETROS['idmarca'],$PARAMETROS['idtemporada'],$PARAMETROS['linea'],$PARAMETROS['modelo'],$PARAMETROS['idcolor'],$PARAMETROS['idcorte'],$PARAMETROS['idtipo_suela'],$PARAMETROS['idconcepto'],$PARAMETROS['estado'],$PARAMETROS['mantenimiento'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Faltan datos requeridos.");
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['idproducto'], $PARAMETROS, false)) {
                    if ($resultado = $this->cambiar_estado($PARAMETROS['idproducto'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Faltan datos requeridos.");
                }
            }

            if ($PARAMETROS['operacion'] == 'tabla') {
                if ($resultado = $this->tabla()) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }
        }
    }

    public function obtener_modelo($modelo, $idmarca, $idtemporada){

        $row = mysql::getrow("SELECT idproducto, modelo, linea, idmarca, marca
            FROM view_producto_modelo WHERE modelo = '$modelo' AND idmarca = '$idmarca' AND idtemporada = '$idtemporada' LIMIT 1");

        if(!$row){
            $this->last_error = "No se encontro el modelo.";
            utils::report_error(validation_error, $modelo, $this->last_error);
            return false;
        }

        return json_encode([
            'idproducto' => (int)$row['idproducto'],
            'codigo'     => $row['modelo'],
            'descripcion'=> $row['linea'],
            'marca'      => $row['marca']
        ]);
    }

    public function cargar_opcion()
    {
        $DATA = [];
        $DATA['temporadas'] = (new temporada())->option_activos();
        $html = new html('carga_productos', $DATA);

        return $html->get_html();
    }

    public function cargar_opcion_producto()
    {
        $DATA = [];
        $DATA['marcas']          = (new marca())->option_activos();
        $DATA['temporadas']      = (new temporada())->option_activos();
        $DATA['set_tallas']      = (new set_talla())->options_activos();
        $DATA['colores']         = (new color())->option_activos();
        $DATA['cortes']          = (new corte())->option_activos();
        $DATA['tipo_suelas']     = (new tipo_suela())->option_activos();
        $DATA['conceptos']       = (new concepto())->option_activos();
        $DATA['tabla_productos'] = $this->tabla();
        $DATA['modelos']         = $this->modelos();

        $html = new html('producto', $DATA);
        return $html->get_html();
    }

    public function tabla()
    {   
        $sql = mysql::getresult("SELECT idproducto, modelo, linea, idmarca, marca, idtemporada, temporada, idcolor, color, idcorte,
                corte, idtipo_suela, tipo_suela, idconcepto, concepto, estado, precios
            FROM view_producto
        ");

        $columnControl = true;
        $responsive    = false;
        $colReorder    = true;
        $select        = false;
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
                            <th>Acciones</th>
                            <th>Id</th>
                            <th>Modelo</th>
                            <th>Línea</th>
                            <th>Marca</th>
                            <th>Temporada</th>
                            <th>Color</th>
                            <th>Corte</th>
                            <th>Tipo Suela</th>
                            <th>Concepto</th>
                            <th>Precios</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tabla_todos">';
                    
        while($row = mysql::getrowresult($sql)){

            $idproducto  = $row['idproducto'];
            $modelo      = $row['modelo'];
            $linea       = $row['linea'];
            $marca       = $row['marca'];
            $temporada   = $row['temporada'];
            $color       = $row['color'];
            $corte       = $row['corte'];
            $tipo_suela  = $row['tipo_suela'];
            $concepto    = $row['concepto'];
            $precios     = $row['precios'];
            $estado      = $row['estado'];

            $str_data = "";
            foreach ($row as $key => $value) {
                $str_data .= $key . "=" . $value . "&";
            }

            $btn = "<button class='btn btn-info btn-sm' onclick='
                    editar_registro(\"$str_data\",this.parentNode.parentNode);
                    showElements(\"div_form_producto\");
                    hideElements(\"boton_nuevo\");
                    element(\"modelo_b\").value = \"$modelo\";
                    objeto(\"titulo_actual\").textContent = \"$modelo\";
                    activar_tabla_precios();
                    desactivar_tabla(\"tabla_productos_datos\");
                    activar_tabla(\"tabla_productos_datos\");
                    goTop();
                    showElements(\"btn_cambiar_estado_producto,tab_producto_precio\");
                '>Editar</button>";

            $tabla .= "<tr>
                        <td>$btn</td>
                        <td>$idproducto</td>
                        <td>$modelo</td>
                        <td>$linea</td>
                        <td>$marca</td>
                        <td>$temporada</td>
                        <td>$color</td>
                        <td>$corte</td>
                        <td>$tipo_suela</td>
                        <td>$concepto</td>
                        <td>$precios</td>
                        <td>$estado</td>
                    </tr>";
        }

        $tabla .= '</tbody></table>';

        return $tabla;
    }

    public function get_idproducto($modelo,$idmarca,$idtemporada,$idcolor)
    {
        $filtro_color = ($idcolor === '' || is_null($idcolor)) ? "idcolor IS NULL" : "idcolor = '$idcolor'";
        $idproducto = mysql::getvalue("SELECT idproducto FROM producto WHERE idmarca = '$idmarca' AND modelo = '$modelo' AND idtemporada = '$idtemporada' AND $filtro_color");

        if(!empty($idproducto)){
            return $idproducto;
        }else{
            return false;
        }
    }

    public function guardar($idproducto,$idmarca,$idtemporada,$linea,$modelo,$idcolor,$idcorte,$idtipo_suela,$idconcepto,$estado = '',$mantenimiento = 'NO') 
    {   
        if($mantenimiento !== 'SI'){
            if($idproducto == ''){
                $idprod_existente = $this->get_idproducto($modelo,$idmarca,$idtemporada,$idcolor);
    
                if($idprod_existente){
                    $idproducto = $idprod_existente;
                }else{
                    $idproducto = '';
                }
            }
        }

        if($idproducto == ''){
            if($mantenimiento == 'SI'){
                $security = new security($this->ACCIONES['crear_x_mante']);
            }else{
                $security = new security($this->ACCIONES['crear']);
            }
            
            $condicion_color = ($idcolor === '' || is_null($idcolor)) ? " AND idcolor IS NULL" : " AND idcolor = '$idcolor'";

            if(mysql::exists('producto',"idtemporada = '$idtemporada' AND modelo = '$modelo' AND idmarca = '$idmarca'" . $condicion_color)){
                $this->last_error = "Ya existe este producto.";
                utils::report_error(validation_error, ['modelo' => $modelo, 'idmarca' => $idmarca, 'idtemporada' => $idtemporada], $this->last_error);
                return false;
            }

            $DATOS = [];
            $DATOS['idmarca']          = $idmarca;
            $DATOS['idtemporada']      = $idtemporada;
            if($linea != ''){
                $DATOS['linea']        = $linea;
            }
            $DATOS['modelo']           = $modelo;

            if($idcolor != ''){
                $DATOS['idcolor']          = $idcolor;
            }
            if($idcorte != ''){
                $DATOS['idcorte']          = $idcorte;
            }
            if($idtipo_suela != ''){
                $DATOS['idtipo_suela']       = $idtipo_suela;
            }
            if($idconcepto != ''){
                $DATOS['idconcepto']       = $idconcepto;
            }

            $DATOS['estado']           = $estado != '' ?  "$estado" :'ACTIVO';
            $DATOS['usuario_creacion'] = $security->get_actual_user();

            if(table::insert_record($DATOS)){
                $referencia = mysql::last_id();
                if($mantenimiento == 'SI'){
                    $security->registrar_bitacora($this->ACCIONES['crear_x_mante'], "Guardar producto");
                }else{
                    $security->registrar_bitacora($this->ACCIONES['crear'], "Guardar producto");
                }

                return $referencia;
            }else{
                $this->last_error = "No se pudo guardar el producto.";
                utils::report_error(bd_error, $DATOS,$this->last_error);

                return false;
            }
        }else{
            if($mantenimiento == 'SI'){
                $security = new security($this->ACCIONES['modificar_x_mante']);
            }else{
                $security = new security($this->ACCIONES['modificar']);
            }

            $condicion_color = ($idcolor === '' || is_null($idcolor)) ? " AND idcolor IS NULL" : " AND idcolor = '$idcolor'";

            if(mysql::exists('producto',"idtemporada = '$idtemporada' AND modelo = '$modelo' AND idmarca = '$idmarca' AND idproducto != '$idproducto'" . $condicion_color)){
                $this->last_error = "Ya existe este producto.";
                utils::report_error(validation_error, ['modelo' => $modelo, 'idmarca' => $idmarca, 'idtemporada' => $idtemporada], $this->last_error);
                return false;
            }

            $DATOS = [];
            $DATOS['idproducto']           = $idproducto;
            if($linea != ''){
                $DATOS['linea']            = $linea;
            }

            if($idcolor != ''){
                $DATOS['idcolor']          = $idcolor;
            }
            if($idcorte != ''){
                $DATOS['idcorte']          = $idcorte;
            }
            if($idtipo_suela != ''){
                $DATOS['idtipo_suela']     = $idtipo_suela;
            }
            if($idconcepto != ''){
                $DATOS['idconcepto']       = $idconcepto;
            }
            $DATOS['usuario_modificacion'] = $security->get_actual_user();
            $DATOS['fecha_modificacion']   = date("Y-m-d H:i:s");
            $llaves                        = ['idproducto'];

            if(table::update_record($DATOS,$llaves)){
                if($mantenimiento == 'SI'){
                    $security->registrar_bitacora($this->ACCIONES['modificar_x_mante'], "Modificar producto");
                }else{
                    $security->registrar_bitacora($this->ACCIONES['modificar'], "Modificar producto");
                }
                

                return $idproducto;
            }else{
                $this->last_error = "No se pudo actualizar el producto.";
                utils::report_error(bd_error, $DATOS,$this->last_error);

                return false;
            }
        }
    }

    public function cargar_productos($idtemporada)
    {
        $security = new security($this->ACCIONES['cargar_productos']);

        $PRODUCTO_PRECIO = new producto_precio();
        $SET_TALLA       = new set_talla();
        $MARCA           = new marca();

        $handle = fopen($_FILES['file_uploaded']['tmp_name'], "r");

        if ($handle === false) {
            $this->last_error = "No se pudo leer el archivo.";
            utils::report_error(validation_error, $_FILES, $this->last_error);

            return false;
        }

        $linea_count = 0;

        while (($row = fgetcsv($handle, 1000, ",", '"', "\\")) !== false) {
            $linea_count++;

            if (!isset($row[0]) || trim($row[0]) === '') {
                continue;
            }

            $nombre_marca = trim($row[0]);
            if (strtoupper($nombre_marca) === 'MARCA') {
                continue;
            }

            if (!isset($row[2], $row[4], $row[6])) {
                fclose($handle);
                $this->last_error = "Formato inválido en la línea $linea_count. Se esperan al menos 7 columnas.";
                utils::report_error(validation_error, $row, $this->last_error);

                return $this->eliminar();
            }

            $idmarca = $MARCA->get_idmarca(addslashes($nombre_marca));
            if (!$idmarca) {
                fclose($handle);
                $this->last_error = "Marca no valida: " . $nombre_marca;
                utils::report_error(validation_error, $nombre_marca, $this->last_error);

                if(!$this->eliminar()){
                    return false;
                }

                return false;
            }

            $estilo_raw = trim($row[2]);
            if ($estilo_raw === '') {
                fclose($handle);
                $this->last_error = "El estilo esta vacio en la linea $linea_count.";
                utils::report_error(validation_error, $row, $this->last_error);

                if(!$this->eliminar()){
                    return false;
                }

                return false;
            }

            $estilos = [$estilo_raw];
            $marca_normalizada = strtoupper(preg_replace('/\s+/', ' ', $nombre_marca));
            switch($marca_normalizada){
                case 'VIA MARTE':
                    $estilos = explode('/', $estilo_raw);
                break;
                default:
                    if(strpos($estilo_raw, '/') !== false){
                        $estilos = explode('/', $estilo_raw);
                    }
                break;
            }

            $set_talla_raw = trim($row[4]);
            $set_talla_raw = preg_replace('/\s+/', '', $set_talla_raw);
            $tallas = explode('-', $set_talla_raw);

            if(count($tallas) < 2){
                fclose($handle);
                $this->last_error = "Set de tallas invalido en la linea $linea_count.";
                utils::report_error(validation_error, $row, $this->last_error);

                if(!$this->eliminar()){
                    return false;
                }

                return false;
            }

            $idset_talla = $SET_TALLA->get_idset_talla(trim($tallas[0]),trim($tallas[1]));
            if(!$idset_talla){
                fclose($handle);
                $this->last_error = $SET_TALLA->last_error . " (linea $linea_count)";
                utils::report_error(validation_error, $row, $this->last_error);

                if(!$this->eliminar()){
                    return false;
                }

                return false;
            }

            $linea  = isset($row[5]) ? trim($row[5]) : '';
            $precio = str_replace(['$', 'Q', ','], '', trim($row[6]));

            if ($precio === '') {
                fclose($handle);
                $this->last_error = "Falta el precio en la línea $linea_count.";
                utils::report_error(validation_error, $row, $this->last_error);

                if(!$this->eliminar()){
                    return false;
                }

                return false;
            }

            foreach($estilos as $modelo){
                $modelo = trim($modelo);
                if($modelo === ''){
                    continue;
                }

                $idproducto = $this->guardar('', $idmarca, $idtemporada, $linea, $modelo, '', '', '', '', 'BORRADOR', '');

                if(!$idproducto){
                    fclose($handle);
                    if(!$this->eliminar()){
                        return false;
                    }

                    return false;
                }
                if(!$PRODUCTO_PRECIO->guardar('', $idproducto, $precio,$idset_talla, 'BORRADOR')){
                    fclose($handle);
                    $this->last_error = $PRODUCTO_PRECIO->last_error;
                    if(!$this->eliminar()){
                        return false;
                    }

                    return false;
                }
            }
        }

        fclose($handle);

        if(!$this->activar()){
            if(!$this->eliminar()){
                return false;
            }

            return false;
        }

        return true;
    }

    public function eliminar()
    {
        $security = new security($this->ACCIONES['eliminar']);
        $usuario  = $security->get_actual_user();

        $DATOS = [];
        $DATOS['usuario_creacion'] = $usuario;
        $DATOS['estado']           = 'BORRADOR';
        
        $PRODUCTO_PRECIO = new producto_precio();

        if($PRODUCTO_PRECIO->eliminar($DATOS)){
            if(table::delete_record($DATOS)){
                $security->registrar_bitacora($this->ACCIONES['eliminar'], "Eliminar productos por falla.");
    
                return true;
            }else{
                $this->last_error = "No se pudieron eliminar los productos.";
                utils::report_error(bd_error, $DATOS,$this->last_error);
    
                return false;
            } 
        }else{
            $this->last_error = $PRODUCTO_PRECIO->last_error;

            return false;
        }
    }

    public function activar()
    {
        $security = new security($this->ACCIONES['activar']);
        $usuario  = $security->get_actual_user();

        $DATOS = [];
        $DATOS['usuario_creacion'] = $usuario;
        $DATOS['estado']           = 'ACTIVO';
        $llaves                    = ['usuario_creacion'];
        
        $PRODUCTO_PRECIO = new producto_precio();

        if($PRODUCTO_PRECIO->activar()){
            if(table::update_record($DATOS,$llaves)){
                $security->registrar_bitacora($this->ACCIONES['activar'], "activar productos por falla.");
    
                return true;
            }else{
                $this->last_error = "No se pudieron activar los productos.";
                utils::report_error(bd_error, $DATOS,$this->last_error);
    
                return false;
            } 
        }else{
            $this->last_error = $PRODUCTO_PRECIO->last_error;

            return false;
        }
    }

    public function modelos()
    {
        return mysql::getoptions("SELECT DISTINCT 
            modelo AS id,
            modelo AS descripcion
        FROM producto
        ORDER BY modelo ASC;");
    }

    public function estado($idproducto)
    {
        return mysql::getvalue("SELECT estado FROM producto WHERE idproducto = '$idproducto'");
    }

    public function cambiar_estado($idproducto)
    {
        $security = new security($this->ACCIONES['cambiar_estado']);
        $estado_actual = $this->estado($idproducto);

        $DATOS = [];
        $DATOS['idproducto']           = $idproducto;
        $DATOS['estado']               = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $DATOS['usuario_modificacion'] = $security->get_actual_user();
        $DATOS['fecha_modificacion']   = date("Y-m-d H:i:s");
        $llaves                        = ['idproducto'];

        if (table::update_record($DATOS, $llaves)) {
            $security->registrar_bitacora($this->ACCIONES['cambiar_estado'], $idproducto, $DATOS['estado']);

            return $this->estado($idproducto);
        } else {
            $this->last_error = "No se pudo cambiar el estado.";
            utils::report_error(validation_error, $idproducto, $this->last_error);

            return false;
        }
    }

}