<?php
class Adendas {
   
    private $adenda = array();

    
    public function getAdenda() {
        echo $this->adenda;
        return 1;
    }
	

    
    public function setAdenda($llave, $valor) {
        $this->adenda[$llave] = $valor;
    }
}