<?php
require_once('../wisetech/table.php');
require_once('../wisetech/security.php');
require_once('../wisetech/html.php');
require_once('../wisetech/utils.php');
class menu extends table{
    use utils;
    private $idmenu;
    private $last_error;
    private $ACCIONES = array();

    public function __construct($PARAMETROS = null){
        parent::__construct(prefijo . '_seguridad', 'usuario');

        $this->ACCIONES['modificar'] = "Modificar_orden_de_menus";

        if(isset($PARAMETROS['operacion'])){

			if($PARAMETROS['operacion']=='guardar'){
                if($resultado = self::guardar_menu($PARAMETROS)){
                    self::end_success($resultado);
                }else{
                    self::end_error($this->last_error);
                }
            }

        }
    }

    public function cargar_opcion(){
        $DATA = array();
		$MENUS = mysql::getresult("SELECT idmenu, nombre, orden FROM menu ORDER BY orden ");
		$tabla_menus ='';
		while ($row = mysql::getrowresult($MENUS)) {
            $idmenu = $row['idmenu'];
            $nombre = $row['nombre'];
            $orden = $row['orden'];
			//$row_data = getrowmysql("SELECT * FROM area WHERE idarea = '$idarea' ");
			$row_data = $row;
			$str_data = "";
			foreach ($row_data as $key => $value) {
				$str_data .= $key."=".$value."&";
			}
			$boton_editar = "<button  class=\"btn btn-primary waves-effect waves-light\" type=\"button\" onclick=\"editar_registro('$str_data',this.parentNode.parentNode);goTop();\"><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>";
			$tabla_menus .= "<tr>
                <td>$boton_editar</td>
                <td>$idmenu</td>
				<td>$nombre</td>
				<td>$orden</td>
			</tr>";
		}
		$DATA['tabla_menus'] = $tabla_menus;
			
		$html = new html('menu',$DATA);
		return $html->get_html();
    }

    /**
	 * @param array $PARAMETROS 
	 * si el menu existe, lo modifica, de lo contraro crea uno nuevo
	 */
	public function guardar_menu($PARAMETROS){        
        $parametros_necesarios = array("idmenu","nombre","orden");
        if(!table::validate_parameter_existence($parametros_necesarios,$PARAMETROS))self::end_error("Datos incompletos.");
        $PARAMETROS = table::sanitize_array($PARAMETROS);

        if(mysql::exists('menu'," idmenu = '{$PARAMETROS['idmenu']}' " )){//es menu nuevo
            $security = new security($this->ACCIONES['modificar']);//modificar registro de menu
			$valores_necesarios = array("idmenu","nombre","orden");
            $DATOS = table::create_subarray($valores_necesarios,$PARAMETROS);
            $llaves = array("idmenu");
            if($resultado = table::update_record($DATOS, $llaves)){
                $security->registrar_bitacora($this->ACCIONES['modificar'],$idmenu);
                return "editado";
            }else{
                $this->last_error = "Error al modificar el registro";
                return false;
            }
        }
	}
}
?>