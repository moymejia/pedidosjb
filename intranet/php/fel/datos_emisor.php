<?php
class DatosEmisor {
 
    private $correo_emisor;

    private $codigo_establecimiento;

    private $nit_emisor;

    private $nombre_comercial;

    private $afiliacion_iva;

    private $nombre_emisor;

    private $direccion;

    private $codigo_postal;

    private $municipio;

    private $departamento;

    private $pais;

    public function getCorreoEmisor() {
        return $this->correo_emisor;
    }

    public function setCorreoEmisor($correoEmisor) {
        $this->correo_emisor = $correoEmisor;
    }

    public function getCodigoEstablecimiento() {
        return $this->codigo_establecimiento;
    }

    public function setCodigoEstablecimiento($codigoEstablecimiento) {
        $this->codigo_establecimiento = $codigoEstablecimiento;
    }

    public function getNitEmisor() {
        return $this->nit_emisor;
    }

    public function setNitEmisor($nitEmisor) {
        $this->nit_emisor = $nitEmisor;
    }

    public function getNombreComercial() {
        return $this->nombre_comercial;
    }


    public function setNombreComercial($nombreComercial) {
        $this->nombre_comercial = $nombreComercial;
    }


    public function getAfiliacionIva() {
        return $this->afiliacion_iva;
    }


    public function setAfiliacionIva($afiliacionIva) {
        $this->afiliacion_iva = $afiliacionIva;
    }

    public function getNombreEmisor() {
        return $this->nombre_emisor;
    }


    public function setNombreEmisor($nombreEmisor) {
        $this->nombre_emisor = $nombreEmisor;
    }


    public function getDireccion() {
        return $this->direccion;
    }

    public function setDireccion($direccion) {
        $this->direccion = $direccion;
    }

    public function getCodigoPostal() {
        return $this->codigo_postal;
    }

    public function setCodigoPostal($codigoPostal) {
        $this->codigo_postal = $codigoPostal;
    }

    public function getMunicipio() {
        return $this->municipio;
    }

    public function setMunicipio($municipio) {
        $this->municipio = $municipio;
    }

    public function getDepartamento() {
        return $this->departamento;
    }

    public function setDepartamento($departamento) {
        $this->departamento = $departamento;
    }

    public function getPais() {
        return $this->pais;
    }

    public function setPais($pais) {
        $this->pais = $pais;
    }
}