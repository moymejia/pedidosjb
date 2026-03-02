<?php 
class html{
	
	private $html = "";
	
	function __construct($file_name,$DATA = null){//CREA LA CONEXION
        if(self::add_file($file_name)){
            if ($DATA != null) self::insert_data($DATA);
        }
	}
    
    /**
     * Carga el contenido del archivo. 
     */
	private function add_file($file_name){
		$path = '../../html/'.$file_name.'.html';
		if(file_exists($path)){
            $this->html = file_get_contents($path);
            return true;
        }else{
            return false;
        }
	}
	
	/**
     * Inserta los datos recibidos en el html cargado actualmente
     */
	public function insert_data($DATA){
        $KEYS = array();
        $VALUES = array();
		foreach ($DATA as $key => $value) {
            array_push($KEYS,'['.$key.']');
            array_push($VALUES,$value);
        }
        $this->html = str_replace($KEYS,$VALUES,$this->html);
        return true;
    }
    
    /**
     * Limpiar datos de html actual.
     */
    public function clear_html(){
        $this->html = '';
        return true;
    }

    public function get_html(){
        return $this->html;
    }
}
?>