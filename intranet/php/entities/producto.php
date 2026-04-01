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

        if(isset($PARAMETROS['operacion'])){

            if ($PARAMETROS['operacion'] == 'obtener_modelo') {
                if (table::validate_parameter_existence(['modelo','idmarca','idtemporada'], $PARAMETROS, false)) {
                    if($resultado = self::obtener_modelo($PARAMETROS['modelo'],$PARAMETROS['idmarca'],$PARAMETROS['idtemporada'])){
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Faltan parámetros");
                }
            }

            if ($PARAMETROS['operacion'] == 'cargar_productos') {
                if (table::validate_parameter_existence(['idmarca','idtemporada'], $PARAMETROS, false)) {
                    if ($resultado = $this->cargar_productos($PARAMETROS['idmarca'],$PARAMETROS['idtemporada'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Parametros faltantes");
                }
            }

            if ($PARAMETROS['operacion'] == 'guardar') {
                if (table::validate_parameter_existence(['idmarca','modelo','idset_talla'], $PARAMETROS, false)) {
                    if ($resultado = $this->guardar($PARAMETROS['idproducto'],$PARAMETROS['idmarca'],$PARAMETROS['idtemporada'],$PARAMETROS['linea'],$PARAMETROS['modelo'],$PARAMETROS['idset_talla'],$PARAMETROS['idcolor'],$PARAMETROS['idcorte'],$PARAMETROS['idtipo_suela'],$PARAMETROS['idconcepto'],$PARAMETROS['estado'],$PARAMETROS['mantenimiento'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Parametros faltantes");
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
                    self::end_error("Parametros faltantes");
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

        $modelo = addslashes($modelo);
        $idmarca = (int)$idmarca;
    
        $result = mysql::getresult("SELECT idproducto, modelo, linea, idcolor, color, idmarca, marca, material, precio, idproducto_precio
            FROM view_producto_modelo 
            WHERE modelo = '$modelo' 
            AND idmarca = '$idmarca'
            AND idproducto IN (
                SELECT idproducto 
                FROM producto 
                WHERE idtemporada = '".intval($idtemporada)."'
            )
            ORDER BY color ASC
        ");
    
        if(!$result || mysql::num_rows($result) == 0){
            $this->last_error = "No se encontro el modelo.";
            utils::report_error(validation_error, $modelo, $this->last_error);
            return false;
        }
    
        $estilo = null;
        $colores_map = [];
    
        while($row = mysql::getrowresult($result)){
    
            if(!$estilo){
                $estilo = [
                    'codigo' => $modelo,
                    'descripcion' => $row['linea'],
                    'marca' => $row['marca'],
                    'colores' => []
                ];
            }

            $colorKey = $row['idcolor'];
    
            if(!isset($colores_map[$colorKey])){
                $colores_map[$colorKey] = [
                    'id' => $row['idcolor'], 
                    'idproducto' => $row['idproducto'],
                    'nombre' => $row['color'],
                    'material_default' => '',
                    'precio' => 0,
                    'imagen' => '',
                    'materiales' => []
                ];
            }
    
            if(!empty($row['material'])){
    
                $materialObj = [
                    'nombre' => $row['material'],
                    'precio' => (float)$row['precio'],
                    'idproducto_precio' => $row['idproducto_precio'] 
                ];
    
                $existe = false;
    
                foreach($colores_map[$colorKey]['materiales'] as $m){
                    if($m['idproducto_precio'] == $materialObj['idproducto_precio']){
                        $existe = true;
                        break;
                    }
                }
    
                if(!$existe){
                    $colores_map[$colorKey]['materiales'][] = $materialObj;
                }
    
                if($colores_map[$colorKey]['material_default'] == ''){
                    $colores_map[$colorKey]['material_default'] = $materialObj['nombre'];
                    $colores_map[$colorKey]['precio'] = $materialObj['precio'];
                }
            }
        }
    
        foreach($colores_map as &$c){
    
            if($c['material_default'] == '' && count($c['materiales']) > 0){
                $c['material_default'] = $c['materiales'][0]['nombre'];
                $c['precio'] = $c['materiales'][0]['precio'];
            }
        }
    
        $estilo['colores'] = array_values($colores_map);
    
        return json_encode($estilo);
    }

    public function cargar_opcion()
    {
        $DATA = [];
        $DATA['marcas']     = (new marca())->option_activos();
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
        $sql = mysql::getresult("
            SELECT 
                idproducto,
                modelo,
                linea,
                idmarca,
                marca,
                idtemporada,
                temporada,
                idset_talla,
                set_talla,
                idcolor,
                color,
                idcorte,
                corte,
                idtipo_suela,
                tipo_suela,
                idconcepto,
                concepto,
                estado
            FROM view_producto
        ");

        $columnControl = true;
        $responsive    = true;
        $colReorder    = true;
        $select        = true;
        $buttons       = false;
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
                            <th>Línea</th>
                            <th>Marca</th>
                            <th>Temporada</th>
                            <th>Set Talla</th>
                            <th>Color</th>
                            <th>Corte</th>
                            <th>Tipo Suela</th>
                            <th>Concepto</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla_todos">';
                    
        while($row = mysql::getrowresult($sql)){

            $idproducto  = $row['idproducto'];
            $modelo      = $row['modelo'];
            $linea       = $row['linea'];
            $marca       = $row['marca'];
            $temporada   = $row['temporada'];
            $set_talla   = $row['set_talla'];
            $color       = $row['color'];
            $corte       = $row['corte'];
            $tipo_suela  = $row['tipo_suela'];
            $concepto    = $row['concepto'];
            $estado      = $row['estado'];

            $str_data = "";
            foreach ($row as $key => $value) {
                $str_data .= $key . "=" . $value . "&";
            }

            $btn = "<button class='btn btn-info btn-sm' onclick='
                    editar_registro(\"$str_data\",this.parentNode.parentNode);
                    showElements(\"div_form_producto\");
                    hideElements(\"div_tabla\");
                    element(\"modelo_b\").value = \"$modelo\";
                    activar_tabla_precios($modelo);
                    goTop();
                    showElements(\"btn_cambiar_estado,tab_producto_precio\");
                '>Editar</button>";

            $tabla .= "<tr>
                        <td>$idproducto</td>
                        <td>$modelo</td>
                        <td>$linea</td>
                        <td>$marca</td>
                        <td>$temporada</td>
                        <td>$set_talla</td>
                        <td>$color</td>
                        <td>$corte</td>
                        <td>$tipo_suela</td>
                        <td>$concepto</td>
                        <td>$estado</td>
                        <td>$btn</td>
                    </tr>";
        }

        $tabla .= '</tbody></table>';

        return $tabla;
    }

    public function get_idproducto($modelo,$idmarca,$idtemporada)
    {
        $idproducto = mysql::getvalue("SELECT idproducto FROM producto WHERE idmarca = '$idmarca' AND modelo = '$modelo' AND idtemporada = '$idtemporada'");

        if(!empty($idproducto)){
            return $idproducto;
        }else{
            return false;
        }
    }

    public function guardar($idproducto,$idmarca,$idtemporada,$linea,$modelo,$idset_talla,$idcolor,$idcorte,$idtipo_suela,$idconcepto,$estado = '',$mantenimiento = 'NO') 
    {   
        if($mantenimiento !== 'SI'){
            if($idproducto == ''){
                $idprod_existente = $this->get_idproducto($modelo,$idmarca,$idtemporada);
    
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
            

            if(mysql::exists('producto',"idtemporada = '$idtemporada' AND modelo = '$modelo' AND idmarca = '$idmarca' AND idcolor = '$idcolor'")){
                $this->last_error = "Ya existe un producto con el mismo modelo, marca,color y temporada.";
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
            $DATOS['idset_talla']      = $idset_talla;

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
                $this->last_error = "Error al guardar el producto";
                utils::report_error(bd_error, $DATOS,$this->last_error);

                return false;
            }
        }else{
            if($mantenimiento == 'SI'){
                $security = new security($this->ACCIONES['modificar_x_mante']);
            }else{
                $security = new security($this->ACCIONES['modificar']);
            }

            if(mysql::exists('producto',"idtemporada = '$idtemporada' AND modelo = '$modelo' AND idmarca = '$idmarca' AND idcolor = '$idcolor'")){
                $this->last_error = "Ya existe un producto con el mismo modelo, marca,color y temporada.";
                utils::report_error(validation_error, ['modelo' => $modelo, 'idmarca' => $idmarca, 'idtemporada' => $idtemporada], $this->last_error);
                return false;
            }

            $DATOS = [];
            $DATOS['idproducto']           = $idproducto;
            if($linea != ''){
                $DATOS['linea']            = $linea;
            }
            $DATOS['idset_talla']          = $idset_talla;

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
                $this->last_error = "Error al modificar el producto";
                utils::report_error(bd_error, $DATOS,$this->last_error);

                return false;
            }
        }
    }

    public function cargar_productos($idmarca,$idtemporada)
    {
        $security = new security($this->ACCIONES['cargar_productos']);

        $PRODUCTO_PRECIO = new producto_precio();
        $TIPO_SUELA      = new tipo_suela();
        $CONCEPTO        = new concepto();
        $COLOR           = new color();
        $CORTE           = new corte();
        $SET_TALLA       = new set_talla();
    
        switch($idmarca){
    
            case 2:

                $handle = fopen($_FILES['file_uploaded']['tmp_name'], "r");
            
                $PRODUCTOS        = [];
            
                if ($handle !== false) {
            
                    $grupo        = null;
                    $idset_talla  = null;
                    $last_modelo  = null;
                    $linea = 0;
            
                    while (($row = fgetcsv($handle, 1000, ",", '"', "\\")) !== false) {
                        $linea++;
                        $conteo_datos = count(array_filter($row, function($v) {
                            return trim($v) !== '';
                        }));

                        if($conteo_datos > 7 ){
                            $this->last_error = "Hay mas datos de los necesarios en la linea $linea";
                            if(!$this->eliminar()){
                            
                                return false;
                            }
                            utils::report_error(validation_error, $linea,$this->last_error);

                            return false;
                        }

                        if($conteo_datos < 7 ){
                            $this->last_error = "Hay menos datos de los necesarios en la linea $linea";
                            if(!$this->eliminar()){
                            
                                return false;
                            }
                            utils::report_error(validation_error, $linea,$this->last_error);

                            return false;
                        }
            
                        if (!isset($row[0])) {
                            continue;
                        }
            
                        $col0 = trim($row[0]);
            
                        if (strtoupper($col0) === 'MODELO') {
            
                            $grupo = trim($row[1]);
                            $tallas = explode('/',$grupo);
                            $idset_talla = $SET_TALLA->get_idset_talla(trim($tallas[0]),trim($tallas[1]));

                            if(!$idset_talla){
                                $this->last_error = $SET_TALLA->last_error;
                                if(!$this->eliminar()){
                        
                                    return false;
                                }
                                
                                return false;
                            }
            
                            $last_modelo = null;
            
                            continue;
                        }
            
                        if (!$idset_talla) {
                            continue;
                        }
            
                        $modelo = trim($row[0]);
            
                        if ($modelo === "") {
                            $modelo = $last_modelo;
                        } else {
                            $last_modelo = $modelo;
                        }
            
                        $precio = str_replace(['$', 'Q'], '', trim($row[1]));
                        $corte  = trim($row[2]);
                        $suela  = trim($row[4]);
            
                        if (!$idtipo_suela = $TIPO_SUELA->get_idtipo_suela($suela)) {
                            $this->last_error = $TIPO_SUELA->last_error;

                            return false;
                        }
            
                        $concepto = trim($row[5]);
                        if (!$idconcepto = $CONCEPTO->get_idconcepto($concepto)) {
                            $this->last_error = $CONCEPTO->last_error;

                            return false;
                        }
            
                        $material = trim($row[6]);
            
                        $cortes  = explode(',', $corte);
                        $colores = explode(',', trim($row[3]));
            
                        foreach ($cortes as $corte_item) {
            
                            $corte_item = trim($corte_item);
                            if (!$idcorte = $CORTE->get_idcorte($corte_item)) {
                                $this->last_error = $CORTE->last_error;

                                return false;
                            }
            
                            foreach ($colores as $color_item) {
            
                                $color_item = trim($color_item);
                                if (!$idcolor = $COLOR->get_idcolor($color_item)) {
                                    $this->last_error = $COLOR->last_error;

                                    return false;
                                }
            
                                $idproducto = $this->guardar('',$idmarca,$idtemporada,'',$modelo,$idset_talla,$idcolor,$idcorte,$idtipo_suela,$idconcepto,"BORRADOR");
            
                                if (!$idproducto) {
                                    if(!$this->eliminar()){
                            
                                        return false;
                                    }

                                    return false;
                                }
            
                                $PRODUCTOS[] = $idproducto;
            
                                if (!$PRODUCTO_PRECIO->guardar('', $idproducto, $material, $precio, 'BORRADOR')) {
                                    if(!$this->eliminar()){
                            
                                        return false;
                                    }
                                    $this->last_error = $PRODUCTO_PRECIO->last_error;

                                    return false;
                                }
                            }
                        }
                    }
                    if(!$this->activar()){
                        if(!$this->eliminar()){
                            
                            return false;
                        }

                        return false;
                    }

                    fclose($handle);
                }
            
            break;

            case 3:

                $handle = fopen($_FILES['file_uploaded']['tmp_name'], "r");

                if ($handle !== false) {
            
                    fgetcsv($handle, 1000, ",", '"', "\\");
                    $linea_count = 0;
                    while (($row = fgetcsv($handle, 1000, ",", '"', "\\")) !== false) {
                        $linea_count++;
                        $conteo_datos = count(array_filter($row, function($v) {
                            return trim($v) !== '';
                        }));

                        if($conteo_datos > 5 ){
                            $this->last_error = "Hay mas datos de los necesarios en la linea $linea_count";
                            if(!$this->eliminar()){
                            
                                return false;
                            }
                            utils::report_error(validation_error, $linea_count,$this->last_error);

                            return false;
                        }

                        if($conteo_datos < 5 ){
                            $this->last_error = "Hay menos datos de los necesarios en la linea $linea_count";
                            if(!$this->eliminar()){
                            
                                return false;
                            }
                            utils::report_error(validation_error, $linea_count,$this->last_error);

                            return false;
                        }

                        $linea      = $row[0];
                        $referencia = explode('.',trim($row[1]));
                        $modelo     = $referencia[0];
                        $color_ref  = $referencia[1];

                        if(!$idcolor = $COLOR->get_idcolor($color_ref)){
                            $this->last_error = $COLOR->last_error;
                            if(!$this->eliminar()){
                    
                                return false;
                            }

                            return false;
                        }

                        $material   = $row[2];
                        $tallas     = explode('-',trim($row[3]));
                        $precio = str_replace(['$', 'Q'], '', trim($row[4]));

                        $idset_talla = $SET_TALLA->get_idset_talla(trim($tallas[0]),trim($tallas[1]));

                        if(!$idset_talla){
                            $this->last_error = $SET_TALLA->last_error;
                            if(!$this->eliminar()){
                    
                                return false;
                            }

                            return false;
                        }


                        $idproducto = $this->guardar('',$idmarca,$idtemporada,$linea,$modelo,$idset_talla,$idcolor,'','','','BORRADOR');
                        
                        if (!$idproducto) {
                            if(!$this->eliminar()){
                    
                                return false;
                            }

                            return false;
                        }
    
                        $PRODUCTOS[] = $idproducto;
    
                        if (!$PRODUCTO_PRECIO->guardar('', $idproducto, $material, $precio, 'BORRADOR')) {
                            if(!$this->eliminar()){
                    
                                return false;
                            }
                            $this->last_error = $PRODUCTO_PRECIO->last_error;

                            return false;
                        }

                    }
                    
                    if(!$this->activar()){
                        if(!$this->eliminar()){
                            
                            return false;
                        }

                        return false;
                    }

                    fclose($handle);
                }

            break;

            case 4:
                $handle = fopen($_FILES['file_uploaded']['tmp_name'], "r");

                if ($handle !== false) {
                    fgetcsv($handle, 1000, ",", '"', "\\");
                    $linea_count = 0;
                    while (($row = fgetcsv($handle, 1000, ",", '"', "\\")) !== false) {
                        $linea_count++;
                        $conteo_datos = count(array_filter($row, function($v) {
                            return trim($v) !== '';
                        }));

                        if($conteo_datos > 3 ){
                            $this->last_error = "Hay mas datos de los necesarios en la linea $linea_count";
                            if(!$this->eliminar()){
                            
                                return false;
                            }
                            utils::report_error(validation_error, $linea_count,$this->last_error);

                            return false;
                        }

                        if($conteo_datos < 3 ){
                            $this->last_error = "Hay menos datos de los necesarios en la linea $linea_count";
                            if(!$this->eliminar()){
                            
                                return false;
                            }
                            utils::report_error(validation_error, $linea_count,$this->last_error);

                            return false;
                        }

                        $datos  = $row[0];
                        preg_match('/^([0-9\/\-\s]+)\s+(.+)$/', trim($datos), $match);

                        $modelos = explode('/',trim($match[1]));
                        $colores = explode(',',trim($match[2]));
                        $precio  = str_replace(['$', 'Q'], '', trim($row[1]));
                        $tallas  = explode('-',trim($row[2]));

                        $idset_talla = $SET_TALLA->get_idset_talla(trim($tallas[0]),trim($tallas[1]));

                        if(!$idset_talla){
                            $this->last_error = $SET_TALLA->last_error;
                            if(!$this->eliminar()){
                    
                                return false;
                            }

                            return false;
                        }

                        foreach ($modelos as $modelo) {
                            foreach ($colores as $color) {
                                if (!$idcolor = $COLOR->get_idcolor($color)) {
                                    $this->last_error = $COLOR->last_error;

                                    return false;
                                }
                                
                                $idproducto = $this->guardar('',$idmarca,$idtemporada,'',$modelo,$idset_talla,$idcolor,'','','','BORRADOR');
                        
                                if (!$idproducto) {
                                    if(!$this->eliminar()){
                            
                                        return false;
                                    }

                                    return false;
                                }
            
                                $PRODUCTOS[] = $idproducto;
            
                                if (!$PRODUCTO_PRECIO->guardar('', $idproducto, '', $precio, 'BORRADOR')) {
                                    if(!$this->eliminar()){
                            
                                        return false;
                                    }
                                    $this->last_error = $PRODUCTO_PRECIO->last_error;

                                    return false;
                                }
                            }
                        }
                    }

                    if(!$this->activar()){
                        if(!$this->eliminar()){
                            
                            return false;
                        }

                        return false;
                    }

                    fclose($handle);
                }
            break;

            case 5:
                $handle = fopen($_FILES['file_uploaded']['tmp_name'], "r");

                if ($handle !== false) {
                    fgetcsv($handle, 1000, ",", '"', "\\");
                    $linea_count = 0;
                    while (($row = fgetcsv($handle, 1000, ",", '"', "\\")) !== false) {
                        $linea_count++;
                        $conteo_datos = count(array_filter($row, function($v) {
                            return trim($v) !== '';
                        }));

                        if($conteo_datos > 5 ){
                            $this->last_error = "Hay mas datos de los necesarios en la linea $linea_count";
                            if(!$this->eliminar()){
                            
                                return false;
                            }
                            utils::report_error(validation_error, $linea_count,$this->last_error);

                            return false;
                        }

                        if($conteo_datos < 5 ){
                            $this->last_error = "Hay menos datos de los necesarios en la linea $linea_count";
                            if(!$this->eliminar()){
                            
                                return false;
                            }
                            utils::report_error(validation_error, $linea_count,$this->last_error);

                            return false;
                        }

                        $linea  = $row[0];
                        $modelo = $row[1];
                        $precio = str_replace(['$', 'Q'], '', trim($row[2]));
                        $tallas = explode('-',trim($row[3]));
                        $color  = $row[4];
                        if (!$idcolor = $COLOR->get_idcolor($color)) {
                            $this->last_error = $COLOR->last_error;

                            return false;
                        }

                        $idset_talla = $SET_TALLA->get_idset_talla(trim($tallas[0]),trim($tallas[1]));

                        if(!$idset_talla){
                            $this->last_error = $SET_TALLA->last_error;
                            if(!$this->eliminar()){
                    
                                return false;
                            }

                            return false;
                        }

                        $idproducto = $this->guardar('',$idmarca,$idtemporada,'',$modelo,$idset_talla,$idcolor,'','','','BORRADOR');
                        
                        if (!$idproducto) {
                            if(!$this->eliminar()){
                    
                                return false;
                            }

                            return false;
                        }
    
                        $PRODUCTOS[] = $idproducto;
    
                        if (!$PRODUCTO_PRECIO->guardar('', $idproducto, '', $precio, 'BORRADOR')) {
                            if(!$this->eliminar()){
                    
                                return false;
                            }
                            $this->last_error = $PRODUCTO_PRECIO->last_error;

                            return false;
                        }
                    }

                    if(!$this->activar()){
                        if(!$this->eliminar()){
                            
                            return false;
                        }

                        return false;
                    }

                    fclose($handle);
                }
            break;

            case 6:
                $handle = fopen($_FILES['file_uploaded']['tmp_name'], "r");

                if ($handle !== false) {
                    fgetcsv($handle, 1000, ",", '"', "\\");
                    $linea_count = 0;
                    while (($row = fgetcsv($handle, 1000, ",", '"', "\\")) !== false) {
                        $linea_count++;
                        $conteo_datos = count(array_filter($row, function($v) {
                            return trim($v) !== '';
                        }));

                        if($conteo_datos > 4 ){
                            $this->last_error = "Hay mas datos de los necesarios en la linea $linea_count";
                            if(!$this->eliminar()){
                            
                                return false;
                            }
                            utils::report_error(validation_error, $linea_count,$this->last_error);

                            return false;
                        }

                        if($conteo_datos < 4 ){
                            $this->last_error = "Hay menos datos de los necesarios en la linea $linea_count";
                            if(!$this->eliminar()){
                            
                                return false;
                            }
                            utils::report_error(validation_error, $linea_count,$this->last_error);

                            return false;
                        }

                        $modelo  = $row[0];
                        $precio = str_replace(['$', 'Q'], '', trim($row[1]));
                        $tallas = explode('-',trim($row[3]));                        
                        $color  = $row[2];
                        if (!$idcolor = $COLOR->get_idcolor($color)) {
                            $this->last_error = $COLOR->last_error;

                            return false;
                        }

                        $idset_talla = $SET_TALLA->get_idset_talla(trim($tallas[0]),trim($tallas[1]));

                        if(!$idset_talla){
                            $this->last_error = $SET_TALLA->last_error;
                            if(!$this->eliminar()){
                    
                                return false;
                            }

                            return false;
                        }

                        $idproducto = $this->guardar('',$idmarca,$idtemporada,'',$modelo,$idset_talla,$idcolor,'','','','BORRADOR');
                        
                        if (!$idproducto) {
                            if(!$this->eliminar()){
                    
                                return false;
                            }

                            return false;
                        }
    
                        $PRODUCTOS[] = $idproducto;
    
                        if (!$PRODUCTO_PRECIO->guardar('', $idproducto, '', $precio, 'BORRADOR')) {
                            if(!$this->eliminar()){
                    
                                return false;
                            }
                            $this->last_error = $PRODUCTO_PRECIO->last_error;

                            return false;
                        }
                    }

                    if(!$this->activar()){
                        if(!$this->eliminar()){
                            
                            return false;
                        }

                        return false;
                    }

                    fclose($handle);
                }
            break;

            case 7:
                $handle = fopen($_FILES['file_uploaded']['tmp_name'], "r");

                if ($handle !== false) {
                    fgetcsv($handle, 1000, ",", '"', "\\");
                    $linea_count = 0;
                    while (($row = fgetcsv($handle, 1000, ",", '"', "\\")) !== false) {
                        $linea_count++;
                        $conteo_datos = count(array_filter($row, function($v) {
                            return trim($v) !== '';
                        }));

                        if($conteo_datos > 7 ){
                            $this->last_error = "Hay mas datos de los necesarios en la linea $linea_count";
                            if(!$this->eliminar()){
                            
                                return false;
                            }
                            utils::report_error(validation_error, $linea_count,$this->last_error);

                            return false;
                        }

                        if($conteo_datos < 7 ){
                            $this->last_error = "Hay menos datos de los necesarios en la linea $linea_count";
                            if(!$this->eliminar()){
                            
                                return false;
                            }
                            utils::report_error(validation_error, $linea_count,$this->last_error);

                            return false;
                        }

                        $modelo = $row[0];
                        $color  = $row[1];
                        if (!$idcolor = $COLOR->get_idcolor($color)) {
                            $this->last_error = $COLOR->last_error;

                            return false;
                        }
                        $suela  = $row[2];
                        if (!$idtipo_suela = $TIPO_SUELA->get_idtipo_suela($suela)) {
                            $this->last_error = $TIPO_SUELA->last_error;

                            return false;
                        }
                        $num = $row[3];
                        $tallas = explode('-',trim($row[4]));
                        $altura = $row[5];
                        $precio = str_replace(['$', 'Q'], '', trim($row[6]));

                        
                        $idset_talla = $SET_TALLA->get_idset_talla(trim($tallas[0]),trim($tallas[1]));

                        if(!$idset_talla){
                            $this->last_error = $SET_TALLA->last_error;
                            if(!$this->eliminar()){
                    
                                return false;
                            }

                            return false;
                        }

                        $idproducto = $this->guardar('',$idmarca,$idtemporada,'',$modelo,$idset_talla,$idcolor,'',$idtipo_suela,'','BORRADOR');
                        
                        if (!$idproducto) {
                            if(!$this->eliminar()){
                    
                                return false;
                            }

                            return false;
                        }
    
                        $PRODUCTOS[] = $idproducto;
    
                        if (!$PRODUCTO_PRECIO->guardar('', $idproducto, '', $precio, 'BORRADOR')) {
                            if(!$this->eliminar()){
                    
                                return false;
                            }
                            $this->last_error = $PRODUCTO_PRECIO->last_error;

                            return false;
                        }
                    }

                    if(!$this->activar()){
                        if(!$this->eliminar()){
                            
                            return false;
                        }

                        return false;
                    }

                    fclose($handle);
                }
            break;
    
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
                $this->last_error = "Error al eliminar los productos.";
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
                $this->last_error = "Error al activar los productos.";
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
            $this->last_error = "Error al cambiar de estado";
            utils::report_error(validation_error, $idproducto, $this->last_error);

            return false;
        }
    }

}