<?php
require_once('../wisetech/table.php');
require_once('../wisetech/security.php');
require_once('../wisetech/html.php');
require_once('../wisetech/utils.php');


class producto extends table{

    use utils;

    private $last_error;
    private $ACCIONES = array();

    public function __construct($PARAMETROS = null){

        parent::__construct(prefijo . '_pedidos', 'producto');

        $this->ACCIONES['opcion_libro_diario'] = 21;

        if(isset($PARAMETROS['operacion'])){

            if ($PARAMETROS['operacion'] == 'obtener_modelo') {
                if (table::validate_parameter_existence(['modelo','idmarca'], $PARAMETROS, false)) {
                    if($resultado = self::obtener_modelo($PARAMETROS['modelo'],$PARAMETROS['idmarca'])){
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

    public function obtener_modelo($modelo, $idmarca){

        $modelo = addslashes($modelo);
        $idmarca = (int)$idmarca;
    
        $result = mysql::getresult("SELECT 
                idproducto, 
                modelo, 
                linea, 
                idcolor,
                color, 
                idmarca, 
                marca, 
                material, 
                precio,
                idproducto_precio
            FROM view_producto_modelo 
            WHERE modelo = '$modelo' 
            AND idmarca = '$idmarca' 
            ORDER BY color ASC");
    
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
    
            // 🔥 USAR ID REAL
            $colorKey = $row['idcolor'];
    
            if(!isset($colores_map[$colorKey])){
                $colores_map[$colorKey] = [
                    'id' => $row['idcolor'], // 👈 IMPORTANTE
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
                    'idproducto_precio' => $row['idproducto_precio'] // 🔥 CLAVE
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



}
?>