<?php
class Respuesta {

    private $resultado;

    private $descripcion;

    private $cantidad_errores;

    private $errores = [];

    private $xml;

    private $informacion_adicional;

    public function getResultado() {
        return $this->resultado;
    }

    public function setResultado($resultado) {
        $this->resultado = $resultado;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function getCantidadErrores() {
        return $this->cantidad_errores;
    }

    public function setCantidadErrores($cantidadErrores) {
        $this->cantidad_errores = $cantidadErrores;
    }

    public function getErrores() {
        return $this->errores;
    }

    public function setErrores($errores) {
        $this->errores = $errores;
    }

    public function getXml() {
        return $this->xml;
    }

    public function setXml($xml) {
        $this->xml = $xml;
    }

    public function getInformacionAdicional() {
        return $this->informacion_adicional;
    }

    public function setInformacionAdicional($informacionAdicional) {
        $this->informacion_adicional = $informacionAdicional;
    }
}
