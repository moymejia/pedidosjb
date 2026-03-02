
<?php
class DocumentoFel {

    private $datos_emisor;

    private $datos_generales;

    private $datos_receptor;

    private $frases= [];

    private $items = [];
	
	private $adendasX=[];

    private $complementos = [];

    private $impuestos_resumen = [];

    private $totales;

    private $adenda;

     public function getAdenda() {
        return $this->adenda;
    }


    public function setAdenda($adenda) {
         $this->adenda=$adenda;
    }


    public function getDatosEmisor() {
        return $this->datos_emisor;
    }

    public function setDatosEmisor($datosEmisor) {
        $this->datos_emisor = $datosEmisor;
    }

    public function getDatosGenerales() {
        return $this->datos_generales;
    }

    public function setDatosGenerales($datosGenerales) {
        $this->datos_generales = $datosGenerales;
    }

    public function getDatosReceptor() {
        return $this->datos_receptor;
    }

    public function setDatosReceptor($datosReceptor) {
        $this->datos_receptor = $datosReceptor;
    }

    public function getfrases() {
        return $this->frases;
    }

    public function setfrases($frases) {
  
        array_push($this->frases,$frases);
    }

    public function getItems() {
        return $this->items;
    }


    public function setItems($items) {
       array_push($this->items,$items);
    }


//----------------------------
public function setAdendaX($Adenda2) {
       array_push($this->adendasX,$Adenda2);
    }
	
public function getAdendaX(){
	
	return $this->adendasX;
}	
	
	
//----------------------------

     public function getImpuestosResumen() {
        return $this->impuestos_resumen;
    }


    public function setImpuestosResumen($impuestos_resumen) {
       array_push($this->impuestos_resumen,$impuestos_resumen);
    }

    public function getComplementos() {
        return $this->complementos;
    }


    public function setComplementos($complementos) {
        array_push($this->complementos,$complementos);
    }

    public function getTotales() {
        return $this->totales;
    }

    public function setTotales($totales) {
        $this->totales = $totales;
    }
}

