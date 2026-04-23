<?php
require_once('../wisetech/table.php');
require_once('../wisetech/security.php');
require_once('../wisetech/html.php');
require_once('../wisetech/utils.php');
require_once('../entities/marca.php');
require_once('../entities/temporada.php');
require_once('../entities/producto_precio.php');
require_once('../entities/set_talla.php');


class producto extends table{

    use utils;

    private $last_error;
    private $ACCIONES = array();

    public function __construct($PARAMETROS = null){

        parent::__construct(prefijo . '_pedidos', 'producto');

        $this->ACCIONES['cargar_productos']    = "Cargar_productos";
        $this->ACCIONES['crear']               = "Crear_producto_carga_productos";
        $this->ACCIONES['eliminar']            = "Eliminar_productos_borrador_carga_productos";
        $this->ACCIONES['activar']             = "Activar_productos_carga_productos";
        $this->ACCIONES['modificar']           = "Modificar_producto_carga_productos";
        $this->ACCIONES['crear_x_mante']       = "Crear_producto_mantenimiento";
        $this->ACCIONES['modificar_x_mante']   = "Modificar_producto_mantenimiento";
        $this->ACCIONES['cambiar_est_x_mante'] = "cambiar_estado_producto_mantenimiento";

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
                    if ($resultado = $this->guardar($PARAMETROS['idproducto'],$PARAMETROS['idmarca'],$PARAMETROS['idtemporada'],$PARAMETROS['linea'],$PARAMETROS['modelo'],$PARAMETROS['estado'],$PARAMETROS['mantenimiento'])) {
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

        $modelo = addslashes(preg_replace('/\s+/', ' ', trim($modelo)));

        $row_temporada_con_precio = mysql::getrow("SELECT v.idproducto, v.modelo, v.linea, v.idmarca, v.marca, v.idtemporada
            FROM view_producto_modelo v
            WHERE v.modelo = '$modelo'
                AND v.idmarca = '$idmarca'
                AND v.idtemporada = '$idtemporada'
                AND EXISTS (
                    SELECT 1
                    FROM producto_precio pp
                    WHERE pp.idproducto = v.idproducto
                        AND pp.estado = 'ACTIVO'
                )
            ORDER BY v.idproducto DESC
            LIMIT 1");

        $row_otra_temporada_con_precio = mysql::getrow("SELECT v.idproducto, v.modelo, v.linea, v.idmarca, v.marca, v.idtemporada
            FROM view_producto_modelo v
            WHERE v.modelo = '$modelo'
                AND v.idmarca = '$idmarca'
                AND v.idtemporada <> '$idtemporada'
                AND EXISTS (
                    SELECT 1
                    FROM producto_precio pp
                    WHERE pp.idproducto = v.idproducto
                        AND pp.estado = 'ACTIVO'
                )
            ORDER BY v.idtemporada DESC, v.idproducto DESC
            LIMIT 1");

        $row_modelo_sin_precio = mysql::getrow("SELECT v.idproducto, v.modelo, v.linea, v.idmarca, v.marca, v.idtemporada
            FROM view_producto_modelo v
            WHERE v.modelo = '$modelo'
                AND v.idmarca = '$idmarca'
            ORDER BY (v.idtemporada = '$idtemporada') DESC, v.idtemporada DESC, v.idproducto DESC
            LIMIT 1");

        $row = null;
        $precio_otra_temporada = false;
        $sin_precio_ninguna_temporada = false;

        if ($row_temporada_con_precio) {
            $row = $row_temporada_con_precio;
        } elseif ($row_otra_temporada_con_precio) {
            $row = $row_otra_temporada_con_precio;
            $precio_otra_temporada = true;
        } elseif ($row_modelo_sin_precio) {
            $row = $row_modelo_sin_precio;
            $sin_precio_ninguna_temporada = true;
        }

        if(!$row){
            $this->last_error = "No se encontro el modelo.";
            utils::report_error(validation_error, $modelo, $this->last_error);
            return false;
        }

        $RESPUESTA = [
            'idproducto' => (int)$row['idproducto'],
            'codigo'     => $row['modelo'],
            'descripcion'=> $row['linea'],
            'marca'      => $row['marca']
        ];

        if ($precio_otra_temporada) {
            $RESPUESTA['precio_otra_temporada'] = true;
            utils::report_error(validation_error,
                ['modelo' => $modelo, 'idmarca' => $idmarca, 'idtemporada' => $idtemporada],
                "No se encontro precio en la temporada seleccionada. Se utilizo la ultima temporada con precio.");
        }

        if ($sin_precio_ninguna_temporada) {
            $RESPUESTA['sin_precio_ninguna_temporada'] = true;
            utils::report_error(validation_error,
                ['modelo' => $modelo, 'idmarca' => $idmarca, 'idtemporada' => $idtemporada],
                "No se encontro precio para el modelo en ninguna temporada.");
        }

        return json_encode($RESPUESTA);
    }

    private function es_temporada_despacho_inmediato($idtemporada)
    {
        $idtemporada = (int)$idtemporada;

        if ($idtemporada === 100) {
            return true;
        }

        $nombre_temporada = mysql::getvalue("SELECT nombre FROM temporada WHERE idtemporada = '$idtemporada' LIMIT 1");

        if (!$nombre_temporada) {
            return false;
        }

        return stripos($nombre_temporada, 'despacho inmediato') !== false;
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
        $DATA['tabla_productos'] = $this->tabla();
        $DATA['modelos']         = $this->modelos();

        $html = new html('producto', $DATA);
        return $html->get_html();
    }

    public function tabla()
    {   
        $sql = mysql::getresult("SELECT idproducto, modelo, linea, idmarca, marca, idtemporada, temporada, estado, precios
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
        $reset         = true;

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
        $data_ .= " data-conf-reset='"         . ($reset         ? "true" : "false") . "' ";
        $data_ .= " data-conf-reset='"         . ($reset         ? "true" : "false") . "' ";


        $tabla = '<table id="tabla_datos" '.$data_.' class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Acciones</th>
                            <th>Id</th>
                            <th>Estilo</th>
                            <th>Descripcion</th>
                            <th>Marca</th>
                            <th>Temporada</th>
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
                        <td>$precios</td>
                        <td>$estado</td>
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

    public function guardar($idproducto,$idmarca,$idtemporada,$linea,$modelo,$estado = '',$mantenimiento = 'NO') 
    {   
        $modelo = preg_replace('/\s+/', ' ', trim($modelo));

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
            
            if(mysql::exists('producto',"idtemporada = '$idtemporada' AND modelo = '$modelo' AND idmarca = '$idmarca'")){
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

            if(mysql::exists('producto',"idtemporada = '$idtemporada' AND modelo = '$modelo' AND idmarca = '$idmarca' AND idproducto != '$idproducto'")){
                $this->last_error = "Ya existe este producto para esta temporada.";
                utils::report_error(validation_error, ['modelo' => $modelo, 'idmarca' => $idmarca, 'idtemporada' => $idtemporada], $this->last_error);
                return false;
            }

            $DATOS = [];
            $DATOS['idproducto']           = $idproducto;
            if($linea != ''){
                $DATOS['linea']            = $linea;
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

        $delimitador = $this->obtener_delimitador_csv($handle);
        rewind($handle);

        if(!$this->validar_formato_delimitador_csv($handle, $delimitador)){
            fclose($handle);
            return false;
        }
        rewind($handle);

        $linea_count = 0;

        while (($row = fgetcsv($handle, 1000, $delimitador, '"', "\\")) !== false) {
            $linea_count++;

            $row = array_map(function($value){
                $value = trim($value);

                $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
                $value = str_replace(["\xC2\xA0"], ' ', $value);
                $value = preg_replace('/\s+/', ' ', $value);

                return $value;
            }, $row);

            if (!isset($row[0]) || trim($row[0]) === '') {
                continue;
            }

            $nombre_marca = $row[0];
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

                $idproducto = $this->guardar('', $idmarca, $idtemporada, $linea, $modelo, 'BORRADOR', '');

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

    private function obtener_delimitador_csv($handle)
    {
        $delimitador = ",";

        while (($linea = fgets($handle)) !== false) {
            $linea = preg_replace('/^\xEF\xBB\xBF/', '', trim($linea));
            if ($linea === '') {
                continue;
            }

            $columnas_coma       = str_getcsv($linea, ",", '"', "\\");
            $columnas_punto_coma = str_getcsv($linea, ";", '"', "\\");

            if (count($columnas_punto_coma) > count($columnas_coma)) {
                $delimitador = ";";
            }

            break;
        }

        return $delimitador;
    }

    private function validar_formato_delimitador_csv($handle, $delimitador)
    {
        $delimitador_alterno = ($delimitador === ",") ? ";" : ",";
        $linea_count = 0;

        while (($linea = fgets($handle)) !== false) {
            $linea_count++;
            $linea = preg_replace('/^\xEF\xBB\xBF/', '', trim($linea));

            if ($linea === '') {
                continue;
            }

            $columnas_detectadas = str_getcsv($linea, $delimitador, '"', "\\");
            $columnas_alternas   = str_getcsv($linea, $delimitador_alterno, '"', "\\");

            $cantidad_detectadas = count($columnas_detectadas);
            $cantidad_alternas   = count($columnas_alternas);

            if ($cantidad_detectadas <= 1 && $cantidad_alternas <= 1) {
                $this->last_error = "Formato CSV invalido en la linea $linea_count. No se reconoce separador ',' o ';'.";
                utils::report_error(validation_error, ['linea' => $linea], $this->last_error);
                return false;
            }

            if ($cantidad_detectadas <= 1 && $cantidad_alternas > 1) {
                $this->last_error = "Formato CSV invalido en la linea $linea_count. Se detecto delimitador '$delimitador_alterno' en un archivo configurado con '$delimitador'.";
                utils::report_error(validation_error, ['linea' => $linea], $this->last_error);
                return false;
            }

            if ($cantidad_detectadas >= 7 && $cantidad_alternas >= 7) {
                $this->last_error = "Formato CSV invalido en la linea $linea_count. Se detectaron delimitadores mezclados ',' y ';'.";
                utils::report_error(validation_error, ['linea' => $linea], $this->last_error);
                return false;
            }
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
        return mysql::getoptions("SELECT DISTINCT modelo AS id, modelo AS descripcion FROM producto ORDER BY modelo ASC;");
    }

    public function estado($idproducto)
    {
        return mysql::getvalue("SELECT estado FROM producto WHERE idproducto = '$idproducto'");
    }

    public function cambiar_estado($idproducto)
    {
        $security = new security($this->ACCIONES['cambiar_est_x_mante']);
        $estado_actual = $this->estado($idproducto);

        $DATOS = [];
        $DATOS['idproducto']           = $idproducto;
        $DATOS['estado']               = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $DATOS['usuario_modificacion'] = $security->get_actual_user();
        $DATOS['fecha_modificacion']   = date("Y-m-d H:i:s");
        $llaves                        = ['idproducto'];

        if (table::update_record($DATOS, $llaves)) {
            $security->registrar_bitacora($this->ACCIONES['cambiar_est_x_mante'], $idproducto, $DATOS['estado']);

            return $this->estado($idproducto);
        } else {
            $this->last_error = "No se pudo cambiar el estado.";
            utils::report_error(validation_error, $idproducto, $this->last_error);

            return false;
        }
    }

}
