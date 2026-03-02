<?php
class DatosGenerales {

    private $fecha_hora_emision;

    private $numero_acceso;

    private $codigo_moneda;

    private $tipo;

    private $exportacion;

    public function getFechaHoraEmision() {
        return $this->fecha_hora_emision;
    }

    public function setFechaHoraEmision($fechaHoraEmision) {
        $this->fecha_hora_emision = $fechaHoraEmision;
    }

    public function getNumeroAcceso() {
        return $this->numero_acceso;
    }


    public function setNumeroAcceso($numeroAcceso) {
        $this->numero_acceso = $numeroAcceso;
    }


    public function getCodigoMoneda() {
        return $this->codigo_moneda;
    }

    public function setCodigoMoneda($codigoMoneda) {
        $this->codigo_moneda = $codigoMoneda;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

 
    public function getExportacion() {
        return $this->exportacion;
    }

    public function setExportacion($exportacion) {
        $this->exportacion = $exportacion;
    }
}
