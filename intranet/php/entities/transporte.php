<?php
require_once('../wisetech/table.php');
require_once('../wisetech/security.php');
require_once('../wisetech/html.php');
require_once('../wisetech/utils.php');


class transporte extends table{

    use utils;

    private $last_error;
    private $ACCIONES = array();

    public function __construct($PARAMETROS = null){

        parent::__construct(prefijo . '_pedidos', 'transporte');

        $this->ACCIONES['opcion_transporte'] = 21;

        if(isset($PARAMETROS['operacion'])){

        
        }
    }

    public function option_activas(){

        return mysql::getoptions("SELECT idtransporte as id, nombre as descripcion FROM transporte WHERE estado = 'ACTIVO' ORDER BY nombre ASC");
    }
    

}
?>