<?php
class Frases {

    private $tipo_frase;

    private $codigo_escenario;

    private $resolucion;

    private $fecha;



    public function getTipoFrase() {
        return $this->tipo_frase;
    }

    public function setTipoFrase($tipoFrase) {
        $this->tipo_frase = $tipoFrase;
    }

    public function getCodigoEscenario() {
        return $this->codigo_escenario;
    }

    public function setCodigoEscenario($codigoExcenario) {
        $this->codigo_escenario = $codigoExcenario;
    }

        public function getResolucion() {
        return $this->resolucion;}

    public function setResolucion($resolucion) {
        $this->resolucion = $resolucion;
    }

  public function getFecha() {
        return $this->fecha;}

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }


}
